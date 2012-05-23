SELECT     c.first_name, c.last_name, v.grade_sis, 'Charge', CONCAT( 'Fall 2011 - ', ecs.name ), '2011-02-01', ecs.fee_block * ecs.total_fee_block, 'Activity Fee'
FROM       civicrm_contact c
INNER JOIN civicrm_value_extended_care s ON s.entity_id = c.id
INNER JOIN civicrm_value_school_information v ON v.entity_id = c.id
INNER JOIN sfschool_extended_care_source ecs ON ecs.term = s.term AND ecs.session = s.session AND ecs.name = s.name AND ecs.day_of_week = s.day_of_week
WHERE      s.has_cancelled = 0
AND        s.term = 'Fall 2011'
AND        ecs.fee_block > 0
AND        ecs.total_fee_block > 0
ORDER BY   ecs.name
INTO OUTFILE '/tmp/fall2011.csv'
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
LINES TERMINATED BY '\n';
