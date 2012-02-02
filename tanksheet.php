<?php

require_once dirname(__FILE__) . '/lib/Application.php';
Horde_Registry::appInit('superbatch');

Horde::addScriptFile('tables.js', 'horde');
Horde::addScriptFile('tooltips.js', 'horde');
require $registry->get('templates', 'horde') . '/common-header.inc';
echo Horde::menu();

$super_driver = $GLOBALS['injector']->getInstance('Superbatch_Factory_Driver')->create();
$tanks = $super_driver->listTanks();
$rowOdd = false;
?>
<table width="100%" cellspacing=0">
  <thead>
    <tr> 
      <th>Tank</th>
      <th>Description</th>
      <th align=right>Capacity</th>
      <th align=right>Previous Measurement</th>
      <th align=right>New Value</th>
    </tr>
  </thead>
  <tbody>
<?php
foreach ($tanks as $tank) {
?>
  <tr class="<?php echo $rowOdd ? 'rowOdd':'rowEven' ?>">
    <td rowspan=2 align=center><?php echo $tank['tanknum'] ?></td>
    <td><?php echo $tank['description'] ?></td>
    <td rowspan=2 class="rightAlign"><?php echo $tank['capacity'] ?></td>
    <td rowspan=2 class="rightAlign"><?php echo $tank['measured_inches'] ?></td>
    <td>&nbsp;</td>
  </tr>
  <tr class="<?php echo $rowOdd ? 'rowOdd':'rowEven' ?>">
    <td><?php echo $tank['compatibility'] ?></td>
    <td>&nbsp;</td>
  </tr>
<?php
    $rowOdd = !$rowOdd;
}
require $registry->get('templates', 'horde') . '/common-footer.inc';
