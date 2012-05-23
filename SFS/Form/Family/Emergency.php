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

require_once 'SFS/Form/Family.php';
require_once 'api/v2/Relationship.php';
require_once 'CRM/Core/BAO/CustomField.php';

class SFS_Form_Family_Emergency extends SFS_Form_Family {

    protected $_relationIds = array( 'contact'      => array(),
                                     'relationship' => array(),
                                     'custom'       => array() );
    const
        BLOCK_NUM = 3;

    function preProcess( ) {
        parent::preProcess();

        $this->_emergencyRelTypeId = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_RelationshipType', 
                                                                  'Emergency Contact Of', 'id', 'name_a_b' );
        $this->_emergencyTableCol  = 'relationship_name';
    }

    function setDefaultValues( ) 
    {
        $defaults = array( );
        $blockId  = 1;
        $hasSibling = false;
        $locationTypeIds = array_flip(CRM_Core_PseudoConstant::locationType());
        $phoneTypeIds    = array_flip(CRM_Core_PseudoConstant::phoneType());

        $params = array( 'contact_id' => $this->_studentId );
        $relationships = civicrm_contact_relationship_get( $params, null, array('Emergency Contact Of') );
        
        if ( $relationships['is_error'] ) {
            $siblings = civicrm_contact_relationship_get( $params, null, array('Sibling of') );
            
            if ( !$siblings['is_error'] ) {
                foreach ( $siblings['result'] as $sibling ) {
                    $siblingParams = array( 'contact_id' => $sibling['cid'] ); 
                    $emergencyRel  = civicrm_contact_relationship_get( $siblingParams, null, array('Emergency Contact Of') );
                    if ( !$emergencyRel['is_error'] ) {
                        $relationships = $emergencyRel;
                        $hasSibling    = true;
                        break;
                    }
                }
            }
        }

        $blockIdSpots = array( );
        for ( $i = 1 ; $i <= self::BLOCK_NUM ; $i++ ) {
            $blockIdSpots[$i] = 0;
        }

        $dataFields = array('first_name', 'last_name', 'email', 'phone');
        if ( is_array($relationships['result']) ) {
            foreach ( $relationships['result'] as $relationship ) {

                // use the description if available and numeric, else use lowest blockId
                if ( is_numeric( $relationship['description'] ) &&
                     (int)$relationship['description'] <= self::BLOCK_NUM ) {
                    $blockId = (int) $relationship['description'];
                } else {
                    $blockId = self::BLOCK_NUM + 1;
                    for ( $i = 1 ; $i <= self::BLOCK_NUM ; $i++ ) {
                        if ( ! $blockIdSpots[$i] ) {
                            $blockId = $i;
                            break;
                        }
                    }
                }
                    
                if ( $blockId > self::BLOCK_NUM ) {
                    break;
                }
            
                $blockIdSpots[$blockId] = 1;

                $this->_relationIds['ec_contact'][$blockId]   = $relationship['cid'];
                if ( !$hasSibling ) {
                    $this->_relationIds['relationship'][$blockId] = $relationship['id'];
                }

                $params['id'] = $params['contact_id'] = $relationship['cid'];
                $params['noRelationships'] = $params['noNotes'] = $params['noGroups'] = true;
                CRM_Contact_BAO_Contact::retrieve( $params, $data );
                
                foreach ( $data as $dataKey => $dataVal ) {
                    if ( ! in_array($dataKey, $dataFields) ) {
                        unset($data[$dataKey]);
                    }
                }

                // fix phone sequence
                $phone = array();
                foreach ( $data['phone'] as $phoneFields ) {
                    if ( $phoneFields['location_type_id'] == $locationTypeIds['Home'] ) {
                        if ( $phoneFields['phone_type_id'] == $phoneTypeIds['Mobile'] ) {
                            $phone[1] = $phoneFields;
                        }
                        if ( $phoneFields['phone_type_id'] == $phoneTypeIds['Phone'] ) {
                            $phone[2] = $phoneFields;
                        }
                        if ( $phoneFields['phone_type_id'] == $phoneTypeIds['Work'] ) {
                            $phone[3] = $phoneFields;
                        }
                    }
                }
                $data['phone'] = $phone;

                // fix email sequence
                $email = array();
                foreach ( $data['email'] as $emailFields ) {
                    if ( $emailFields['location_type_id'] == $locationTypeIds['Home'] ) {
                        $email[1] = $emailFields;
                    }
                }
                $data['email'] = $email;

                $defaults['ec_contact'][$blockId] = $data;
                
                $groupTree = CRM_Core_BAO_CustomGroup::getTree( 'Relationship', $this, $relationship['id'], 
                                                                -1, $this->_emergencyRelTypeId );

                foreach ( $groupTree as $gId => $gFields ) {
                    if ( array_key_exists('fields', $gFields) ) {
                        foreach ( $gFields['fields'] as $fId => $fFields ) {
                            if ( $fFields['column_name'] == $this->_emergencyTableCol ) {
                                $defaults['ec_contact'][$blockId]['relationship'] = $fFields['customValue'][1]['data'];
                                if ( !empty($fFields['customValue'][1]['id']) && !$hasSibling ) {
                                    $this->_relationIds['custom'][$blockId] = $fFields['customValue'][1]['id'];
                                }
                                break;
                            }
                        }
                    }
                }
            }
        } 

        return $defaults;
    }

    function formRule( $params, $files, $form ) {
        $errors   = array( );  
        $countFilled = 0;
        for ( $blockId = 1; $blockId <= self::BLOCK_NUM; $blockId++ ) {
            if ( !empty($params['ec_contact'][$blockId]['first_name']) ||
                 !empty($params['ec_contact'][$blockId]['last_name']) ||
                 !empty($params['ec_contact'][$blockId]['email'][1]['email']) ) {
                $countFilled ++; 
            }
        }
         
        if ( $countFilled < 2 ) {
            $errors['ec_contact[2][first_name]'] = ts("Please fill at least 2 contacts details.");
        }

        return $errors;
    }

    function buildQuickForm( ) {

        $attributes = CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Contact');

        require_once 'CRM/Contact/Form/Edit/Address.php';
        for ( $blockId = 1; $blockId <= self::BLOCK_NUM; $blockId++ ) {
            $this->addElement('text', "ec_contact[$blockId][first_name]"  ,
                              ts('First Name'), $attributes['first_name'] );
            $this->addElement('text', "ec_contact[$blockId][last_name]"   , 
                              ts('Last Name'), $attributes['last_name' ] );

            $this->addElement('text', "ec_contact[$blockId][email][1][email]"   , 
                              ts('Email'), CRM_Core_DAO::getAttribute('CRM_Core_DAO_Email', 'email') );
             $this->addRule( "ec_contact[$blockId][email][1][email]", ts('Email is not valid.'), 'email' );

            $this->addElement('text', "ec_contact[$blockId][relationship]", 
                              ts('Relationship'), $attributes['last_name' ] );
            $this->addElement('text', "ec_contact[$blockId][phone][1][phone]"  ,  
                              ts('Cell Phone'), $attributes['last_name' ] );

            $this->addElement('text', "ec_contact[$blockId][phone][2][phone]"  ,  
                              ts('Home Phone'), $attributes['last_name' ] );

            $this->addElement('text', "ec_contact[$blockId][phone][3][phone]"  ,  
                              ts('Work Phone'), $attributes['last_name' ] );
        }
        parent::buildQuickForm( );

        $this->addFormRule( array( 'SFS_Form_Family_Emergency', 'formRule' ) );
    }

    function postProcess() 
    {
        $params = $this->controller->exportValues( $this->_name );
        require_once 'CRM/Contact/BAO/Contact.php';
        require_once 'CRM/Core/BAO/CustomValueTable.php';
        require_once 'CRM/Dedupe/Finder.php';

        $fieldId      = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomField', 
                                                     $this->_emergencyTableCol, 'id', 'column_name' );  

        $locationTypeIds = array_flip(CRM_Core_PseudoConstant::locationType());
        $phoneTypeIds    = array_flip(CRM_Core_PseudoConstant::phoneType());

        for ( $blockId = 1; $blockId <= self::BLOCK_NUM; $blockId++ ) {
            if ( !empty($params['ec_contact'][$blockId]['first_name']) ||
                 !empty($params['ec_contact'][$blockId]['last_name']) ||
                 !empty($params['email'][$blockId]['email']) ) {

                $params['ec_contact'][$blockId]['email'][1]['location_type_id'] = $locationTypeIds['Home'];
                $params['ec_contact'][$blockId]['phone'][1]['location_type_id'] = $locationTypeIds['Home'];
                $params['ec_contact'][$blockId]['phone'][1]['phone_type_id']    = $phoneTypeIds['Mobile'];
                $params['ec_contact'][$blockId]['phone'][2]['location_type_id'] = $locationTypeIds['Home'];
                $params['ec_contact'][$blockId]['phone'][2]['phone_type_id']    = $phoneTypeIds['Phone'];
                $params['ec_contact'][$blockId]['phone'][3]['location_type_id'] = $locationTypeIds['Home'];
                $params['ec_contact'][$blockId]['phone'][3]['phone_type_id']    = $phoneTypeIds['Work'];

                $dropContactId = 0;
                $dedupeParams  = CRM_Dedupe_Finder::formatParams( $params['ec_contact'][$blockId], 'Individual' );
                $dedupeParams['civicrm_relationship'] = array( 'contact_id_a'         => $this->_studentId,
                                                               'relationship_type_id' => $this->_emergencyRelTypeId );
                if ( $dupeId = $this->findDupe( $dedupeParams ) ) {
                    $params['ec_contact'][$blockId]['contact_id'] = $dupeId;
                }
                if ( isset($this->_relationIds['ec_contact'][$blockId]) && 
                     !in_array($dupeId, $this->_relationIds['ec_contact']) ) {
                    // drop old relationship
                    $dropContactId = $this->_relationIds['ec_contact'][$blockId];
                } 

                $contactId = CRM_Contact_BAO_Contact::createProfileContact( $params['ec_contact'][$blockId],
                                                                            CRM_Core_DAO::$_nullArray );

                // create relationship if doesn't already exist
                $relationships = civicrm_get_relationships( array( 'contact_id' => $this->_studentId ), 
                                                            array( 'contact_id' => $contactId ), 
                                                            array( 'Emergency Contact Of' ) );
                $relationshipId = null;
                if ( $relationships['is_error'] ) {
                    $relParams    = array( 'contact_id_a'         => $this->_studentId,
                                           'contact_id_b'         => $contactId,
                                           'relationship_type_id' => $this->_emergencyRelTypeId,
                                           'start_date'           => date('Ymd'),
                                           'is_active'            => 1,
                                           'description'          => $blockId
                                           );
                    $relationship = civicrm_relationship_create( $relParams );
                    $relationshipId = $relationship['result']['id'];
                } else {
                    foreach ( $relationships['result'] as $relId => $dontCare ) {
                        $relationshipId = $relId;
                    }
                }

                if ( $dropContactId ) {
                    $oldRelation = new CRM_Contact_DAO_Relationship( );
                    $oldRelation->contact_id_a = $this->_studentId;
                    $oldRelation->contact_id_b = $dropContactId;
                    $oldRelation->relationship_type_id = $this->_emergencyRelTypeId;
                    $oldRelation->delete();
                }

                $fieldParams = $customFields = array();
                if ( $relationshipId ) {
                    $fieldParams['custom_' . $fieldId . (isset($this->_relationIds['custom'][$blockId]) ? 
                                                         "_{$this->_relationIds['custom'][$blockId]}" : '_-1')] = 
                        trim($params['ec_contact'][$blockId]['relationship']);
                    require_once 'CRM/Core/BAO/CustomValueTable.php';
                    CRM_Core_BAO_CustomValueTable::postProcess( $fieldParams,
                                                                $customFields,
                                                                'civicrm_relationship',
                                                                $relationshipId,
                                                                'Relationship' );
                }
            }
        }

        parent::endPostProcess( );
    }
}
