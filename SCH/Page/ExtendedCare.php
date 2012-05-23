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

require_once 'CRM/Core/Page.php';

class SCH_Page_ExtendedCare extends CRM_Core_Page {

    private static $_actionLinks;

    function &actionLinks()
    {
        // check if variable _actionsLinks is populated
        if (!isset(self::$_actionLinks)) {

            self::$_actionLinks = array(
                                        CRM_Core_Action::UPDATE  => array(
                                                                          'name'  => ts('Edit'),
                                                                          'url'   => CRM_Utils_System::currentPath( ),
                                                                          'qs'    => 'reset=1&action=update&objectID=%%objectID%%&id=%%id%%&object=%%object%%',
                                                                          'title' => ts('Update')
                                                                          ),

                                        CRM_Core_Action::DELETE => array(
                                                                          'name'  => ts('Delete'),
                                                                          'url'   => CRM_Utils_System::currentPath( ),
                                                                          'qs'    => 'reset=1&action=delete&objectID=%%objectID%%&id=%%id%%&object=%%object%%',
                                                                          'title' => ts('Delete'),
                                                                          ),
                                        );
        }
        return self::$_actionLinks;
    }

    function run( ) {
        $id = CRM_Utils_Request::retrieve( 'id',
                                           'Integer',
                                           $this,
                                           true );

        $action = CRM_Utils_Request::retrieve('action', 'String',
                                              $this, false, 'browse' );
        $this->assign('action', $action);

        $currentYear  = date( 'Y' );
        $currentMonth = date( 'm' );
        if ( $currentMonth < 9 ) {
            $currentYear--;
        }
        // this hack is to allow us to go back to last year
        // and see stuff
        // $currentYear--;

        // for this year ONLY lets start from december
        $currentMonth = '09';
        $startDate = CRM_Utils_Request::retrieve( 'startDate',
                                                  'String',
                                                  $this,
                                                  false,
                                                  "{$currentYear}{$currentMonth}01" );
        $endDate = CRM_Utils_Request::retrieve( 'endDate',
                                                'String',
                                                $this,
                                                false,
                                                date( 'Ymd' ) );
        $this->assign( 'displayName',
                       CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                                    $id,
                                                    'display_name' ) );

        $actionPermission = false;
        if ( CRM_Core_Permission::check( 'access CiviCRM' ) && CRM_Core_Permission::check( 'administer CiviCRM' ) ) {
            $actionPermission = true;
        }
        $this->assign( 'enableActions', $actionPermission );

        if ( $action & CRM_Core_Action::VIEW ) {
            $this->view( $id, $startDate, $endDate, $actionPermission , false );
        } elseif ( $action & ( CRM_Core_Action::ADD | CRM_Core_Action::UPDATE | CRM_Core_Action::DELETE ) ) {
            $this->edit( $id, $startDate, $endDate );
            return;
        } else {
            $this->browse( $id, $startDate, $endDate, $actionPermission );
        }

