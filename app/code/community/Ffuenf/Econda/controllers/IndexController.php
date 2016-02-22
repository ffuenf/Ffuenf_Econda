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

class Ffuenf_Econda_IndexController extends Mage_Core_Controller_Front_Action
{
    protected function _getProductsCsv()
    {
        return Mage::getModel('ffuenf_econda/export')->getProductsCsv($store);
    }

    protected function _getCategoriesCsv()
    {
        return Mage::getModel('ffuenf_econda/export')->getCategoriesCsv($store);
    }

    protected function _getStoresCsv()
    {
        return Mage::getModel('ffuenf_econda/export')->getStoresCsv();
    }

    public function indexAction()
    {
        $storeId = $_GET['store'];
        $actStore = null;
        $stores = Mage::app()->getStores();
        foreach ($stores as $store => $val) {
            $storeIdShop = Mage::app()->getStore($store)->getId();
            if ($storeIdShop == $storeId) {
                $actstore = $storeId;
            }
        }
        
        switch ($_GET['type']) {
            case '0':
                if (!Mage::getSingleton('ffuenf_econda/export')->isAllowedIp($storeId)) {
                    return;
                }
                echo $this->_getStoresCsv();
                break;
            case '1':
                if (!Mage::getSingleton('ffuenf_econda/export')->isAllowedIp($storeId) || $actstore == null) {
                    return;
                }
                echo $this->_getProductsCsv($actstore);
                break;
            case '2':
                if (!Mage::getSingleton('ffuenf_econda/export')->isAllowedIp($storeId) || $actstore == null) {
                    return;
                }
                echo $this->_getCategoriesCsv($actstore);
                break;
            
            default:
                return;
                break;
        }
    }
}
