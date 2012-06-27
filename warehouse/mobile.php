<?php
   
require_once dirname(__FILE__) . '/lib/Application.php';
Horde_Registry::appInit('warehouse');

$title = _("My Calendar");

$view = new Horde_View(array('templatePath' => WAREHOUSE_TEMPLATES . '/mobile'));
$view->today = new Horde_Date($_SERVER['REQUEST_TIME']);
$view->registry = $registry;
$view->portal = Horde::getServiceLink('portal', 'horde')->setRaw(false);
$view->logout = Horde::getServiceLink('logout')->setRaw(false);

require $registry->get('templates', 'horde') . '/common-header-mobile.inc';

echo $view->render('head');
echo $view->render('day');
echo $view->render('event');
echo $view->render('month');
echo $view->render('summary');
echo $view->render('notice');
$registry->get('templates', 'horde') . '/common-footer-mobile.inc';