        parent::run( );
    }

    function edit( $id, $startDate, $endDate ) {
        $addcurrentPath = "reset=1&id={$id}";
        isset( $startDate )? $addcurrentPath .= "&startDate={$startDate}" : null;
        isset( $endDate )? $addcurrentPath .= "&endDate={$endDate}" : null;

        // set breadcrumb
        $breadCrumb = array( array('title' => ts('Browse Activities'),
                                   'url'   => CRM_Utils_System::url( CRM_Utils_System::currentPath( ), $addcurrentPath )) );

        CRM_Utils_System::appendBreadCrumb( $breadCrumb );
        $session =& CRM_Core_Session::singleton();
        $session->pushUserContext( CRM_Utils_System::url( CRM_Utils_System::currentPath( ), $addcurrentPath ) );
        $controller =& new CRM_Core_Controller_Simple( 'SCH_Form_ExtendedCare' ,'Edit Activity block');
        $controller->process( );
        $controller->run( );
    }

    function view( $id, $startDate, $endDate, $actionPermission , $calledByBrowse = false ) {
        require_once 'SCH/Utils/ExtendedCare.php';

        $showSignoutDetails = true;

        if( $calledByBrowse ) {
            // show recent 10 activities
            $details = SCH_Utils_ExtendedCare::signoutDetails( null, null, true, true, false, $id, 10 );

        } else {
            $month = CRM_Utils_Request::retrieve( 'month','String',$this, false, date('m') );
            $year  = CRM_Utils_Request::retrieve( 'year','String',$this, false, date('Y') );

            $detailStartDate = "{$year}{$month}01";
            $detailEndDate   = "{$year}{$month}".date("t", strtotime( $year . "-" . $month . "-01"));

            $backButtonUrl= CRM_Utils_System::url( CRM_Utils_System::currentPath( ), "reset=1&id={$id}&startDate={$startDate}&endDate={$endDate}" );
            $this->assign( 'backButtonUrl', $backButtonUrl );

            $details = SCH_Utils_ExtendedCare::signoutDetails( $detailStartDate,
                                                               $detailEndDate,
                                                               true,
                                                               true,
                                                               false,
                                                               $id );
        }

        $signoutDetails = array_pop( $details );

        if ( ! empty( $signoutDetails ) && $actionPermission ) {
            foreach( $signoutDetails['details'] as $key => $value ) {
                $signoutDetails['details'][$key]['action'] = CRM_Core_Action::formLink( self::actionLinks(),
                                                                                        null,
                                                                                        array( 'objectID' => $key,
                                                                                               'id'       => $id ,
                                                                                               'object'   => 'signout' ) );
            }
        }
        $this->assign_by_ref( 'signoutDetail', $signoutDetails );

    }

    function browse( $id, $startDate, $endDate, $actionPermission ) {
        require_once 'SCH/Utils/ExtendedCare.php';
        require_once 'SCH/Utils/ExtendedCareFees.php';

        $this->view( $id, $startDate, $endDate, $actionPermission, true );

        if( date('Ymd') <= date( 'Ymd',strtotime($endDate) ) ) {
            $endDateNew = date('Ymd');
        } else {
            $endDateNew  = date( 'Ymd',
                                 mktime(0, 0, 0, date('m',strtotime($endDate)),
                                        date('t',strtotime($endDate)),
                                        date('Y',strtotime($endDate))));
            if( date('Ymd') <= $endDateNew ) {
                $endDateNew =  date('Ymd');
            }
        }

        $details = SCH_Utils_ExtendedCareFees::feeDetails( $startDate,
                                                           $endDateNew,
                                                           null,
                                                           false,
                                                           true,
                                                           $id,
                                                           null );
        $feeDetails = array_pop( $details );

        $monthlySignout = SCH_Utils_ExtendedCare::signoutDetailsPerMonth( $startDate, $endDate, $id );

        if ( ! empty( $feeDetails ) && $actionPermission ) {
            foreach( $feeDetails['details'] as $key => $value ) {
                $feeDetails['details'][$key]['action'] = CRM_Core_Action::formLink( self::actionLinks(),
                                                                                    null,
                                                                                    array( 'objectID' => $key,
                                                                                           'id'       => $id ,
                                                                                           'object'   => 'fee' ) );
            }
        }
        $this->assign_by_ref( 'feeDetail', $feeDetails );

        if( ! empty( $monthlySignout ) ) {
            $detailLink = array( CRM_Core_Action::VIEW  => array('name'  => ts('View Details'),
                                                                 'url'   => CRM_Utils_System::currentPath( ),
                                                                 'qs'    => 'reset=1&action=view&id=%%id%%&year=%%year%%&month=%%month%%',
                                                                 'title' => ts('View Details')
                                                                 ) );
            foreach( $monthlySignout as $month => $detail ) {
                $monthlySignout[$month]['action'] = CRM_Core_Action::formLink( $detailLink,
                                                                               null,
                                                                               array( 'id'       => $id ,
                                                                                      'year'     => $detail['year'],
                                                                                      'month'    => $detail['month'] ));

            }
        }
        $this->assign_by_ref( 'monthlySignout', $monthlySignout );

        // get remaining balance
        $balanceDetails = SCH_Utils_ExtendedCare::balanceDetails( $id );

        if ( ! empty( $balanceDetails ) ) {
            $balanceDetails = array_pop( $balanceDetails );
            $this->assign_by_ref( 'balanceDetails', $balanceDetails );
        } else {
            $this->assign( 'balanceDetails', null );
        }

        if( $actionPermission ) {
            $addBlockUrl = CRM_Utils_System::url( CRM_Utils_System::currentPath( ),"reset=1&id={$id}&action=add&object=signout");
            $addFeeUrl   = CRM_Utils_System::url( CRM_Utils_System::currentPath( ),"reset=1&id={$id}&action=add&object=fee");
            $this->assign( 'addActivityBlock', $addBlockUrl );
            $this->assign( 'addFeeEntity', $addFeeUrl );
        }
    }

}
