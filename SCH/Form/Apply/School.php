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

class SFS_Form_Apply_School extends SFS_Form_Apply {
    const 

        CUSTOM_TABLE = 'civicrm_value_school_ideal_information';

    function preProcess() {

        parent::preProcess();
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $this->_schoolInformation =  CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomGroup', 
                                                               self::CUSTOM_TABLE, 'id', 'table_name' );
        
        $groupTree = CRM_Core_BAO_CustomGroup::getTree( 'Individual',
                                                        $this,
                                                        $this->_cid,
                                                        $this->_schoolInformation );

        $this->_groupTreeFinal = CRM_Core_BAO_CustomGroup::formatGroupTree( $groupTree , 1 , $this);
         foreach ( $this->_groupTreeFinal as $gid => $groupTree ) {
            foreach ( $groupTree['fields'] as $fid => $fieldTree ) {
                $this->_detailMapper[] = "custom_{$fid}";
            }
        }
    }

     function setDefaultValues( ) {
        
        $defaults = array( );
        $params = array( 'contact_id' => $this->_cid );
        if ( isset( $this->_cid ) ) {
            
            foreach ( $this->_groupTreeFinal as $groupId => $groupValue ) {
                if ( array_key_exists('fields',$groupValue  ) ) {
                    foreach ( $groupValue['fields'] as $key => $value ) {
                        $defaults[$value['column_name']] = $value['element_value'];                            
                    }   
                }
            }
            
            CRM_Contact_BAO_Contact::retrieve( $params, $data );
            
        }
        $defaults['school_address']  = $data['address'][1]['street_address'];
        $defaults['city']            = $data['address'][1]['city'];
        $defaults['country']         = $data['address'][1]['country_id'];
        $defaults['state']           = $data['address'][1]['state_province_id'];
        $defaults['zip']             = $data['address'][1]['postal_code'];
        $defaults['school_phone_number'] = $data['phone'][1]['phone'];
        
        return $defaults;   
        
     }

    function buildQuickForm( ) {

        $this->add( 'text', 'current_school', ts('Current School:') );
        $this->add( 'text', 'current_grade', ts('Current Grade:') );
        $this->add( 'text', 'dates_attended', ts('Dates of Attended:') );
        $this->add( 'text', 'school_address', ts('School Address:') );
        $this->add( 'text', 'city', ts('City:') );
        $this->add( 'select', 'country', ts('Country:'), array('' => '- Select -') +  CRM_Core_PseudoConstant::country( ) ); 
        $this->add( 'select', 'state', ts('State:'), array('' => '- Select -') +  CRM_Core_PseudoConstant::stateProvince( ) );
        $this->add( 'text', 'zip', ts('Zip:') );
        $this->add( 'text', 'school_phone_number', ts('School Phone Number:') );
        $this->add( 'text', 'name_of_head_of_school', ts('Name of Head of school:') );
        $this->add( 'text', 'othe_school_attended', ts('Other School Attended:') );  

        parent::buildQuickForm( );
    }

    function postProcess() 
    {

        $session =& CRM_Core_Session::singleton( );
        $params = $this->controller->exportValues( $this->_name );
        
        $contactID = $session->get( 'inserted_id' );
        $currentSchool       = $params['current_school'];
        $currentGrade        = $params['current_grade'];
        $datesAttended       = $params['dates_attended'];
        $otherSchoolAttended = $params['other_schools_attended'];
        $nameofHead          = $params['head_of_school'];
        $address = array(
                         1 => array(
                                    'city'              => $params['city'],
                                    'state_province_id' => $params['state'],
                                    'country_id'        => $params['country'],
                                    'postal_code'       => $params['zip'],
                                    'street_address'    => $params['school_address'],
                                    'location_type'     => 'Home',
                                    ),
                         );
        $phone = array(
                       1 => array(
                                  'phone' => $params['school_phone_number']
                                  ),
                       );
                
        $contactParams = array(
                         'contact_id' => $this->_cid,
                         'phone'      => $phone,
                         'address'    => $address,
                         );
        
        CRM_Contact_BAO_Contact::create($contactParams);
        $customFields = CRM_Core_BAO_CustomField::getFields( 'Individual' );
        require_once 'CRM/Core/BAO/CustomValueTable.php';
        $query = "DELETE FROM " . self::CUSTOM_TABLE . " WHERE entity_id = %1";
        $contactParams = array( 1 => array( $contactID, 'Integer') );
        
        $customParams = array(
                              $this->_detailMapper[0]. '_-1' => CRM_Utils_Array::value( 'current_school', $params),
                              $this->_detailMapper[1]. '_-1' => CRM_Utils_Array::value( 'current_grade', $params),
                              $this->_detailMapper[2]. '_-1' => CRM_Utils_Array::value( 'dates_attended', $params),
                              $this->_detailMapper[3]. '_-1' => CRM_Utils_Array::value( 'name_of_head_of_school', $params),
                              $this->_detailMapper[4]. '_-1' => CRM_Utils_Array::value( 'othe_school_attended', $params)
                              );
        CRM_Core_DAO::executeQuery( $query, $contactParams );

        CRM_Core_BAO_CustomValueTable::postProcess( $customParams,
                                                    $customFields,
                                                    'civicrm_contact',
                                                    $this->_cid,
                                                    'Contact' );

        parent::endPostProcess( );
    }
}
