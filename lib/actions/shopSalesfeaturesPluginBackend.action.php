<?php

class shopSalesfeaturesPluginBackendAction extends waViewAction {

    public function execute() {
        list($start_date, $end_date, $group_by, $request_options) = shopReportsSalesAction::getTimeframeParams();
        $sales_channel = waRequest::request('sales_channel', null, 'string');
        $order_by = waRequest::request('sort', 'profit', 'string');
        $options = array();
        if ($sales_channel) {
            $request_options['sales_channel'] = $sales_channel;
            $options['sales_channel'] = $sales_channel;
        }
        if ($order_by != 'sales') {
            $order_by = 'profit';
        }
        if ($order_by) {
            $request_options['sort'] = $order_by;
        }

        $feature_model = new shopFeatureModel();
        $features = $feature_model->select('*')->fetchAll('id');
        foreach ($features as $index => $feature) {
            if (strpos($feature['type'], 'dimension') !== false) {
                unset($features[$index]);
            }
        }
        unset($feature);

        $settings = wa()->getPlugin('salesfeatures')->getSettings();

        $feature_id = waRequest::request('feature_id', null, 'int');
        if ($feature_id) {
            $request_options['feature_id'] = $feature_id;
            $options['feature_id'] = $feature_id;
            $feature = $features[$feature_id];
            $settings['feature_id'] = $feature_id;
            wa()->getPlugin('salesfeatures')->saveSettings($settings);
        } elseif (!empty($settings['feature_id'])) {
            $request_options['feature_id'] = $settings['feature_id'];
            $options['feature_id'] = $settings['feature_id'];
            $feature = $features[$settings['feature_id']];
        } else {
            $feature = reset($features);
            $request_options['feature_id'] = $feature['id'];
            $options['feature_id'] = $feature['id'];
        }


        $sales_feature_model = new shopSalesfeaturesPluginModel();
        $sales = $sales_feature_model->getSalesFeature($order_by, $start_date, $end_date, $options)->fetchAll();

        $max_sales = 0;
        $max_profit = 0;
        $total_sales = 0;
        $total_profit = 0;
        foreach ($sales as &$sale) {
            $max_sales = max($sale['sales'], $max_sales);
            $max_profit = max($sale['profit'], $max_profit);
            $total_sales += $sale['sales'];
            $total_profit += $sale['profit'];
            $sale['profit_percent'] = 0;
            $sale['sales_percent'] = 0;
        }

        if ($max_sales > 0 || $max_profit > 0) {
            $val = 100 / max($max_profit, $max_sales);
            foreach ($sales as &$sale) {
                $sale['profit_percent'] = round($sale['profit'] * $val);
                $sale['sales_percent'] = round($sale['sales'] * $val);
            }
        }
        unset($sale);

        $feature_values = $feature_model->getFeatureValues($feature);

        $def_cur = wa()->getConfig()->getCurrency();

        $pie_data = array();
        $pie_total = 0;
        foreach ($sales as $sale) {
            $pie_data[] = array(
                'label' => (string) $feature_values[$sale['feature_value_id']],
                'value' => (float) $sale['sales'],
            );
            $pie_total += $sale['sales'];
        }


        if ($pie_total) {
            foreach ($pie_data as &$row) {
                $row['label'] .= ' (' . round($row['value'] * 100 / ifempty($pie_total, 1), 1) . '%)';
            }
            unset($row);
        }



        $this->view->assign(array(
            'total_sales' => $total_sales,
            'total_profit' => $total_profit,
            'features' => $features,
            'feature' => $feature,
            'feature_values' => $feature_values,
            'def_cur' => $def_cur,
            'sales' => $sales,
            'request_options' => $request_options,
            'sales_channels' => shopReportsSalesAction::getSalesChannels(),
            'graph_data' => null,
            'pie_data' => $pie_data,
        ));
    }

}
