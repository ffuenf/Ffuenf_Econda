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

class Ffuenf_Econda_Model_Adminhtml_System_Config_Source_Productstatuses
{
    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = Mage::getModel('catalog/product_status')->getAllOptions();
        array_shift($options);
        
        return $options;
    }
}
