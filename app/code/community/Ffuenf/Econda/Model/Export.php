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

class Ffuenf_Econda_Model_Export
{
    const XML_PATH_EXTENSION_RESTRICTBYIP                   = 'ffuenf_econda/general/restrictbyip';
    const XML_PATH_EXTENSION_ALLOWEDIPS                     = 'ffuenf_econda/general/allowed_ips';
    const XML_PATH_EXTENSION_PRODUCTS_STATUS                = 'ffuenf_econda/products/status';
    const XML_PATH_EXTENSION_PRODUCTS_TYPEIDS               = 'ffuenf_econda/products/typeids';
    const XML_PATH_EXTENSION_PRODUCTS_DESCRIPTION_TYPE      = 'ffuenf_econda/products/description_type';
    const XML_PATH_EXTENSION_PRODUCTS_NAME_USEPARENT        = 'ffuenf_econda/products/name_useparent';
    const XML_PATH_EXTENSION_PRODUCTS_DESCRIPTION_USEPARENT = 'ffuenf_econda/products/description_useparent';
    const XML_PATH_EXTENSION_PRODUCTS_URL_USEPARENT         = 'ffuenf_econda/products/url_useparent';
    const XML_PATH_EXTENSION_PRODUCTS_IMAGE_USEPARENT       = 'ffuenf_econda/products/image_useparent';
    const XML_PATH_EXTENSION_PRODUCTS_PRICE_USEPARENT       = 'ffuenf_econda/products/price_useparent';
    const XML_PATH_EXTENSION_PRODUCTS_PRICEOLD_USEPARENT    = 'ffuenf_econda/products/priceold_useparent';
    const XML_PATH_EXTENSION_PRODUCTS_NEW_USEPARENT         = 'ffuenf_econda/products/new_useparent';
    const XML_PATH_EXTENSION_PRODUCTS_STOCK_USEPARENT       = 'ffuenf_econda/products/stock_useparent';
    const XML_PATH_EXTENSION_PRODUCTS_SKU_USEPARENT         = 'ffuenf_econda/products/sku_useparent';
    const XML_PATH_EXTENSION_PRODUCTS_BRAND_USEPARENT       = 'ffuenf_econda/products/brand_useparent';
    const XML_PATH_EXTENSION_PRODUCTS_CATEGORIES_USEPARENT  = 'ffuenf_econda/products/categories_useparent';
    const PRODUCTS_EXPORT_FILE          = 'products.csv';
    const CATEGORIES_EXPORT_FILE        = 'categories.csv';
    const STORES_EXPORT_FILE            = 'stores.csv';
    const EXPORT_SEPARATOR              = '|';
    const CATEGORIES_SEPARATOR          = '^^';

    protected $report = array();

    public function isAllowedIp($storeId)
    {
        if (!Mage::getStoreConfig(self::XML_PATH_EXTENSION_RESTRICTBYIP, $storeId)) {
            return true;
        }
        $allowedIps = explode(',', Mage::getStoreConfig(self::XML_PATH_EXTENSION_ALLOWEDIPS, $storeId));
        $remoteAddr = Mage::helper('core/http')->getRemoteAddr(false);
        
        return in_array($remoteAddr, $allowedIps) ? true : false;
    }

