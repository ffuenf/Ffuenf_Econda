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

class Ffuenf_Econda_Model_Feeds_Econda_Recommendations_Categories extends Ffuenf_Econda_Model_Feeds
{
    /**
     * @return null|string
     */
    public function getCategoriesCsv($store)
    {
        if (!Mage::helper('ffuenf_econda')->isExtensionActive()) {
            return;
        }
        $storeId = $store;
        $catRoot = Mage::app()->getStore()->getRootCategoryId();
        $collection = Mage::getModel('catalog/category')
                      ->getCollection()
                      ->addAttributeToSelect('id')
                      ->addAttributeToSelect('ffuenf_econda_feed')
                      ->setStoreId($storeId);
        $catIds = $collection->getAllIds();
        $cat = Mage::getModel('catalog/category');
        $csv = "ID|ParentID|Name\n";
        foreach ($catIds as $catId) {
            $category = $cat->load($catId);
            if (!$category->getFfuenfEcondaFeed()) {
                continue;
            }
            if ($category->getLevel() != 0) {
                $catPath = explode('/', $category->getPath());
                $catParent = $catPath[sizeof($catPath) - 2];
                if ($catParent != '' || $category->getId() == $catRoot) {
                    $csv .= $category->getId() . self::EXPORT_SEPARATOR;
                    if ($category->getId() == $catRoot) {
                        $csv .= "ROOT" . self::EXPORT_SEPARATOR;
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
}
