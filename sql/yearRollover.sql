
UPDATE civicrm_domain SET config_backend = null;

# suppress all 8th grade students
UPDATE civicrm_value_school_information SET is_currently_enrolled = 0 where grade = 8;

# increment everyone else's grade
UPDATE civicrm_value_school_information SET grade_sis = grade_sis + 1 WHERE grade_sis IS NOT NULL;

UPDATE civicrm_value_school_information SET grade = '9' WHERE grade = '8';
UPDATE civicrm_value_school_information SET grade = '8' WHERE grade = '7';
UPDATE civicrm_value_school_information SET grade = '7' WHERE grade = '6';
UPDATE civicrm_value_school_information SET grade = '6' WHERE grade = '5';
UPDATE civicrm_value_school_information SET grade = '5' WHERE grade = '4';
UPDATE civicrm_value_school_information SET grade = '4' WHERE grade = '3';
UPDATE civicrm_value_school_information SET grade = '3' WHERE grade = '2';
UPDATE civicrm_value_school_information SET grade = '2' WHERE grade = '1';
UPDATE civicrm_value_school_information SET grade = '1' WHERE grade = 'K N';
UPDATE civicrm_value_school_information SET grade = '1' WHERE grade = 'K S';
UPDATE civicrm_value_school_information SET grade = 'K N' WHERE grade = 'PK4 N';
UPDATE civicrm_value_school_information SET grade = 'K S' WHERE grade = 'PK4 S';
UPDATE civicrm_value_school_information SET grade = 'PK4 N' WHERE grade = 'PK3 N';
UPDATE civicrm_value_school_information SET grade = 'PK4 S' WHERE grade = 'PK3 S';

UPDATE civicrm_value_school_information SET grade = 'K N' WHERE entity_id IN ( 61, 63 );
UPDATE civicrm_value_school_information SET grade_sis = 0 WHERE entity_id IN ( 61, 63 );

# delete all prior student <-> teacher relationships
DELETE FROM civicrm_relationship WHERE relationship_type_id = 10;
