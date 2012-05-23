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

class SCH_Utils_Conference {
    const
        ADVISOR_RELATIONSHIP_TYPE_ID = 10,
        CONFERENCE_ACTIVITY_TYPE_ID  = 20,
        SUBJECT  = 'Spring 2012 Parent Teacher Conference',
        LOCATION = 'San Francisco School',
        STATUS   = 1;

    static function buildForm( &$form, $childID ) {
        $advisorID = CRM_Utils_Request::retrieve( 'advisorID', 'Integer', $form, false, null, $_REQUEST );
        $ptc       = CRM_Utils_Request::retrieve( 'ptc'      , 'Integer', $form, false, null, $_REQUEST );

        if ( empty( $advisorID ) || $ptc != 1 ) {
            return;
        }


        // add scheduling information if any
        $sql = "
SELECT     r.contact_id_b, a.id as activity_id, a.activity_date_time, a.subject, a.location, aac.display_name, aac.nick_name, aac.id as advisor_id
FROM       civicrm_activity a
INNER JOIN civicrm_activity_assignment aa ON a.id = aa.activity_id
INNER JOIN civicrm_contact            aac ON aa.assignee_contact_id = aac.id
INNER JOIN civicrm_relationship         r ON r.contact_id_a = aac.id
LEFT  JOIN civicrm_activity_target     at ON a.id = at.activity_id
WHERE      a.activity_type_id = %4
AND        r.relationship_type_id = %3
AND        r.is_active = 1
AND        r.contact_id_b = %2
AND        a.status_id = 1
AND        a.activity_date_time > ADDDATE( NOW(), 1 )
AND        ( at.target_contact_id IS NULL OR at.target_contact_id = %2 )
ORDER BY   a.activity_date_time asc
";

        $params  = array( 2 => array( $childID   , 'Integer' ),
                          3 => array( self::ADVISOR_RELATIONSHIP_TYPE_ID, 'Integer' ),
                          4 => array( self::CONFERENCE_ACTIVITY_TYPE_ID , 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        $elements = array( );
        while ( $dao->fetch( ) ) {
            $dateTime = CRM_Utils_Date::customFormat( $dao->activity_date_time,
                                                      "%l:%M %P on %b %E%f" );
            $advisorName = $dao->nick_name ? $dao->nick_name : $dao->display_name;
            $elements[$dao->activity_id] = "$dateTime w/{$advisorName}";
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

        if ( ! empty( $elements ) ) {
            $form->addElement( 'select', 'sfschool_activity_id', "Choose a Meeting time for {$dao->subject}", $elements, true );

            // get the default values
            $values = array( );
            self::getValues( $childID, $values, true, $parentID );
            if ( isset( $values[$childID] ) ) {
                $defaults = array( 'sfschool_activity_id' => $values[$childID]['meeting']['id'] );
                $form->setDefaults( $defaults );
            }
        }
    }

    static function postProcess( $class, &$form, $gid ) {
        $advisorID = CRM_Utils_Request::retrieve( 'advisorID', 'Integer', $form, false, null, $_REQUEST );
        $ptc       = CRM_Utils_Request::retrieve( 'ptc'      , 'Integer', $form, false, null, $_REQUEST );

        if ( empty( $advisorID ) || $ptc != 1 ) {
            return;
        }

        $params = $form->controller->exportValues( $form->getVar( '_name' ) );

        $activityID = CRM_Utils_Array::value( 'sfschool_activity_id', $params );
        $childID    = $form->getVar( '_id' );


        self::selectPTC( $advisorID, $childID, $activityID );
    }

    function sendConferenceEmail( $activityID, $advisorID, $childID, $dateTime = null ) {
        require_once 'SCH/Utils/Query.php';
        $templateVars = array( );
        list( $templateVars['advisorName'],
              $templateVars['advisorEmail'] ) = SCH_Utils_Query::getNameAndEmail( $advisorID );

        if ( $dateTime == null ) {
            $dateTime = CRM_Core_DAO::getFieldValue( 'CRM_Activity_DAO_Activity',
                                                     $activityID,
                                                     'activity_date_time' );
        }

        $templateVars['dateTime'] = CRM_Utils_Date::customFormat( $dateTime,
                                                                  "%l:%M %P on %b %E%f" );

        // now send a message to the parents about what they did
        require_once 'SCH/Utils/Mail.php';
        SCH_Utils_Mail::sendMailToParents( $childID,
                                           'SCH/Mail/Conference/Subject.tpl',
                                           'SCH/Mail/Conference/Message.tpl',
                                           $templateVars );
    }

    function sendNotScheduledReminderEmail( $advisorID, $childID ) {
        require_once 'SCH/Utils/Query.php';
        $templateVars = array( );
        list( $templateVars['advisorName'],
              $templateVars['advisorEmail'] ) = SCH_Utils_Query::getNameAndEmail( $advisorID );

        // now send a message to the parents about what they did
        require_once 'SCH/Utils/Mail.php';
        SCH_Utils_Mail::sendMailToParents( $childID,
                                           'SCH/Mail/Conference/NotScheduledSubject.tpl',
                                           'SCH/Mail/Conference/NotScheduledMessage.tpl',
                                           $templateVars );
    }

    function &getValues( $childrenIDs,
                         &$values,
                         $onlyScheduled = false,
                         $parentID = null ) {
        // check if we need to schedule this parent for a meeting
        // or display any future scheduled meetings
        if ( empty( $childrenIDs ) ) {
            return;
        }

        $single = false;
        if ( ! is_array( $childrenIDs ) ) {
            $childrenIDs = array( $childrenIDs );
            $single = true;
        }

        $childrenIDString = implode( ',', array_values( $childrenIDs ) );

        // find first all scheduled meetings in the future
        $sql = "
SELECT     a.id, a.activity_date_time, a.subject, a.location, r.contact_id_b,
           aac.id as advisor_id, aac.display_name as aac_display_name, aac.nick_name as aac_nick_name,
           rcb.display_name as rcb_display_name,
           s.grade_sis as grade
FROM       civicrm_activity a
INNER JOIN civicrm_activity_assignment aa ON a.id = aa.activity_id
INNER JOIN civicrm_activity_target     at ON a.id = at.activity_id
INNER JOIN civicrm_contact            aac ON aa.assignee_contact_id = aac.id
INNER JOIN civicrm_contact            aat ON at.target_contact_id   = aat.id
INNER JOIN civicrm_value_school_information s ON s.entity_id = aat.id
INNER JOIN civicrm_relationship         r ON r.contact_id_a         = aac.id
INNER JOIN civicrm_contact            rcb ON r.contact_id_b         = rcb.id
WHERE      a.activity_type_id = %2
AND        a.status_id = 1
AND        a.activity_date_time > NOW()
AND        r.relationship_type_id = %1
AND        r.is_active = 1
AND        r.contact_id_b IN ( $childrenIDString )
AND        aa.assignee_contact_id = r.contact_id_a
AND        at.target_contact_id = r.contact_id_b;
";

        $parent = null;
        if ( $parentID ) {
            $parent = "parentID=$parentID";
        }
        $params = array( 1 => array( self::ADVISOR_RELATIONSHIP_TYPE_ID, 'Integer' ),
                         2 => array( self::CONFERENCE_ACTIVITY_TYPE_ID , 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        while ( $dao->fetch( ) ) {
            $url = CRM_Utils_System::url( 'civicrm/profile/edit', "reset=1&gid=4&id={$dao->contact_id_b}&advisorID={$dao->advisor_id}&ptc=1&$parent" );
            $dateTime = CRM_Utils_Date::customFormat( $dao->activity_date_time,
                                                      "%l:%M %P on %b %E%f" );
            $advisorName = $dao->aac_nick_name ? $dao->aac_nick_name : $dao->aac_display_name;
            $values[$dao->contact_id_b]['meeting']['title'] = "Your {$dao->subject} is scheduled for $dateTime with {$advisorName}";
            if ( $dao->grade == 9 ||
                 $dao->grade == -1 ||
                 $dao->grade == 10 ||
                 $dao->grade == 11 ||
                 $dao->grade == 12 ) {
                $values[$dao->contact_id_b]['meeting']['edit']  = '';
            } else {
                $values[$dao->contact_id_b]['meeting']['edit']  = "<a href=\"{$url}\">Modify conference time for {$dao->rcb_display_name}</a>";
            }
            $values[$dao->contact_id_b]['meeting']['id']    = $dao->id;
            // FIXME when we have access to the web :)
            $newChildrenIDs = array( );
            foreach ( $childrenIDs as $childID ) {
                if ( $dao->contact_id_b != $childID ) {
                    $newChildrenIDs[] = $childID;
                }
            }
            $childrenIDs = $newChildrenIDs;
        }

        // check if other children left to schedule a meeting
        if ( $onlyScheduled ||
             empty( $childrenIDs ) ) {
            return;
        }

        $childrenIDString = implode( ',', array_values( $childrenIDs ) );

        $sql = "
SELECT     r.contact_id_b, a.subject, a.location,
           aac.display_name as aac_display_name, aac.nick_name as aac_nick_name, aac.id as advisor_id,
           rcb.display_name as rcb_display_name,
           s.grade_sis as grade
FROM       civicrm_activity a
INNER JOIN civicrm_activity_assignment aa ON a.id = aa.activity_id
INNER JOIN civicrm_contact            aac ON aa.assignee_contact_id = aac.id
INNER JOIN civicrm_relationship         r ON r.contact_id_a = aac.id
LEFT  JOIN civicrm_activity_target     at ON a.id = at.activity_id
INNER JOIN civicrm_contact            rcb ON r.contact_id_b = rcb.id
INNER JOIN civicrm_value_school_information s ON s.entity_id = rcb.id
WHERE      a.activity_type_id = %2
AND        r.relationship_type_id = %1
AND        r.is_active = 1
AND        r.contact_id_b IN ($childrenIDString)
AND        a.status_id = 1
AND        a.activity_date_time > NOW()
AND        at.target_contact_id IS NULL
GROUP BY r.contact_id_b
";

        $params = array( 1 => array( self::ADVISOR_RELATIONSHIP_TYPE_ID, 'Integer' ),
                         2 => array( self::CONFERENCE_ACTIVITY_TYPE_ID , 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        while ( $dao->fetch( ) ) {
            $url = CRM_Utils_System::url( 'civicrm/profile/edit', "reset=1&gid=4&id={$dao->contact_id_b}&advisorID={$dao->advisor_id}&ptc=1&$parent" );
            $advisorName = $dao->aac_nick_name ? $dao->aac_nick_name : $dao->aac_display_name;
            if ( $dao->grade == 9 ||
                 $dao->grade == -1 ||
                 $dao->grade == 10 ||
                 $dao->grade == 11 ||
                 $dao->grade == 12 ) {
                $values[$dao->contact_id_b]['meeting']['title'] = "<strong>Online registration is now closed. Please contact your child's head teacher ({$advisorName}) directly to schedule an Intake Conference.</strong>";
                $values[$dao->contact_id_b]['meeting']['edit'] = "";
            } else if ( $dao->grade == 9 ) {
                $values[$dao->contact_id_b]['meeting']['title'] = "<strong>Online registration closed Jan 16th. Please contact your child's advisor ({$advisorName}) directly to schedule an Intake Conference by September.</strong>";
                $values[$dao->contact_id_b]['meeting']['edit'] = "";
            } else {
                $values[$dao->contact_id_b]['meeting']['title'] = "Please schedule your {$dao->subject} with {$advisorName}. Online registraton will <strong>close Mar 20th</strong>";
                $values[$dao->contact_id_b]['meeting']['edit'] = "<a href=\"{$url}\">Schedule a conference for {$dao->rcb_display_name}</a>";
            }
        }
    }

    static function createConferenceSchedule( ) {
        require_once 'CRM/Utils/Request.php';

        // we need the admin id, teacher id, date, start time and end time
        $adminID   = CRM_Utils_Request::retrieve( 'adminID',
                                                  'Integer',
                                                  CRM_Core_DAO::$_nullObject,
                                                  true );
        $teacherID = CRM_Utils_Request::retrieve( 'teacherID',
                                                  'Integer',
                                                  CRM_Core_DAO::$_nullObject,
                                                  true );
        $date      = CRM_Utils_Request::retrieve( 'date',
                                                  'Date',
                                                  CRM_Core_DAO::$_nullObject,
                                                  true );
        $start     = CRM_Utils_Request::retrieve( 'start',
                                                  'Integer',
                                                  CRM_Core_DAO::$_nullObject,
                                                  true );
        $end       = CRM_Utils_Request::retrieve( 'end',
                                                  'Integer',
                                                  CRM_Core_DAO::$_nullObject,
                                                  true );
        $duration  = CRM_Utils_Request::retrieve( 'duration',
                                                  'Integer',
                                                  CRM_Core_DAO::$_nullObject,
                                                  true );

        // perform validation on the parameters
        require_once 'CRM/Activity/DAO/Activity.php';
        require_once 'CRM/Activity/DAO/ActivityAssignment.php';

        // create 1:10 - 1:40 slot
        self::createConference( $adminID, $teacherID,
                                self::CONFERENCE_ACTIVITY_TYPE_ID,
                                "20091117131000",
                                self::SUBJECT,
                                self::LOCATION,
                                self::STATUS,
                                $duration );

        // create 1:45 - 2:15 slot
        self::createConference( $adminID, $teacherID,
                                self::CONFERENCE_ACTIVITY_TYPE_ID,
                                "20091117134500",
                                self::SUBJECT,
                                self::LOCATION,
                                self::STATUS,
                                $duration );

        // create 2:20 - 2:50 slot
        self::createConference( $adminID, $teacherID,
                                self::CONFERENCE_ACTIVITY_TYPE_ID,
                                "20091117142000",
                                self::SUBJECT,
                                self::LOCATION,
                                self::STATUS,
                                $duration );

        // create 3:15 - 3:45 slot
        self::createConference( $adminID, $teacherID,
                                self::CONFERENCE_ACTIVITY_TYPE_ID,
                                "20091117151500",
                                self::SUBJECT,
                                self::LOCATION,
                                self::STATUS,
                                $duration );

        // create 8:00 - 8:30 slot
        self::createConference( $adminID, $teacherID,
                                self::CONFERENCE_ACTIVITY_TYPE_ID,
                                "20091118080000",
                                self::SUBJECT,
                                self::LOCATION,
                                self::STATUS,
                                $duration );

        // create 1:10 - 1:40 slot
        self::createConference( $adminID, $teacherID,
                                self::CONFERENCE_ACTIVITY_TYPE_ID,
                                "20091118131000",
                                self::SUBJECT,
                                self::LOCATION,
                                self::STATUS,
                                $duration );

        // create 1:45 - 2:15 slot
        self::createConference( $adminID, $teacherID,
                                self::CONFERENCE_ACTIVITY_TYPE_ID,
                                "20091118134500",
                                self::SUBJECT,
                                self::LOCATION,
                                self::STATUS,
                                $duration );

        // create 2:20 - 2:50 slot
        self::createConference( $adminID, $teacherID,
                                self::CONFERENCE_ACTIVITY_TYPE_ID,
                                "20091118142000",
                                self::SUBJECT,
                                self::LOCATION,
                                self::STATUS,
                                $duration );

        // create 3:15 - 3:45 slot
        self::createConference( $adminID, $teacherID,
                                self::CONFERENCE_ACTIVITY_TYPE_ID,
                                "20091118151500",
                                self::SUBJECT,
                                self::LOCATION,
                                self::STATUS,
                                $duration );

        // create 8:00 - 8:30 slot
        self::createConference( $adminID, $teacherID,
                                self::CONFERENCE_ACTIVITY_TYPE_ID,
                                "20091119080000",
                                self::SUBJECT,
                                self::LOCATION,
                                self::STATUS,
                                $duration );

        // create 1:10 - 1:40 slot
        self::createConference( $adminID, $teacherID,
                                self::CONFERENCE_ACTIVITY_TYPE_ID,
                                "20091119131000",
                                self::SUBJECT,
                                self::LOCATION,
                                self::STATUS,
                                $duration );

        // create 1:45 - 2:15 slot
        self::createConference( $adminID, $teacherID,
                                self::CONFERENCE_ACTIVITY_TYPE_ID,
                                "20091119134500",
                                self::SUBJECT,
                                self::LOCATION,
                                self::STATUS,
                                $duration );

        // create 2:20 - 2:50 slot
        self::createConference( $adminID, $teacherID,
                                self::CONFERENCE_ACTIVITY_TYPE_ID,
                                "20091119142000",
                                self::SUBJECT,
                                self::LOCATION,
                                self::STATUS,
                                $duration );

        // create 3:15 - 3:45 slot
        self::createConference( $adminID, $teacherID,
                                self::CONFERENCE_ACTIVITY_TYPE_ID,
                                "20091119151500",
                                self::SUBJECT,
                                self::LOCATION,
                                self::STATUS,
                                $duration );

        /*********
        for ( $time = $start; $time < $end; $time++ ) {
            // skip lunch hour for 6th grade conference
            if ( $time == 12 ) {
                continue;
            }

            if ( $time < 10 ) {
                $time = "0{$time}";

            }

            // skip 8:00 am slot for middle school
            if ( $time != '08' ) {
                self::createConference( $adminID, $teacherID,
                                        self::CONFERENCE_ACTIVITY_TYPE_ID,
                                        "{$date}{$time}0000",
                                        self::SUBJECT,
                                        self::LOCATION,
                                        self::STATUS,
                                        $duration );
            }

            // skip 5:30 pm slot
            if ( $time != '17' ) {
                self::createConference( $adminID, $teacherID,
                                        self::CONFERENCE_ACTIVITY_TYPE_ID,
                                        "{$date}{$time}3000",
                                        self::SUBJECT,
                                        self::LOCATION,
                                        self::STATUS,
                                        $duration );
            }

        }
        ****/
    }

    static function createConference( $adminID,
                                      $teacherID,
                                      $activityTypeID,
                                      $activityDateTime,
                                      $subject,
                                      $location,
                                      $statusID,
                                      $duration = 30 ) {
        require_once 'CRM/Activity/DAO/Activity.php';

        $activity = new CRM_Activity_DAO_Activity( );

        $activity->source_contact_id  = $adminID;
        $activity->activity_type_id   = $activityTypeID;
        $activity->activity_date_time = $activityDateTime;
        $activity->status_id          = $statusID;
        $activity->subject            = $subject;
        $activity->duration           = $duration;
        $activity->location           = $location;
        $activity->save( );

        require_once 'CRM/Activity/DAO/ActivityAssignment.php';
        $assignment = new CRM_Activity_DAO_ActivityAssignment( );
        $assignment->activity_id = $activity->id;
        $assignment->assignee_contact_id = $teacherID;
        $assignment->save( );

        return $activity->id;
    }

    static function deleteAll( $childID ) {
        $sql = "
UPDATE     civicrm_activity a,
           civicrm_activity_assignment aa,
           civicrm_activity_target     at
SET        a.phone_number = NULL
WHERE      a.activity_type_id = %2
AND        a.id = aa.activity_id
AND        a.id = at.activity_id
AND        a.status_id = 1
AND        a.activity_date_time > NOW()
AND        at.target_contact_id = %1
";
        $params  = array( 1 => array( $childID , 'Integer' ),
                          2 => array( self::CONFERENCE_ACTIVITY_TYPE_ID , 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );

        $sql = "
DELETE     at.*
FROM       civicrm_activity a,
           civicrm_activity_assignment aa,
           civicrm_activity_target     at
WHERE      a.activity_type_id = %2
AND        a.id = aa.activity_id
AND        a.id = at.activity_id
AND        a.status_id = 1
AND        a.activity_date_time > NOW()
AND        at.target_contact_id = %1
";
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
    }

    static function getReminderDetails( $days = 7, $offset = 7 ) {
        $daysOffset = $days - $offset;

        $sql = "
SELECT     c.display_name as advisorName, s.grade_sis as grade
FROM       civicrm_activity a,
           civicrm_activity_assignment aa,
           civicrm_activity_target     at,
           civicrm_contact             c ,
           civicrm_value_school_information s
WHERE      a.activity_type_id = %1
AND        aa.assignee_contact_id = c.id
AND        s.entity_id = at.target_contact_id
AND        a.status_id = 1
AND        aa.activity_id = a.id
AND        at.activity_id = a.id
AND        a.activity_date_time > NOW( )
GROUP BY   c.id
ORDER BY   s.grade_sis
";
        $params = array( 1 => array( self::CONFERENCE_ACTIVITY_TYPE_ID, 'Integer' ),
                         2 => array( $days                            , 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );

        $result = array( );
        while ( $dao->fetch( ) ) {
            $result[$dao->advisorName] = $dao->grade;
        }

        return $result;
    }

    static function sendReminderEmail( $days = 7, $offset = 7 ) {

        $daysOffset = $days - $offset;

        $sql = "
SELECT     a.id, a.activity_date_time,
           aa.assignee_contact_id as advisor_id,
           at.target_contact_id   as child_id
FROM       civicrm_activity a
INNER JOIN civicrm_activity_assignment aa ON aa.activity_id = a.id
INNER JOIN civicrm_activity_target     at ON at.activity_id = a.id
INNER JOIN civicrm_value_school_information s ON s.entity_id = at.target_contact_id
WHERE      a.activity_type_id = %1
AND        a.status_id = 1
AND        a.activity_date_time > NOW( )
AND        s.grade_sis >= 1
AND        s.grade_sis <= 5
";
        $params = array( 1 => array( self::CONFERENCE_ACTIVITY_TYPE_ID, 'Integer' ),
                         2 => array( $days                            , 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );

        $activityIDs = array( );
        while ( $dao->fetch( ) ) {
            self::sendConferenceEmail( $dao->id, $dao->advisor_id, $dao->child_id, $dao->activity_date_time );
            $activityIDs[] = $dao->id;
        }
    }

    static function getPTCValuesOccupied( $teacherID, &$values ) {
        $sql = "
SELECT     c.id, c.display_name, a.id as activity_id, a.activity_date_time, at.id as target_id
FROM       civicrm_contact c
INNER JOIN civicrm_relationship r ON c.id = r.contact_id_b
INNER JOIN civicrm_activity_assignment aa ON aa.assignee_contact_id = %1
INNER JOIN civicrm_activity a ON a.id = aa.activity_id
INNER JOIN civicrm_activity_target at ON at.target_contact_id = c.id AND at.activity_id = a.id
WHERE      r.contact_id_a = %1
AND        aa.assignee_contact_id = %1
AND        r.relationship_type_id = %2
AND        a.status_id = 1
ORDER BY   a.activity_date_time
";

        $params  = array( 1 => array( $teacherID, 'Integer' ),
                          2 => array( self::ADVISOR_RELATIONSHIP_TYPE_ID, 'Integer' ) );

        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        while ( $dao->fetch( ) ) {
            $values[$dao->id] = array( 'id'            => $dao->id,
                                       'name'          => $dao->display_name,
                                       'activity_id'   => $dao->activity_id,
                                       'target_id'     => $dao->target_id,
                                       'time'          => CRM_Utils_Date::customFormat( $dao->activity_date_time,
                                                                                        "%l:%M %P on %b %E%f" ) );
        }
    }

    static function getPTCValuesEmpty( $teacherID, &$values ) {
        $sql = "
SELECT     a.id as activity_id, a.activity_date_time
FROM       civicrm_activity a
INNER JOIN civicrm_activity_assignment aa ON a.id = aa.activity_id
INNER JOIN civicrm_contact            aac ON aa.assignee_contact_id = aac.id
INNER JOIN civicrm_relationship         r ON r.contact_id_a = aac.id
LEFT  JOIN civicrm_activity_target     at ON a.id = at.activity_id
WHERE      a.activity_type_id = %3
AND        r.relationship_type_id = %2
AND        r.is_active = 1
AND        r.contact_id_a = %1
AND        a.status_id = 1
AND        a.activity_date_time > NOW()
AND        ( at.target_contact_id IS NULL OR at.target_contact_id = %1 )
ORDER BY   a.activity_date_time asc
";
        $params  = array( 1 => array( $teacherID   , 'Integer' ),
                          2 => array( self::ADVISOR_RELATIONSHIP_TYPE_ID, 'Integer' ),
                          3 => array( self::CONFERENCE_ACTIVITY_TYPE_ID , 'Integer' ) );

        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        while ( $dao->fetch( ) ) {
            $values[$dao->activity_id] = array( 'id'        => $dao->activity_id,
                                                'time'      => CRM_Utils_Date::customFormat( $dao->activity_date_time,
                                                                                             "%l:%M %P on %b %E%f" ) );
        }
    }

    static function &getPTCNeedToScheduleIDs( $staffID,
                                              $alreadyScheduledIDs ) {
        $sql = "
SELECT     c.id, c.display_name
FROM       civicrm_contact c
INNER JOIN civicrm_relationship r
WHERE      c.id = r.contact_id_b
AND        r.is_active = 1
AND        r.contact_id_a = %1
AND        r.relationship_type_id = %2
";
        if ( ! empty( $alreadyScheduledIDs ) ) {
            $sql .= "
AND        c.id NOT IN ( $alreadyScheduledIDs )
";
        }

        $sql .= "
ORDER BY c.display_name
";

        $params = array( 1 => array( $staffID, 'Integer' ),
                         2 => array( self::ADVISOR_RELATIONSHIP_TYPE_ID, 'Integer' ) );

        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        $values = array( );
        while ( $dao->fetch( ) ) {
            $values[$dao->id] = $dao->display_name;
        }
        return $values;
    }

    static function buildPTCForm( &$form, $staffID ) {

        $occupiedSlots = array( );
        self::getPTCValuesOccupied( $staffID, $occupiedSlots );

        // create a checkbox to cancel someone's slot
        foreach ( $occupiedSlots as $id => $values ) {
            $occupiedSlots[$id]['cb_name'] = "cancel_{$id}_{$values['target_id']}";
            $form->addElement( 'checkbox', $occupiedSlots[$id]['cb_name'], ts( 'Cancel this Meeting?' ) );
        }

        $emptySlots = array( );
        self::getPTCValuesEmpty( $staffID, $emptySlots );

        $needToScheduleIDs = self::getPTCNeedToScheduleIDs( $staffID,
                                                            implode( ',', array_keys( $occupiedSlots ) ) );
        $needToScheduleIDs[$staffID] = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                                                    $staffID,
                                                                    'display_name' );
        $needToScheduleIDs = array( '' => ' - select - ' ) + $needToScheduleIDs;

        // create a checkbox to cancel someone's slot
        foreach ( $emptySlots as $id => $values ) {
            $emptySlots[$id]['cb_name'] = "delete_{$id}";
            $form->addElement( 'checkbox', $emptySlots[$id]['cb_name'], ts( 'Delete this timeslot?' ) );

            // also add a select box so they can slot a student (or themselves in there)
            $emptySlots[$id]['select_name'] = "select_{$id}";
            $form->add( 'select', $emptySlots[$id]['select_name'], null, $needToScheduleIDs );
        }

        $form->assign_by_ref( 'occupiedSlots', $occupiedSlots );
        $form->assign_by_ref( 'emptySlots'   , $emptySlots );

        // also expose elements to allow the staff to create a conference
        // we need a date time and duration
        $form->addDate( "slot_date", ts( 'Date' ) );
        $form->add( 'text', "slot_duration" , ts( 'Duration' ),
                    array( 'size'=> 4,'maxlength' => 8 ) );
        $form->add( 'select', 'slot_contact_id', null, $needToScheduleIDs );

        $form->addRule('slot_duration',
                       ts('Please enter the duration as number of minutes (integers only).'), 'positiveInteger');
    }

    static function validatePTCForm( &$form, &$fields ) {
        $errors = array( );

        $selectedIDs = array( );
        foreach ( $fields as $name => $value ) {
            $match = preg_match( '/^(select_|delete_|cancel_)(\d+)_?(\d+)?$/', $name, $matches );
            if ( ! empty( $value ) &&
                 $match ) {
                if ( $matches[1] == 'delete_' ) {
                    if ( array_key_exists( "select_{$matches[2]}", $fields ) &&
                         ! empty( $fields["select_{$matches[2]}"] ) ) {
                        $errors[$name] = ts( 'You cannot schedule and delete a slot at the same time' );
                    }
                }

                if ( $matches[1] == 'select_' ) {
                    if ( array_key_exists( $value, $selectedIDs ) ) {
                        $errors[$name] = ts( 'You cannot schedule the same person multiple times' );
                    }
                    $selectedIDs[$value] = 1;
                }
            }
        }
        return empty( $errors ) ? true : $errors;
    }

    static function postProcessPTC( &$form, $advisorID ) {
        $params =  $form->controller->exportValues( $form->getVar( '_name' ) );

        // collect all the ids
        foreach ( $params as $name => $value ) {
            $match = preg_match( '/^(select_|delete_|cancel_)(\d+)_?(\d+)?$/', $name, $matches );
            if ( ! empty( $value ) &&
                 $match ) {
                if ( $matches[1] == 'delete_' ) {
                    self::deletePTC( $advisorID, $matches[2] );
                } else if ( $matches[1] == 'select_' ) {
                    self::selectPTC( $advisorID, $value, $matches[2] );
                } else if ( $matches[1] == 'cancel_' ) {
                    self::cancelPTC( $advisorID, $matches[2], $matches[3] );
                }
            }
        }

        // check if date and duration are filled
        if ( ! empty( $params['slot_date'] ) ) {
            $duration = empty( $params['slot_duration'] ) ? 30 : $params['slot_duration'];
            $date =  CRM_Utils_Date::format( $params['slot_date'] );

            $activityID = self::createConference( $advisorID, $advisorID,
                                                  self::CONFERENCE_ACTIVITY_TYPE_ID,
                                                  $date,
                                                  self::SUBJECT,
                                                  self::LOCATION,
                                                  self::STATUS,
                                                  $duration );

            if ( ! empty( $params['slot_contact_id'] ) &&
                 $params['slot_contact_id'] > 0 ) {
                self::selectPTC( $advisorID, $params['slot_contact_id'], $activityID );
            }
        }
    }

    static function deletePTC( $advisorID, $activityID ) {
        $sql = "
DELETE     a.*, aa.*
FROM       civicrm_activity a
INNER JOIN civicrm_activity_assignment aa ON a.id = aa.activity_id
WHERE      a.id = %1
AND        aa.assignee_contact_id = %2
";
        $params = array( 1 => array( $activityID, 'Integer' ),
                         2 => array( $advisorID , 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
    }

    static function cancelPTC( $advisorID, $childID, $targetID ) {
        if ( empty( $advisorID ) ) {
            return;
        }

        self::deleteAll( $childID );
    }

    static function selectPTC( $advisorID, $childID, $activityID ) {
        if ( empty( $activityID ) || empty( $childID ) ) {
            return;
        }

        // first we need to delete all the existing meetings for this childID
        self::deleteAll( $childID );

        // insert these two into civicrm_target
        // we actually need to lock this and then ensure the space is available
        // lets do that at a later stage
        $sql = "
REPLACE INTO civicrm_activity_target (activity_id, target_contact_id)
VALUES
( %1, %2 )
";
        $params = array( 1 => array( $activityID, 'Integer' ),
                         2 => array( $childID   , 'Integer' ) );
        CRM_Core_DAO::executeQuery( $sql, $params );

        self::sendConferenceEmail( $activityID, $advisorID, $childID );
    }

    static function notScheduledReminder( ) {
        // elementary school
        $staff = array( 740, 703, 698, 1272, 10 );

        // middle school
        $staff = array( 54, 71, 185, 527, 626, 705, 1114, 3956 );

        // 6th grade only
        $staff = array( 527, 626, 705 );

        foreach ( $staff as $staffID ) {
            $occupiedSlots = array( );
            self::getPTCValuesOccupied( $staffID, $occupiedSlots );

            $needToScheduleIDs = self::getPTCNeedToScheduleIDs( $staffID,
                                                                implode( ',', array_keys( $occupiedSlots ) ) );

            foreach ( $needToScheduleIDs as $studentID => $studentName ) {
                self::sendNotScheduledReminderEmail( $staffID, $studentID );
            }
        }
    }


}
