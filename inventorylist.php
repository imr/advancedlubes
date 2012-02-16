<?php

require_once dirname(__FILE__) . '/lib/Application.php';
require_once dirname(__FILE__) . '/dompdf/dompdf_config.inc.php';
Horde_Registry::appInit('superbatch');

Horde::addScriptFile('tables.js', 'horde');

$super_driver = $GLOBALS['injector']->getInstance('Superbatch_Factory_Driver')->create();
$inventories = $super_driver->listInventories();

$html = '<table width="100%" cellspacing=0 class="striped sortable"><thead><tr><th>Time</th><th>User</th>' .
        '<th>Tanks</th></thead><tbody>';
foreach ($inventories as $inventory) {
    $html .= "<tr>" .
             "<td>$inventory[time]</td>" .
             "<td>$inventory[user_id]</td>" .
             "<td>$inventory[tanks]</td></tr>";
}
$html .= '</tbody></table>';
require $registry->get('templates', 'horde') . '/common-header.inc';
echo Horde::menu();
$notification->notify(array('listeners' => 'status'));     
echo $html;
require $registry->get('templates', 'horde') . '/common-footer.inc';