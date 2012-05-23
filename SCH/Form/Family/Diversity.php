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

require_once 'SCH/Form/Family.php';
require_once 'CRM/Core/BAO/CustomField.php';

class SCH_Form_Family_Diversity extends SCH_Form_Family {

    protected $_raceEthnicityMap;

    function preProcess( ) {
        parent::preProcess();
        $this->_raceEthnicityMap = array( );
    }

    function setDefaultValues( )
    {
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $defaults = $defaultsCustom = array( );
        $groupID  = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomGroup', SCH_Form_Family::RACE_ETHNICITY_TABLE, 'id', 'table_name' );

        $groupTree = CRM_Core_BAO_CustomGroup::getTree( 'Individual',
                                                        $this,
                                                        $this->_studentId,
                                                        $groupID
                                                        );
        $groupTree = CRM_Core_BAO_CustomGroup::formatGroupTree( $groupTree, 1, $this );
        CRM_Core_BAO_CustomGroup::setDefaults( $groupTree, $defaultsCustom );

        if ( !empty($defaultsCustom ) ) {
            $mappedFields = array_flip($this->_raceEthnicityMap);
            foreach( $defaultsCustom as $field => $value )  {
                list($fieldId, $dataId) = CRM_Core_BAO_CustomField::getKeyID( $field, true );
                $key = 'custom_' . $fieldId;
                if ( CRM_Utils_Array::value($key, $mappedFields) ) {
                    $defaults[$mappedFields[$key]] = $value;
                    $this->_raceEthnicityMap[$mappedFields[$key]] = "custom_{$fieldId}_{$dataId}";
                }
            }
        }

        return $defaults;
    }

    function formRule( $params, $files ) {
        $errors   = array( );
        $sections = array( ''                  => t('Race'),
                           '_hispanic'         => ts('Hispanic American/Latino') ,
                           '_asian'            => ts('Asian American') );

        foreach( $sections as $section => $sectionTitle ) {
            if ( CRM_Utils_Array::value( 'other', $params["race{$section}"] ) &&
                 ! CRM_Utils_Array::value( "race{$section}_other", $params ) ) {
                $errors["race{$section}_other"] = ts('Other %1 is require Field.',array( 1 => $sectionTitle));
            }
        }

        return $errors;
    }

    function buildQuickForm( ) {

        //  get all the field ids of the labels we are interested in
        $sql = "
SELECT     f.id, f. label, f.column_name, f.data_type
FROM       civicrm_custom_field f
INNER JOIN civicrm_custom_group g ON f.custom_group_id = g.id
WHERE      g.table_name = '". SCH_Form_Family::RACE_ETHNICITY_TABLE ."'
AND        f.column_name like 'race%'
";
        $dao = CRM_Core_DAO::executeQuery( $sql );

        $fieldNames = array( );
        while ( $dao->fetch( ) ) {
            $fieldNames[] = $dao->column_name;

            CRM_Core_BAO_CustomField::addQuickFormElement( $this,
                                                           $dao->column_name,
                                                           $dao->id,
                                                           false,
                                                           false );

            $this->_raceEthnicityMap[$dao->column_name] = 'custom_' . $dao->id;
        }

        $this->assign( 'fieldNames', $fieldsNames );

        parent::buildQuickForm( );

        $this->addFormRule( array( 'SCH_Form_Family_Diversity', 'formRule' ) );
    }

    function postProcess( )
    {
        $params = $this->controller->exportValues( $this->_name );
        require_once 'CRM/Core/BAO/CustomValueTable.php';

        $customData = array( );
        foreach ( $this->_raceEthnicityMap as $column_name => $field ) {
            if ( array_key_exists($column_name, $params) ) {
                $customData[$field] = $params[$column_name];
            }
        }

        $customFields =
            CRM_Core_BAO_CustomField::getFields( 'Individual', false, true, $this->_studentId );

        CRM_Core_BAO_CustomValueTable::postProcess( $customData,
                                                    $customFields,
                                                    'civicrm_contact',
                                                    $this->_studentId,
                                                    'Individual' );

        parent::endPostProcess( );
    }
}
