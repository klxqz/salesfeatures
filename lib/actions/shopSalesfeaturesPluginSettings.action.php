<?php

class shopSalesfeaturesPluginSettingsAction extends waViewAction {

    public function execute() {
        $this->view->assign(array(
            'plugin' => wa()->getPlugin('salesfeatures'),
        ));
    }

}
