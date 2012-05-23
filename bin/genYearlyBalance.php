<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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

define( 'SCH_BALANCE_OVERDUE', 10 );
require_once 'Utils.php';

function run( ) {
    SCH_bin_Utils_auth( );

    $config =& CRM_Core_Config::singleton( );

    require_once '../drupal/sfschool/sfschool.module';
    sfschool_civicrm_config( $config );

    require_once 'SCH/Utils/ExtendedCare.php';
    $details = SCH_Utils_ExtendedCare::balanceDetails( null, '20100901', '20110831' );

    $values = array( );
    $globalID = 1784;
    foreach ( $details as $contactID => $detail ) {
        if ( $detail['balanceCredit'] > 10 ) {
            $values[] = "$globalID,$contactID,Credit,Carry Over Credit from 2010-2011 Academic year,2011-09-01,{$detail['balanceCredit']},Carry Over Blocks";
            $globalID++;
        }

        if ( $detail['balanceDue'] > 10 ) {
            $values[] = "$globalID,$contactID,Charge,Carry Over Balance Due from 2010-2011 Academic year,2011-09-01,{$detail['balanceDue']},Carry Over Blocks";
            $globalID++;
        }
    }

    CRM_Core_Error::debug( implode( "\n", $values ) );
}

run( );
