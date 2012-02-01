<?php

require_once dirname(__FILE__) . '/lib/Application.php';
Horde_Registry::appInit('superbatch');

$vars = Horde_Variables::getDefaultVariables();
$form = new Superbatch_Form_TankMeasure($vars);

Horde::addScriptFile('tables.js', 'horde');
Horde::addScriptFile('tooltips.js', 'horde');
require $registry->get('templates', 'horde') . '/common-header.inc';
echo Horde::menu();
if ($form->validate($vars)) {

}      
$form->renderActive();
require $registry->get('templates', 'horde') . '/common-footer.inc';
