<?php

require_once dirname(__FILE__) . '/lib/Application.php';
Horde_Registry::appInit('production');

$vars = Horde_Variables::getDefaultVariables();
$form = new Production_Form_MaterialUsage($vars);

Horde::addScriptFile('tables.js', 'horde');
Horde::addScriptFile('tooltips.js', 'horde');
require $registry->get('templates', 'horde') . '/common-header.inc';
echo Horde::menu();
if ($form->validate($vars)) {
       
    $week_start = $vars->get('week_start');
    $week_end = $vars->get('week_end');
    $week_total = $week_end - $week_start + 1;
    $week_array = range($week_end, $week_start);
    $super_driver = $GLOBALS['injector']->getInstance('Production_Factory_Driver')->create();
    $results = $super_driver->listMaterials();
    $row_odd = false;
?>
<h3>Material Usage for <?php echo $week_start ?> to <?php echo $week_end ?></h3>
<table width="100%" cellspacing=0">
  <thead>
    <tr> 
      <th>Material</th>
      <th>Description</th>
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
            $row_results = $super_driver->getMaterialUsagebyWeek($result['materialid'],$week_start,$week_end);
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
                while ($week_array[$wa] > $this_week) {
                    $top_row .= '<td class="rightAlign">0</td>';
                    $bottom_row .= '<td class="rightAlign">0</td>';
                    $wa++;
                }
 
                $tool_data = $super_driver->getTankUsageforWeek($result['_kp_tankid'], $this_week);
                $tooltip = '';
                foreach ($tool_data as $tool_row) {
                    $tooltip .= $tool_row['date'] . ' ' . $tool_row['increase'] . ' ' . $tool_row['decrease'] . '<br>';
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
