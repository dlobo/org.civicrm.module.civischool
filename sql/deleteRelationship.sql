DELETE     t.*
FROM       civicrm_activity_target t
INNER JOIN civicrm_value_school_information s ON t.target_contact_id = s.entity_id
INNER JOIN civicrm_activity a ON t.activity_id = a.id
WHERE      a.status_id = 1
AND        s.grade = 'PK4 N'
AND        s.subtype = 'Student'
AND        s.is_currently_enrolled = 1;