    public function getProductsCsv($store)
    {
        if (!Mage::helper('ffuenf_econda')->isExtensionActive()) {
            return;
        }
        $report['getProductsCsv']['start']['time'] = microtime(true);
        $report['getProductsCsv']['start']['memory'] = memory_get_usage(true);
        $storeId = $store;
        $products = Mage::getResourceModel('catalog/product_collection');
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($products);
        $products->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));
        $types = array();
        $types = implode(',', Mage::getStoreConfig(self::XML_PATH_EXTENSION_PRODUCTS_TYPEIDS, $store));
        foreach ($types as $type) {
            $products->addAttributeToFilter('type_id', array('eq' => $type));
        }
        $products->addAttributeToSelect(array('id', 'parent_product_ids', 'name', 'description', 'category_ids', 'manufacturer', 'image', 'price', 'news_from_date', 'news_to_date'));
        $products->addStoreFilter($storeId);
        $csv = "ID|Name|Description|ProductURL|ImageURL|Price|OldPrice|New|Stock|SKU|Brand|ProductCategory\n";
        foreach ($products as $product) {
            $csv .= $this->_getProductCsv($product, $store);
        }
        $productsCsv = trim($csv);
        $report['getProductsCsv']['stop']['time'] = microtime(true);
        $report['getProductsCsv']['stop']['memory'] = memory_get_usage(true);
        Ffuenf_Common_Model_Logger::logProfile(
            array(
                'class' => 'Ffuenf_Econda',
                'type' => 'getProductsCsv',
                'items' => $products->getSize(),
                'start' => array(
                    'time' => $report['getProductsCsv']['start']['time'],
                    'memory' => $report['getProductsCsv']['start']['memory'],
                ),
                'stop' => array(
                    'time' => $report['getProductsCsv']['stop']['time'],
                    'memory' => $report['getProductsCsv']['stop']['memory'],
                )
            )
        );
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=" . self::PRODUCTS_EXPORT_FILE);
        header("Content-Description: products csv export");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        return $productsCsv;
    }

    public function getCategoriesCsv($store)
    {
        if (!Mage::helper('ffuenf_econda')->isExtensionActive()) {
            return;
        }
        $storeId = $store;
        $catRoot = Mage::app()->getStore()->getRootCategoryId();
        $collection = Mage::getModel('catalog/category')->getCollection()->setStoreId($storeId);
        $catIds = $collection->getAllIds();
        $cat = Mage::getModel('catalog/category');
        $csv .= "ID|ParentID|Name\n";
        foreach ($catIds as $catId) {
            $category = $cat->load($catId);
            if ($category->getLevel() != 0) {
                $catPath = explode('/',$category->getPath());
                $catParent = $catPath[sizeof($catPath) - 2];
                if ($catParent != '' || $category->getId() == $catRoot) {
                    $csv .= $category->getId() . self::EXPORT_SEPARATOR;
                    if ($category->getId() == $catRoot) {
                        $csv .= "" . self::EXPORT_SEPARATOR;
                    } else {
                        $csv .= $catParent . self::EXPORT_SEPARATOR;
                    }
                    $csv .= trim($category->getName()) . "\n";
                }
            }
        }
        $categoriesCsv = trim($csv);
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=" . self::CATEGORIES_EXPORT_FILE);
        header("Content-Description: categories csv export");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        return $categoriesCsv;
    }

    public function getStoresCsv()
    {
        if (!Mage::helper('ffuenf_econda')->isExtensionActive()) {
            return;
        }
        $csv .= "ID|Name|Code|isActive|homeUrl\n";
        $allStores = Mage::app()->getStores();
        foreach ($allStores as $eachStoreId => $val) {
            $storeCode = Mage::app()->getStore($eachStoreId)->getCode();
            $storeName = Mage::app()->getStore($eachStoreId)->getName();
            $storeId = Mage::app()->getStore($eachStoreId)->getId();
            $storeActiv = Mage::app()->getStore($eachStoreId)->getIsActive();
            $storeUrl = Mage::app()->getStore($eachStoreId)->getBaseUrl();
            $csv .= $storeId . self::EXPORT_SEPARATOR;
            $csv .= $storeName . self::EXPORT_SEPARATOR;
            $csv .= $storeCode . self::EXPORT_SEPARATOR;
            $csv .= $storeActiv . self::EXPORT_SEPARATOR;
            $csv .= $storeUrl . self::EXPORT_SEPARATOR;
            $csv .= "\n";
        }
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=" . self::STORES_EXPORT_FILE);
        header("Content-Description: stores csv export");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        return $csv;
    }

    protected function _getProductCsv($product, $store)
    {
        $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
        if (isset($parentIds[0])) {
            $parentProduct = Mage::getModel('catalog/product')->load(
                $parentIds[0], array(
                    'id',
                    'name',
                    'short_description',
                    'description',
                    'category_ids',
                    'manufacturer',
                    'image',
                    'price',
                    'news_from_date',
                    'news_to_date',
                    'status'
                )
            );
            if ($parentProduct->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                return;
            }
        } else {
            $parentProduct = $product;
        }
        $csv .= "";
        $csv .= trim($this->_getProductId($product, $store)) . self::EXPORT_SEPARATOR;
        $csv .= trim($this->_getProductName((self::XML_PATH_EXTENSION_PRODUCTS_NAME_USEPARENT ? $parentProduct : $product))) . self::EXPORT_SEPARATOR;
        $csv .= trim($this->_getProductDescription((self::XML_PATH_EXTENSION_PRODUCTS_DESCRIPTION_USEPARENT ? $parentProduct : $product))) . self::EXPORT_SEPARATOR;
        if (self::XML_PATH_EXTENSION_PRODUCTS_URL_USEPARENT) {
            $csv .= trim($parentProduct->getProductUrl()) . self::EXPORT_SEPARATOR;
        } else {
            $csv .= trim($product->getProductUrl()) . self::EXPORT_SEPARATOR;
        }
        if (self::XML_PATH_EXTENSION_PRODUCTS_IMAGE_USEPARENT) {
            $csv .= $parentProduct->getMediaConfig()->getMediaUrl($parentProduct->getData('image')) . self::EXPORT_SEPARATOR;
        } else {
            $csv .= $product->getMediaConfig()->getMediaUrl($product->getData('image')) . self::EXPORT_SEPARATOR;
        }
        $csv .= trim($this->_getProductPrice((self::XML_PATH_EXTENSION_PRODUCTS_PRICE_USEPARENT ? $parentProduct : $product))) . self::EXPORT_SEPARATOR;
        $csv .= trim($this->_getProductPriceOld((self::XML_PATH_EXTENSION_PRODUCTS_PRICEOLD_USEPARENT ? $parentProduct : $product))) . self::EXPORT_SEPARATOR;
        $csv .= trim($this->_getProductNew((self::XML_PATH_EXTENSION_PRODUCTS_NEW_USEPARENT ? $parentProduct : $product))) . self::EXPORT_SEPARATOR;
        $csv .= (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct((self::XML_PATH_EXTENSION_PRODUCTS_STOCK_USEPARENT ? $parentProduct : $product))->getQty() . self::EXPORT_SEPARATOR;
        if (self::XML_PATH_EXTENSION_PRODUCTS_SKU_USEPARENT) {
            $csv .= trim($parentProduct->getSKU()) . self::EXPORT_SEPARATOR;
        } else {
            $csv .= trim($product->getSKU()) . self::EXPORT_SEPARATOR;
        }
        $csv .= trim($this->_getProductBrand((self::XML_PATH_EXTENSION_PRODUCTS_BRAND_USEPARENT ? $parentProduct : $product))) . self::EXPORT_SEPARATOR;
        $csv .= trim($this->_getProductCategoriesCsv((self::XML_PATH_EXTENSION_PRODUCTS_CATEGORIES_USEPARENT ? $parentProduct : $product)));
        
        return $csv . "\n";
    }

    protected function _getProductCategoriesCsv($product)
    {
        $catIds = $product->getCategoryIds();
        $csv = "";
        foreach ($catIds as $catId) {
            $csv .= self::CATEGORIES_SEPARATOR . $catId;
        }
        
        return substr($csv, 2);
    }

    protected function _getProductName($product)
    {
        $product_name = $product->getName();
        $product_name = str_replace("\n", "", strip_tags($product_name));
        $product_name = str_replace("\r", "", strip_tags($product_name));
        $product_name = str_replace("\t", " ", strip_tags($product_name));
        $product_name = str_replace(self::EXPORT_SEPARATOR, "/", strip_tags($product_name));
        
        return $product_name;
    }

    protected function _getProductId($product, $store)
    {
        $idType = Mage::getStoreConfig('recommendationexp/recommendationexp_settings/recommendationexp_productid', $store);
        if ($idType == '1') {
            $product_id = $product->getSku();
        } else {
            $product_id = $product->getId();
        }
        
        return $product_id;
    }

    protected function _getProductPrice($product)
    {
        if ($product->getSpecialPrice() && (date("Y-m-d G:i:s") > $product->getSpecialFromDate() || !$product->getSpecialFromDate()) &&  (date("Y-m-d G:i:s") < $product->getSpecialToDate() || !$product->getSpecialToDate())) {
            $price = $product->getSpecialPrice();
        } else {
            $price = $product->getPrice();
        }
        
        return $this->_formatPrice($product, $price);
    }

    protected function _getProductPriceOld($product)
    {
        $taxHelper = Mage::helper('tax');
        $price = $product->getPrice();
        
        return $this->_formatPrice($product, $price);
    }

    protected function _getProductNew($product)
    {
        $now = date("Y-m-d");
        $newsFrom = substr($product->getData('news_from_date'), 0, 10);
        $newsTo = substr($product->getData('news_to_date'), 0, 10);
        if ($now >= $newsFrom && $now <= $newsTo) {
            return '1';
        }
        
        return '0';
    }

    protected function _getProductBrand($product)
    {
        if ($product->getResource()->getAttribute('manufacturer')) {
            $manufacturer = $product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($product);
            if (strtolower($manufacturer) == "no" || strtolower($manufacturer) == "nein") {
                $manufacturer = "";
                return $manufacturer;
            } else {
                return $manufacturer;
            }
        } else {
            return $manufacturer = "";
        }
    }

    protected function _getProductDescription($product)
    {
        $descriptionType = Mage::getStoreConfig(self::XML_PATH_EXTENSION_PRODUCTS_DESCRIPTION_TYPE, $store);
        $description = strip_tags($product->getData($descriptionType));
        $description = preg_replace("/\r|\n|/s", "", $description);
        $description = str_replace(self::EXPORT_SEPARATOR, "/", $description);
        
        return $description;
    }
    
    protected function _formatPrice($product, $price)
    {
        $taxHelper  = Mage::helper('tax');
        $price = $taxHelper->getPrice($product, $price, true);
        $price = number_format($price, '2');
        
        return $price;
    }
}
