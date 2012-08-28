<?php

require_once dirname(__FILE__) . '/lib/Application.php';
Horde_Registry::appInit('production');

$vars = Horde_Variables::getDefaultVariables();
$form = new Production_Form_TankMeasure($vars);

if ($form->validate($vars)) {
    try {
        $form->execute();
        $notification->push("The tank inventory has been sucessfully updated.");
        Horde::url('tanksheet.php', true)->redirect();
    } catch (Exception $e) {
        $notification->push($e, 'horde.error');
    }
}
 
$page_output->header();
echo Horde::menu();
$notification->notify(array('listeners' => 'status'));
if ($GLOBALS['production_perms']->hasPermission('production:tank sheet', $GLOBALS['registry']->getAuth(), Horde_Perms::EDIT)) {
    $form->renderActive();
} else {
    echo "Not authorized";
}
$page_output->footer();
