<?php
/**
 * Lab test suite.
 *
 * @author     Ian Roth <iron_hat@hotmail.com>
 * @package    Lab
 * @subpackage UnitTests
 */

/**
 * Define the main method
 */
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Lab_AllTests::main');
}

/**
 * Prepare the test setup.
 */
require_once 'Horde/Test/AllTests.php';

/**
 * @package    Lab
 * @subpackage UnitTests
 */
class Lab_AllTests extends Horde_Test_AllTests
{
}

Lab_AllTests::init('Lab', __FILE__);

if (PHPUnit_MAIN_METHOD == 'Lab_AllTests::main') {
    Lab_AllTests::main();
}
