<?php

class shopSalesfeaturesPlugin extends shopPlugin {

    public function backendReports() {
        $view = wa()->getView();
        $html = $view->fetch($this->path . '/templates/BackendReports.html');
        return array('menu_li' => $html);
    }

}
