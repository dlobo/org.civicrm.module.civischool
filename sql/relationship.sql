INSERT INTO civicrm_relationship (contact_id_a, contact_id_b, relationship_type_id, is_active )
SELECT 209, c.id, 10, 1
FROM civicrm_contact c
INNER JOIN civicrm_value_school_information s ON c.id = s.entity_id
WHERE s.grade = 'PK3 N'
AND   s.subtype = 'Student'
AND   s.is_currently_enrolled = 1;
