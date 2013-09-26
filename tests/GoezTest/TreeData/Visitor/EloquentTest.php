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
        $node = Menu::where('name', 'Website Development')->first()->tree();
        $children = $node->children;
        $this->assertEquals(0, count($children));
        $this->assertInstanceOf('\Illuminate\Database\Eloquent\Collection', $children);

        $node->addChild(new Menu(array('name' => 'HTML')));
        $node->addChild(new Menu(array('name' => 'CSS')));
        $node->addChild(new Menu(array('name' => 'JavaScript')));

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
        $node = Menu::where('name', 'Computer Repairs')->first()->tree();
        $parent = $node->parent;

        $this->assertEquals('Computer Services', $parent->name);
    }

    // 1. 要可以從目前節點去呈現 breadcrumbs 。 (往上找 N 層直到 root)
    // 2. 目前節點可以用一個 query 找出 path 。 (往上找 N 層直到 root)
    // 3. 可以從目前節點往下找出一層屬於該節點的子節點。 (顯示子樹)
    // 4. 新增節點時， query 數及影響的 row 應儘可能地少，並計算出該節點 level 。
    // 5. 刪除節點時，若有子節點的話，子節點應附加在上一層節點。 (調整樹的平衡)
    // 6. 可找出任意 level 的節點群。 (撈第 N 層的所有節點)

}
