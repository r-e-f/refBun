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
 *  ( . .) ♥️
 *  c(")(")
 */

declare(strict_types=1);

namespace ref\tool\reftools\renamer;

use PhpParser\Node;

use function array_merge;
use function count;
use function floor;
use function range;

class SerialRenamer extends Renamer{
    /** @var string[] */
    private array $firstChars, $otherChars;

    public function __construct(){
        $this->generateChars();
    }

    public function generate(Node $node, string $property = "name") : void{
        if($this->getName($node->$property) !== null)
            return;

        $variableCount = count($this->getNameTable());
        $firstCount = count($this->firstChars);
        $newName = $this->firstChars[$variableCount % $firstCount];
        if($variableCount){
            if(($sub = floor($variableCount / $firstCount) - 1) > -1){
                $newName .= $this->otherChars[$sub];
            }
        }
        $this->setName($node->$property, $newName);
    }

    public function setIgnorecase(bool $value = true) : void{
        parent::setIgnorecase($value);
        $this->generateChars();
    }

    private function generateChars() : void{
        $this->firstChars = array_merge(["_"], range("a", "z"));
        if(!$this->isIgnorecase()){
            $this->firstChars = array_merge($this->firstChars, range("A", "Z"));
        }
        $this->otherChars = array_merge(range("0", "9"), $this->firstChars);
    }
}