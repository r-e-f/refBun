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

namespace ref\bundle\traits\renamer;

use ref\bundle\renamer\Renamer;
use PhpParser\NodeVisitorAbstract;

/**
 * This trait override most methods in the {@link NodeVisitorAbstract} abstract class for implements {@link IRenamerHolder} interface.
 */
trait RenamerHolderTrait{
    protected Renamer $renamer;

    public function __construct(Renamer $renamer){
        $this->setRenamer($renamer);
    }

    public function getRenamer() : Renamer{
        return $this->renamer;
    }

    public function setRenamer(Renamer $renamer) : void{
        $this->renamer = $renamer;
    }
}