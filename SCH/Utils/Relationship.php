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

class SFS_Utils_Relationship {
    
    static function getChildren( $parentID, &$values, $permissioned = null ) {
        // get all children only        
        $sql = "
SELECT     c.id, c.display_name, r.is_permission_b_a, sis.subtype, sis.grade
FROM       civicrm_contact c
INNER JOIN civicrm_relationship r ON r.contact_id_a = c.id
LEFT JOIN  civicrm_value_school_information sis ON sis.entity_id = c.id
WHERE      r.relationship_type_id = 1
AND        r.is_active    = 1
AND        r.contact_id_b = %1
AND        r.contact_id_a = c.id
AND        sis.entity_id = c.id
AND        sis.is_currently_enrolled = 1
";
        if ( $permissioned ) {
            $sql .= " AND        r.is_permission_b_a = 1";
        }

        $sql .= " ORDER BY sis.grade_sis DESC";

        $params  = array( 1 => array( $parentID, 'Integer' ) );

        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        while ( $dao->fetch( ) ) {
            $values[$dao->id] =
                array( 'id'              => $dao->id,
                       'name'            => $dao->display_name,
                       'is_permissioned' => $dao->is_permission_b_a,
                       'sub_type'        => $dao->sub_type,
                       'grade'           => $dao->grade,
                       'parent_id'       => $parentID,
                       'meeting'         => null,
                       'extendedCare'    => null );
        }
    }

    static function getParents( $childID, &$values, $permissioned = null ) {
        // get all parents (permissioned or not)
        $sql = "
SELECT     p.id, p.display_name, r.is_permission_b_a, ph.phone, e.email
FROM       civicrm_contact p
INNER JOIN civicrm_relationship r ON r.contact_id_b = p.id
LEFT  JOIN civicrm_email e  ON e.contact_id  = p.id
LEFT  JOIN civicrm_phone ph ON ph.contact_id = p.id
WHERE      r.relationship_type_id = 1
AND        r.is_active    = 1
AND        r.contact_id_a = %1
";

        if ( $permissioned ) {
            $sql .= " AND        r.is_permission_b_a = 1";
        }


        $params = array( 1 => array( $childID, 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        while ( $dao->fetch( ) ) {
            $values[$dao->id] =
                array( 'id'              => $dao->id,
                       'name'            => $dao->display_name,
                       'is_permissioned' => $dao->is_permission_b_a,
                       'phone'           => $dao->phone,
                       'email'           => $dao->email );
        }
    }
    
}
