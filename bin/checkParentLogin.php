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

    require_once '../SFS/Utils/Conference.php';

    // first cache all the contacts who have created a login
    $sql = "
SELECT c.id
FROM   civicrm_contact c,
       civicrm_uf_match uf,
       drupal_sfs.users u
WHERE  uf.contact_id = c.id
AND    uf.uf_id = u.uid
AND    u.created != u.access
";
    $dao = CRM_Core_DAO::executeQuery( $sql );

    $accountsCreted = array( );
    while ( $dao->fetch( ) ) {
        $accountsCreated[$dao->id] = 1;
    }

    // now fetch all the student parent information
    $sql = "
SELECT      c.id as c_id, c.display_name as c_name, s.grade_sis as c_grade, p.id as p_id, p.display_name as p_name, ep.email as p_email
FROM        civicrm_contact c
INNER JOIN  civicrm_value_school_information s ON s.entity_id = c.id
INNER JOIN  civicrm_relationship r ON r.contact_id_a = c.id
INNER JOIN  civicrm_contact p      ON r.contact_id_b = p.id
LEFT  JOIN  civicrm_email   ep     ON ep.contact_id  = p.id
WHERE s.subtype = 'Student'
AND   s.grade_sis >= 1
AND   r.relationship_type_id = 1
ORDER BY p_id
";
    
    $parentsDoNotHaveLogin = array( );
    $parentsDoHaveLogin    = array( );

    $dao = CRM_Core_DAO::executeQuery( $sql );
    while ( $dao->fetch( ) ) {
        if ( array_key_exists( $dao->p_id, $accountsCreated ) ) {
            unset( $parentsDoNotHaveLogin[$dao->c_id] );
            if ( ! array_key_exists( $dao->c_id, $parentsDoHaveLogin ) ) {
                $parentsDoHaveLogin[$dao->c_id] = array( );
            }
            $parentsDoHaveLogin[$dao->c_id][] = array( $dao->c_name, $dao->c_grade, $dao->p_id, $dao->p_name, $dao->p_email );
        } else if ( array_key_exists( $dao->c_id,  $parentsDoHaveLogin ) ) {
            unset( $parentsDoNotHaveLogin[$dao->c_id] );
            $parentsDoHaveLogin[$dao->c_id][] = array( $dao->c_name, $dao->c_grade, $dao->p_id, $dao->p_name, $dao->p_email );
        } else {
            if ( ! array_key_exists( $dao->c_id, $parentsDoNotHaveLogin ) ) {
                $parentsDoNotHaveLogin[$dao->c_id] = array( );
            }
            $parentsDoNotHaveLogin[$dao->c_id][] = array( $dao->c_name, $dao->c_grade, $dao->p_id, $dao->p_name, $dao->p_email );
        }
    }

    $families = array( );
    $emailAddress = array( );
    foreach ( $parentsDoNotHaveLogin as $cid => $pValues ) {
        $familyKey = $familyValue = array( );
        foreach ( $pValues as $pValue ) {
            $familyKey[]   = $pValue[2];
            if ( ! empty( $pValue[4] ) ) {
                $familyValue[]  = "{$pValue[3]} <{$pValue[4]}>";
                $emailAddress[$pValue[4]] = "{$pValue[3]} <{$pValue[4]}>";
            } else {
                $familyValue[]  = $pValue[3];
            }
        }
        $families[implode('_', $familyKey )] = implode( ', ', $familyValue );
    }

    CRM_Core_Error::debug( count( $emailAddress ), implode( ', ', $emailAddress ) );
    CRM_Core_Error::debug( count( $families ), $families );

    $familiesLoggedIn = array( );
    foreach ( $parentsDoHaveLogin as $cid => $pValues ) {
        $familyKey = $familyValue = array( );
        foreach ( $pValues as $pValue ) {
            $familyKey[]   = $pValue[2];
            $familyValue[] = "{$pValue[3]} <{$pValue[4]}>";
        }
        $familiesLoggedIn[implode('_', $familyKey )] = implode( ', ', $familyValue );
    }

    CRM_Core_Error::debug( count( $familiesLoggedIn ), $familiesLoggedIn );
}

run( );
