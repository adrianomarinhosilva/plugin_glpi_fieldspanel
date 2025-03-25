<?php
include('../../../inc/includes.php');
Html::header("FieldsPanel", $_SERVER['PHP_SELF'], "plugins", "PluginFieldspanelConfig");
Html::redirect("config.form.php");
Html::footer();