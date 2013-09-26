<?php
namespace GoezTest\TreeData\Visitor;

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
        ['id' => 1, 'name' => 'Services', 'parent_id' => null, 'level' => 1],

        // Level 2
        ['id' => 2, 'name' => 'Computer Services', 'parent_id' => 1, 'level' => 2],
        ['id' => 3, 'name' => 'Website Development', 'parent_id' => 1, 'level' => 2],
        ['id' => 4, 'name' => 'Graphic Design', 'parent_id' => 1, 'level' => 2],

        // Level 3
        ['id' => 5, 'name' => 'Computer Repairs', 'parent_id' => 2, 'level' => 3],
        ['id' => 6, 'name' => 'Virus Removal', 'parent_id' => 2, 'level' => 3],
        ['id' => 7, 'name' => 'OS Installation', 'parent_id' => 2, 'level' => 3],
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
            array('Services'),
            array('Contact Us'),
        );
    }

    public function testInsertNodeAsRoot()
    {

    }

}
