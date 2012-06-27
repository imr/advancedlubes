<?php
/**
 * Production test suite.
 *
 * @author     Your Name <you@example.com>
 * @license    http://www.fsf.org/copyleft/gpl.html GPL
 * @category   Horde
 * @package    Production
 * @subpackage UnitTests
 */

/**
 * Define the main method
 */
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Production_AllTests::main');
}

/**
 * Prepare the test setup.
 */
require_once 'Horde/Test/AllTests.php';

/**
 * @package    Production
 * @subpackage UnitTests
 */
class Production_AllTests extends Horde_Test_AllTests
{
}

Production_AllTests::init('Production', __FILE__);

if (PHPUnit_MAIN_METHOD == 'Production_AllTests::main') {
    Production_AllTests::main();
}
