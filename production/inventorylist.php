<?php

require_once dirname(__FILE__) . '/lib/Application.php';
Horde_Registry::appInit('production');

Horde::addScriptFile('tables.js', 'horde');

$super_driver = $GLOBALS['injector']->getInstance('Production_Factory_Driver')->create();
$inventories = $super_driver->listNotes();

$html = '<table width="50%" cellspacing=0 class="striped sortable"><thead><tr><th>Time</th><th>User</th>' .
        '</thead><tbody>';
foreach ($inventories as $inventory) {
    $html .= "<tr>" .
             "<td>$inventory[date]</td>" .
             "<td>$inventory[user_id]</td></tr>";
}
$html .= '</tbody></table>';
require $registry->get('templates', 'horde') . '/common-header.inc';
echo Horde::menu();
$notification->notify(array('listeners' => 'status'));     
echo $html;
require $registry->get('templates', 'horde') . '/common-footer.inc';
