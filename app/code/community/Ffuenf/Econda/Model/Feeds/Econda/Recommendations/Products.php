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

class Ffuenf_Econda_Model_Feeds_Econda_Recommendations_Products extends Ffuenf_Econda_Model_Feeds
{
    /**
     * @return null|string
     */
    public function getProductsCsv($store)
    {
        if (!Mage::helper('ffuenf_econda')->isExtensionActive()) {
            return;
        }
        $report = array();
        $report['getProductsCsv']['start']['time'] = microtime(true);
        $report['getProductsCsv']['start']['memory'] = memory_get_usage(true);
        $storeId = $store;
        $products = Mage::getResourceModel('catalog/product_collection');
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($products);
        $statuses = explode(',', Mage::getStoreConfig(self::XML_PATH_EXTENSION_PRODUCTS_STATUS, $store));
        foreach ($statuses as $status) {
            $products->addAttributeToFilter('status', array('eq' => $status));
        }
        $types = explode(',', Mage::getStoreConfig(self::XML_PATH_EXTENSION_PRODUCTS_TYPEIDS, $store));
        $products->addAttributeToFilter('type_id', array('in' => $types));
        $products->addAttributeToSelect(array('id', 'parent_product_ids', 'name', 'description', 'category_ids', 'manufacturer', 'image', 'price', 'news_from_date', 'news_to_date'));
        $products->addStoreFilter($storeId);
        $csv = "ID|SKU|Name|Description|ProductURL|ImageURL|Price|OldPrice|New|Stock|EAN|Brand|ProductCategory\n";
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

    /**
     * @return null|string
     */
    protected function _getProductCsv($product, $store)
    {
        $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
        $statuses = explode(',', Mage::getStoreConfig(self::XML_PATH_EXTENSION_PRODUCTS_STATUS, $store));
        if (isset($parentIds[0])) {
            $parentProduct = Mage::getModel('catalog/product')->load(
                $parentIds[0], array(
                    'id',
                    'sku',
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
            if (!in_array($parentProduct->getStatus(), $statuses)) {
                return;
            }
        } else {
            if ($product->getTypeId() == 'configurable') {
                $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
                $productCollection = $conf->getUsedProductCollection()->addFilterByRequiredOptions();
                Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($productCollection);
                $cnt = $productCollection->count();
                if ($cnt == 0) {
                    return;
                }
            }
            $parentProduct = $product;
        }
        if ($this->_getProductId($parentProduct, $store) == '') {
            Ffuenf_Common_Model_Logger::logSystem(
                array(
                    'class'   => 'Ffuenf_Econda',
                    'type'    => Zend_Log::DEBUG,
                    'message' => 'There\'s a problem with product-id ' . trim($this->_getProductId($product, $store)),
                    'details' => 'Looks like there is no corresponding configurable product'
                )
            );
            return;
        }
        $csv = trim($this->_getProductId($parentProduct, $store)) . self::EXPORT_SEPARATOR;
        $csv .= (($product->getTypeId() == 'simple') ? '"' . trim($this->_getProductId($product, $store)) . '"' : '""') . self::EXPORT_SEPARATOR;
        $csv .= '"' . trim($this->_getProductName((self::XML_PATH_EXTENSION_PRODUCTS_NAME_USEPARENT ? $parentProduct : $product))) . '"' . self::EXPORT_SEPARATOR;
        $csv .= '"' . trim($this->_getProductDescription((self::XML_PATH_EXTENSION_PRODUCTS_DESCRIPTION_USEPARENT ? $parentProduct : $product), $store)) . '"' . self::EXPORT_SEPARATOR;
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
        $csv .= (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty() . self::EXPORT_SEPARATOR;
        if (self::XML_PATH_EXTENSION_PRODUCTS_EAN_USEPARENT) {
            $csv .= '"' . trim($parentProduct->getSku()) . '"' . self::EXPORT_SEPARATOR;
        } else {
            $csv .= '"' . trim($product->getSku()) . '"' . self::EXPORT_SEPARATOR;
        }
        $csv .= '"' . trim($this->_getProductBrand((self::XML_PATH_EXTENSION_PRODUCTS_BRAND_USEPARENT ? $parentProduct : $product))) . '"' . self::EXPORT_SEPARATOR;
        $csv .= trim($this->_getProductCategoriesCsv((self::XML_PATH_EXTENSION_PRODUCTS_CATEGORIES_USEPARENT ? $parentProduct : $product), $store));
        
        return $csv . "\n";
    }

    /**
     * @return string
     */
    protected function _getProductCategoriesCsv($product, $store)
    {
        $categoryIds = $product->getCategoryIds();
        $csv = "";
        $catIds = Mage::getResourceModel('catalog/category_collection')
                  ->addAttributeToSelect(array('entity_id'))
                  ->addAttributeToFilter('entity_id', array('in' => $categoryIds))
                  ->addAttributeToFilter('ffuenf_econda_feed', array('eq' => 1))
                  ->setStoreId($store);
        foreach ($catIds as $catId) {
            $csv .= self::CATEGORIES_SEPARATOR . $catId->getId();
        }
        
        return substr($csv, 2);
    }

    /**
     * @return string
     */
    protected function _getProductName($product)
    {
        $productName = $product->getName();
        $productName = strip_tags($productName);
        $productName = preg_replace("/\r|\n|/s", "", $productName);
        $productName = str_replace('"', "&quot;", $productName);
        $productName = str_replace(self::EXPORT_SEPARATOR, "/", $productName);
        
        return $productName;
    }

    /**
     * @return string
     */
    protected function _getProductId($product, $store)
    {
        $idType = Mage::getStoreConfig(self::XML_PATH_EXTENSION_PRODUCTS_ID_TYPE, $store);
        $productId = ($idType == '1' ? $product->getSku() : $product->getId());
        
        return $productId;
    }

    /**
     * @return string
     */
    protected function _getProductPrice($product)
    {
        if ($product->getSpecialPrice() && (date("Y-m-d G:i:s") > $product->getSpecialFromDate() || !$product->getSpecialFromDate()) && (date("Y-m-d G:i:s") < $product->getSpecialToDate() || !$product->getSpecialToDate())) {
            $price = $product->getSpecialPrice();
        } else {
            $price = $product->getPrice();
        }
        
        return $this->_formatPrice($product, $price);
    }

    /**
     * @return string
     */
    protected function _getProductPriceOld($product)
    {
        $price = $product->getPrice();
        
        return $this->_formatPrice($product, $price);
    }

    /**
     * @return string
     */
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

    /**
     * @return string
     */
    protected function _getProductBrand($product)
    {
        if ($product->getResource()->getAttribute('manufacturer')) {
            $manufacturer = $product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($product);
            if (strtolower($manufacturer) == "no" || strtolower($manufacturer) == "nein") {
                return "";
            } else {
                return $manufacturer;
            }
        } else {
            return "";
        }
    }

    /**
     * @return string
     */
    protected function _getProductDescription($product, $store)
    {
        $descriptionType = Mage::getStoreConfig(self::XML_PATH_EXTENSION_PRODUCTS_DESCRIPTION_TYPE, $store);
        $description = strip_tags($product->getData($descriptionType));
        $description = preg_replace("/\r|\n|/s", "", $description);
        $description = str_replace('"', "&quot;", $description);
        $description = str_replace(self::EXPORT_SEPARATOR, "/", $description);
        
        return $description;
    }

    /**
     * @return string
     */
    protected function _formatPrice($product, $price)
    {
        $taxHelper = Mage::helper('tax');
        $price = $taxHelper->getPrice($product, $price, true);
        $price = number_format($price, '2');
        
        return $price;
    }
}
