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

class SCH_Form_BatchConsent extends CRM_Core_Form {

    protected $_date;

    protected $_nameValues;

    function preProcess( ) {
        parent::preProcess( );

        $this->_character = CRM_Utils_Request::retrieve( 'c'   , 'String' , $this, false, 'A' );
        $this->_character = CRM_Utils_Type::escape( $this->_character, 'String' );

        $this->assign( 'displayDate',
                       date( 'l - F d, Y', strtotime( $this->_date ) ) );

        $this->assign( 'date'     , $this->_date      );
    }

    function buildQuickForm( ) {
        CRM_Utils_System::setTitle( 'EConsent - Batch Update' );

        $this->buildAlphaPagerLinks( $this->_character );

        // get all the students, their grade and parent
        $query = "
SELECT     p.id as parent_id,  p.display_name as parent_name ,
           c.id as student_id, c.display_name as student_name,
           s.grade as student_grade,
           rd.econsent_signed as econsent_signed,
           rd.econsent_signed_date as econsent_signed_date
FROM       civicrm_contact p
INNER JOIN civicrm_relationship r ON r.contact_id_b = p.id
INNER JOIN civicrm_contact c ON r.contact_id_a = c.id
INNER JOIN civicrm_value_school_information s ON s.entity_id = c.id
LEFT  JOIN civicrm_value_parent_relationship_data rd ON rd.entity_id = r.id
WHERE      r.relationship_type_id = 1
AND        r.is_active    = 1
AND        c.last_name LIKE '{$this->_character}%'
AND        s.is_currently_enrolled = 1
ORDER BY   c.last_name, s.grade_sis desc, c.id, p.id
";

        $dao = CRM_Core_DAO::executeQuery( $query );

        require_once 'CRM/Utils/Date.php';
        list( $dateDefault, $ignore ) = CRM_Utils_Date::setDateDefaults( '2010-05-01' );

        $defaults   = array( );
        $dateString = 'Date';
        $this->_nameValues = array( );
        while ( $dao->fetch( ) ) {
            $dateName = "econsent_date_{$dao->parent_id}_{$dao->student_id}";
            $defaults[$dateName] = $dateDefault;
            $this->addDate( $dateName, $dateString );
            $this->_nameValues[$dateName] = array( 'name'        => "{$dao->student_name} - {$dao->parent_name}",
                                                   'parentID'    => $dao->parent_id,
                                                   'studentID'   => $dao->student_id,
                                                   'currentDate' => '&nbsp;');
            if ( $dao->econsent_signed ) {
                $this->_nameValues[$dateName]['currentDate'] =
                    CRM_Utils_Date::customFormat( substr( $dao->econsent_signed_date, 0, 10 ) );
            }
        }

        $this->setDefaults( $defaults );
        $this->assign( 'nameValues', $this->_nameValues );

        $this->addButtons(array(
                                array ( 'type'      => 'refresh',
                                        'name'      => ts( 'Process' ),
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                        'isDefault' => true   ),
                                array ( 'type'      => 'cancel',
                                        'name'      => ts('Cancel') ),
                                 )
                          );

        $this->addFormRule( array( 'SCH_Form_BatchConsent', 'formRule' ), $this );
    }

    static function formRule( $fields, $files, $form )
    {
        $errors = array( );
        return $errors;
    }

    function postProcess( ) {
        $params = $this->controller->exportValues( $this->_name );

        foreach ( $this->_nameValues as $formName => $value ) {
            if ( empty( $params[$formName] ) ||
                 $params[$formName] == '05/01/2010' ) {
                continue;
            }

            $mysqlDate = CRM_Utils_Date::processDate( $params[$formName] );
            if ( ! $mysqlDate ) {
                continue;
            }

            $this->processEConsentSignature( $value['studentID'],
                                             $value['parentID'],
                                             $mysqlDate );
        }

        CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/sfschool/batchConsent',
                                                           "reset=1&c={$this->_character}" ) );
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

    function buildAlphaPagerLinks( $currentChar = 'A' ) {
        $staticAlphabets = array('A','B','C','D','E','F','G','H',
                                 'I','J','K','L','M','N','O','P',
                                 'Q','R','S','T','U','V','W','X',
                                 'Y','Z');

        $baseURL = CRM_Utils_System::url( 'civicrm/sfschool/batchConsent', 'reset=1&c=' );
        $aToZBar = array( );
        foreach ( $staticAlphabets as $char ) {
            $klass = '';
            if ( $char == $currentChar ) {
                $klass = 'class="active"';
                $link  = $char;
            } else {
                $url = $baseURL . $char;
                $link = sprintf( '<a href="%s">%s</a>',
                                 $url, $char );
            }
            $aToZBar[] = array( 'class' => $klass,
                                'link'  => $link
                                );
        }

        $this->assign( 'currentChar', $currentChar );
        $this->assign( 'aToZ', $aToZBar );
    }

}