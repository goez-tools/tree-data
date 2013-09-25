<?php
/**
 * Goez
 *
 * @package    Goez
 * @license    MIT License
 * @version    $Id$
 */

namespace Goez\TreeData\Visitor;

use Illuminate\Database\Query\Expression;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent Model Adapter of Node
 *
 * @package    Goez
 * @license    MIT License
 *
 * @property int $lft
 * @property int $rgt
 * @property int $tree
 */
class Eloquent extends Model
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $_object = null;

    /**
     * @param \Illuminate\Database\Eloquent\Model $object
     */
    public function __construct(Model $object)
    {
        $this->_object = $object;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->_object, $method), $arguments);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->_object->__set($name, $value);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_object->__get($name);
    }

}
