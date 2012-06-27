<?php

require_once dirname(__FILE__) . '/lib/Application.php';
Horde_Registry::appInit('warehouse');

/* Determine View */
$mode = $session->get('horde', 'mode');

/* Load mobile? */
if ($mode == 'smartmobile' || $mode == 'mobile') {
    include WAREHOUSE_BASE . '/mobile.php';
    exit;
}

/* Traditional? */
if (!Warehouse::showAjaxView()) {
    if ($mode == 'dynamic' || ($mode == 'auto' && $prefs->getValue('dynamic_view'))) {
        $notification->push(_("Your browser is too old to display the dynamic mode. Using traditional mode instead."), 'horde.warning');
        $session->set('horde', 'mode', 'traditional');
    }
    include WAREHOUSE_BASE . '/' . $prefs->getValue('defaultview') . '.php';
    exit;
}

