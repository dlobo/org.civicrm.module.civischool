SELECT     c1.entity_id, c1.signin_time, c2.signout_time, c1.class, c2.signout_time, c2.pickup_person_name, c2.at_school_meeting
FROM       civicrm_value_extended_care_signout c1
INNER JOIN civicrm_value_extended_care_signout c2 ON c1.entity_id = c2.entity_id
WHERE      date( c1.signin_time ) = date( c2.signout_time )
AND        c1.signout_time IS NULL
AND        c2.signout_time IS NOT NULL
AND        ( c1.is_morning = 0 OR c1.is_morning IS NULL )
AND        ( c2.is_morning = 0 OR c2.is_morning IS NULL )
AND        date( c1.signin_time ) > '2010-09-01'
;

UPDATE     civicrm_value_extended_care_signout c1 
INNER JOIN civicrm_value_extended_care_signout c2 USING (entity_id)
SET        c2.class = c1.class,
           c2.signin_time = c1.signin_time
WHERE      date( c1.signin_time ) = date( c2.signout_time )
AND        c1.signout_time IS NULL
AND        c2.signout_time IS NOT NULL
AND        ( c1.is_morning = 0 OR c1.is_morning IS NULL )
AND        ( c2.is_morning = 0 OR c2.is_morning IS NULL )
AND        date( c1.signin_time ) > '2010-09-01'
;

DELETE     c1.* 
FROM       civicrm_value_extended_care_signout c1 
INNER JOIN civicrm_value_extended_care_signout c2 USING (entity_id) 
WHERE      date( c1.signin_time ) = date( c2.signout_time )
AND        c1.signout_time IS NULL
AND        c2.signout_time IS NOT NULL
AND        ( c1.is_morning = 0 OR c1.is_morning IS NULL )
AND        ( c2.is_morning = 0 OR c2.is_morning IS NULL )
AND        date( c1.signin_time ) > '2010-09-01'
;

// multiple signouts same day

SELECT     c1.id, c2.id, c1.entity_id, c1.signin_time, c1.signout_time, c1.class, c2.signin_time, c2.signout_time
FROM       civicrm_value_extended_care_signout c1
INNER JOIN civicrm_value_extended_care_signout c2 ON c1.entity_id = c2.entity_id
WHERE      date( c1.signout_time ) = date( c2.signout_time )
AND        date( c1.signout_time ) = date( c2.signout_time )
AND        ( c1.is_morning = 0 OR c1.is_morning IS NULL )
AND        ( c2.is_morning = 0 OR c2.is_morning IS NULL )
AND        date( c1.signin_time ) > '2010-09-01'
AND        c2.id > c1.id
GROUP BY   c1.entity_id
;

DELETE     c2.* 
FROM       civicrm_value_extended_care_signout c1 
INNER JOIN civicrm_value_extended_care_signout c2 USING (entity_id) 
WHERE      date( c1.signin_time ) = date( c2.signin_time )
AND        date( c1.signout_time ) = date( c2.signout_time )
AND        ( c1.is_morning = 0 OR c1.is_morning IS NULL )
AND        ( c2.is_morning = 0 OR c2.is_morning IS NULL )
AND        date( c1.signin_time ) > '2010-09-01'
AND        c2.id > c1.id
;

