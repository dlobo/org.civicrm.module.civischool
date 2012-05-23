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

class SFS_Form_EConsent extends CRM_Core_Form {

    protected $_date;

    protected $_time;

    protected $_numberOfSlots = 35;

    function preProcess( ) {
        parent::preProcess( );

        $this->_date      = CRM_Utils_Request::retrieve( 'date'     , 'String' , $this, false, date( 'Y-m-d' ) );

        // get the dayOfWeek from the date
        $this->_dayOfWeek = date( 'l', strtotime( $this->_date ) );

        $this->assign( 'displayDate',
                       date( 'l - F d, Y', strtotime( $this->_date ) ) );

        $this->assign( 'dayOfWeek', $this->_dayOfWeek );
        $this->assign( 'date'     , $this->_date      );
        $this->assign( 'numberOfSlots', $this->_numberOfSlots );
    }

    function buildQuickForm( ) {
        CRM_Utils_System::setTitle( 'EConsent - Batch Update' );


        // get all the students, their grade and parent
        $query = "
SELECT     p.id as parent_id,  p.display_name as parent_name ,
           c.id as student_id, c.display_name as student_name,
           s.grade as student_grade
FROM       civicrm_contact p
INNER JOIN civicrm_relationship r ON r.contact_id_b = p.id
INNER JOIN civicrm_contact c ON r.contact_id_a = c.id
INNER JOIN civicrm_value_school_information s ON s.entity_id = c.id
WHERE      r.relationship_type_id = 1
AND        r.is_active    = 1
AND        s.grade_sis != 8
AND        s.is_currently_enrolled = 1
ORDER BY   c.display_name, c.id, p.id
";

        $studentSelect = array( '' => ts( '- select student -' ) );
        $parentSelect  = array( '' => array( '' => ts( '- select parent -' ) ) );
        
        $dao = CRM_Core_DAO::executeQuery( $query );
        while ( $dao->fetch( ) ) {
            if ( ! array_key_exists( $dao->student_id, $studentSelect ) ) {
                $studentSelect[$dao->student_id] = "{$dao->student_name} - {$dao->student_grade}";
            }

            if ( ! array_key_exists( $dao->student_id, $parentSelect ) ) {
                $parentSelect[$dao->student_id] = array( );
            }
            
            $parentSelect[$dao->student_id][$dao->parent_id] = $dao->parent_name;
        }

        require_once 'CRM/Utils/Date.php';
        list( $dateDefault, $ignore ) = CRM_Utils_Date::setDateDefaults(date("Y-m-d"));
        
        $this->addDate("econsent_date",
                       ts( 'EConsent Signature Date' ),
                       true );
        $defaults["econsent_date"] = $dateDefault;

        $defaults = array( );
        for ( $i = 1; $i < $this->_numberOfSlots; $i++ ) {
            $sel =& $this->addElement('hierselect',
                                      "student_parent_$i",
                                      null,
                                      null,
                                      '&nbsp;&nbsp;&nbsp;' );
            $sel->setOptions(array( $studentSelect, $parentSelect ) );
            
        }
        
        $this->setDefaults( $defaults );
        $this->addButtons(array( 
                                array ( 'type'      => 'refresh', 
                                        'name'      => ts( 'Process' ),
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                        'isDefault' => true   ), 
                                array ( 'type'      => 'cancel', 
                                        'name'      => ts('Cancel') ), 
                                 )
                          );
        
        $this->addFormRule( array( 'SFS_Form_EConsent', 'formRule' ), $this );
    }

    static function formRule( $fields, $files, $form ) 
    {  
        $errors = array( );
        return $errors;
    }

    function postProcess( ) {
        $params = $this->controller->exportValues( $this->_name );

        $mysqlDate = CRM_Utils_Date::processDate( $params["econsent_date"] );
        for ( $i = 1; $i < $this->_numberOfSlots; $i++ ) {
            if ( ! empty( $params["student_parent_$i"][0] ) &&
                 ! empty( $params["student_parent_$i"][1] ) ) {
                $this->processEConsentSignature( $params["student_parent_$i"][0],
                                                 $params["student_parent_$i"][1],
                                                 $mysqlDate );
            }
        }

        CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/sfschool/econsent',
                                                           'reset=1' ) );
    }

    function processEConsentSignature( $studentID,
                                       $parentID,
                                       $date ) {
        // first get the relationship ID
        $query = "
SELECT r.id
FROM   civicrm_relationship r
WHERE  r.contact_id_a = %1
AND    r.contact_id_b = %2
AND    r.relationship_type_id = 1
AND    r.is_active    = 1
";
        $params = array( 1 => array( $studentID, 'Integer' ),
                         2 => array( $parentID , 'Integer' ) );
        $relationshipID = CRM_Core_DAO::singleValueQuery( $query, $params );
        if ( ! $relationshipID ) {
            CRM_Core_Error::fatal( );
        }

        // check if entry exists in civicrm_value_parent_relationship_data
        $query = "
SELECT id
FROM   civicrm_value_parent_relationship_data
WHERE  entity_id = %1
";
        $params = array( 1 => array( $relationshipID, 'Integer' ) );
        $dataID = CRM_Core_DAO::singleValueQuery( $query, $params );

        if ( $dataID ) {
            $query = "
UPDATE civicrm_value_parent_relationship_data
SET    econsent_signed = 1,
       econsent_signed_date = %1
WHERE  id = %2
";
            $params = array( 1 => array( $date, 'Timestamp' ),
                             2 => array( $dataID, 'Integer' ) );
        } else {
            $query = "
INSERT INTO civicrm_value_parent_relationship_data
  ( entity_id, econsent_signed, econsent_signed_date )
VALUES
  ( %1, 1, %2 )
";
            $params = array( 1 => array( $relationshipID, 'Integer' ),
                             2 => array( $date, 'Timestamp' ) );
        }
        CRM_Core_DAO::executeQuery( $query, $params );
    }

}