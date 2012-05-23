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

class SFS_Form_ConferenceReminder extends CRM_Core_Form {

    function preProcess( ) {
        parent::preProcess( );
    }

    function buildQuickForm( ) {

        // get the details of who is having a conference in the next 7 days
        // and the grades
        require_once 'SFS/Utils/Conference.php';
        $details = SFS_Utils_Conference::getReminderDetails( );

        foreach ( $details as $name => $grade ) {
            $string[] = "{$name} (Grade: {$grade})";
        }
        $this->assign( 'conferenceTeachers',
                       implode( ', ', $string ) );

        $this->addButtons(array( 
                                array ( 'type'      => 'refresh', 
                                        'name'      => ts( 'Send Reminder' ),
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                        'isDefault' => true   ), 
                                array ( 'type'      => 'cancel', 
                                        'name'      => ts('Cancel') ), 
                                 )
                          );
    }

    function postProcess( ) {
        SFS_Utils_Conference::sendReminderEmail( );
        
        require_once 'CRM/Core/Session.php';
        CRM_Core_Session::setStatus( "Reminder emails have been sent to all the parents" );

        CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/dashboard',
                                                           'reset=1' ) );
    }

}