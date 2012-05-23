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
require_once 'api/v2/Relationship.php';

class SCH_Form_Family_Medical extends SCH_Form_Family {

    protected $_infoElementMapper  = array( 'Allergies' => array( 'Nuts'    => 'nuts_specifics',
                                                                  'Dairy'   => 'dairy_products_specifics' ,
                                                                  'Insects' => 'insects_specifics' ,
                                                                  'Animals' => 'animals_specifics',
                                                                  'Medicine'=> 'medicine_specifics',
                                                                  'Other'   => 'other_specifics'),
                                            'Medical'   => array( 'Asthma'  => 'asthma_specifics',
                                                                  'Other'   => 'other') );

    function preProcess( ) {
        parent::preProcess();

        require_once 'CRM/Core/BAO/CustomGroup.php';

        $this->_medDetailId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomGroup',
                                                           SCH_Form_Family::MEDICAL_DETAILS_TABLE, 'id', 'table_name' );
        $this->_medInfoId   = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomGroup',
                                                           SCH_Form_Family::MEDICAL_INFO_TABLE, 'id', 'table_name' );
        $detailGroupTree    = CRM_Core_BAO_CustomGroup::getTree( 'Contact',
                                                                 $this,
                                                                 $this->_studentId,
                                                                 $this->_medDetailId );
        $this->_detailGroupTree = CRM_Core_BAO_CustomGroup::formatGroupTree( $detailGroupTree, 1, $this );
        foreach ( $this->_detailGroupTree as $gid => $groupTree ) {
            foreach ( $groupTree['fields'] as $fid => $fieldTree ) {
                $this->_detailMapper[] = "custom_{$fid}";
            }
        }

        // relationship / counselor part
        $this->_relDataId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomGroup',
                                                         SCH_Form_Family::RELATION_TABLE, 'id', 'table_name' );
        $this->_relTypeId = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_RelationshipType',
                                                         'Child Of', 'id', 'name_a_b' );
        $relationships = civicrm_get_relationships( array( 'contact_id' => $this->_studentId ),
                                                    array( 'contact_id' => $this->_parentId ),
                                                    array('Child of') );
        if ( !$relationships['is_error'] && is_array($relationships['result']) ) {
            foreach ( $relationships['result'] as $rid => $rFields ) {
                $this->_relId = $rid;
                break;
            }
        } else {
            CRM_Core_Error::fatal();
        }

        $relGroupTree = CRM_Core_BAO_CustomGroup::getTree( 'Relationship',
                                                           $this,
                                                           $this->_relId,
                                                           $this->_relDataId,
                                                           $this->_relTypeId );
        $this->_relGroupTree = CRM_Core_BAO_CustomGroup::formatGroupTree( $relGroupTree, 1, $this );
        foreach ( $this->_relGroupTree as $gid => $groupTree ) {
            foreach ( $groupTree['fields'] as $fid => $fieldTree ) {
                $this->_relationMapper[$fieldTree['column_name']] = $fieldTree["element_name"];
            }
        }
    }

    function setDefaultValues( )
    {
        $defaults = array( );

        // 1. set defaults for medical details
        $isAllergy = $isCondition = null;
        $query = "SELECT * FROM ". SCH_Form_Family::MEDICAL_DETAILS_TABLE . " WHERE entity_id = %1";
        $contactParams = array( 1 => array( $this->_studentId, 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $query, $contactParams );
        while( $dao->fetch( ) ) {
            if ( isset($this->_infoElementMapper[$dao->medical_type]) &&
                 isset($this->_infoElementMapper[$dao->medical_type][$dao->name]) ) {
                $isAllergy   = $dao->medical_type == 'Allergies' ? 1 : $isAllergy;
                $isCondition = $dao->medical_type == 'Medical' ? 1 : $isCondition;
                $defaults[$this->_infoElementMapper[$dao->medical_type][$dao->name]] = $dao->details;
            }
        }

        // 2. set defaults for medical info
        CRM_Core_BAO_CustomGroup::setDefaults( $this->_infoGroupTree, $infoCustomDefaults);
        foreach( $this->_infoMapper as $colName => $eleName ) {
            $defaults[$colName] = $infoCustomDefaults[$eleName];
        }
        if ( empty( $infoCustomDefaults ) ) {
            $relationships = civicrm_get_relationships( array( 'contact_id' => $this->_studentId ),
                                                        null, array( 'Sibling of' ) );
            if ( ! $relationships['is_error'] ) {
                foreach ( $relationships['result'] as $relVal ) {
                    $siblingId = $relVal['cid'];
                    break;
                }
                $infoGroupTree = CRM_Core_BAO_CustomGroup::getTree( 'Individual',
                                                                    $this,
                                                                    $siblingId,
                                                                    $this->_medInfoId );
                $infoGroupTree = CRM_Core_BAO_CustomGroup::formatGroupTree( $infoGroupTree, 1, $this );
                foreach ( $infoGroupTree as $gId => $groupTree ) {
                    foreach ( $groupTree['fields'] as $fId => $fieldTree ) {
                        if ( ! in_array($fieldTree['column_name'], array('child_insured', 'medical_authorization')) ) {
                            $defaults[$fieldTree['column_name']] = $fieldTree['element_value'];
                        }
                    }
                }
            }
        }

        $defaults['is_allergy']   = $defaults['medical_authorization'] ? ($isAllergy ? 1 : 0) : null;
        $defaults['is_condition'] = $defaults['medical_authorization'] ? ($isCondition ? 1 : 0) : null;

        // 3. set defaults for relationship
        CRM_Core_BAO_CustomGroup::setDefaults( $this->_relGroupTree, $relCustomDefaults);
        foreach( $this->_relationMapper as $colName => $eleName ) {
            $defaults[$colName] = $relCustomDefaults[$eleName];
        }

        return $defaults;
    }

    function buildQuickForm( ) {
        $infoGroupTree = CRM_Core_BAO_CustomGroup::getTree( 'Individual',
                                                            $this,
                                                            $this->_studentId,
                                                            $this->_medInfoId );
        $this->_infoGroupTree = CRM_Core_BAO_CustomGroup::formatGroupTree( $infoGroupTree, 1, $this );
        foreach ( $this->_infoGroupTree as $gId => $groupTree ) {
            foreach ( $groupTree['fields'] as $fId => $fieldTree ) {
                if ( ! in_array($fieldTree['column_name'], array('child_insured', 'medical_authorization')) ) {
                    CRM_Core_BAO_CustomField::addQuickFormElement($this, $fieldTree['column_name'],
                                                                  $fieldTree['id'], false, $fieldTree['is_required']);
                }
                $this->_infoMapper[$fieldTree['column_name']] = $fieldTree['element_name'];
            }
        }
        $this->add( 'checkbox', 'child_insured', null );
        $this->add( 'checkbox', 'medical_authorization', ts('Medical Authorization'), null, true );

        $this->add( 'checkbox', 'counselor_authorization', ts('Counselor Authorization'), null, false );

        // for Allergies
        $this->addYesNo  ('is_allergy', ts( 'Allergies' ));
        $this->addElement('textarea', 'nuts_specifics', ts('Nuts Specifics'), array('rows'=> 1, 'cols' => 40));
        $this->addElement('textarea', 'dairy_products_specifics', ts('Dairy Products Specifics'), array('rows'=> 1, 'cols' => 40));
        $this->addElement('textarea', 'animals_specifics', ts('Animals Specifics'), array('rows'=> 1, 'cols' => 40));
        $this->addElement('textarea', 'medicine_specifics', ts('Medicine Specifics'), array('rows'=> 1, 'cols' => 40));
        $this->addElement('textarea', 'insects_specifics', ts('Insects Specifics'), array('rows'=> 1, 'cols' => 40));
        $this->addElement('textarea', 'other_specifics', ts('Other Specifics'), array('rows'=> 1, 'cols' => 40));

        // for Medical Conditions
        $this->addYesNo  ('is_condition', ts( 'Medical Conditions' ));
        $this->addElement('textarea', 'asthma_specifics', ts('Asthma Specifics'), array('rows'=> 1, 'cols' => 40));
        $this->addElement('textarea', 'other', ts('Other'), array('rows'=> 1, 'cols' => 40));

        // parent::buildQuickForm( );

        $buttons   = array();
        $buttons[] = array ( 'type'      => 'next',
                             'name'      => ts('Save and Next'),
                             'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                             'js'        => array( 'onclick' => 'return confirmClicks();') );

        $buttons[] = array ( 'type'      => 'cancel',
                             'name'      => ts('Cancel') );

        $this->addButtons( $buttons );

        $this->addFormRule( array( 'SCH_Form_Family_Medical', 'formRule' ) );
    }

    static function formRule( $params, $files ) {
        $errors   = array( );

        if ( ! CRM_Utils_Array::value( 'child_insured', $params ) ) {
            $fieldsRequired = array( 'insurance_company' => 'Insurance Company',
                                     'physician_name'    => 'Physician Name'   ,
                                     'physician_number'  => 'Physician Phone' ,
                                     );
            foreach ( $fieldsRequired as $fieldName => $title ) {
                if ( empty( $params[$fieldName] ) ) {
                    $errors[$fieldName] = "{$title} is a required field";
                }
            }
        }
        return empty( $errors ) ? true : $errors;
    }

    function postProcess()
    {
        $params = $this->controller->exportValues( $this->_name );
        require_once 'CRM/Core/BAO/CustomValueTable.php';

        // especial treatment for checkbox values
        $checkboxes = array('child_insured', 'medical_authorization');
        foreach ( $checkboxes as $cb ) {
            $params[$cb] = CRM_Utils_Array::value( $cb, $params, "null" );
        }

        // 1. _____medical details_____
        $customParams = array( );
        $count = 1;
        foreach( $this->_infoElementMapper as $medicalType => $nameDetails ) {
            foreach( $nameDetails as $name => $nameAlias ) {
                if ( ! empty( $params[$nameAlias] ) ) {
                    $customParams[$this->_detailMapper[0] . '_-' . $count] = $medicalType;
                    $customParams[$this->_detailMapper[1] . '_-' . $count] = $name;
                    $customParams[$this->_detailMapper[2] . '_-' . $count] = $params[$nameAlias];
                    $count++;
                }
            }
        }

        // always delete previous entries and add new ones
        $query = "DELETE FROM " . SCH_Form_Family::MEDICAL_DETAILS_TABLE . " WHERE entity_id = %1";
        $contactParams = array( 1 => array( $this->_studentId, 'Integer') );
        CRM_Core_DAO::executeQuery( $query, $contactParams );

        CRM_Core_BAO_CustomValueTable::postProcess( $customParams,
                                                    $this->_detailGroupTree[$this->_medDetailId]['fields'],
                                                    'civicrm_contact',
                                                    $this->_studentId,
                                                    'Contact' );

        // 2. _____medical info______
        $customParams = array( );
        foreach( $this->_infoMapper as $colName => $eleName ) {
            if ( $params[$colName] ) {
                $customParams[$eleName] = $params[$colName];
            }
        }
        $customFields =
            CRM_Core_BAO_CustomField::getFields( 'Individual' );
        CRM_Core_BAO_CustomValueTable::postProcess( $customParams,
                                                    $customFields,
                                                    'civicrm_contact',
                                                    $this->_studentId,
                                                    'Individual' );

        // 3. _____relationship data______
        $customParams = array( $this->_relationMapper['counselor_authorization']
                               => CRM_Utils_Array::value( 'counselor_authorization',
                                                          $params, "null" ) );
        $customFields = CRM_Core_BAO_CustomField::getFields( 'Relationship', false, true, $this->_relTypeId );
        CRM_Core_BAO_CustomValueTable::postProcess( $customParams,
                                                    $customFields,
                                                    'civicrm_relationship',
                                                    $this->_relId,
                                                    'Relationship' );

        parent::endPostProcess( );
    }
}
