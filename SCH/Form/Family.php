<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/Form.php';

/**
 * This class generates form components for processing Event  
 * 
 */
class SFS_Form_Family extends CRM_Core_Form
{
    const
        SCHOOL_INFO_TABLE     =  'civicrm_value_school_information',
        MEDICAL_DETAILS_TABLE =  'civicrm_value_medical_details',
        MEDICAL_INFO_TABLE    =  'civicrm_value_medical_information',
        RELATION_TABLE        =  'civicrm_value_parent_relationship_data',
        RACE_ETHNICITY_TABLE  =  'civicrm_value_race',
        EMERGENCY_REL_TABLE   =  'civicrm_value_emergency_contact_data';

    protected $_studentId;
    protected $_parentId;
    
    function preProcess( ) {
        $this->_studentId = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this, true, 0, 'REQUEST' );
        $this->_parentId  = CRM_Utils_Request::retrieve( 'pid', 'Positive', $this, true, 0, 'REQUEST' );

        if ( $this->_studentId ) {
            // make sure _studentId is a student
            require_once 'SFS/Utils/Query.php';
            $subType = SFS_Utils_Query::getSubType( $this->_studentId );
            
            // if subType is not student then hide the extended care tab
            if ( $subType != 'Student' ) {
                CRM_Core_Error::fatal( ts( 'The family form is for a Contact of type Student.' ) );
            }
            
            $subType = SFS_Utils_Query::getSubType( $this->_parentId );
            if ( $subType != 'Parent' &&
                 $subType != 'Staff' ) {
                 CRM_Core_Error::fatal( ts( 'The family form is accessible only to  parents.' ) );
            }

            require_once 'CRM/Contact/BAO/Contact/Permission.php';

            // check that the current user has permission to act as parentID
            if ( ! CRM_Contact_BAO_Contact_Permission::allow( $this->_parentId ) ) {
                CRM_Core_Error::fatal( ts( 'Specified user does not have permission to access parent record.' ) );
            }

            // check that the parent has permission to update
            if ( ! CRM_Contact_BAO_Contact_Permission::relationship( $this->_studentId, $this->_parentId ) ) {
                CRM_Core_Error::fatal( ts( 'Specified parent does not have permission to edit this student details.' ) );
            }

            // make sure logged in user is either - parent OR admin
            $session =& CRM_Core_Session::singleton( );
            $userId  = $session->get( 'userID' );
            if ( $userId != $this->_parentId && 
                 !CRM_Core_Permission::check( 'administer CiviCRM' ) ) {
                CRM_Core_Error::fatal( ts('Not enough permission.') );
            }

            $this->assign( 'cid', $this->_studentId );

            require_once 'CRM/Contact/BAO/Contact.php';

            $this->add( 'hidden', 'cid', $this->_studentId );
            $this->add( 'hidden', 'pid', $this->_parentId  );

            $displayName = CRM_Contact_BAO_Contact::displayName( $this->_studentId );
            CRM_Utils_System::setTitle( ts( 'SFS Family Information for %1',  array( 1 => $displayName ) ) );
        }

