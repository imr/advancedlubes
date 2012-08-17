<?php
/**
 * Lab application API.
 *
 * This file defines Horde's core API interface. Other core Horde libraries
 * can interact with Lab through this API.
 *
 * Copyright 2010-2012 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @package Lab
 */

/* Determine the base directories. */
if (!defined('LAB_BASE')) {
    define('LAB_BASE', __DIR__ . '/..');
}

if (!defined('HORDE_BASE')) {
    /* If Horde does not live directly under the app directory, the HORDE_BASE
     * constant should be defined in config/horde.local.php. */
    if (file_exists(LAB_BASE . '/config/horde.local.php')) {
        include LAB_BASE . '/config/horde.local.php';
    } else {
        define('HORDE_BASE', LAB_BASE . '/..');
    }
}

/* Load the Horde Framework core (needed to autoload
 * Horde_Registry_Application::). */
require_once HORDE_BASE . '/lib/core.php';

class Lab_Application extends Horde_Registry_Application
{
    /**
     */
    public $version = 'H5 (0.1)';

    /**
     */
    protected function _init()
    {
        $GLOBALS['injector']->bindFactory('Lab_Driver', 'Lab_Factory_Driver', 'create');
    }

    /**
     */
    public function menu($menu)
    {
        $menu->add(Horde::url('list.php'), _("List"), 'user.png');
    }
}
