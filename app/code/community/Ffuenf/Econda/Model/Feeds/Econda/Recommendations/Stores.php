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

class Ffuenf_Econda_Model_Feeds_Econda_Recommendations_Stores extends Ffuenf_Econda_Model_Feeds
{
    /**
     * @return null|string
     */
    public function getStoresCsv()
    {
        if (!Mage::helper('ffuenf_econda')->isExtensionActive()) {
            return;
        }
        $csv = "ID|Name|Code|isActive|homeUrl\n";
        $allStores = Mage::app()->getStores();
        foreach ($allStores as $eachStoreId => $val) {
            $store = Mage::app()->getStore($eachStoreId);
            $storeCode = $store->getCode();
            $storeName = $store->getName();
            $storeId = $store->getId();
            $storeActiv = $store->getIsActive();
            $storeUrl = $store->getBaseUrl();
            $csv .= $storeId . self::EXPORT_SEPARATOR;
            $csv .= $storeName . self::EXPORT_SEPARATOR;
            $csv .= $storeCode . self::EXPORT_SEPARATOR;
            $csv .= $storeActiv . self::EXPORT_SEPARATOR;
            $csv .= $storeUrl;
            $csv .= "\n";
        }
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=" . self::STORES_EXPORT_FILE);
        header("Content-Description: stores csv export");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        return $csv;
    }
}
