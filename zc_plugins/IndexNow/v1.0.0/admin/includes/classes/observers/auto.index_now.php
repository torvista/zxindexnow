<?php

declare(strict_types=1);
/**
 * auto observer for IndexNow submission on product or category update
 * @package admin
 * Copyright 2025 ZenExpert - https://zenexpert.com
 * @version 07 June 2026 torvista
 */

class zcObserverIndexNow extends base
{
    // allow disabling of the submission for testing purposes only
    private bool $noSubmit = false;
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

    public function update(&$class, $eventID, $p1): void
    {
        global $db;
        switch ($eventID) {
            case 'NOTIFY_MODULES_UPDATE_PRODUCT_END':
                // 'NOTIFY_MODULES_UPDATE_PRODUCT_END', ['action' => $action, 'products_id' => $products_id]
                $products_id = (int)$p1['products_id'];
                $product = $db->Execute("SELECT products_type, master_categories_id FROM " . TABLE_PRODUCTS . " WHERE products_id = " . $products_id);
                $type_handler = zen_get_handler_from_type($product->fields['products_type']);
                $cPath = zen_get_generated_category_path_rev($product->fields['master_categories_id']);
                $url = zen_catalog_href_link($type_handler . '_info', 'cPath=' . $cPath . '&products_id=' . $products_id);
                $this->submitToIndexNow($url);
                break;

            case 'NOTIFY_ADMIN_CATEGORIES_UPDATE_OR_INSERT_FINISH':
                // 'NOTIFY_ADMIN_CATEGORIES_UPDATE_OR_INSERT_FINISH', ['action' => $action, 'categories_id' => (int)$categories_id]
                $cat_id = (int)$p1['categories_id'];
                $url = zen_catalog_href_link('index', zen_get_path($cat_id));
                $this->submitToIndexNow($url);
                break;


            default:
                break;
        }
    }

    private function submitToIndexNow($url): void
    {
        global $messageStack;
        $indexnow_key = ZX_INDEXNOW_KEY;
        $endpoint = 'https://bing.com/indexnow';

        $query = http_build_query([
            'url' => $url,
            'key' => $indexnow_key
        ]);
        $full_url = $endpoint . '?' . $query;

        if ($this->noSubmit) {
            $messageStack->add_session('IndexNow url NOT submitted: "' . $url . '"', 'info');
            return;
        }

        $ch = curl_init($full_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $indexnow_responses = [
            200 => ['OK', 'URL submitted successfully'],
            202 => ['Accepted', 'URL received. IndexNow key validation pending.'],
            400 => ['Bad request', 'Invalid format'],
            403 => ['Forbidden', 'Key not valid (e.g. key not found, file found but key not in the file)'],
            422 => ['Unprocessable Entity', 'URLs don’t belong to the host or key not matching the schema'],
            429 => ['Too Many Requests', 'Too Many Requests (potential Spam)'],
        ];

        if (isset($indexnow_responses[$http_code])) {
            $message = "IndexNow response $http_code: {$indexnow_responses[$http_code][0]} - {$indexnow_responses[$http_code][1]}";
        } else {
            $message = "IndexNow submission failed. HTTP code: $http_code";
        }
        $messageStack->add_session($message . ', $url:' . $url, ($http_code === 200 || $http_code === 202) ? 'success' : 'error');
    }
}