        // set up tabs
        require_once 'SFS/Form/Family/TabHeader.php';
        SFS_Form_Family_TabHeader::build( $this );
    }

    function buildQuickForm( ) {
        $className = CRM_Utils_String::getClassName( $this->_name );

        $buttons   = array();
        
        if ( $className !== 'Diversity' ) {
            $buttons[] = array ( 'type'      => 'next',
                                 'name'      => ts('Save and Next'),
                                 'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',  );
        } else {
            $buttons[] = array ( 'type'      => 'submit',
                                 'name'      => ts('Save'),
                                 'isDefault' => true   );
        }

        $buttons[] = array ( 'type'      => 'cancel',
                             'name'      => ts('Cancel') );

        $this->addButtons( $buttons );
    }

    function getTemplateFileName( ) {
        if ( $this->controller->getPrint( ) == CRM_Core_Smarty::PRINT_NOFORM ||
             $this->getVar( '_studentId' ) <= 0 ||
             ( $this->_action & CRM_Core_Action::DELETE ) ) {
            return parent::getTemplateFileName( );
        } else {
            return 'CRM/common/TabHeader.tpl';
        }
    }

    function endPostProcess( )
    {

        // lets update the updatedBy and updatedDate fields in the student record
        $query = "
UPDATE civicrm_value_school_information
SET    updated_by = %1,
       updated_date = %2
WHERE  entity_id = %3
";
        
        $session = CRM_Core_Session::singleton( );

        require_once 'CRM/Utils/Date.php';
        $params = array( 1 => array( $session->get( 'userID' ), 'Integer' ),
                         2 => array( CRM_Utils_Date::currentDBDate( ), 'Timestamp' ),
                         3 => array( $this->_studentId, 'Integer' ) );

        $dao = CRM_Core_DAO::executeQuery( $query, $params );

        $className = CRM_Utils_String::getClassName( $this->_name );

        $title = SFS_Form_Family_TabHeader::getSubPageInfo( $this, $className );
        CRM_Core_Session::setStatus( ts( 'Your %1 information has been saved.',
                                         array( 1 => $title ) ) );

        if ( $this->controller->getButtonName('submit') == "_qf_{$className}_next" ) {
            $nextTab = SFS_Form_Family_TabHeader::getNextSubPage( $this, $className );
            $nextUrl = CRM_Utils_System::url( 'civicrm/sfschool/family/' . strtolower($nextTab),
                                              "reset=1&cid={$this->_studentId}&pid={$this->_parentId}" );
            CRM_Utils_System::redirect( $nextUrl );
        } else if ( $className == 'Diversity' ) {
            $displayName = CRM_Contact_BAO_Contact::displayName( $this->_studentId );
            $taskList    = $this->isAppCompleted( );
            if ( $taskList['is_completed'] ) {
                $familyUrl = CRM_Utils_System::url( 'civicrm/sfschool/family',
                                                    "reset=1&cid={$this->_studentId}&pid={$this->_parentId}" );
                $session   = CRM_Core_Session::singleton();
                $session->getStatus( true );
                $session->setStatus( ts( "Thank you for completing the SFS Family Information Online Forms. 
Please note, you may access your account at any time to update or edit your records through the SFS Parent Portal.<br />At any time, you can edit your %1 for %2 from your SFS Parent Portal",
                                         array( 1 => "<a href='{$familyUrl}'>Family Information</a>",
                                                2 => $displayName ) ) );
                CRM_Utils_System::redirect( CRM_Utils_System::url('civicrm/sfschool/family/complete', 
                                                                  "reset=1&cid={$this->_studentId}&pid={$this->_parentId}") );
            }
        }
    }

    function findDupe( $params )
    {
        $dupeId = false;
        $input  = array();
        foreach ( array('civicrm_contact', 'civicrm_email') as $table ) {
            if ( array_key_exists($table, $params) ) {
                foreach ( $params[$table] as $field => $value ) {
                    $input[$field] = CRM_Utils_Type::escape( $params[$table][$field], 'String' );
                }
            }
        }
        foreach ( array('civicrm_relationship') as $table ) {
            if ( array_key_exists($table, $params) ) {
                foreach ( $params[$table] as $field => $value ) {
                    $input[$field] = CRM_Utils_Type::escape( $params[$table][$field], 'Integer' );
                }
            }
        }

        // 1. make first check based on relationship
        if ( $input['relationship_type_id'] && $input['contact_id_a'] ) {
            $sql    = "
SELECT cc.id FROM civicrm_contact cc 
INNER JOIN civicrm_relationship cr ON cc.id = cr.contact_id_b AND 
           cr.contact_id_a = {$input['contact_id_a']} AND 
           cr.relationship_type_id = {$input['relationship_type_id']} 
WHERE cc.first_name = '{$input['first_name']}' AND 
      cc.last_name  = '{$input['last_name']}'
LIMIT 1";
            $dupeId = CRM_Core_DAO::singleValueQuery($sql);
        }

        // 2. if no dupe is found based on rel AND email is present, do another check based on email.
        // Note: this sequence of 1->2 is important to avoid creating duplicate contact for cases when user 
        // actually just want to specify / add an email to existing contact /w only first & last name
        if ( !$dupeId && !empty( $input['email'] ) ) {
            $sql    = "
SELECT cc.id FROM civicrm_contact cc 
INNER JOIN civicrm_email ce ON cc.id = ce.contact_id
WHERE cc.first_name = '{$input['first_name']}' AND 
      cc.last_name =  '{$input['last_name']}'  AND 
      ce.email = '{$input['email']}' LIMIT 1";
            $dupeId = CRM_Core_DAO::singleValueQuery($sql);
        }
        return $dupeId;
    }

    function isAppCompleted( $studentID = null ) {
        if ( ! $studentID ) {
            $studentID = $this->_studentId;
        }

        require_once 'api/v2/Relationship.php';
        $taskList = array( 'household'    => true,
                           'emergency'    => true,
                           'medical'      => true,
                           'release'      => true,
                           'diversity'    => true );
        
        $fieldMapper = array( self::RELATION_TABLE      => 
                              array( 'parent_index'           => array( 'op'    => '',
                                                                        'value' => 'IS NOT NULL' )),
                              self::MEDICAL_INFO_TABLE  => 
                              array( 'medical_authorization'  => array( 'op'    => '=',
                                                                        'value' => 1   )),
                              self::SCHOOL_INFO_TABLE   => 
                              array( 'activity_authorization' => array( 'op'    => '=',
                                                                        'value' => 1  ),  
                                     'handbook_authorization' => array( 'op'    => '=',
                                                                        'value' => 1  )),
                              self::RACE_ETHNICITY_TABLE =>
                              array( 'race_family_structure'  => array( 'op'    => '',
                                                                        'value' => 'IS NOT NULL' )),
                              );
        $params  = array( 'contact_id' => $studentID );

        // Household
        $relationships = civicrm_get_relationships( $params, null, array('Child of') );
        if ( $relationships['is_error'] ) {
            $taskList['household'] = false;
        } else {
            $query  = "SELECT COUNT(*) FROM ".self::RELATION_TABLE." WHERE entity_id IN(". implode( ',', array_keys($relationships['result']) ) .")";
            $query  =  $this->_buildWhereClause( $query, $fieldMapper[self::RELATION_TABLE] );
            $result = CRM_Core_DAO::singleValueQuery($query);

            if ( count($relationships['result']) > $result ) {
                $taskList['household'] = false;
            }
        }
        
        // Emergency
        $relationships = civicrm_contact_relationship_get( $params, null, array('Emergency Contact Of') );
        if ( $relationships['is_error'] ) {
            $taskList['emergency'] = false;
        } else {
            if ( count($relationships['result']) < 2 ) {
                $taskList['emergency'] = false;
            }
        }

        // Medical
        $query  = "SELECT COUNT(*) FROM ".self::MEDICAL_INFO_TABLE." WHERE entity_id = ".$studentID;
        $query  = $this->_buildWhereClause( $query, $fieldMapper[self::MEDICAL_INFO_TABLE] );
        $result = CRM_Core_DAO::singleValueQuery($query);
        if ( $result < 1 ) {
            $taskList['medical'] = false;
        }
        
        // Release
        $query  = "SELECT COUNT(*) FROM ".self::SCHOOL_INFO_TABLE." WHERE entity_id = ".$studentID;
        $query  = $this->_buildWhereClause( $query, $fieldMapper[self::SCHOOL_INFO_TABLE] );
        $result = CRM_Core_DAO::singleValueQuery($query);
        if ( $result < 1 ) {
            $taskList['release'] = false;
        }
        
        // Diversity
        $query  = "SELECT COUNT(*) FROM ".self::RACE_ETHNICITY_TABLE." WHERE entity_id = ".$studentID;
        $query  = $this->_buildWhereClause( $query, $fieldMapper[self::RACE_ETHNICITY_TABLE] );
        $result = CRM_Core_DAO::singleValueQuery($query);
        if ( $result < 1 ) {
            $taskList['diversity'] = false;
        }

        $taskList['is_completed'] = true;
        foreach ( $taskList as $task => $value ) {
            if ( !$value ) {
                $taskList['is_completed'] = false;
                break;
            }
        }

        return $taskList;
    }

    function _buildWhereClause( $query, $mapper ) {
        $addWhereClause = array( );
        
        foreach( $mapper as $field => $values ) {
            $addWhereClause[ ] = "{$field} {$values['op']} {$values['value']}";  
        }
        if ( !empty($addWhereClause) ) {
            $query .= " AND ". implode(" AND ", $addWhereClause);   
        }

        return $query;
    }
}

