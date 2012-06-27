<?php
/**
 * Warehouse test suite.
 *
 * @author     Your Name <you@example.com>
 * @license    http://www.horde.org/licenses/gpl GPL
 * @category   Horde
 * @package    Warehouse
 * @subpackage UnitTests
 */

/**
 * Define the main method
 */
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Warehouse_AllTests::main');
}

/**
 * Prepare the test setup.
 */
require_once 'Horde/Test/AllTests.php';

/**
 * @package    Warehouse
 * @subpackage UnitTests
 */
class Warehouse_AllTests extends Horde_Test_AllTests
{
}

Warehouse_AllTests::init('Warehouse', __FILE__);

if (PHPUnit_MAIN_METHOD == 'Warehouse_AllTests::main') {
    Warehouse_AllTests::main();
}
