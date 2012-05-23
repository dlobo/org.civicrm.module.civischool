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
class SCH_Form_Apply extends CRM_Core_Form
{

    function preProcess( ) {

        // set up tabs
        require_once 'SCH/Form/Apply/TabHeader.php';
        SCH_Form_Apply_TabHeader::build( $this );
    }

    function buildQuickForm( ) {
        $className = CRM_Utils_String::getClassName( $this->_name );

        $buttons   = array();
        $buttons[] = array ( 'type'      => 'submit',
                             'name'      => ts('Save'),
                             'isDefault' => true   );

        if ( $className !== 'Diversity' ) {
            $buttons[] = array ( 'type'      => 'next',
                                 'name'      => ts('Save and Next'),
                                 'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',  );
        }

        $buttons[] = array ( 'type'      => 'cancel',
                             'name'      => ts('Cancel') );

        $this->addButtons( $buttons );
    }

    function getTemplateFileName( ) {
        if ( $this->controller->getPrint( ) == CRM_Core_Smarty::PRINT_NOFORM ||
             ( $this->_action & CRM_Core_Action::DELETE ) ) {
            return parent::getTemplateFileName( );
        } else {
            return 'CRM/common/TabHeader.tpl';
        }
    }

    function endPostProcess( )
    {
        $className = CRM_Utils_String::getClassName( $this->_name );
        if ( $this->controller->getButtonName('submit') == "_qf_{$className}_next" ) {
            $nextTab = SCH_Form_Apply_TabHeader::getNextSubPage( $this, $className );
            $nextUrl = CRM_Utils_System::url( 'civicrm/school/apply/' . strtolower($nextTab),
                                              "reset=1" );
            CRM_Utils_System::redirect( $nextUrl );
        }
    }
}
