<?php

/* Determine the base directories. */
if (!defined('DRUM_BASE')) {
    define('DRUM_BASE', dirname(__FILE__) . '/..');
}

if (!defined('HORDE_BASE')) {
    /* If Horde does not live directly under the app directory, the HORDE_BASE
     * constant should be defined in config/horde.local.php. */
    if (file_exists(DRUM_BASE . '/config/horde.local.php')) {
        include DRUM_BASE . '/config/horde.local.php';
    } else {
        define('HORDE_BASE', DRUM_BASE . '/..');
    }
}

/* Load the Horde Framework core (needed to autoload
 * Horde_Registry_Application::). */
require_once HORDE_BASE . '/lib/core.php';

class Drum_Application extends Horde_Registry_Application
{
    public $version = '0.1';

    protected function _init()
    {
        $GLOBALS['injector']->bindFactory('Drum_Driver', 'Drum_Factory_Driver', 'create');
    }

    /**
     */
    public function menu($menu)
    {
    }

}
