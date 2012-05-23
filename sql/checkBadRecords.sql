SELECT c1.id, c2.id, c1.display_name
FROM   civicrm_contact c1
INNER JOIN civicrm_contact c2 ON c1.display_name = c2.display_name
INNER JOIN civicrm_value_school_information s1 ON s1.entity_id = c1.id
INNER JOIN civicrm_value_school_information s2 ON s2.entity_id = c2.id
WHERE s1.subtype = 'Student'
AND   s2.subtype = 'Student'
AND   c2.id > c1.id
;


