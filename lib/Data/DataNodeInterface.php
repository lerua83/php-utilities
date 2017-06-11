<?php
declare(strict_types=1);
/*
 * This file is part of the <package> package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm\Data;

/**
 * Interface DataNodeInterface
 *
 * @package Asm\Data
 * @author Marc Aschmann <maschmann@gmail.com>
 */
interface DataNodeInterface
{
    /**
     * @param mixed $name
     * @return bool|mixed
     */
    public function get($name);

    /**
     * @param mixed $name
     * @return bool
     */
    public function has($name): bool;
}