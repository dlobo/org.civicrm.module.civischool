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

class SFS_Form_ExtendedCareSummary extends CRM_Core_Form {
    protected $_startDate;

    protected $_endDate;
    
    protected $_includeMorning;

    protected $_showDetails;

    function preProcess( ) {
        parent::preProcess( );

        $this->_startDate      = CRM_Utils_Request::retrieve( 'startDate', 'String' , $this, false,
                                                         date( 'Y-m-d', time( ) - 7 * 24 * 60 * 60 ) );
        $this->_endDate        = CRM_Utils_Request::retrieve( 'endDate'  , 'String' , $this, false,
                                                         date( 'Y-m-d' ) );
        $this->_includeMorning = CRM_Utils_Request::retrieve( 'includeMorning', 'Integer', $this, false, 1 );
        $this->_showDetails    = CRM_Utils_Request::retrieve( 'showDetails'   , 'Integer', $this, false, 1 );
        $this->_notSignedOut   = CRM_Utils_Request::retrieve( 'notSignedOut'  , 'Integer', $this, false, 0 );
    }

    function buildQuickForm( ) {
        $this->addDate( 'start_date', ts('Start Date'), true );
        $this->addDate( 'end_date'  , ts('End Date'  ), true );

        $this->add('checkbox', 'include_morning', ts( 'Include Morning Blocks?' ) );
        $this->add('checkbox', 'show_details'   , ts( 'Show Detailed breakdown for each student?' ) );
        $this->add('checkbox', 'not_signed_out' , ts( 'Show ONLY signed In but not signed out?' ) );
        $this->add('checkbox', 'show_balances'  , ts( 'Show Charges and Payments (all other options are ignored)' ) );

        require_once 'SFS/Utils/Query.php';
        $students = array( '' => '- Select Student -' ) + SFS_Utils_Query::getStudentsByGrade( true, false );
        
        $this->add( 'select',
                    "student_id",
                    ts( 'Student' ),
                    $students );

        $this->addButtons(array( 
                                array ( 'type'      => 'submit', 
                                        'name'      => ts( 'Process' ),
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                        'isDefault' => true   ), 
                                array ( 'type'      => 'cancel', 
                                        'name'      => ts('Cancel') ), 
                                 )
                          );
    }

    function setDefaultValues( ) {
        $defaults = array( 'include_morning' => $this->_includeMorning,
                           'show_details'    => $this->_showDetails,
                           'not_signed_out'  => $this->_notSignedOut );

        list($defaults['start_date'], $defaults['start_date_time']) = 
            CRM_Utils_Date::setDateDefaults($this->_startDate);
        list($defaults['end_date'], $defaults['end_date_time']) = 
            CRM_Utils_Date::setDateDefaults($this->_endDate);
        return $defaults;
    }

    function postProcess( ) {
        $params = $this->controller->exportValues( $this->_name );

        $startDate      = CRM_Utils_Date::processDate( $params['start_date'],
                                                       null, false, 'Ymd' );
        $endDate        = CRM_Utils_Date::processDate( $params['end_date'  ],
                                                       null, false, 'Ymd' );
        $includeMorning = CRM_Utils_Array::value( 'include_morning', $params, false );
        $showDetails    = CRM_Utils_Array::value( 'show_details'   , $params, false );
        $notSignedOut   = CRM_Utils_Array::value( 'not_signed_out' , $params, false );
        $showBalances   = CRM_Utils_Array::value( 'show_balances', $params, false   );
         
        require_once 'SFS/Utils/ExtendedCare.php';
        if ( $showBalances ) {
            $showDetails = false;
            $summary =& SFS_Utils_ExtendedCare::balanceDetails( );
        } else {
            $summary =& SFS_Utils_ExtendedCare::signoutDetails( $startDate     ,
                                                                $endDate       ,
                                                                $includeMorning,
                                                                $showDetails   ,
                                                                $notSignedOut  ,
                                                                $params['student_id'] );
        }

        $this->assign( 'summary'     , $summary      );
        $this->assign( 'showBalances', $showBalances );
        $this->assign( 'showDetails' , $showDetails  );
    }

}