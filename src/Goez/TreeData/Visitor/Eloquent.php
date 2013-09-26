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
 * @property int $id
 * @property int $parent_id
 * @property int $level
 * @property int $tree
 */
class Eloquent extends Model
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $_object = null;

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $_children = null;

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
        static $allowMethods = array(
            'children',
            'parent',
        );

        if (in_array($name, $allowMethods)) {
            return call_user_func(array($this, $name));
        }

        return $this->_object->__get($name);
    }

    /**
     * @return \Goez\TreeData\Visitor\Eloquent
     */
    public function asRoot()
    {
        $this->_object->level = 1;
        $this->_object->parent_id = null;
        $this->_object->save();

        $this->_object->tree = $this->_object->{$this->_object->primaryKey};
        $this->_object->save();

        return $this;
    }

    /**
     * @return bool
     */
    public function isRoot()
    {
        return (null === $this->_object->parent_id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function children()
    {
        return $this->_object
            ->hasMany(get_class($this->_object), 'parent_id')
            ->getResults();
    }

    /**
     * @param Model $node
     */
    public function addChild(Model $node)
    {
        $node->parent_id = $this->id;
        $node->level = $this->level + 1;
        $node->tree = $this->tree;
        $node->save();

        $this->children->put($node->id, $node);
    }

    /**
     * @return \Goez\TreeData\Visitor\Eloquent
     */
    public function parent()
    {
        return $this->where('id', $this->parent_id)
            ->first()
            ->tree();
    }
}
