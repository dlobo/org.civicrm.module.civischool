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
    SFS_bin_Utils_auth( false );

    $config =& CRM_Core_Config::singleton( );

    require_once '../drupal/sfschool/sfschool.module';
    sfschool_civicrm_config( $config );

    require_once 'SFS/Utils/ExtendedCare.php';
    require_once 'SFS/Utils/ExtendedCareFees.php';

    $startDate = '20100901';
    $endDate   = '20110831';

    $config = CRM_Core_Config::singleton( );
    require_once 'CRM/Utils/File.php';
    CRM_Utils_File::createDir( $config->configAndLogDir . DIRECTORY_SEPARATOR . '2010-2011' );

    $sql = "
SELECT c.id, c.sort_name, v.grade_sis
FROM   civicrm_contact c
INNER JOIN civicrm_value_school_information v ON c.id = v.entity_id
WHERE v.subtype = 'Student'
AND   v.grade_sis > -2
AND   v.grade_sis < 10
ORDER BY v.grade_sis, c.id
";

    $dao = CRM_Core_DAO::executeQuery( $sql );
    while ( $dao->fetch( ) ) {
        $id = $dao->id;
        $studentName = $dao->sort_name;
        echo "$id, $studentName, {$dao->grade_sis}<p>";
        flush( );

        $feeDetails = SFS_Utils_ExtendedCareFees::feeDetails( $startDate,
                                                              $endDate,
                                                              null,
                                                              false,
                                                              true,
                                                              $id,
                                                              null );
    
        $allDetails = array( );
        $allDetails['Payments and Charges'] = array_pop( $feeDetails );

        $ecMonth = SFS_Utils_ExtendedCare::signoutDetailsPerMonth( $startDate, $endDate, $id );
        if ( ! empty( $ecMonth ) ) {
            $allDetails['Extended care charges per month'] = 
                array( 'details' => $ecMonth );
        }

        $dayCharges = SFS_Utils_ExtendedCare::signoutDetails( $startDate, $endDate, true, true, false, $id );
        if ( ! empty( $dayCharges ) ) {
            $allDetails['Extended care charges per day'] =  array_pop( $dayCharges );
        }

        if ( ! CRM_Utils_Array::crmIsEmptyArray( $allDetails ) ) {
            $fp = fopen( $config->configAndLogDir . DIRECTORY_SEPARATOR .
                         '2010-2011' . DIRECTORY_SEPARATOR .
                         "{$studentName}.csv", "w" );

            foreach ( $allDetails as $name => $fields ) {
                $displayHeaders = false;
                if ( ! empty( $fields['details'] ) ) {
                    $tempHeaders = array( 'Charge Type' );
                    $tempValues  = array( $name );
                    foreach ( $fields as $key => $value ) {
                        if ( $key == 'details' ) {
                            continue;
                        }
                        $tempHeaders[] = $key;
                        $tempValues[]  = $value;
                    }
                    if ( ! empty( $tempHeaders ) ) {
                        fputcsv( $fp, $tempHeaders );
                        fputcsv( $fp, $tempValues );
                        fputcsv( $fp, array( ) );
                    }

                    foreach ( $fields['details'] as $fID => $fValues ) {
                        if ( ! $displayHeaders ) {
                            $displayHeaders = true;
                            fputcsv( $fp, array_keys( $fValues ) );
                        }
                        fputcsv( $fp, array_values( $fValues ) );
                    }
                    fputcsv( $fp, array( ) );
                }
            }
            fclose( $fp );
        }
    }
}

run( );
