<?php
/**
 * Superbatch test suite.
 *
 * @author     Your Name <you@example.com>
 * @license    http://www.fsf.org/copyleft/gpl.html GPL
 * @category   Horde
 * @package    Superbatch
 * @subpackage UnitTests
 */

/**
 * Define the main method
 */
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Superbatch_AllTests::main');
}

/**
 * Prepare the test setup.
 */
require_once 'Horde/Test/AllTests.php';

/**
 * @package    Superbatch
 * @subpackage UnitTests
 */
class Superbatch_AllTests extends Horde_Test_AllTests
{
}

Superbatch_AllTests::init('Superbatch', __FILE__);

if (PHPUnit_MAIN_METHOD == 'Superbatch_AllTests::main') {
    Superbatch_AllTests::main();
}
