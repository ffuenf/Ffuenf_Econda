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

class Ffuenf_Econda_Model_Adminhtml_System_Config_Source_Productdescription
{
    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array('short_description' => Mage::helper('ffuenf_econda')->__('Short Description'), 'description' => Mage::helper('ffuenf_econda')->__('Description'));
    }
}
