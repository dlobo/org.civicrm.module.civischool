SELECT     c.display_name, c.external_identifier, s.grade, s.media_authorization, s.subtype
FROM       civicrm_contact c
INNER JOIN civicrm_value_school_information s ON s.entity_id = c.id
WHERE      s.is_currently_enrolled = 1
AND        s.subtype = 'Student'
AND        ( s.media_authorization = 0 OR s.media_authorization IS NULL )


