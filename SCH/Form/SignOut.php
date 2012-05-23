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

class SFS_Form_SignOut extends CRM_Core_Form {
    protected $_students;

    function buildQuickForm( ) {
        CRM_Utils_System::setTitle( 'Parent Signout - Extended Care' );

        $this->add( 'text',
                    'pickup_name',
                    ts( 'Parent Name' ),
                    'autocomplete="off"',
                    true );

        $this->assign( 'date', 
                       date( 'l - F d, Y' ) );

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

        $this->addDefaultButtons( 'Sign Out', 'next', null, true );
    }

    static function postProcessStudent( $pickupName,
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

        $atSchoolMeeting = $atSchoolMeeting ? '1' : '0';

        $sql = "
SELECT e.id, e.class
FROM   civicrm_value_extended_care_signout e
WHERE  entity_id = %1
AND    DATE(signin_time) = %2
AND    ( is_morning = 0 OR is_morning IS NULL )
";
        $params = array( 1 => array( $studentID, 'Integer' ),
                         2 => array( $_date    , 'String'  ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );

        $params = array( 1 => array( $studentID      , 'Integer'   ),
                         2 => array( $pickupName     , 'String'    ),
                         3 => array( $_now           , 'Timestamp' ),
                         4 => array( $atSchoolMeeting, 'Integer'   ) );

        $class = null;
        if ( $dao->fetch( ) ) {
            $class = $dao->class;
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
( entity_id, pickup_person_name, signout_time, at_school_meeting, is_morning )
VALUES
( %1, %2, %3, %4, 0 )
";
        }

        CRM_Core_DAO::executeQuery( $sql, $params );
        return $class;
    }

    function postProcess( ) {
        $params = $this->controller->exportValues( $this->_name );

        require_once 'SFS/Utils/ExtendedCare.php';
        $pickup = CRM_Utils_Array::value( 'pickup_name', $params );
        for ( $i = 1 ; $i <= 6; $i++ ) {
            $studentID       = CRM_Utils_Array::value( "student_id_$i"       , $params );
            $atSchoolMeeting = CRM_Utils_Array::value( "at_school_meeting_$i", $params, false );
            if ( ! empty( $studentID ) ) {
                SFS_Utils_ExtendedCare::processSignOut( $pickup,
                                                        $studentID,
                                                        $atSchoolMeeting );
            }
        }

        $url = CRM_Utils_System::url( 'civicrm/sfschool/signout', 'reset=1' );
        CRM_Utils_System::redirect( $url );
    }


    static function addSignOutRecord( ) {
        $pickup    = CRM_Utils_Request::retrieve( 'pickupName',
                                                  'String',
                                                  CRM_Core_DAO::$_nullObject,
                                                  true,
                                                  null,
                                                  'REQUEST' );

        $result = null;
        require_once 'SFS/Utils/ExtendedCare.php';
        for ( $i = 1; $i <= 6; $i++ ) {
            $studentID       = CRM_Utils_Request::retrieve( "studentID_$i",
                                                            'Positive',
                                                            CRM_Core_DAO::$_nullObject,
                                                            false,
                                                            null,
                                                            'REQUEST' );
            $atSchoolMeeting = CRM_Utils_Request::retrieve( "atSchoolMeeting_$i",
                                                            'Boolean',
                                                            CRM_Core_DAO::$_nullObject,
                                                            false,
                                                            false,
                                                            'REQUEST' );
            if ( ! empty( $studentID ) ) {
                $className = SFS_Utils_ExtendedCare::processSignOut( $pickup,
                                                                     $studentID,
                                                                     $atSchoolMeeting );
                if ( empty( $className ) ) {
                    $className = 'Yard Play';
                }

                $studentName = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                                            $studentID,
                                                            'display_name' );
                $result[] = "{$studentName} @ {$className}";
            }
        }

        echo implode( ", ", $result );
        exit( );
    }

}



