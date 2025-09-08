<?php
/*
 * auto observer for IndexNow submission on product or category update
 * @package admin
 * Copyright 2025 ZenExpert - https://zenexpert.com
 */

class zcObserverIndexNow extends base
{
    public function __construct()
    {
        $this->attach(
            $this,
            [
                'NOTIFY_MODULES_UPDATE_PRODUCT_END',
                'NOTIFY_ADMIN_CATEGORIES_UPDATE_OR_INSERT_FINISH'
            ]
        );
    }

    public function update(&$class, $eventID, &$p1, &$p2, &$p3, &$p4)
    {
        global $db;
        switch ($eventID) {
            case 'NOTIFY_MODULES_UPDATE_PRODUCT_END':
                // receive the action and product ID data array
                $products_id = (int)$p1['products_id'];
                $product = $db->Execute("SELECT products_type, master_categories_id FROM " . TABLE_PRODUCTS . " WHERE products_id = " . $products_id);
                $type_handler = zen_get_handler_from_type($product->fields['products_type']);
                $cPath = $product->fields['master_categories_id'];

                $url = zen_catalog_href_link($type_handler . '_info', 'cPath=' . $cPath . '&products_id=' . $products_id);

                $this->submitToIndexNow($url);

                break;

            case 'NOTIFY_ADMIN_CATEGORIES_UPDATE_OR_INSERT_FINISH':
                // receive the action and category ID data array
                $cat_id = (int)$p1['categories_id'];

                $url = zen_catalog_href_link('index', zen_get_path($cat_id));

                $this->submitToIndexNow($url);

                break;
            default:
                break;
        }
    }

    private function submitToIndexNow($url)
    {
        global $messageStack;
        $indexnow_key = ZX_INDEXNOW_KEY;
        $endpoint = 'https://bing.com/indexnow';

        $query = http_build_query([
            'url' => $url,
            'key' => $indexnow_key
        ]);
        $full_url = $endpoint . '?' . $query;

        $ch = curl_init($full_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $indexnow_responses = [
            200 => ['OK', 'URL submitted successfully'],
            202 => ['Accepted', 'URL received. IndexNow key validation pending.'],
            400 => ['Bad request', 'Invalid format'],
            403 => ['Forbidden', 'Key not valid (e.g. key not found, file found but key not in the file)'],
            422 => ['Unprocessable Entity', 'URLs don’t belong to the host or key not matching the schema'],
            429 => ['Too Many Requests', 'Too Many Requests (potential Spam)'],
        ];

        if (isset($indexnow_responses[$http_code])) {
            $message = "IndexNow response: {$indexnow_responses[$http_code][0]} - {$indexnow_responses[$http_code][1]}";
        } else {
            $message = "IndexNow submission failed. HTTP code: $http_code";
        }
        $messageStack->add_session($message, ($http_code === 200 || $http_code === 202) ? 'success' : 'error');
    }
}
