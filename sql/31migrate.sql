UPDATE `civicrm_custom_group` SET `table_name` = 'civicrm_value_school_information' WHERE `civicrm_custom_group`.`id` = 1;
UPDATE `civicrm_custom_group` SET `table_name` = 'civicrm_value_extended_care' WHERE `civicrm_custom_group`.`id` = 2;
UPDATE `civicrm_custom_group` SET `table_name` = 'civicrm_value_extended_care_signout' WHERE `civicrm_custom_group`.`id` = 4;

RENAME TABLE `civicrm_value_school_information_1` TO `civicrm_value_school_information`;
RENAME TABLE `civicrm_value_extended_care_signout_3` TO `civicrm_value_extended_care_signout`;
RENAME TABLE `civicrm_value_extended_care_2` TO `civicrm_value_extended_care`;

truncate civicrm_cache;