<?php
/**
 * Drum test suite.
 *
 * @author     Your Name <you@example.com>
 * @license    http://www.fsf.org/copyleft/gpl.html GPL
 * @category   Horde
 * @package    Drum
 * @subpackage UnitTests
 */

/**
 * Define the main method
 */
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Drum_AllTests::main');
}

/**
 * Prepare the test setup.
 */
require_once 'Horde/Test/AllTests.php';

/**
 * @package    Drum
 * @subpackage UnitTests
 */
class Drum_AllTests extends Horde_Test_AllTests
{
}

Drum_AllTests::init('Drum', __FILE__);

if (PHPUnit_MAIN_METHOD == 'Drum_AllTests::main') {
    Drum_AllTests::main();
}
