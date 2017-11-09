<?php
$plugin_id = array('shop', 'salesfeatures');
$app_settings_model = new waAppSettingsModel();
$app_settings_model->set($plugin_id, 'status', '1');
$app_settings_model->set($plugin_id, 'join_on', 'product');