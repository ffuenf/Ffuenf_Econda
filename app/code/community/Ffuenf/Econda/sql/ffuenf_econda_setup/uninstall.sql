-- add table prefix if you have one
DELETE FROM core_config_data WHERE path like 'ffuenf_econda/%';
DELETE FROM core_config_data WHERE path = 'advanced/modules_disable_output/Ffuenf_Econda';
DELETE FROM core_resource WHERE code = 'ffuenf_econda_setup';
DELETE FROM eav_attribute WHERE attribute_code = 'ffuenf_econda_feed';