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

class Ffuenf_Econda_CategoriesController extends Mage_Core_Controller_Front_Action
{
    /**
     * @return string
     */
    protected function _getCategoriesCsv($store)
    {
        return Mage::getModel('ffuenf_econda/feeds_econda_recommendations_categories')->getCategoriesCsv($store);
    }

    public function indexAction()
    {
        $storeId = $this->getRequest()->getParam('store');
        $actStore = null;
        $stores = Mage::app()->getStores();
        foreach ($stores as $store => $val) {
            $storeIdShop = Mage::app()->getStore($store)->getId();
            if ($storeIdShop == $storeId) {
                $actStore = $storeId;
            }
        }
        
        if (!Mage::getSingleton('ffuenf_econda/feeds')->isAllowedIp($storeId) || $actStore == null) {
            return;
        }
        echo $this->_getCategoriesCsv($actStore);
    }
}
