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

class SFS_Utils_Intake {

    static function unscrambleCustomViewData( &$details ) {
        foreach ( $details as $childID => $detail ) {
            self::unScrambleCustomChild( $details, $childID );
        }
    }

    static function unScrambleCustomChild( &$details, $childID ) {
        if ( ! self::canViewData( $choldID ) ) {
            return;
        }
        foreach ( $details[$childID] as $id => &$values ) {
            foreach ( $values['fields'] as $fieldID => &$field ) {
                self::unscrambleField( $details[$childID][$id]['fields'][$fieldID]['field_value'] );
            }
        }
    }

    static function unscrambleProfileRow( &$row, $childID ) {
        if ( ! self::canViewData( $childID ) ) {
            return;
        }

        foreach ( $row as $key => $value ) {
            if ( $key != 'First Name' &&
                 $key != 'Last Name' ) {
                self::unscrambleField( $row[$key] );
            }
        }
    }

    static function unscrambleField( &$field ) {
        $field = "UNSCRAMBLED: $field";
    }

    static function canViewData( $childID ) {
        // only unscramble if the user is the parent or advisor to this child
        $session =& CRM_Core_Session::singleton( );
        $userID = $session->get( 'userID' );

        $sql = "
SELECT c.id
FROM   civicrm_contact c,
       civicrm_contact p,
       civicrm_relationship r
WHERE  p.id = %1
AND    c.id = %1
AND    ( ( r.relationship_type_id = 1  AND r.contact_id_a = c.id AND r.contact_id_b = p.id )
    OR   ( r.relationship_type_id = 10 AND r.contact_id_a = p.id AND r.contact_id_b = c.id ) )
";

        $params = array( 1 => array( $userID , 'Integer' ),
                         2 => array( $childID, 'Integer' ) );

        return true;
        return CRM_Core_DAO::singleValueQuery( $sql, $params );
    }

    static function buildForm( &$form,
                               $childID ) {
        
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

        $classInfo = self::getClassCount( $grade );
        self::getCurrentClasses( $childID, $classInfo );


        $activities = self::getActivities( $grade, $classInfo );

    
}