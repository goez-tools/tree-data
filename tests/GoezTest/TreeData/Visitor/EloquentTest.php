<?php
namespace GoezTest\TreeData;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as DB;
use Goez\TreeData\Tree;

class Menu extends Eloquent
{
    protected $table = 'nodes';
    public $timestamps = false;
    protected $guarded = array();

    /**
     * @return \Goez\NestedSets\Visitor\Eloquent
     */
    public function tree()
    {
        return Tree::accept($this);
    }
}

class EloquentTest extends \PHPUnit_Framework_TestCase
{
    private static $_tree = [
        // Level 1
        ['id' => 1, 'name' => 'Services', 'parent_id' => null, 'level' => 1, 'tree' => 1],

        // Level 2
        ['id' => 2, 'name' => 'Computer Services', 'parent_id' => 1, 'level' => 2, 'tree' => 1],
        ['id' => 3, 'name' => 'Website Development', 'parent_id' => 1, 'level' => 2, 'tree' => 1],
        ['id' => 4, 'name' => 'Graphic Design', 'parent_id' => 1, 'level' => 2, 'tree' => 1],

        // Level 3
        ['id' => 5, 'name' => 'Computer Repairs', 'parent_id' => 2, 'level' => 3, 'tree' => 1],
        ['id' => 6, 'name' => 'Virus Removal', 'parent_id' => 2, 'level' => 3, 'tree' => 1],
        ['id' => 7, 'name' => 'OS Installation', 'parent_id' => 2, 'level' => 3, 'tree' => 1],
    ];

    protected static function _connectTestDb()
    {
        $db = new DB();
        $db->addConnection(array(
            'driver'   => 'sqlite',
            'database' => __DIR__ . '/../../database/production.sqlite',
            'prefix'   => '',
        ));
        $db->setAsGlobal();
        $db->bootEloquent();
    }

    protected static function _truncateStorage()
    {
        DB::table('nodes')->truncate();
    }

    protected static function _seedTestData()
    {
        DB::table('nodes')->insert(static::$_tree);
    }

    protected static function _getMenuByName($name)
    {
        $node = Menu::where('name', $name)->first();

        return $node ? $node->tree() : $node;
    }

    public static function setUpBeforeClass()
    {
        static::_connectTestDb();
    }

    public function setUp()
    {
        static::_truncateStorage();
        static::_seedTestData();
    }

    public function rootProvider()
    {
        return array(
            array('Home'),
            array('About'),
            array('Contact Us'),
        );
    }

    /**
     * @dataProvider rootProvider
     */
    public function testInsertNodeAsRoot($name)
    {
        $node = (new Menu(array(
            'name' => $name,
        )))->tree();
        $node->asRoot();

        $this->assertTrue($node->isRoot());
    }

    public function testInsertNodeAsChild()
    {
        $node = static::_getMenuByName('Website Development');
        $children = $node->children;
        $this->assertEquals(0, count($children));
        $this->assertInstanceOf('\Illuminate\Database\Eloquent\Collection', $children);

        $node->addChildForcibly(new Menu(array('name' => 'HTML')));
        $node->addChildForcibly(new Menu(array('name' => 'CSS')));
        $node->addChildForcibly(new Menu(array('name' => 'JavaScript')));

        $children = $node->children;
        $this->assertEquals(3, count($children));

        $child1 = $children->find(8);
        $child2 = $children->find(9);
        $child3 = $children->find(10);

        $this->assertEquals(1, $child1->tree);
        $this->assertEquals(1, $child2->tree);
        $this->assertEquals(1, $child3->tree);

        $this->assertEquals(3, $child1->parent_id);
        $this->assertEquals(3, $child2->parent_id);
        $this->assertEquals(3, $child3->parent_id);

        $this->assertEquals(3, $child1->level);
        $this->assertEquals(3, $child2->level);
        $this->assertEquals(3, $child3->level);
    }

    public function testGetParent()
    {
        $node = static::_getMenuByName('Computer Repairs');
        $parent = $node->parent;

        $this->assertEquals('Computer Services', $parent->name);
    }

    public function testGetParents()
    {
        $node = static::_getMenuByName('Computer Repairs');
        $parents = $node->parents;

        $this->assertCount(2, $parents);

        $this->assertEquals('Services', $parents->shift()->name);
        $this->assertEquals('Computer Services', $parents->shift()->name);
    }

    public function testGetRoot()
    {
        $node = static::_getMenuByName('Computer Repairs');
        $root = $node->root;

        $this->assertEquals('Services', $root->name);
    }

    public function testRemoveLeafNode()
    {
        $node = static::_getMenuByName('Computer Repairs');
        $node->delete();
        $node = static::_getMenuByName('Computer Repairs');
        $this->assertNull($node);

    }

    public function testRemoveNodeButKeepChildren()
    {
        $node = static::_getMenuByName('Computer Services');
        $node->delete();

        $child1 = static::_getMenuByName('Computer Repairs');
        $child2 = static::_getMenuByName('Virus Removal');
        $child3 = static::_getMenuByName('OS Installation');

        $this->assertEquals(1, (int) $child1->parent_id);
        $this->assertEquals(1, (int) $child2->parent_id);
        $this->assertEquals(1, (int) $child3->parent_id);

        $this->assertEquals(2, (int) $child1->level);
        $this->assertEquals(2, (int) $child2->level);
        $this->assertEquals(2, (int) $child3->level);
    }

    public function testRemoveNodeWithChildren()
    {
        $node = static::_getMenuByName('Computer Services');
        $node->deleteWithChildren();

        $child1 = static::_getMenuByName('Computer Repairs');
        $child2 = static::_getMenuByName('Virus Removal');
        $child3 = static::_getMenuByName('OS Installation');

        $this->assertNull($child1);
        $this->assertNull($child2);
        $this->assertNull($child3);
    }
}
