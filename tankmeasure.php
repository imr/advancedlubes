<?php

require_once dirname(__FILE__) . '/lib/Application.php';
Horde_Registry::appInit('superbatch');

$vars = Horde_Variables::getDefaultVariables();
$form = new Superbatch_Form_TankMeasure($vars);

if ($form->validate($vars)) {
    try {
        $form->execute();
        $notification->push("The tank inventory has been sucessfully updated.");
    } catch (Exception $e) {
        $notification->push($e, 'horde.error');
    }
}
 
require $registry->get('templates', 'horde') . '/common-header.inc';
echo Horde::menu();
$notification->notify(array('listeners' => 'status'));     
$form->renderActive();
require $registry->get('templates', 'horde') . '/common-footer.inc';
