<?php

require_once dirname(__FILE__) . '/lib/Application.php';
Horde_Registry::appInit('superbatch');
Horde::addScriptFile('jquery-1.4.2-min.js', 'superbatch');
Horde::addScriptFile('jquery-gqplot.js', 'superbatch');
Horde::addScriptFile('jquery-gqplot-min.js', 'superbatch');

require $registry->get('templates', 'horde') . '/common-header.inc';
echo Horde::menu();
?>
<div id="chart" width=200 height=200>
</div>
<script language="javascript" type="text/javascript">
<?php
echo $js;
?>
</script>
<?php
require $registry->get('templates', 'horde') . '/common-footer.inc';
