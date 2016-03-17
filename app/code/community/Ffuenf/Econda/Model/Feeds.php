<?php
/**
 * Ffuenf_Econda extension.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category   Ffuenf
 *
 * @author     Achim Rosenhagen <a.rosenhagen@ffuenf.de>
 * @copyright  Copyright (c) 2016 ffuenf (http://www.ffuenf.de)
 * @license    http://opensource.org/licenses/mit-license.php MIT License
 */

class Ffuenf_Econda_Model_Feeds
{
    const XML_PATH_EXTENSION_RESTRICTBYIP                   = 'ffuenf_econda/general/restrictbyip';
    const XML_PATH_EXTENSION_ALLOWEDIPS                     = 'ffuenf_econda/general/allowed_ips';
    const XML_PATH_EXTENSION_PRODUCTS_STATUS                = 'ffuenf_econda/products/status';
    const XML_PATH_EXTENSION_PRODUCTS_TYPEIDS               = 'ffuenf_econda/products/typeids';
    const XML_PATH_EXTENSION_PRODUCTS_ID_TYPE               = 'ffuenf_econda/products/id_type';
    const XML_PATH_EXTENSION_PRODUCTS_DESCRIPTION_TYPE      = 'ffuenf_econda/products/description_type';
    const XML_PATH_EXTENSION_PRODUCTS_NAME_USEPARENT        = 'ffuenf_econda/products/name_useparent';
    const XML_PATH_EXTENSION_PRODUCTS_DESCRIPTION_USEPARENT = 'ffuenf_econda/products/description_useparent';
    const XML_PATH_EXTENSION_PRODUCTS_URL_USEPARENT         = 'ffuenf_econda/products/url_useparent';
    const XML_PATH_EXTENSION_PRODUCTS_IMAGE_USEPARENT       = 'ffuenf_econda/products/image_useparent';
    const XML_PATH_EXTENSION_PRODUCTS_PRICE_USEPARENT       = 'ffuenf_econda/products/price_useparent';
    const XML_PATH_EXTENSION_PRODUCTS_PRICEOLD_USEPARENT    = 'ffuenf_econda/products/priceold_useparent';
    const XML_PATH_EXTENSION_PRODUCTS_NEW_USEPARENT         = 'ffuenf_econda/products/new_useparent';
    const XML_PATH_EXTENSION_PRODUCTS_STOCK_USEPARENT       = 'ffuenf_econda/products/stock_useparent';
    const XML_PATH_EXTENSION_PRODUCTS_EAN_USEPARENT         = 'ffuenf_econda/products/ean_useparent';
    const XML_PATH_EXTENSION_PRODUCTS_BRAND_USEPARENT       = 'ffuenf_econda/products/brand_useparent';
    const XML_PATH_EXTENSION_PRODUCTS_CATEGORIES_USEPARENT  = 'ffuenf_econda/products/categories_useparent';
    const XML_PATH_EXTENSION_CATEGORIES_STATUS              = 'ffuenf_econda/categories/category_state';
    const PRODUCTS_EXPORT_FILE                              = 'products.csv';
    const CATEGORIES_EXPORT_FILE                            = 'categories.csv';
    const STORES_EXPORT_FILE                                = 'stores.csv';
    const EXPORT_SEPARATOR                                  = '|';
    const CATEGORIES_SEPARATOR                              = '^^';

    /**
     * @return boolean
     */
    public function isAllowedIp($storeId)
    {
        if (!Mage::getStoreConfig(self::XML_PATH_EXTENSION_RESTRICTBYIP, $storeId)) {
            return true;
        }
        $allowedIps = explode(',', Mage::getStoreConfig(self::XML_PATH_EXTENSION_ALLOWEDIPS, $storeId));
        $remoteAddr = Mage::helper('core/http')->getRemoteAddr(false);
        
        return in_array($remoteAddr, $allowedIps) ? true : false;
    }
}
