<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

class SCH_Utils_ReportCard {

    static function getValues( $childrenIDs, &$values, $schoolYear = '2011-2012' ) {
        if ( empty( $childrenIDs ) ) {
            return;
        }

        $single = false;
        if ( ! is_array( $childrenIDs ) ) {
            $childrenIDs = array( $childrenIDs );
            $single = true;
        }

        $childrenIDString = implode( ',', array_values( $childrenIDs ) );

        $query = "
SELECT     c.id as contact_id,
           r.report_pdf_76 as report_id,
           r.report_year as year,
           r.report_term as term,
           r.report_grade as grade
FROM       civicrm_contact c
INNER JOIN civicrm_value_report_cards r ON c.id = r.entity_id
INNER JOIN civicrm_value_school_information s ON c.id = s.entity_id
WHERE     c.id IN ($childrenIDString)
AND       s.subtype = %1
ORDER BY  c.id
";
        $params = array( 1 => array( 'Student'  , 'String'  ) );
        $dao = CRM_Core_DAO::executeQuery( $query, $params );

        while ( $dao->fetch( ) ) {
            // pre-school does not have report cards
            if ( ! is_numeric( $dao->grade ) ) {
                continue;
            }

            if ( ! isset( $values[$dao->contact_id]['reportCards'] ) ) {
                $values[$dao->contact_id]['reportCards']    = array( );
            }

            $values[$dao->contact_id]['reportCards'][] = self::formLink( $dao );
        }
    }

    static function formLink( $dao ) {
        $url = CRM_Utils_System::url( 'civicrm/file',
                                      "reset=1&id={$dao->report_id}&eid={$dao->contact_id}",
                                      true );

        $title  = "Download Report Card for Year: {$dao->year}, Grade: {$dao->grade}, ";
        $title .= $dao->term == 'S1' ? 'Fall Semester' : 'Spring Semester';

        return array( 'url'   => $url,
                      'title' => $title );
    }

}



