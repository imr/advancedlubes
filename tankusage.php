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
    $week_total = $week_end - $week_start + 1;
    $week_array = range($week_end, $week_start);
    $super_driver = $GLOBALS['injector']->getInstance('Superbatch_Factory_Driver')->create();
    if ($vars->get('display_all') == false) { // Only want resource tanks
        $tankstoget = 'Resource';
    }
    $results = $super_driver->listTanks($tankstoget);
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
<?php
    for ($i = $week_end; $i>= $week_start; $i--) {
?>
      <th><?php echo $i ?></th>
<?php
    }
?>
    </tr>
  </thead>
  <tbody>
<?php

        foreach ($results as $result) {
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
                while ($week_array[$wa] > $row_data['week']) {
                    $top_row .= '<td class="rightAlign">0</td>';
                    $bottom_row .= '<td class="rightAlign">0</td>';
                    $wa++;
                }
                    
                $top_row .= '<td class="rightAlign tooltip" title="stuff to tooltip">' . (int) $row_data['increase'] . '</td>';
                $bottom_row .= '<td class="rightAlign">' . (int) $row_data['decrease'] . '</td>';
                $increase_total += $row_data['increase'];
                $decrease_total += $row_data['decrease'];
                $wa++;
            }
            for ($j = $wa; $j < $week_total; $j++) {
                $top_row .= '<td>&nbsp;</td>';
                $bottom_row .= '<td>&nbsp;</td>';
            }
            echo '<td class="rightAlign">' . (int) ($increase_total / $count_week) . '</td>' . $top_row;
?>
    </tr>
    <tr class="<?php echo $row_odd ? 'rowOdd' : 'rowEven' ?>">
<?php
            echo '<td>' . $result['compatibility'] . '</td><td class="rightAlign">' . (int) ($decrease_total / $count_week) . '</td>' . $bottom_row;
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
