<?php
/**
 * Goez
 *
 * @package    Goez
 * @license    MIT License
 * @version    $Id$
 */

namespace Goez\TreeData;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Eloquent Model Adapter of Tree
 *
 * @package    Goez
 * @license    MIT License
 *
 * @property int $lft
 * @property int $rgt
 * @property int $tree
 */
abstract class Tree
{
    /**
     * @param $object
     * @return Visitor\Eloquent
     */
    public static function accept($object)
    {
        if ($object instanceof Eloquent) {
            return new Visitor\Eloquent($object);
        }
    }
}
