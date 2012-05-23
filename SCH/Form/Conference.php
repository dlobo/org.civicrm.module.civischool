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

class SCH_Form_Conference extends CRM_Core_Form {

    protected $_multipleDay   = false;

    protected $_numberOfSlots = 35;

    function preProcess( ) {
        parent::preProcess( );

        $this->_multipleDay = CRM_Utils_Request::retrieve( 'multipleDay',
                                                           'Boolean',
                                                           $this,
                                                           false );
        $this->assign( 'multipleDay'  , $this->_multipleDay   );
        $this->assign( 'numberOfSlots', $this->_numberOfSlots );
    }

    function buildQuickForm( ) {

        // get all the potential advisors
        $sql = "
SELECT     DISTINCT(c.id), c.display_name
FROM       civicrm_contact c
INNER JOIN civicrm_relationship r ON r.contact_id_a = c.id
WHERE      r.relationship_type_id = 10
ORDER BY   c.display_name
";

        $advisors = array( '' => '- Select a Teacher -' );
        $dao = CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch( ) ) {
            $advisors[$dao->id] = $dao->display_name;
        }
        $this->add( 'select',
                    'advisor_id',
                    ts( 'Advisor' ),
                    $advisors,
                    true );

        if ( ! $this->_multipleDay ) {
            $this->addDate('ptc_date',
                           ts( 'Conference Date' ),
                           true );
        }
        $this->add( 'text',
                    'ptc_subject',
                    ts( 'Conference Subject' ),
                    true );

        $this->add( 'text',
                    'ptc_duration',
                    ts( 'Conference Duration' ),
                    true );

        for ( $i = 1; $i < $this->_numberOfSlots; $i++ ) {
            $this->addDateTime("ptc_date_$i",
                               ts( 'Conference Start Time' ),
                               false );
        }

        $this->addButtons(array(
                                array ( 'type'      => 'refresh',
                                        'name'      => ts( 'Process' ),
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                        'isDefault' => true   ),
                                array ( 'type'      => 'cancel',
                                        'name'      => ts('Cancel') ),
                                 )
                          );

        $this->addFormRule( array( 'SCH_Form_Conference', 'formRule' ), $this );
    }

    static function formRule( $fields, $files, $form )
    {
        $errors = array( );

        if  ( ! CRM_Utils_Array::value( 'ptc_date_1_time',$fields) ) {
            $errors['ptc_date_1_time'] = ts('Conference Start Time is a required field.');
        }

        if ( $form->_multipleDay &&
             ! CRM_Utils_Array::value( 'ptc_date_1' ,$fields ) ) {
            $errors['ptc_date_1'] = ts('Conference Start Day is a required field.');
        }
        return $errors;
    }

    function setDefaultValues( ) {
        require_once 'SCH/Utils/Conference.php';

        $defaults = array( );

        list($defaults['ptc_date'], $defaults['ptc_date_time'])
            = CRM_Utils_Date::setDateDefaults(date("Y-m-d", time( ) + 14 * 24 * 60 * 60 ));
        $defaults['ptc_duration'] = 25;

        $defaults['ptc_subject'] = SCH_Utils_Conference::SUBJECT;

        for ( $i = 1; $i < 10; $i++ ) {
            $defaults["ptc_date_{$i}"] = $defaults['ptc_date'];
            $time = (int ) ( $i + 1 ) / 2;
            $defaults["ptc_date_{$i}_time"] = "$time:00 PM";
            $i++;
            $defaults["ptc_date_{$i}"] = $defaults['ptc_date'];
            $defaults["ptc_date_{$i}_time"] = "$time:30 PM";
        }
        return $defaults;
    }


    function postProcess( ) {
        $params = $this->controller->exportValues( $this->_name );

        $session =& CRM_Core_Session::singleton( );
        $userID = $session->get( 'userID' );

        $totalSlots = 0;
        for ( $i = 1 ; $i < $this->_numberOfSlots; $i++ ) {
            if ( empty( $params["ptc_date_{$i}_time"] ) ) {
                continue;
            }

            if ( $this->_multipleDay ) {
                $mysqlDate = CRM_Utils_Date::processDate( $params["ptc_date_$i"], $params["ptc_date_{$i}_time"] );
            } else {
                $mysqlDate = CRM_Utils_Date::processDate( $params['ptc_date'], $params["ptc_date_{$i}_time"] );
            }

            $totalSlots++;
            SCH_Utils_Conference::createConference( $userID,
                                                    $params['advisor_id'],
                                                    SCH_Utils_Conference::CONFERENCE_ACTIVITY_TYPE_ID,
                                                    $mysqlDate,
                                                    $params['ptc_subject'],
                                                    SCH_Utils_Conference::LOCATION,
                                                    SCH_Utils_Conference::STATUS,
                                                    $params['duration'] );
        }

        require_once 'CRM/Core/Session.php';
        CRM_Core_Session::setStatus( "We created {$totalSlots} conference entries" );
    }

}