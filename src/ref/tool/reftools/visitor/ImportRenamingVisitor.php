<?php

/*
 *
 *  ____  _             _         _____
 * | __ )| |_   _  __ _(_)_ __   |_   _|__  __ _ _ __ ___
 * |  _ \| | | | |/ _` | | '_ \    | |/ _ \/ _` | '_ ` _ \
 * | |_) | | |_| | (_| | | | | |   | |  __/ (_| | | | | | |
 * |____/|_|\__,_|\__, |_|_| |_|   |_|\___|\__,_|_| |_| |_|
 *                |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  Blugin team
 * @link    https://github.com/Blugin
 * @license https://www.gnu.org/licenses/lgpl-3.0 LGPL-3.0 License
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

declare(strict_types=1);

namespace ref\tool\reftools\visitor;

use ref\tool\reftools\renamer\IRenamerHolder;
use ref\tool\reftools\renamer\Renamer;
use ref\tool\reftools\traits\renamer\RenamerHolderTrait;
use PhpParser\ErrorHandler;
use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitor\NameResolver;

use function is_array;
use function ltrim;

class ImportRenamingVisitor extends NameResolver implements IRenamerHolder{
    use RenamerHolderTrait, GetFullyQualifiedTrait;

    public function __construct(Renamer $renamer, ErrorHandler $errorHandler = null, array $options = []){
        parent::__construct($errorHandler, $options);
        $this->setRenamer($renamer);
    }

    public function setRenamer(Renamer $renamer) : void{
        $this->renamer = $renamer;
        $renamer->setIgnorecase();
    }

    /**
     * @param Node[] $nodes
     *
     * @return Node[]|null
     **/
    public function beforeTraverse(array $nodes) : ?array{
        $this->nameContext->startNamespace();
        $this->getRenamer()->init();
        $this->registerUses($nodes);
        return $nodes;
    }

    public function enterNode(Node $node) : ?Node{
        if($node instanceof FunctionLike){
            foreach($node->getParams() as $param){
                if($param->default instanceof ConstFetch && $param->default->name->parts[0] === "null"){
                    $param->default->setAttribute("byParams", $param);
                }
            }
        }
        if($node instanceof ConstFetch && $node->hasAttribute("byParams")){
            return $node;
        }
        return parent::enterNode($node);
    }

    /** @return int|Node|Node[]|null */
    public function leaveNode(Node $node){
        if($node instanceof Use_ || $node instanceof GroupUse){
            foreach($node->uses as $use){
                $newName = $this->renamer->rename(new Identifier($this->getFullyQualifiedString($use, $node)));
                if($newName instanceof Identifier){
                    $use->alias = $newName;
                }
            }
            return $node;
        }
        return null;
    }

    protected function resolveName(Name $name, int $type) : Name{
        $result = parent::resolveName($name, $type);
        if(!$this->replaceNodes)
            return $result;

        $newName = $this->renamer->rename(new Identifier(ltrim($result->toCodeString(), "\\")));
        if($newName instanceof Identifier)
            return new Name($newName->name, $result->getAttributes());
        return $result;
    }

    /** @param Node[] $nodes * */
    private function registerUses(array $nodes) : void{
        foreach($nodes as $node){
            if($node instanceof Use_ || $node instanceof GroupUse){
                foreach($node->uses as $use){
                    $this->renamer->generate(new Identifier($this->getFullyQualifiedString($use, $node)));
                }
            }

            //Child node with recursion processing
            if(isset($node->stmts) && is_array($node->stmts)){
                $this->registerUses($node->stmts);
            }
        }
    }
}