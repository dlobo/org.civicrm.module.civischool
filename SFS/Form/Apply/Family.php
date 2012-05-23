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
require_once 'CRM/Contact/BAO/Relationship.php';
require_once 'CRM/Contact/BAO/Contact.php';

class SFS_Form_Apply_Family extends SFS_Form_Apply {
    
    const
        
        CUSTOM_TABLE = 'civicrm_value_family_information';
    
    
    function buildQuickForm( ) {
        
        $prefix = CRM_Core_PseudoConstant::individualPrefix( );
        if ( !empty( $prefix ) ) {
            $this->add('select', 'p1_prefix_id', ts('Prefix'), array('' => '') + $prefix );
        }
        
        $this->add('text', 'p1_name', ts('Parent\'s Name'));
        
        $this->add('select',
                   'p1_relationship_type_id',
                   ts('Relationship Type'),
                   array('' => ts('- select -')) +
                   CRM_Contact_BAO_Relationship::getContactRelationshipType( null, null, null, 'Individual' )
                   );
        
        $this->add('text', 'p1_home_address', ts('Home Address'));
        
        $this->add('select', 'p1_country', ts('Country'), array('' => '') +  CRM_Core_PseudoConstant::country( ) );
        
        $config =& CRM_Core_Config::singleton( );
        $countryDefault = $config->defaultContactCountry;
        
        $this->add('select', 'p1_state', ts('State'), array('' => '') +  
                   CRM_Core_PseudoConstant::stateProvinceForCountry( $countryDefault ) );
        
        $this->add('text', 'p1_city', ts('City'));
        $this->add('text', 'p1_zip', ts('Zip'));
        $this->add('text', 'p1_home_phone', ts('Home Phone'));
        $this->add('text', 'p1_cell', ts('Cell'));
        $this->add('text', 'p1_email', ts('Email'));
        $this->add('text', 'p1_employer', ts('Employer'));
        $this->add('text', 'p1_occupation', ts('Occupation'));
        $this->add('text', 'p1_position', ts('Position'));
        $this->add('text', 'p1_business_address', ts('Business Address'));
        $this->add('text', 'p1_business_phone', ts('Business Phone'));
        
        // Fields for parent 2
        
        $prefix = CRM_Core_PseudoConstant::individualPrefix( );
        if ( !empty( $prefix ) ) {
            $this->add('select', 'p2_prefix_id', ts('Prefix'), array('' => '') + $prefix );
        }
        
        $this->add('text', 'p2_name', ts('Parent\'s Name'));
        
        $this->add('select',
                   'p2_relationship_type_id',
                   ts('Relationship Type'),
                   array('' => ts('- select -')) +
                   CRM_Contact_BAO_Relationship::getContactRelationshipType( null, null, null, 'Individual' )
                   );
        
        $this->add('text', 'p2_home_address', ts('Home Address'));
        
        $this->add('select', 'p2_country', ts('Country'), array('' => '') +  CRM_Core_PseudoConstant::country( ) );
        
        $config =& CRM_Core_Config::singleton( );
        $countryDefault = $config->defaultContactCountry;
        
        $this->add('select', 'p2_state', ts('State'), array('' => '') +  
                   CRM_Core_PseudoConstant::stateProvinceForCountry( $countryDefault ) );
        
        $this->add('text', 'p2_city', ts('City'));
        $this->add('text', 'p2_zip', ts('Zip'));
        $this->add('text', 'p2_home_phone', ts('Home Phone'));
        $this->add('text', 'p2_cell', ts('Cell'));
        $this->add('text', 'p2_email', ts('Email'));
        $this->add('text', 'p2_employer', ts('Employer'));
        $this->add('text', 'p2_occupation', ts('Occupation'));
        $this->add('text', 'p2_position', ts('Position'));
        $this->add('text', 'p2_business_address', ts('Business Address'));
        $this->add('text', 'p2_business_phone', ts('Business Phone'));
        
        // Common Field
        
        $this->add('text', 'p_language', ts('What language, other than English, is regularly spoken at home'));
        
        //  get all the field ids of the labels we are interested in
        $sql = "
SELECT     f.id, f. label, f.column_name
FROM       civicrm_custom_field f
INNER JOIN civicrm_custom_group g ON f.custom_group_id = g.id
WHERE      g.table_name = '". self::CUSTOM_TABLE ."'
AND column_name IN ('known_language','marital_status','living_status','correspondence_info','billing_info','additional_info')
";
        $dao = CRM_Core_DAO::executeQuery( $sql );
        
        require_once 'CRM/Core/BAO/CustomField.php';
        $fieldNames = array( );
        while ( $dao->fetch( ) ) {
            $fieldsNames[] = $dao->column_name;
            CRM_Core_BAO_CustomField::addQuickFormElement( $this,
                                                           $dao->column_name,
                                                           $dao->id );
        }
        
        $this->assign( 'fieldNames', $fieldsNames );
        
        parent::buildQuickForm( );
    }
    
    function postProcess() 
    {
        parent::endPostProcess( );
    }
}
