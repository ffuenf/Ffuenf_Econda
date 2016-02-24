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

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$entityTypeId     = $installer->getEntityTypeId('catalog_category');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);
 
$installer->addAttribute(
    'catalog_category', 'ffuenf_econda_feed', array(
        'input'                    => 'select',
        'type'                     => 'int',
        'label'                    => 'Include in Econda Recommendation Feeds',
        'source'                   => 'eav/entity_attribute_source_boolean',
        'global'                   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'                  => true,
        'required'                 => false,
        'visible_on_front'         => false,
        'is_html_allowed_on_front' => false,
        'is_configurable'          => false,
        'searchable'               => false,
        'filterable'               => false,
        'comparable'               => false,
        'unique'                   => false,
        'user_defined'             => false,
        'default'                  => '1',
        'is_user_defined'          => false,
        'used_in_product_listing'  => true
    )
);
$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'ffuenf_econda_feed',
    '11' //last Magento's attribute position in General tab is 10
);
$attributeId = $installer->getAttributeId($entityTypeId, 'ffuenf_econda_feed');
$installer->run("INSERT INTO `{$installer->getTable('catalog_category_entity_int')}`
    (`entity_type_id`, `attribute_id`, `entity_id`, `value`)
    SELECT '{$entityTypeId}', '{$attributeId}', `entity_id`, '1'
    FROM `{$installer->getTable('catalog_category_entity')}`;
");

$installer->endSetup();
