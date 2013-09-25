<?php
namespace GoezTest\TreeData\Visitor;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as DB;
use Goez\NestedSets\NestedSets;

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
        ['id' => 1, 'name' => 'Home', 'lft' => 1, 'rgt' => 2, 'tree' => 1],
        ['id' => 2, 'name' => 'About', 'lft' => 1, 'rgt' => 2, 'tree' => 2],
        ['id' => 3, 'name' => 'Services', 'lft' => 1, 'rgt' => 14, 'tree' => 3],
        ['id' => 9, 'name' => 'Contact Us', 'lft' => 1, 'rgt' => 2, 'tree' => 4],

        // Level 2
        ['id' => 4, 'name' => 'Computer Services', 'lft' => 2, 'rgt' => 9, 'tree' => 3],
        ['id' => 7, 'name' => 'Website Development', 'lft' => 10, 'rgt' => 11, 'tree' => 3],
        ['id' => 8, 'name' => 'Graphic Design', 'lft' => 12, 'rgt' => 13, 'tree' => 3],

        // Level 3
        ['id' => 5, 'name' => 'Computer Repairs', 'lft' => 3, 'rgt' => 4, 'tree' => 3],
        ['id' => 6, 'name' => 'Virus Removal', 'lft' => 5, 'rgt' => 6, 'tree' => 3],
        ['id' => 10, 'name' => 'OS Installation', 'lft' => 7, 'rgt' => 8, 'tree' => 3],
    ];

    public static function setUpBeforeClass()
    {
        static::_connectTestDb();
        static::_truncateStorage();
    }

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

    public function rootProvider()
    {
        return array(
            array('Home'),
            array('About'),
            array('Services'),
            array('Contact Us'),
        );
    }

}
