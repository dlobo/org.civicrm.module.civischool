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

require_once 'Utils.php';

function run( ) {
    SFS_bin_Utils_auth( );

    $config =& CRM_Core_Config::singleton( );

    require_once '../drupal/sfschool/sfschool.module';
    sfschool_civicrm_config( $config );

    $time = null;
    
    // if first day of month, then generate monthly report
    if ( $_GET['all'] ) {
        $time = null;
    } else if ( date( 'j' ) == 1 || $_GET['month'] ) {
        $time = strftime( "%Y-%m-%d", time( ) - 31 * 24 * 60 * 60 );
    } else if ( date( 'N' ) == 1 || $_GET['week'] ) { // if monday, generate weekly report
        $time = strftime( "%Y-%m-%d", time( ) - 8 * 24 * 60 * 60 );
    } else { // generate daily report
        $time = strftime( "%Y-%m-%d", time( ) - 30 * 60 * 60 );
    }

    require_once 'SFS/Utils/PowerSchool.php';
    SFS_Utils_PowerSchool::export( $time, true );
}

// first set the time limit to infinity
set_time_limit(0);

run( );
