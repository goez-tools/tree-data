<?php
/**
 * Goez
 *
 * @package    Goez
 * @license    MIT License
 * @version    $Id$
 */

namespace Goez\TreeData\Visitor;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;

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
 * @property \Illuminate\Database\Eloquent\Collection $children
 * @property \Illuminate\Database\Eloquent\Collection $parents
 * @property \Illuminate\Database\Eloquent\Model $parent
 */
class Eloquent extends Model
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $_parent = null;

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
        static $allowMethods = array(
            'children',
            'parent',
            'parents',
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
     * @param \Illuminate\Database\Eloquent\Model $node
     */
    public function addChild(Model $node)
    {
        $this->children->put($node->id, $node);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $node
     */
    public function addChildForcibly(Model $node)
    {
        $node->parent_id = $this->id;
        $node->level = $this->level + 1;
        $node->tree = $this->tree;
        $node->save();

        $this->addChild($node);
    }

    /**
     * @return \Goez\TreeData\Visitor\Eloquent
     */
    public function parent()
    {
        return $this->_parent
            ?: $this->_parent = $this->where('id', $this->parent_id)
                                    ->first()
                                    ->tree();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function parents()
    {
        $models = $this->getTreeByLevel(array(
            'level' => $this->level - 1,
            'direction' => 'up',
            'levelOrder' => 'desc',
        ));

        $collection = new Collection();
        $current = $this;
        foreach ($models as $model) {
            if ((int) $model->id === (int) $current->parent_id) {
                $collection->put($model->id, $model);
                $current = $model;
            }
        }
        $models = null;
        unset($models);

        return $collection->reverse();
    }

    /**
     * @param array $options
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTreeByLevel($options)
    {
        $options = array_merge(array(
            'level' => null,
            'direction' => null,
            'levelOrder' => 'asc',
        ), $options);

        $query = $this->where('tree', $this->tree);

        if (is_int($options['level'])) {
            switch ($options['direction']) {
                case 'up':
                    $query->where('level', '<=', $options['level']);
                    break;
                case 'down':
                    $query->where('level', '>=', $options['level']);
                    break;
                default:
                    $query->where('level', $options['level']);
                    break;
            }
        }

        $query->orderBy('level', strtolower($options['levelOrder']));
        return $query->get();
    }

    public function delete()
    {
        $children = $this->children;

        if (count($children)) {
            $children->each(function ($child) {
                $child->tree()->levelUp();
            });
        }

        $this->_object->delete();
    }

    public function levelUp()
    {
        $this->_object->level -= 1;
        $this->_object->parent_id = $this->_object->tree()->parent->parent_id;
        $this->_object->save();
    }
}
