<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$
 *
 */

class SFS_Utils_ExtendedCare {
    const
        TERM_POSITION = 0,
        MIN_GRADE_POSITION = 1,
        MAX_GRADE_POSITION = 2,
        DAY_POSITION = 3,
        SESSION_POSITION = 4,
        NAME_POSITION = 5,
        DESC_POSITION = 6,
        INSTR_POSITION = 7,
        MAX_POSITION = 8,
        FEE_POSITION = 9,
        START_POSITION = 10,
        END_POSITION = 11,
        TERM = 'Spring 2012',
        COORDINATOR_NAME  = 'Vivian Walz',
        COORDINATOR_EMAIL = 'vwalz@sfschool.org';

    static
        $_extendedCareElements = null,
        $_registeredElements   = null;

    static function buildForm( &$form,
                               $childID,
                               $term = null ) {

        $excare = CRM_Utils_Request::retrieve( 'excare', 'Integer', $form, false, null, $_REQUEST );
        if ( $excare != 1 ) {
            return;
        }

        require_once 'SFS/Utils/Query.php';
        $grade  = SFS_Utils_Query::getGrade( $childID );
        if ( ! is_numeric( $grade ) ) {
            return;
        }

        $parentID = CRM_Utils_Request::retrieve( 'parentID', 'Integer', $form, false, null, $_REQUEST );
        if ( $parentID ) {
            $sess =& CRM_Core_Session::singleton( );
            $url  =  CRM_Utils_System::url( 'civicrm/profile/view',
                                            "reset=1&gid=3&id=$parentID" );
            $form->removeElement( 'cancelURL' );
            $form->add( 'hidden', 'cancelURL', $url );
            $sess->pushUserContext( $url );
        }

        $term = self::getTerm( $term );

        $classInfo = self::getClassCount( $grade, false, $term );
        self::getCurrentClasses( $childID, $classInfo, $term );

        $activities = self::getActivities( $grade, $classInfo, true, $term );

        self::$_extendedCareElements = array( );
        self::$_registeredElements   = array( );

        foreach ( $activities as $day => $dayValues ) {
            foreach ( $dayValues as $session => $values ) {
                if ( ! empty( $values['select'] ) ) {
                    $time = self::getTime( $session );
                    $select = array( '' => '- select -' ) + $values['select'];

                    $element =& $form->addElement( 'select',
                                                   "sfschool_activity_{$day}_{$session}",
                                                   "{$day} - {$time}",
                                                   $select );

                    self::$_extendedCareElements[] = "sfschool_activity_{$day}_{$session}";
                }
            }
        }

        $form->assign_by_ref( 'extendedCareElements',
                              self::$_extendedCareElements );

        self::setDefaults( $form, $activities, $childID, $term );
    }

