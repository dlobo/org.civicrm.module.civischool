<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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

require_once 'SFS/Form/Apply.php';
require_once 'CRM/Core/PseudoConstant.php';
require_once 'CRM/Contact/BAO/Contact.php';
require_once 'CRM/Core/BAO/CustomField.php';

class SFS_Form_Apply_Applicant extends SFS_Form_Apply {
    
    const
        
        CUSTOM_TABLE = 'civicrm_value_school_information';

    function preProcess( ) {

        parent::preProcess();
        
        require_once 'CRM/Core/BAO/CustomGroup.php';

        $this->_customTable = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomGroup', 
                                                               self::CUSTOM_TABLE, 'id', 'table_name' );
        
        $groupTree = CRM_Core_BAO_CustomGroup::getTree( 'Individual',
                                                        $this,
                                                        
                                                        $this->_customTable );
        
        
        $this->_detailGroupTree = CRM_Core_BAO_CustomGroup::formatGroupTree( $groupTree, 1, $this );
        
        foreach ( $this->_detailGroupTree as $gid => $groupTree ) {
            foreach ( $groupTree['fields'] as $fid => $fieldTree ) {
                $this->_detailMapper[] = "custom_{$fid}";
            }
        }
        
        $this->_schoolTableCol  = 'grade';
    }
    
    function setDefaultValues( ) {
        
        $defaults = array( );
        
        $params = array( 'contact_id' => $this->_cid );
        if ( isset( $this->_cid ) ) {
            
            foreach ( $this->_detailGroupTree as $groupId => $groupValue ) {
                if ( array_key_exists('fields',$groupValue  ) ) {
                    foreach ( $groupValue['fields'] as $key => $value ) {
                        if ( $value['column_name'] == $this->_schoolTableCol ) {
                            $defaults['grade'] = $value['element_value'];
                        }
                    }   
                }
            }
            
            CRM_Contact_BAO_Contact::retrieve( $params, $data );
            $dataFields = array('first_name', 'last_name' , 'middle_name' , 'nick_name','gender_id','birth_date');
            foreach ( $data as $dataKey => $dataVal ) {
                if ( ! in_array($dataKey, $dataFields) ) {
                    unset($data[$dataKey]);
                }
            }
        }
        $defaults['applicants_first_name']  = $data['first_name'];
        $defaults['applicants_middle_name'] = $data['last_name'];
        $defaults['applicants_last_name']   = $data['last_name'];
        $defaults['prefered_name']          = $data['nick_name'];
        $defaults['gender']                 = $data['gender_id'];
        $defaults['dob']                    = $data['birth_date'];
        
        return $defaults;        
    }
        
    function buildQuickForm( ) {
        
        $this->addElement( 'text', 'applicants_first_name', ts('Applicants First Name:') );
        $this->addElement( 'text', 'applicants_middle_name', ts('Applicants Middle Name:') );
        $this->addElement( 'text', 'applicants_last_name', ts('Applicants Last Name:') );
        $this->addElement( 'text', 'prefered_name', ts('Prefered Name/Nickname:') );
        $this->addElement( 'select', 'gender', ts('Gender:') ,array( '' => ts( '- select -' ) ) +
                     CRM_Core_PseudoConstant::gender( ));        
        $sql = "
SELECT     v.label, v.value
FROM       civicrm_option_value v
LEFT JOIN civicrm_option_group g ON g.id = v.option_group_id
WHERE      g.name LIKE '%grade%'
";
        $dao = CRM_Core_DAO::executeQuery( $sql );
        
        require_once 'CRM/Core/BAO/CustomField.php';
        require_once 'CRM/Contact/Form/Edit/Address.php';
        $fieldNames = array( );
        while ( $dao->fetch( ) ) {
            $fieldsNames[$dao->label] = $dao->label;
        }
        
        $this->addElement('select', 'grade', ts('Applying for Grade:'), array('' => '- Select -') +  $fieldsNames );        
        $this->addElement( 'text', 'year', ts('For Year:') );
        $this->addDate('dob', ts('Date of Birth:') );
        $this->addElement( 'text', 'current_school', ts('Current School:') );
 
        parent::buildQuickForm( );
    }

    function postProcess() {

        $params = $this->controller->exportValues( $this->_name );
        
        $session =& CRM_Core_Session::singleton( );        
        $contactParams = array( 'first_name'   => $params['applicants_first_name'],
                                'middle_name'  => $params['applicants_middle_name'],
                                'last_name'    => $params['applicants_last_name'],
                                'nick_name'    => $params['prefered_name'],
                                'contact_type' => 'Individual',
                                'gender_id'    => $params['gender'],
                                'birth_date'   => $params['dob']
                                );
        
        $get_old_id = $this->_cid;
        if(isset( $get_old_id )) {
            $contactParams['contact_id'] = $get_old_id;                
        }
        
        require_once 'CRM/Contact/BAO/Contact.php';
        $contactId = CRM_Contact_BAO_Contact::add( $contactParams );        
        if ( $get_old_id ) {
            $cid = $get_old_id;
        } else {
            $cid = $contactId->id;
        }
        $session->set( $cid );

        $customFields = CRM_Core_BAO_CustomField::getFields( 'Individual' );
        
        require_once 'CRM/Core/BAO/CustomValueTable.php';
        $query = "DELETE FROM " . self::CUSTOM_TABLE . " WHERE entity_id = %1";
        $contactParams = array( 1 => array( $cid, 'Integer') );

        $customParams = array($this->_detailMapper[1]. '_-1' => CRM_Utils_Array::value( 'grade', 
                                                            $params));
        CRM_Core_DAO::executeQuery( $query, $contactParams );

        CRM_Core_BAO_CustomValueTable::postProcess( $customParams,
                                                    $customFields,
                                                    'civicrm_contact',
                                                    $cid,
                                                    'Contact' );        

        parent::endPostProcess( );
    }
}
