<?php

require_once dirname(__FILE__) . '/lib/Application.php';
Horde_Registry::appInit('production');

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
$page_output->addScriptFile('tables.js', 'horde');
$page_output->header();
echo Horde::menu();
$notification->notify(array('listeners' => 'status'));     
echo $html;
$page_output->footer();
