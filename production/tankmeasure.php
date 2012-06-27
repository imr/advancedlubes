<?php

require_once dirname(__FILE__) . '/lib/Application.php';
Horde_Registry::appInit('superbatch');

$vars = Horde_Variables::getDefaultVariables();
$form = new Superbatch_Form_TankMeasure($vars);

if ($form->validate($vars)) {
    try {
        $form->execute();
        $notification->push("The tank inventory has been sucessfully updated.");
        Horde::url('tanksheet.php', true)->redirect();
    } catch (Exception $e) {
        $notification->push($e, 'horde.error');
    }
}
 
require $registry->get('templates', 'horde') . '/common-header.inc';
echo Horde::menu();
$notification->notify(array('listeners' => 'status'));
if ($GLOBALS['superbatch_perms']->hasPermission('superbatch:tank sheet', $GLOBALS['registry']->getAuth(), Horde_Perms::EDIT)) {
    $form->renderActive();
} else {
    echo "Not authorized";
}
require $registry->get('templates', 'horde') . '/common-footer.inc';
