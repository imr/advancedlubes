<?php

require_once dirname(__FILE__) . '/lib/Application.php';
Horde_Registry::appInit('superbatch');

$vars = Horde_Variables::getDefaultVariables();
$form = new Superbatch_Form_Chart($vars);

if ($form->validate($vars)) {
    require 'tankhistory.php';
} else {
    require $registry->get('templates', 'horde') . '/common-header.inc';
    echo Horde::menu();
    $form->renderActive();
    require $registry->get('templates', 'horde') . '/common-footer.inc';
}
