<?php

require_once dirname(__FILE__) . '/lib/Application.php';
Horde_Registry::appInit('superbatch');

$id = $vars->get('tank');
$all = $vars->get('tankall');
$start_array = $vars->get('time_start');
$end_array = $vars->get('time_end');
$start_year = empty($start_array['year']) ? null : $start_array['year'];
$start_month = empty($start_array['month']) ? null : $start_array['month'];
$start_day = empty($start_array['day']) ? null : $start_array['day'];
$start_time = (int)strtotime("$start_month/$start_day/$start_year");
$end_year = empty($end_array['year']) ? null : $end_array['year'];
$end_month = empty($end_array['month']) ? null : $end_array['month'];
$end_day = empty($end_array['day']) ? null: $end_array['day'];
$end_time = (int)strtotime("$end_month/$end_day/$end_year");

$both = false;
switch ($vars->get('data')) {
    case 'both':
        $both = true;
        $datacolumn = 'temperature';
        $datacolumn2 = 'volume';
        $charttitle = 'Temperature and Volume in';
        break;
    case 'temp':
        $datacolumn = 'temperature';
        $charttitle = 'Temperature in ';
        break;
    case 'vol':
        $datacolumn = 'volume';
        $charttitle = 'Volume in ';
        break;
    case 'meas':
        $measure = true;
        $datacolumn = 'volume';
        $charttitle = 'Measured volume in ';
        break;
    case 'twovol':
        $twovolume = true;
        $datacolumn = 'measured';
        $datacolumn2 = 'sensor';
        $charttitle = 'Measured and sensor volumes in ';
        break;
}

$super_driver = $GLOBALS['injector']->getInstance('Superbatch_Factory_Driver')->create();
if ($all) {
    $id = array();
    $charttitle .= ' all tanks';
} else {
    foreach ($id as $name) {
        $names .= $super_driver->getTankNamefromId($name) . ', ';
    }
    $charttitle .= ' tanks ' . substr($names, 0, strlen($names) - 2);
}
 
if ($measure) {
    $data = $super_driver->getTanksHistoryMeasurebyIds($id, $start_time, $end_time); 
} elseif ($twovolume) {
    $data = $super_driver->getTanksHistoriesbyIds($id, $start_time, $end_time);
} else {
    $data = $super_driver->getTanksHistorybyIds($id, $start_time, $end_time);
}
$prevtank = $data[0]['tanknum'];
$js = '[[';
if ($both || $twovolume) {
    $labels = "['" . $data[0]['tanknum'] . " $datacolumn','" . $data[0]['tanknum'] . " $datacolumn2',";
    foreach ($data as $point) {
        if ($point['tanknum'] <> $prevtank) {
            $js = substr($js, 0, strlen($js) -1) . '],[';
            $js .= substr($jssecond, 0, strlen($jssecond) -1) . '],[';
            $jssecond = '';
            $labels .= "'" . $point['tanknum'] . " $datacolumn','" . $point['tanknum'] . " $datacolumn2',";
        }
        $js .= '["' . $point['timeunix'] . '",' . $point[$datacolumn] . '],';
        $jssecond .= '["' . $point['timeunix'] . '",' . $point[$datacolumn2] . '],';
        $prevtank = $point['tanknum'];
    }
    $js = substr($js, 0, strlen($js) -1) . '],[';
    $js .= $jssecond;
} else {
    $labels = "['" . $data[0]['tanknum'] . "',";
    foreach ($data as $point) {
        if ($point['tanknum'] <> $prevtank) {
            $js = substr($js, 0, strlen($js) -1) . '],[';
            $labels .= "'" . $point['tanknum'] . "',";
        }
        $js .= '["' . $point['timeunix'] . '",' . $point["$datacolumn"] . '],';
        $prevtank = $point['tanknum'];
    }
}
$js = substr($js, 0, strlen($js) -1);
$js .= "]];";
$labels = substr($labels, 0, strlen($labels) - 1);
$labels .= "];";

if (4 > strlen($js)) {
    $notify = "No data for chart";
}
?>
<!DOCTYPE html>

<html lang="en">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
 <title>Production Charts</title>
 <!--[if IE]><script language="javascript" type="text/javascript" src="../excanvas.min.js"></script><![endif]-->

 <link rel="stylesheet" type="text/css" href="jquery.jqplot.css" />

 <!-- BEGIN: load jquery -->
 <script language="javascript" type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
 <!-- END: load jquery -->

 <!-- BEGIN: load jqplot -->
 <script language="javascript" type="text/javascript" src="js/jquery.jqplot.js"></script>
 <script language="javascript" type="text/javascript" src="js/plugins/jqplot.dateAxisRenderer.js"></script>
 <script language="javascript" type="text/javascript" src="js/plugins/jqplot.highlighter.js"></script>
 <script language="javascript" type="text/javascript" src="js/plugins/jqplot.cursor.js"></script>

 <!-- END: load jqplot -->
 <style type="text/css" media="screen">
   .jqplot-axis {
     font-size: 0.85em;
   }
 </style>
 
 <script type="text/javascript" language="javascript">

 $(document).ready(function(){
      $.jqplot.config.enablePlugins = true;
<?php echo 'var chartdata = ' . $js ?>

<?php echo ($single && !$both) ? '' : 'var legendLabels = ' . $labels; ?>

  plot = $.jqplot('chart', chartdata, {
     title: '<?php echo $charttitle ?>',
<?php echo ($single && !$both) ? '' : "    legend:{show:true, labels: legendLabels, rendererOptions:{placement: 'outside'}},";
      echo ($both) ? "    series:[{}, {yaxis:'y2axis'}],axesDefaults:{useSeriesColor: true}," : '';?>

     axes: {
         xaxis:{
             renderer:$.jqplot.DateAxisRenderer,
             tickOptions:{formatString:"%m/%d %H:%M"}
         }
     },
     cursor:{zoom:true, showTooltip:false, clickReset:true}
  });
 });
 </script>
 </head>
 <body>
<?php include "nav.inc"; ?>
   <div id="chart" style="margin-top:20px; margin-left:20px; width:1000px; height:500px;"></div>
   <div style="padding-top:20px"><button value="reset" type="button" onclick="$.jqplot.Cursor.resetZoom(plot)">Zoom Out</button></div>
 </body>
</html>
