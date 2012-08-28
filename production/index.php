<?php

require_once dirname(__FILE__) . '/lib/Application.php';
Horde_Registry::appInit('production');

$vars = Horde_Variables::getDefaultVariables();
$form = new Production_Form_Chart($vars);

if ($form->validate($vars)) {
    require 'tankhistory.php';
} else {
    $page_output->header(array('title' => $title));
    echo Horde::menu();
    $form->renderActive();
    $page_output->footer();
}
