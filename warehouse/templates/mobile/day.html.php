<div data-role="page" id="dayview">
  <div data-role="header">
    <h1><?php echo _("Day")?></h1>
    <a rel="external" href="<?php echo $this->portal ?>"><?php echo _("Portal")?></a>
    <?php if ($this->logout): ?>
    <a href="<?php echo $this->logout ?>" rel="external" data-theme="e" data-icon="delete"><?php echo _("Log out") ?></a>
    <?php endif ?>
    <div class="ui-bar-a warehouseDayHeader">
      <a href="#" class="warehousePrevDay" data-icon="arrow-l" data-iconpos="notext"><?php echo _("Previous")?></a>
      <span class="warehouseDayDate"></span>
      <a href="#" data-icon="arrow-r" data-iconpos="notext" class="warehouseNextDay"><?php echo _("Next")?></a>
    </div>
  </div>
  <div data-role="content" class="ui-body"></div>
</div>
