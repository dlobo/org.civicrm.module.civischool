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

class SCH_Form_Family_Release extends SCH_Form_Family {
    function preProcess( ) {
        parent::preProcess();

        require_once 'CRM/Core/BAO/CustomGroup.php';
        $this->_schoolInfoId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomGroup',
                                                            SCH_Form_Family::SCHOOL_INFO_TABLE, 'id', 'table_name' );
        $groupTree = CRM_Core_BAO_CustomGroup::getTree( 'Contact',
                                                        $this,
                                                        $this->_studentId,
                                                        $this->_schoolInfoId );
        $this->_groupTree = CRM_Core_BAO_CustomGroup::formatGroupTree( $groupTree, 1, $this );
        foreach ( $this->_groupTree as $gid => $this->_groupTree ) {
            foreach ( $this->_groupTree['fields'] as $fid => $fieldTree ) {
                if ( in_array($fieldTree['column_name'], array('activity_authorization',
                                                               'handbook_authorization',
                                                               'media_authorization',
                                                               'ms_release_authorization')) ) {
                    $this->_infoMapper[$fieldTree['column_name']] = $fieldTree["element_name"];
                }
            }
        }
    }

    function setDefaultValues( )
    {
        $defaults = array( );

        $this->_groupTree = array( $this->_schoolInfoId => $this->_groupTree );
        CRM_Core_BAO_CustomGroup::setDefaults( $this->_groupTree, $customDefaults );

        foreach( $this->_infoMapper as $colName => $eleName ) {
            $defaults[$colName] = $customDefaults[$eleName];
        }

        return $defaults;
    }

    function buildQuickForm( ) {
        require_once 'SCH/Utils/Query.php';
        $this->add( 'checkbox', 'media_authorization', ts('Media Release'), null, false );
        $this->add( 'checkbox', 'activity_authorization', ts('Activity Acknowledgement'), null, true );
        $this->add( 'checkbox', 'handbook_authorization', ts('Handbook Acknowledgement'), null, true );

        $grade =  SCH_Utils_Query::getGrade($this->_studentId);
        if ( intval($grade) >= 6 ) {
            $releaseAuthorization[ ] = HTML_QuickForm::createElement('radio', null, '', '<strong>&nbsp;'.ts('I have read and agree to the statement noted above.').'</strong>', 1);
            $releaseAuthorization[ ] = HTML_QuickForm::createElement('radio', null, '', '<strong>&nbsp;'.ts('My child is to remain on campus until picked up by an authorized adult.').'</strong>', 0);
            $this->addGroup($releaseAuthorization, 'ms_release_authorization', '', '<br /><br />');

        }

        $buttons   = array();
        $buttons[] = array ( 'type'      => 'next',
                             'name'      => ts('Save and Next'),
                             'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                             'js'        => array( 'onclick' => 'return confirmClicks();') );

        $buttons[] = array ( 'type'      => 'cancel',
                             'name'      => ts('Cancel') );

        $this->addButtons( $buttons );
    }

    function postProcess()
    {
        require_once 'CRM/Core/BAO/CustomValueTable.php';
        $params = $this->controller->exportValues( $this->_name );

        foreach( $this->_infoMapper as $colName => $elementName ) {
            $params[$colName] = CRM_Utils_Array::value( $colName, $params, 0 );
            $customParams[$elementName] = $params[$colName];
        }

        $customFields =
            CRM_Core_BAO_CustomField::getFields( 'Contact', false, false, $this->_studentId );
        CRM_Core_BAO_CustomValueTable::postProcess( $customParams,
                                                    $customFields,
                                                    'civicrm_contact',
                                                    $this->_studentId,
                                                    'Contact' );

        parent::endPostProcess( );
    }
}