    static function setDefaults( &$form,
                                 &$activities,
                                 $childID,
                                 $term ) {
        $sql = "
SELECT entity_id, term, day_of_week, session, name, description, instructor, fee_block, start_date, end_date
FROM   civicrm_value_extended_care
WHERE  entity_id = %1 AND has_cancelled = 0 AND term = %2
";
        $params = array( 1 => array( $childID, 'Integer' ),
                         2 => array( $term   , 'String'  ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );

        while ( $dao->fetch( ) ) {
            $id   = self::makeID( $dao, 'Custom' );
            $name = "sfschool_activity_{$dao->day_of_week}_{$dao->session}";
            $defaults[$name] = $id;
            $form->addElement( 'checkbox', "{$name}_cancel", ts( 'Cancel this activity?' ) );

            self::$_registeredElements[] = $name;
        }

        // also freeze these form element so folks cannot change them
        $form->freeze( self::$_registeredElements );
        $form->setDefaults( $defaults );
    }

    static function &getActivities( $grade, &$classInfo, $is_active = true, $term = null ) {
        static $_all = array( );

        if ( empty( $grade ) ) {
            $grade = 'ALL';
        }
        $gradeStr .= $is_active ? "_1" : "_0";

        if ( array_key_exists( $gradeStr, $_all ) ) {
            return $_all[$gradeStr];
        }
        $_all[$gradeStr] = array( );

        $term       =  self::getTerm( $term );

        $sql = "
SELECT *
FROM   sfschool_extended_care_source
WHERE  term  = %1
";
        if( $is_active ) {
            $sql .= " AND  is_active = 1";
        } else {
            $sql .= " AND  is_active = 0";
        }

        if ( ! CRM_Core_Permission::check( 'access CiviCRM' ) ) {
            $sql .= " AND is_hidden = 0";
        }

        $params = array( 1 => array( $term , 'String'  ) );

        if ( is_numeric( $grade ) ) {
            $sql .= "
AND    %2 >= min_grade
AND    %2 <= max_grade
";
            $params[2] = array( $grade, 'Integer' );
        }

        $daysOfWeek =& self::daysOfWeek( );
        $sessions   =& self::sessions( );

        foreach ( $daysOfWeek as $day )  {
            $_all[$gradeStr][$day] = array( );
            foreach ( $sessions as $session ) {
                $_all[$gradeStr][$day][$session] = array( 'select'  => array( ),
                                                          'details' => array( ) );
            }
        }

        $errors = array( );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        while ( $dao->fetch( ) ) {
            $id = self::makeID( $dao, 'Source' );

            if ( $classInfo &&
                 array_key_exists( $id, $classInfo ) ) {
                // check if the person is not enrolled and the class is full
                if ( ! $classInfo[$id]['enrolled'] &&
                     $classInfo[$id]['max'] > 0 &&
                     $classInfo[$id]['current'] >= $classInfo[$id]['max'] ) {
                    continue;
                }
            }

            $title = $dao->name;

            if ( ! empty( $dao->instructor ) ) {
                $title .= " w/{$dao->instructor}";
            }

            if ( $dao->fee > 1 ) {
                $title .= " - {$dao->fee} activity blocks";
            }

            if ( strstr($dao->url, 'http:') || strstr($dao->url, 'https:') ) {
                $url = $dao->url;
            } else if ( $dao->url ) {
                $urlParts = explode(';;', $dao->url);
                $url = CRM_Utils_System::url( $urlParts[0], $urlParts[1] );
            } else {
                $url = null;
            }

            $_all[$gradeStr][$dao->day_of_week][$dao->session]['select'][$id]  = $title;
            $_all[$gradeStr][$dao->day_of_week][$dao->session]['details'][$id] =
                array( 'id'               => $id,
                       'title'            => $title,
                       'name'             => $dao->name,
                       'term'             => $dao->term,
                       'day'              => $dao->day_of_week,
                       'session'          => $dao->session,
                       'name'             => $dao->name,
                       'instructor'       => $dao->instructor,
                       'max_participants' => $dao->max_participants,
                       'fee_block'        => $dao->fee_block,
                       'total_fee_block'  => $dao->total_fee_block,
                       'start_date'       => $dao->start_date,
                       'end_date'         => $dao->end_date,
                       'min_grade'        => $dao->min_grade,
                       'max_grade'        => $dao->max_grade,
                       'url'              => $url,
                       'location'         => $dao->location,
                       'index'            => $dao->id
                       );

        }

        return $_all[$gradeStr];
    }

    static function &daysOfWeek( ) {
        static $_daysOfWeek    = null;
        if ( ! $_daysOfWeek ) {
            $_daysOfWeek = array( 'Monday', 'Tuesday',
                                  'Wednesday', 'Thursday',
                                  'Friday' );
        }
        return $_daysOfWeek;
    }

    static function getTerm( $term = null ) {
        static $_term = null;
        if ( $term !== null ) {
            $_term = $term;
        }

        if ( $_term === null ) {
            $_term = defined( 'SFSCHOOL_TERM' ) ? SFSCHOOL_TERM : self::TERM;
        }
        return $_term;
    }

    static function &sessions( ) {
        static $_sessions = null;
        if ( $_sessions === null ) {
            $_sessions = array( 'Morning','First', 'Second' );
        }
        return $_sessions;
    }

    static function makeID( &$dao, $class = 'Source' ) {
        $id = $class == 'Source'
            ? "{$dao->day_of_week}_{$dao->session}_{$dao->name}"
            : "{$dao->day_of_week}_{$dao->session}_{$dao->name}";

        return preg_replace( '/\s+|\W+/', '_',
                             trim( $id ) );
    }


    function postProcess( $class, &$form, $gid, $term ) {
        $excare = CRM_Utils_Request::retrieve( 'excare', 'Integer', $form, false, null, $_REQUEST );
        if ( $excare != 1 ) {
            return;
        }

        $childID   = $form->getVar( '_id' );

        if ( empty( $childID ) ||
             ! CRM_Utils_Rule::positiveInteger( $childID ) ) {
            return;
        }

        $params = $form->controller->exportValues( $form->getVar( '_name' ) );

        $daysOfWeek =& self::daysOfWeek( );
        $sessions   =& self::sessions( );

        $classSignedUpFor = array( );
        $classCancelled   = array( );

        foreach ( $daysOfWeek as $day )  {
            foreach ( $sessions as $session ) {
                $name = "sfschool_activity_{$day}_{$session}";
                if ( ! empty( $params["{$name}_cancel"] ) ) {
                    if ( ! array_key_exists( $day, $classCancelled ) ) {
                        $classCancelled[$day] = array( );
                    }
                    $classCancelled[$day][$session] = $params[$name];
                    continue;
                }
                if ( ! in_array( $name, self::$_registeredElements ) &&
                     ! empty( $params[$name] ) ) {
                    if ( ! array_key_exists( $day, $classSignedUpFor ) ) {
                        $classSignedUpFor[$day] = array( );
                    }
                    $classSignedUpFor[$day][$session] = $params[$name];
                }
            }
        }

        if ( empty( $classSignedUpFor ) && empty( $classCancelled ) ) {
            return;
        }

        require_once 'SFS/Utils/Query.php';
        $grade  = SFS_Utils_Query::getGrade( $childID );
        if ( ! is_numeric( $grade ) ) {
            return;
        }

        $classInfo = self::getClassCount( $grade, false, $term );
        self::getCurrentClasses( $childID, $classInfo, $term );

        $activities = self::getActivities( $grade, $classInfo, true, $term );

        $templateVars = array( 'term'             => $term,
                               'classCancelled'   => array( ),
                               'classSignedUpFor' => array( ) );

        // first deal with all cancelled classes
        if ( ! empty( $classCancelled ) ) {
            foreach ( $classCancelled as $day => $dayValues ) {
                foreach( $dayValues as $session => $classID ) {
                    if ( array_key_exists( $classID, $activities[$day][$session]['details'] ) ) {
                        self::postProcessClass( $childID,
                                                $activities[$day][$session]['details'][$classID],
                                                'Cancelled' );
                        $templateVars['classCancelled'][$classID] = $activities[$day][$session]['details'][$classID];
                    } else {
                        CRM_Core_Error::fatal( $classID );
                    }
                }
            }
        }

        if ( ! empty( $classSignedUpFor ) ) {
            foreach ( $classSignedUpFor as $day => $dayValues ) {
                foreach( $dayValues as $session => $classID ) {
                    if ( array_key_exists( $classID, $activities[$day][$session]['details'] ) ) {
                        self::postProcessClass( $childID,
                                                $activities[$day][$session]['details'][$classID],
                                                'Added' );
                        $templateVars['classSignedUpFor'][$classID] = $activities[$day][$session]['details'][$classID];
                    } else {
                        CRM_Core_Error::fatal( $classID );
                    }
                }
            }
        }

        self::sendExtendedCareEmail( $childID, $templateVars );
    }

    static function sendExtendedCareEmail( $childID, $templateVars = array( ) ) {
        if ( ! array_key_exists( 'classSignedUpFor', $templateVars ) ) {
            $templateVars['classSignedUpFor'] = $templateVars['classCancelled'] = array( );
        }

        // get all the class enrolled by the child
        $values = array( );
        self::getValues( $childID, $values );
        $templateVars['classEnrolled'] = CRM_Utils_Array::value( 'extendedCare', $values[$childID] );

        $templateVars['extendedCareCoordinatorName' ] = self::COORDINATOR_NAME ;
        $templateVars['extendedCareCoordinatorEmail'] = self::COORDINATOR_EMAIL;

        // now send a message to the parents about what they did
        require_once 'SFS/Utils/Mail.php';
        SFS_Utils_Mail::sendMailToParents( $childID,
                                           'SFS/Mail/ExtendedCare/Subject.tpl',
                                           'SFS/Mail/ExtendedCare/Message.tpl',
                                           $templateVars,
                                           self::COORDINATOR_EMAIL );
    }

    static function postProcessClass( $childID,
                                      $classValues,
                                      $operation = 'Added' ) {

        $startDate = CRM_Utils_Date::isoToMysql( $classValues['start_date'] );
        $rightNow  = CRM_Utils_Date::getToday( null, 'YmdHis' );

        if ( $operation == 'Added' ) {
            $query = "
INSERT INTO civicrm_value_extended_care
( entity_id, term, name, description, instructor, day_of_week, session, fee_block, start_date, end_date, has_cancelled )
VALUES
( %1, %2, %3, %4, %5, %6, %7, %8, %9, %10, 0 )
";

            $useStart = ( $startDate > $rightNow ) ? $startDate : $rightNow;

            // fix it if null value
            $instructor = CRM_Utils_Array::value( 'instructor', $classValues, '' );
            $instructor = $instructor ? $instructor : '';

            $description = CRM_Utils_Array::value( 'description', $classValues, '' );
            $description = $description ? $description : '';
            $params = array( 1  => array( $childID, 'Integer' ),
                             2  => array( $classValues['term'], 'String' ),
                             3  => array( $classValues['name'], 'String' ),
                             4  => array( $description, 'String' ),
                             5  => array( $instructor, 'String' ),
                             6  => array( $classValues['day'], 'String' ),
                             7  => array( $classValues['session'], 'String' ),
                             8  => array( $classValues['fee_block'], 'Float' ),
                             9  => array( $useStart, 'Timestamp' ),
                             10 => array( CRM_Utils_Date::isoToMysql( $classValues['end_date'] ),
                                          'Timestamp' ) );
        } else if ( $operation == 'Cancelled' ) {
            // check if the class has already started, if so cancel it
            // else delete it

            if ( $startDate > $rightNow ) {
                $query = "
DELETE
FROM   civicrm_value_extended_care
WHERE  entity_id   = %1
AND    term        = %2
AND    name        = %3
AND    day_of_week = %4
AND    session     = %5
";
            } else {
                $query = "
UPDATE civicrm_value_extended_care
SET    end_date = %6, has_cancelled = 1
WHERE  entity_id     = %1
AND    term          = %2
AND    name          = %3
AND    day_of_week   = %4
AND    session       = %5
AND    has_cancelled = 0
";
            }
            $params = array( 1  => array( $childID, 'Integer' ),
                             2  => array( $classValues['term'], 'String' ),
                             3  => array( $classValues['name'], 'String' ),
                             4  => array( $classValues['day'], 'String' ),
                             5  => array( $classValues['session'], 'String' ),
                             6  => array( CRM_Utils_Date::getToday( null, 'YmdHis' ), 'Timestamp' ) );
        } else {
            CRM_Core_Error::fatal( );
        }
        CRM_Core_DAO::executeQuery( $query, $params );
    }

    static function getValues( $childrenIDs, &$values, $parentID = null, $term = null ) {
        if ( empty( $childrenIDs ) ) {
            return;
        }

        $single = false;
        if ( ! is_array( $childrenIDs ) ) {
            $childrenIDs = array( $childrenIDs );
            $single = true;
        }

        $childrenIDString = implode( ',', array_values( $childrenIDs ) );
        $term = self::getTerm( $term );

        $query = "
SELECT    c.id as contact_id, e.term, e.name, e.description,
          e.instructor, e.day_of_week, e.session, e.fee_block,
          e.start_date, e.end_date, s.grade
FROM      civicrm_contact c
LEFT JOIN civicrm_value_extended_care e ON ( c.id = e.entity_id AND term = %1 AND has_cancelled = 0 )
LEFT JOIN civicrm_value_school_information s ON c.id = s.entity_id
WHERE     c.id IN ($childrenIDString)
AND       s.subtype = %2
ORDER BY  c.id, e.day_of_week, e.session
";
        $params = array( 1 => array( $term    , 'String' ),
                         2 => array( 'Student', 'String' ) );
        $dao = CRM_Core_DAO::executeQuery( $query, $params );

        while ( $dao->fetch( ) ) {
            if ( ! is_numeric( $dao->grade ) ) {
                continue;
            }

            // check if there is any data for extended care
            if ( $dao->name ) {
                if ( ! $values[$dao->contact_id]['extendedCareDay'] ) {
                    $values[$dao->contact_id]['extendedCare']    = array( );
                    $values[$dao->contact_id]['extendedCareDay'] = array( );
                }

                if ( ! isset( $values[$dao->contact_id]['extendedCareDay'][$dao->day_of_week] ) ) {
                    $values[$dao->contact_id]['extendedCareDay'][$dao->day_of_week] = array( );
                }

                $time = self::getTime( $dao->session );
                $title = "{$dao->day_of_week} $time";
                $title .= " : {$dao->name}";
                if ( $dao->instructor ) {
                    $title .= " w/{$dao->instructor}";
                }

                $values[$dao->contact_id]['extendedCareDay'][$dao->day_of_week][] =
                    array( 'day'  => $dao->day_of_week,
                           'time' => $time,
                           'name' => $dao->name,
                           'desc' => $dao->description,
                           'instructor' => $dao->instructor,
                           'title' => $title );
            }
        }

        $daysOfWeek =& self::daysOfWeek( );
        foreach ( $values as $contactID => $value ) {
            foreach ( $daysOfWeek as $day )  {
                if ( ! empty( $values[$contactID]['extendedCareDay'][$day] ) ) {
                    $values[$contactID]['extendedCare'] =
                        array_merge( $values[$contactID]['extendedCare'],
                                     $values[$contactID]['extendedCareDay'][$day] );
                }
            }
            unset( $values[$contactID]['extendedCareDay'] );
            if ( is_numeric( $values[$contactID]['grade'] ) ) {
                $parent = null;
                if ( $parentID ) {
                    $parent = "&parentID=$parentID";
                }
                $values[$contactID]['extendedCareEdit'] =
                    CRM_Utils_System::url( 'civicrm/profile/edit', "reset=1&gid=4&id={$contactID}&excare=1&$parent" );
                $values[$contactID]['extendedCareView'] =
                    CRM_Utils_System::url( 'civicrm/sfschool/extendedCare', "reset=1&id={$contactID}" );
            }
        }

    }

    static function &getClassCount( $grade, $all = false, $term = null ) {
        $term = self::getTerm( $term );

        $sql = "
SELECT     count(entity_id) as current, s.max_participants as max, s.term, s.day_of_week, s.session, s.name
FROM       civicrm_value_extended_care e
INNER JOIN sfschool_extended_care_source s ON ( s.term = e.term AND s.day_of_week = e.day_of_week AND s.session = e.session AND s.name = e.name )
WHERE      e.has_cancelled = 0
AND        s.term = %1
";
        $params = array( 1 => array( $term, 'String' ) );
        if ( $grade ) {
            $params[2] = array( $grade, 'Integer' );
            $sql .= "
AND %2 >= s.min_grade
AND %2 <= s.max_grade
";
        }
        if ( ! $all ) {
            $sql .= " AND s.is_active = 1";
        }

        $sql .= " GROUP BY term, day_of_week, session, name";

        $values = array( );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        while ( $dao->fetch( ) ) {
            $id = self::makeID( $dao, 'Custom' );
           $values[$id] = array( 'current'   => $dao->current,
                                  'max'      => $dao->max,
                                  'enrolled' => 0 );
        }

        return $values;
    }

    static function getCurrentClasses( $childID, &$values, $term = null ) {
        $term = self::getTerm( $term );

        $sql = "
SELECT entity_id, term, day_of_week, session, name
FROM   civicrm_value_extended_care
WHERE  entity_id = %1 AND has_cancelled = 0 AND term = %2
";
        $params = array( 1 => array( $childID, 'Integer' ),
                         2 => array( $term   , 'String'  ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );

        while ( $dao->fetch( ) ) {
            $id = self::makeID( $dao, 'Custom' );

            if ( ! array_key_exists( $id, $values ) ) {
                CRM_Core_Error::fatal( $id );
            }
            $values[$id]['enrolled'] = 1;
        }
    }

    function sortDetails( &$details ) {
        foreach ( $details as $childID => $detail ) {
            self::sortDetail( $details, $childID );
        }
    }

    function sortDetail( &$details, $childID ) {
        $yesDetail = $noDetail = array( );

        $daysOfWeek =& self::daysOfWeek( );
        $sessions   =& self::sessions( );

        foreach ( $daysOfWeek as $day ) {
            $yesDetail[$day] = array( );
            $noDetail[$day]  = array( );
            foreach ( $sessions as $session ) {
                $yesDetail[$day][$session] = array( );
                $noDetail[$day][$session]  = array( );
            }
        }


        foreach ( $details[$childID] as $id => &$values ) {
            $day     = $values['fields'][10]['field_value'];
            $session = $values['fields'][11]['field_value'];
            $yesno   = trim( $values['fields'][13]['field_value'] );

            if ( $yesno == 'Yes' ) {
                $yesDetail[$day][$session][] = $values;
            } else {
                $noDetail[$day][$session][] = $values;
            }
        }

        $newDetail = array( );

        foreach ( $noDetail as $day => $detailValues ) {
            foreach ( $detailValues as $session =>& $values ) {
                foreach ( $values as $value ) {
                    $newDetail[] = $value;
                }
            }
        }

        foreach ( $yesDetail as $day => $detailValues ) {
            foreach ( $detailValues as $session =>& $values ) {
                foreach ( $values as $value ) {
                    $newDetail[] = $value;
                }
            }
        }

        $details[$childID] = $newDetail;
    }

    static function getTime( $session ) {
        switch ( $session ) {
        case 'First':
            return '3:30 pm - 4:30 pm';
        case 'Second':
            return '4:30 pm - 5:30 pm';
        case 'Morning':
            return '7:30 am - 8:30 am';
        }
    }

    static function processSignOut( $pickupName,
                                    $studentID,
                                    $atSchoolMeeting = false ) {
        static $_now  = null;
        static $_date = null;

        if ( ! $_now ) {
            $_now = CRM_Utils_Date::getToday( null, 'YmdHis' );
        }

        if ( ! $_date ) {
            $_date = CRM_Utils_Date::getToday( null, 'Y-m-d' );
        }

        if ( $atSchoolMeeting === 'true' ) {
            $atSchoolMeeting = 1;
        } else if ( $atSchoolMeeting === 'false' ) {
            $atSchoolMeeting = 0;
        }

        $atSchoolMeeting = $atSchoolMeeting ? '1' : '0';

        $sql = "
SELECT e.id, e.class, s.location
FROM   civicrm_value_extended_care_signout e
LEFT JOIN sfschool_extended_care_source s ON ( e.class = s.name AND  s.day_of_week = DAYNAME( '{$_date}' ) )
WHERE  entity_id = %1
AND    signin_time LIKE '{$_date}%'
AND    ( is_morning = 0 OR is_morning IS NULL )
";
        $params = array( 1 => array( $studentID, 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );

        $params = array( 1 => array( $studentID      , 'Integer'   ),
                         2 => array( $pickupName     , 'String'    ),
                         3 => array( $_now           , 'Timestamp' ),
                         4 => array( $atSchoolMeeting, 'Integer'   ) );

        $class = null;
        if ( $dao->fetch( ) ) {
            if ( $dao->location ) {
                $class = "{$dao->class} ({$dao->location})" ;
            } else {
                $class = $dao->class;
            }

            $sql = "
UPDATE civicrm_value_extended_care_signout
SET    pickup_person_name = %2,
       signout_time       = %3,
       at_school_meeting  = %4
WHERE  id = %5
";
            $params[5] = array( $dao->id, 'Integer' );
        } else {
            $sql = "
INSERT INTO civicrm_value_extended_care_signout
( entity_id, pickup_person_name, signin_time, signout_time, at_school_meeting, is_morning )
VALUES
( %1, %2, %3, %3, %4, 0 )
";
        }

        CRM_Core_DAO::executeQuery( $sql, $params );
        return $class;
    }

    static function &signoutDetails( $startDate        = null,
                                     $endDate          = null,
                                     $isMorning        = true,
                                     $includeDetails   = false,
                                     $onlyNotSignedOut = false,
                                     $studentID        = null,
                                     $limit            = null
                                     ) {

        $clauses = array( );

        if( $startDate && $endDate ) {
             $clauses[] = "( DATE(s.signout_time) >= $startDate OR DATE(s.signin_time) >= $startDate ) AND
                           ( DATE(s.signout_time) <= $endDate   OR DATE(s.signin_time) <= $endDate   ) ";
        }

        if ( ! $isMorning ) {
            $clauses[] = "( is_morning = 0 OR is_morning IS NULL )";
        }

        if ( $onlyNotSignedOut ) {
            $clauses[] = "( s.signout_time IS NULL )";
        }

        if ( $studentID ) {
            $studentID = CRM_Utils_Type::escape( $studentID, 'Integer' );
            $clauses[] = "c.id = $studentID";
        }

        $clauses[] = "v.is_currently_enrolled = 1";

        $clause = implode( ' AND ', $clauses );

        $sql = "
SELECT     c.id, c.display_name, c.first_name, c.last_name, v.grade_sis,
           s.signout_time, s.signin_time,
           s.class, s.pickup_person_name,
           s.is_morning, s.at_school_meeting,
           v.extended_care_status_2011 as extended_care_status, s.id as signout_id
FROM       civicrm_value_extended_care_signout s
INNER JOIN civicrm_contact c ON c.id = s.entity_id
INNER JOIN civicrm_value_school_information v ON c.id = v.entity_id
WHERE      $clause
ORDER BY   c.sort_name, signin_time DESC
";
        if( $limit ) {
            $sql .= " LIMIT 0, {$limit} ";
        }

        $dao = CRM_Core_DAO::executeQuery( $sql );

        $freeClasses = array( 'Volleyball', 'Cross Country', 'Amnesty International',
                              'SMART', 'Yearbook', 'Futsol Practice', 'Futsol',
                              'Basketball Team 3:30-5:00 p.m.', 'Band', 'Basketball/MS',
                              'Counselor in Training',
                              'Middle School Basketball', 'Shakespeare', 'World Music', 'MS Basketball',
                              'Junior Counselor/Preschool', 'Junior Counselor/Creative Kitchen',
                              'Junior Counselor/Creative Kitchen, Too', 'Junior Counselor/Comics and Cartooning',
                              'Junior Counselor', 'Junior Counselor Preschool',
                              'Futsal', 'Math Olympiad' );
        $freeStatus  = array( 'SMART', 'Staff', 'Unlimited');

        $summary = array( );
        while ( $dao->fetch( ) ) {
            $dao->class = trim( $dao->class );
            $studentID = $dao->id;
            if ( ! array_key_exists( $studentID, $summary ) ) {
                $summary[$studentID] = array( 'id'           => $studentID,
                                              'name'         => $dao->display_name,
                                              'first'        => $dao->first_name,
                                              'last'         => $dao->last_name,
                                              'grade'        => $dao->grade_sis,
                                              'blockCharge'  => 0,
                                              'doNotCharge'  => null );
                if ( $includeDetails ) {
                    $summary[$studentID]['details'] = array( );
                }
            }

            $blockCharge  = 0;
            $blockMessage = null;
            if ( $dao->is_morning ) {
                $blockMessage = 'Morning extended care';
                if ($dao->at_school_meeting ) {
                    $blockMessage = 'At School Meeting / Work - No Charge';
                } else if ( self::chargeMorningBlock( $dao->signin_time ) ) {
                    $blockCharge  = 0.5;
                }
                $dao->signout_time = $dao->signin_time;
            } else if ( $dao->at_school_meeting ) {
                $blockMessage = 'At School Meeting / Work - No Charge';
            } else if ( in_array( $dao->class, $freeClasses ) ) {
                $blockMessage = 'Free Class - No Charge';
                if ( ! $dao->signout_time ) {
                    $dao->signout_time = $dao->signin_time;
                }
            } else {
                    if ( $dao->signout_time ) {
                        $blockCode = self::signoutBlock( $dao->signout_time );
                        switch ( $blockCode ) {
                        case 1:
                            break;
                        case 2:
                            $blockCharge = 1;
                            break;
                        case 3:
                            $blockCharge = 1.5;
                            break;
                        case 4:
                        default:
                            $blockCharge = 2.0;
                            break;
                        }
                    } else {
                        // account for the case where the person is signed in but not signed out
                        if ( $dao->signin_time ) {
                            $blockCharge  = 2.0;
                            $dao->signout_time = $dao->signin_time;
                            $blockMessage = 'Signed in but did not sign out';
                        }
                }
            }

            $summary[$studentID]['blockCharge'] += $blockCharge;
            if ( in_array( $dao->extended_care_status, $freeStatus ) ) {
                $summary[$studentID]['doNotCharge'] = $dao->extended_care_status;
            }

            if ( $includeDetails ) {
                $summary[$studentID]['details'][$dao->signout_id] = array( 'charge'  => $blockCharge,
                                                                           'message' => $blockMessage,
                                                                           'class'   => $dao->class,
                                                                           'pickup'  => $dao->pickup_person_name,
                                                                           'signout' => strftime( "%l:%M %p on %a, %b %d",
                                                                                                  CRM_Utils_Date::unixTime( $dao->signout_time ) ) );
            }
        }

        return $summary;
    }

    static function getMonthlySignoutCount( $startDate, $endDate, $studentID = null ) {
        $addClause = "";
        if( $studentID ) {
           $addClause = " AND  c.id={$studentID}";
        }
        $signoutActivites = array( );
        $sql = "
SELECT     COUNT(s.signin_time) as count,
           c.id as contactId ,
           DATE_FORMAT(s.signin_time ,'%b- %Y') as monthName,
           DATE_FORMAT(s.signin_time, '%Y') as year,
           DATE_FORMAT(s.signin_time, '%m') as month
FROM       civicrm_value_extended_care_signout s
INNER JOIN civicrm_contact c ON c.id = s.entity_id
INNER JOIN civicrm_value_school_information v ON c.id = v.entity_id
WHERE      ( DATE(s.signout_time) >= $startDate OR DATE(s.signin_time) >= $startDate )
AND        ( DATE(s.signout_time) <= $endDate   OR DATE(s.signin_time) <= $endDate   )
           $addClause
GROUP BY  YEAR(s.signin_time), MONTH(s.signin_time) ORDER BY s.signin_time DESC";

        $dao = CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch() ) {
            $signoutActivites[$dao->contactId][$dao->monthName] = array( 'count'      => $dao->count,
                                                                         'year'       => $dao->year,
                                                                         'month'      => $dao->month
                                                                         );

        }
        return $signoutActivites;
    }

    static function signoutDetailsPerMonth( $startDate, $endDate, $studentID = null ) {
        // always do per academic year
        // which goes from Sept (09) - June (06)
        $currentYear  = date( 'Y' );
        $m = $currentMonth = date( 'm' );

        $dateRange = array( );

        if ( $m >= 9 ) {
            for ( $i = 9 ; $i <= $m ; $i++ ) {
                $mon = ( $i == 9 ) ? '09' : $i;
                $end = self::getDaysInMonth( $i, $currentYear );
                $dateRange[] = array( 'start' => "{$currentYear}{$mon}01",
                                      'end'   => "{$currentYear}{$mon}{$end}",
                                      'year'  => $currentYear,
                                      'mon'   => $mon );
            }
        } else {
            $startYear  = $currentYear - 1;
            for ( $i = 9 ; $i <= 12 ; $i++ ) {
                $mon = ( $i == 9 ) ? '09' : $i;
                $end = self::getDaysInMonth( $i, $startYear );
                $dateRange[] = array( 'start' => "{$startYear}{$mon}01",
                                      'end'   => "{$startYear}{$mon}{$end}",
                                      'year'  => $startYear,
                                      'mon'   => $mon );
            }
            $nextYear = $currentYear + 1;
            for ( $i = 1 ; $i <= $m ; $i++ ) {
                $mon = "0{$i}";
                $end = self::getDaysInMonth( $i, $nextYear );
                $dateRange[] = array( 'start' => "{$currentYear}{$mon}01",
                                      'end'   => "{$currentYear}{$mon}{$end}",
                                      'year'  => $currentYear,
                                      'mon'   => $mon );
            }
        }

        // also include all of 2010-2011 school year
        /***
        $prevYear = '2010';
        $nextYear = '2011';
        for ( $i = 6 ; $i >= 1 ; $i-- ) {
            $mon = "0{$i}";
            $end = self::getDaysInMonth( $i, $nextYear );
            $dateRange[] = array( 'start' => "{$nextYear}{$mon}01",
                                  'end'   => "{$nextYear}{$mon}{$end}",
                                  'year'  => $nextYear,
                                  'mon'   => $mon );
        }
        for ( $i = 12; $i >= 9; $i-- ) {
            $mon = ( $i == 9 ) ? '09' : $i;
            $end = self::getDaysInMonth( $i, $prevYear );
            $dateRange[] = array( 'start' => "{$prevYear}{$mon}01",
                                  'end'   => "{$prevYear}{$mon}{$end}",
                                  'year'  => $prevYear,
                                  'mon'   => $mon );
        }
        **/
        // $dateRange = array_reverse( $dateRange );

        $monthNames = CRM_Utils_Date::getAbbrMonthNames( );
        $result = array( );
        foreach ( $dateRange as $date ) {
            $d = self::signoutDetails( $date['start'],
                                       $date['end'],
                                       true, false, false, $studentID );
            if ( ! empty( $d ) &&
                 isset( $d[$studentID] ) &&
                 $d[$studentID]['blockCharge'] > 0 &&
                 empty( $d[$studentID]['doNotCharge'] ) ) {
                $mon = (int ) $date['mon'];
                $yearMon = "{$date['year']} - {$monthNames[$mon]}";
                $monYear = "{$monthNames[$mon]} - {$date['year']}";
                $term = ( $mon >= 9 ) ? 'Fall' : 'Spring';
                $result[$yearMon] = array( 'blockCharge' => $d[$studentID]['blockCharge'],
                                           'description' => "{$term} {$date['year']} - $monYear",
                                           'year'        => $date['year'],
                                           'month'       => $date['mon'],
                                           );
            }
        }
        return $result;
    }

    static function getDaysInMonth( $month, $year ) {
        $daysInMonth = array( 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );

        if ( $month == 2 ) {
            return ( $year % 400 == 0 ||
                     ( $year % 4 == 0 && $year % 100 != 0 ) ) ? 29 : 28;
        }

        if ( $month < 1 ||
             $month > 12 ) {
            CRM_Core_Error::fatal( );
        }

        return $daysInMonth[$month - 1];
    }

    function sendNotSignedOutEmail( $startDate, $endDate ) {
        $templateVars = array( );
        $templateVars['extendedCareCoordinatorName' ] = self::COORDINATOR_NAME ;
        $templateVars['extendedCareCoordinatorEmail'] = self::COORDINATOR_EMAIL;

        $sql = "
SELECT entity_id, signin_time
FROM   civicrm_value_extended_care_signout
WHERE  signout_time IS NULL
AND    DATE(signin_time ) >= %1
AND    DATE(signin_time ) <= %2
ORDER BY entity_id
";

        $params = array( 1 => array( $startDate, 'String' ),
                         2 => array( $endDate  , 'String' ) );

        require_once 'SFS/Utils/Mail.php';

        $currentEntityID = null;
        $days = array( );

        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        while ( $dao->fetch( ) ) {
            if ( $dao->entity_id != $currentEntityID &&
                 $currentEntityID != null &&
                 ! empty( $days ) ) {
                $templateVars['days'] = implode( "\n", $days );

                // now send a message to the parents about what they did
                SFS_Utils_Mail::sendMailToParents( $currentEntityID,
                                                   'SFS/Mail/ExtendedCare/NotSignedOutSubject.tpl',
                                                   'SFS/Mail/ExtendedCare/NotSignedOutMessage.tpl',
                                                   $templateVars,
                                                   self::COORDINATOR_EMAIL );
                $days = array( );
            }

            $currentEntityID = $dao->entity_id;
            $days[] = CRM_Utils_Date::customFormat( $dao->signin_time, "%b %E%f" );
        }

    }

    static function chargeMorningBlock( $time ) {
        if ( empty( $time ) ) {
            return false;
        }

        require_once 'SFS/Utils/Date.php';
        $dateParts = SFS_Utils_Date::unformat( $time );

        if ( (int ) $dateParts['H'] <= 7 && (int ) $dateParts['i'] <= 55 ) {
            return true;
        }

        return false;
    }

    static function signoutBlock( $time ) {
        if ( empty( $time ) ) {
            return null;
        }

        require_once 'SFS/Utils/Date.php';
        $dateParts = SFS_Utils_Date::unformat( $time );

        if ( $dateParts['H'] < 15 ||
             ( $dateParts['H'] == 15 && $dateParts['i'] <= 35 ) ) {
            return 1;
        }

        if ( $dateParts['H'] == 15 ||
             ( $dateParts['H'] == 16 && $dateParts['i'] <= 35 ) ) {
            return 2;
        }

        if ( $dateParts['H'] == 16 ||
             ( $dateParts['H'] == 17 && $dateParts['i'] <= 20 ) ) {
            return 3;
        }

        if ( $dateParts['H'] == 17 ||
             ( $dateParts['H'] == 18 && $dateParts['i'] <= 15 ) ) {
            return 4;
        }

        return 5;
    }

    static function &balanceDetails( $studentID = null,
                                     $startDate = null,
                                     $endDate   = null ) {

        if ( $startDate == null ||
             $endDate == null ) {
            // always do per academic year
            // which goes from Sept (09) - June (06)
            $currentYear  = date( 'Y' );

            $startYear = $endYear = $currentYear;
            $startMonth = '09';

            $m = $currentMonth = date( 'm' );
            if ( $m < 9 ) {
                $startYear--;
            }

            // $startDate = "{$startYear}{$startMonth}01";
            $startDate = "20110901";
            $endDate   = date( 'Ymd' );
            // $endDate   = "20100831";
        }

        // first get all the dynamic charges
        $dynamicDetails =& self::signoutDetails( $startDate,
                                                 $endDate,
                                                 true,
                                                 false,
                                                 false,
                                                 $studentID );

        require_once 'SFS/Utils/ExtendedCareFees.php';
        $feeDetails = SFS_Utils_ExtendedCareFees::feeDetails( $startDate,
                                                              $endDate  ,
                                                              null      ,
                                                              false     ,
                                                              false     ,
                                                              $studentID );


        $completeDetails = CRM_Utils_Array::crmArrayMerge( $dynamicDetails, $feeDetails );

        foreach ( $completeDetails as $id =>& $value ) {
            if ( ! empty( $value['doNotCharge'] ) ) {
                $value['blockCharge'] = 0;
            }

            $value['totalCharges']  =
                CRM_Utils_Array::value( 'blockCharge', $value, 0 ) + CRM_Utils_Array::value( 'charges', $value, 0 );
            $value['blockCharges'] =
                CRM_Utils_Array::value( 'blockCharge', $value, 0 ) + CRM_Utils_Array::value( 'ecCharges', $value, 0 );
            $value['totalPayments'] =
                CRM_Utils_Array::value( 'payments', $value, 0 ) + CRM_Utils_Array::value( 'refunds', $value, 0 );

            if ( $value['totalCharges']  == 0 &&
                 $value['totalPayments'] == 0 ) {
                unset( $completeDetails[$id] );
                continue;
            }

            if ( $value['totalCharges'] >= $value['totalPayments'] ) {
                $value['balanceDue'   ] = $value['totalCharges'] - $value['totalPayments'];
                $value['balanceCredit'] = 0;
            } else {
                $value['balanceDue'] = 0;
                $value['balanceCredit'] = $value['totalPayments'] - $value['totalCharges'];
            }
        }

        return $completeDetails;
    }

    static function sendBalanceInvoiceEmail( $cutoff = 10 ) {
        $details = self::balanceDetails( );

        require_once 'SFS/Utils/Mail.php';
        foreach ( $details as $id =>& $value ) {
            if ( $value['balanceDue'] < $cutoff ) {
                continue;
            }

            // set params not set to 0
            $varsToCheck = array( 'payments', 'charges', 'ecCharges', 'classCharges', 'refunds' );
            foreach ( $varsToCheck as $var ) {
              if ( ! array_key_exists( $var, $value ) ) {
                $value[$var] = 0;
              }
            }

            // now send a message to the parents about what they did
            SFS_Utils_Mail::sendMailToParents( $id,
                                               'SFS/Mail/ExtendedCare/InvoiceSubject.tpl',
                                               'SFS/Mail/ExtendedCare/InvoiceMessage.tpl',
                                               $value,
                                               null );

        }
    }

}
