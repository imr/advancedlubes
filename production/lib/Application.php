<?php

/* Determine the base directories. */
if (!defined('SUPERBATCH_BASE')) {
    define('SUPERBATCH_BASE', dirname(__FILE__) . '/..');
}

if (!defined('HORDE_BASE')) {
    /* If Horde does not live directly under the app directory, the HORDE_BASE
     * constant should be defined in config/horde.local.php. */
    if (file_exists(SUPERBATCH_BASE . '/config/horde.local.php')) {
        include SUPERBATCH_BASE . '/config/horde.local.php';
    } else {
        define('HORDE_BASE', SUPERBATCH_BASE . '/..');
    }
}

/* Load the Horde Framework core (needed to autoload
 * Horde_Registry_Application::). */
require_once HORDE_BASE . '/lib/core.php';

class Superbatch_Application extends Horde_Registry_Application
{
    public $version = '0.1';

    protected function _init()
    {
        $GLOBALS['superbatch_perms'] = $GLOBALS['injector']->getInstance('Horde_Perms');
        $GLOBALS['injector']->bindFactory('Superbatch_Driver', 'Superbatch_Factory_Driver', 'create');
    }

    public function perms()
    {
        $perms = array(
            'tank sheet' => array(
                'title' => _('Tank Sheet')
            )
        );

        return $perms;
    }

    public function menu($menu)
    {
        $menu->add(Horde::url('index.php'), _("_Tank Charts"), 'help_index.png', null, null, null);
        $menu->add(Horde::url('flux.php'), _("_Fluctuation"), 'calendar.png', null, null, null);
        $menu->add(Horde::url('materialusage.php'), _("_Material Usage"), '', null, null, null);
        $menu->add(Horde::url('tankusage.php'), _("_Tank Usage"), 'info.png', null, null, null);
        $menu->add(Horde::url('tanksheet.php'), _("_Tank Sheet"), 'layout.png', null, null, null);
    }
}
