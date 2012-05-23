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

require_once 'CRM/Core/Form.php';

class SFS_Form_Morning extends CRM_Core_Form {
    protected $_students;

    function buildQuickForm( ) {
        CRM_Utils_System::setTitle( 'Morning SignIn - Extended Care' );

        $this->add( 'text',
                    'pickup_name',
                    ts( 'Parent Name' ),
                    'autocomplete="off"',
                    true );

        $this->_date = CRM_Utils_Request::retrieve( 'date', 'String' , $this, false, date( 'Y-m-d' ) );
        $this->_time = CRM_Utils_Request::retrieve( 'time', 'String' , $this, false, null );

        
        $this->assign( 'date', $this->_date );
        $this->assign( 'time', $this->_time );

        $this->assign( 'displayDate', 
                       date( 'l - F d, Y', strtotime( $this->_date ) ) );

        require_once 'SFS/Utils/Query.php';
        $students =
            array( ''  => '- Select Student -' ) + 
            SFS_Utils_Query::getStudentsByGrade( true, false, true , ''  );
        
        for ( $i = 1; $i <= 6; $i++ ) {
            $required = ( $i == 1 ) ? true : false;
            $this->add( 'select',
                        "student_id_$i",
                        ts( 'Student' ),
                        $students,
                        $required );
            $this->add( 'checkbox', "at_school_meeting_$i", ts( 'School Meeting?' ) );
        }

        $this->addDefaultButtons( 'Morning Extended Care Signup', 'next', null, true );
    }

    static function postProcessStudent( $studentID, $pickup, $date, $time, $atSchoolMeeting = 0 ) {
        if ( $atSchoolMeeting === 'true' ) {
            $atSchoolMeeting = 1;
        } else if ( $atSchoolMeeting === 'false' ) {
            $atSchoolMeeting = 0;
        }
        
        $atSchoolMeeting = $atSchoolMeeting ? '1' : '0';

        $params = array( 1 => array( $studentID       , 'Integer' ),
                         2 => array( "{$date} {$time}", 'String'  ),
                         3 => array( "{$date} 08:30"  , 'String'  ),
                         5 => array( $pickup          , 'String'  ),
                         6 => array( $atSchoolMeeting , 'Integer' ) );


        $sql = "
SELECT e.id
FROM   civicrm_value_extended_care_signout e
WHERE  entity_id = %1
AND    signin_time LIKE '{$date}%'
AND    is_morning = 1
";

        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        if ( $dao->fetch( ) ) {
            $params[4] = array( $dao->id, 'Integer' );
            $sql = "
UPDATE civicrm_value_extended_care_signout 
SET    signin_time        = %2,
       signout_time       = %3,
       pickup_person_name = %5,
       at_school_meeting  = %6
WHERE  id = %4
";
        } else {
            $sql = "
INSERT INTO civicrm_value_extended_care_signout
( entity_id, pickup_person_name, signin_time, signout_time, is_morning, at_school_meeting )
VALUES
( %1, %5, %2, %3, 1, %6 )
";
        }
        CRM_Core_DAO::executeQuery( $sql, $params );
        return;
    }

    static function addMorningRecord( ) {
        $pickup = CRM_Utils_Request::retrieve( 'pickupName',
                                               'String',
                                               CRM_Core_DAO::$_nullObject,
                                               false,
                                               '',
                                               'REQUEST' );
        
        $date = CRM_Utils_Request::retrieve( 'date',
                                             'String',
                                             CRM_Core_DAO::$_nullObject,
                                             false,
                                             date( 'Y-m-d' ),
                                             'REQUEST' );
        
        $time = CRM_Utils_Request::retrieve( 'time',
                                             'String',
                                             CRM_Core_DAO::$_nullObject,
                                             false,
                                             date( 'h:i' ),
                                             'REQUEST' );
        if ( empty( $time ) ) {
            $time = date( 'h:i' );
        }
        
        $result = null;
        for ( $i = 1; $i <= 6; $i++ ) {
            $studentID = CRM_Utils_Request::retrieve( "studentID_$i",
                                                      'Positive',
                                                      CRM_Core_DAO::$_nullObject,
                                                      false,
                                                      null,
                                                      'REQUEST' );

            $atSchoolMeeting = CRM_Utils_Request::retrieve( "atSchoolMeeting_$i",
                                                            'Boolean',
                                                            CRM_Core_DAO::$_nullObject,
                                                            false,
                                                            0,
                                                            'REQUEST' );

            if ( ! empty( $studentID ) ) {
                self::postProcessStudent( $studentID, $pickup, $date, $time, $atSchoolMeeting );
                $result[] = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                                         $studentID,
                                                         'display_name' );
            }
        }

        echo implode( ", ", $result );
        exit( );
    }

}



