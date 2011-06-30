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
    echo '<br><br>' . Horde::link(Horde::url('flux.php')) . 'Click for fluctuation data</A>';
    require $registry->get('templates', 'horde') . '/common-footer.inc';
}
