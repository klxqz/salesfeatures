<?php

class shopSalesfeaturesPluginModel extends waModel {

    public function getSalesFeature($order = 'sales', $start_date = null, $end_date = null, $options = array()) {
        $paid_date_sql = shopOrderModel::getDateSql('`so`.`paid_date`', $start_date, $end_date);

        if ($order !== 'sales') {
            $order = 'profit';
        }


        $storefront_join = '';
        $storefront_where = '';
        if (!empty($options['storefront'])) {
            $storefront_join = "JOIN shop_order_params AS op2
                                    ON op2.order_id=so.id
                                        AND op2.name='storefront'";
            $storefront_where = "AND op2.value='" . $this->escape($options['storefront']) . "'";
        }
        if (!empty($options['sales_channel'])) {
            $storefront_join .= " JOIN shop_order_params AS opst2
                                    ON opst2.order_id=so.id
                                        AND opst2.name='sales_channel' ";
            $storefront_where .= " AND opst2.value='" . $this->escape($options['sales_channel']) . "' ";
        }

        $sales_subtotal = '(`soi`.`price` * `so`.`rate` * `soi`.`quantity`)';
        $order_subtotal = '(`so`.`total` + `so`.`discount` - `so`.`tax` - `so`.`shipping`)';
        $discount = "IF({$order_subtotal} <= 0, 0, `soi`.`price` * `so`.`rate` * `soi`.`quantity` * `so`.`discount` / {$order_subtotal})";
        $purchase = '(IF(`soi`.`purchase_price` > 0, `soi`.`purchase_price` * `so`.`rate`, `sps`.`purchase_price` * `sc`.`rate`) * `soi`.`quantity`)';

        if (wa()->getPlugin('salesfeatures')->getSettings('join_on') == 'product') {
            $feature_join_on = '`spf`.`sku_id` IS NULL';
        } else {
            $feature_join_on = '`soi`.`sku_id` = `spf`.`sku_id`';
        }


        $sql = "SELECT 
                `spf`.`feature_value_id`,
                SUM({$sales_subtotal} - {$discount}) AS sales,
                SUM({$sales_subtotal} - {$discount} - {$purchase}) AS profit,
                SUM({$sales_subtotal}) AS sales_subtotal,
                SUM({$discount}) AS discount,
                SUM({$purchase}) AS purchase
                FROM `shop_order` AS `so`
                JOIN `shop_order_items` AS `soi`
                    ON `so`.`id` = `soi`.`order_id`
                JOIN `shop_product` AS `sp`
                    ON `soi`.`product_id` = `sp`.`id`
                JOIN `shop_product_skus` AS `sps`
                    ON `soi`.`sku_id` = `sps`.`id`
                JOIN `shop_currency` AS `sc`
                    ON `sc`.`code` = `sp`.`currency`
               JOIN `shop_product_features` AS `spf` 
                    ON `soi`.`product_id` = `spf`.`product_id`
                    AND $feature_join_on
                {$storefront_join}
                WHERE $paid_date_sql AND `soi`.`type` = 'product'
                AND `spf`.`feature_id` = {$options['feature_id']}
                {$storefront_where}
                GROUP BY `spf`.`feature_value_id` ORDER BY {$order} DESC";
        return $this->query($sql);
    }

}
