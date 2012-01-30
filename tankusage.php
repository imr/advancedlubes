<?php

require_once dirname(__FILE__) . '/lib/Application.php';
Horde_Registry::appInit('superbatch');

$vars = Horde_Variables::getDefaultVariables();
$form = new Superbatch_Form_TankUsage($vars);

Horde::addScriptFile('tables.js', 'horde');
Horde::addScriptFile('tooltips.js', 'horde');
require $registry->get('templates', 'horde') . '/common-header.inc';
echo Horde::menu();
if ($form->validate($vars)) {
       
    $week_start = $vars->get('week_start');
    $week_end = $vars->get('week_end');
    $super_driver = $GLOBALS['injector']->getInstance('Superbatch_Factory_Driver')->create();
    $weeks = $super_driver->listTankWeeks($week_start, $week_end);
    $week_array = array();
    $week_total = 0;
    foreach ($weeks as $week) {
        $week_array[$week_total] = $week['week'];
        $week_total++;
    }
    $week_array_top = $week_total -1;
    switch ($vars->get('display_type')) {
        case 0: // All tanks
            break;
        case 1: // Resource
            $typetoget = 'Resource';
            break;
        case 2: // Finish
            $typetoget = 'Finish';
            break;
        case 3: // Selection
            $tankstoget = $vars->get('tanks');
            break;
    }
    $results = $super_driver->listTanks($typetoget, $tankstoget);
    $row_odd = false; // use this for table striping
?>
<h3>Tank Usage for <?php echo $week_start ?> to <?php echo $week_end ?></h3>
<table width="100%" cellspacing=0">
  <thead>
    <tr> 
      <th>Tank</th>
      <th>Description</th>
      <th>Volume</th>
      <th>Average</th>
      <th>Total</th>
<?php
    for ($i = $week_array_top; $i> -1; $i--) {
?>
      <th><?php echo $week_array[$i] ?></th>
<?php
    }
?>
    </tr>
  </thead>
  <tbody>
<?php

        foreach ($results as $result) { // Layout each tank row of two rows
            $row_results = $super_driver->getTankUsagebyWeek($result['_kp_tankid'],$week_start,$week_end);
            $count_week = 0;
	    $top_row = '';
            $bottom_row = '';
            $increase_total = 0;
            $decrease_total = 0;
?>
    <tr class="<?php echo $row_odd ? 'rowOdd' : 'rowEven' ?>">
      <td rowspan=2><?php echo $result['tanknum'] ?></td>
      <td><?php echo $result['description'] ?></td>
      <td rowspan=2 class="rightAlign"><?php echo $result['volume'] ?></td>
<?php
            $wa = 0;
            foreach ($row_results as $row_data) {
                $this_week = $row_data['week'];
                while ($week_array[$week_array_top - $wa] > $this_week) {
                    $top_row .= '<td class="rightAlign">0</td>';
                    $bottom_row .= '<td class="rightAlign">0</td>';
                    $wa++;
                }
 
                $tool_data = $super_driver->getTankUsageforWeek($result['_kp_tankid'], $this_week);
                $tooltip = '';
                foreach ($tool_data as $tool_row) {
                    $tooltip .= $tool_row['date'] . "\t" . $tool_row['increase'] . "\t" . $tool_row['decrease'] . "\n";
                }
                $top_row .= '<td class="rightAlign tooltip" title="' . htmlspecialchars($tooltip) . '">' . (int) $row_data['increase'] . '</td>';
                $bottom_row .= '<td class="rightAlign tooltip" title="' . $tooltip .'">' . (int) $row_data['decrease'] . '</td>';
                $increase_total += $row_data['increase'];
                $decrease_total += $row_data['decrease'];
                $count_week++;
                $wa++;
            }
            for ($j = $wa; $j < $week_total; $j++) {
                $top_row .= '<td>&nbsp;</td>';
                $bottom_row .= '<td>&nbsp;</td>';
            }
            echo '<td class="rightAlign">' . (int) ($increase_total / $count_week) . '</td><td class="rightAlign">' . (int) $increase_total . '</td>' . $top_row;
?>
    </tr>
    <tr class="<?php echo $row_odd ? 'rowOdd' : 'rowEven' ?>">
<?php
            echo '<td>' . $result['compatibility'] . '</td><td class="rightAlign">' . (int) ($decrease_total / $count_week) . '</td><td class="rightAlign">' . (int) $decrease_total . '</td>' . $bottom_row;
?>
    </tr>
<?php
            $row_odd = !$row_odd;
        }
}
?>
  </tbody>
</table>
</br>
<?php
$form->renderActive();
require $registry->get('templates', 'horde') . '/common-footer.inc';
