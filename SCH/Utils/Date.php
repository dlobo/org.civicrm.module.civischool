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

class SFS_Utils_Date {

    /**
     * given a string in mysql format, transform the string 
     * into qf format
     *
     * @param string $date a mysql type date string
     *
     * @return array       a qf formatted date array
     * @static
     */     
    static function &unformat( $date, $separator = '-' ) 
    {
        $value = array( );
        if ( empty( $date ) ) {
            return $value;
        }

        $value['Y'] = $value['M'] = $value['d'] = null;

        if ( $separator != '' ) {
            list( $year, $mon, $day ) = explode( $separator, $date, 3 );
        } else {
            $year = substr( $date, 0, 4 );
            $mon  = substr( $date, 4, 2 );
            $day  = substr( $date, 6, 2 );
        } 
        
        if( strlen( $day ) > 2 ) {
            if( substr_compare( $day,':', 3 ) ) {
                $time = substr( $day, 3, 8 );
                $day  = substr( $day, 0, 2 );
                list( $hr, $min, $sec ) = explode( ':', $time, 3 );
            }
        }
        
        if ( is_numeric( $year ) && $year > 0 ) {
            $value['Y'] = $year;
        }

        if ( is_numeric( $mon ) && $mon > 0 ) {
            $value['M'] = $mon;
        }

        if ( is_numeric( $day ) && $day > 0 ) {
            $value['d'] = $day;
        }

        if ( isset( $hr ) && is_numeric( $hr ) && $hr >= 0 ) {
            $value['h'] = $hr;
            $value['H'] = $hr;
            if( $hr > 12 ) {
                $value['h'] -= 12;
                $value['H'] = $hr;
                $value['A'] = 'PM';
                $value['a'] = 'pm';
            } else if( $hr == 0 ) {
                $value['h'] = 12;
                $value['A'] = 'AM';
                $value['a'] = 'am';
            } else if( $hr == 12 ) {
                $value['A'] = 'PM';
                $value['a'] = 'pm';
            } else {
                $value['A'] = 'AM';
                $value['a'] = 'am';
            }
        }
        
        if ( isset( $min ) && is_numeric( $min ) && $min >= 0 ) {
            $value['i'] = $min;
        }
        return $value;
    }
}
