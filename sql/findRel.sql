SELECT     r.id, c.display_name, s.grade_sis
FROM       civicrm_contact c
INNER JOIN civicrm_value_school_information s ON c.id = s.entity_id
INNER JOIN civicrm_relationship r ON r.contact_id_b = c.id
WHERE      r.contact_id_a = 744
AND        s.subtype = 'Student'
AND        s.is_currently_enrolled = 1
AND        s.grade_sis < 0
