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

class SFS_Utils_Query {

    static function checkSubType( $id, $subType = 'Student', $redirect = true ) {
        $entitySubType = self::getSubType( $id );

        if ( ! is_array( $subType ) ) {
            $subType = array( $subType );
        }

        if ( ! in_array( $entitySubType, $subType ) ) {
            if ( $redirect ) {
                $config = CRM_Core_Config::singleton( );
                CRM_Utils_System::redirect( $config->userFrameworkBaseURL );
            }
            return false;
        }
        return true;
    }

    static function getSubType( $id ) {
        static $_cache = array( );
        
        if ( ! array_key_exists( $id, $_cache ) ) {
            $sql = "
SELECT subtype
FROM   civicrm_value_school_information
WHERE  entity_id = %1
";
            $params = array( 1 => array( $id, 'Integer' ) );
            $_cache[$id] = CRM_Core_DAO::singleValueQuery( $sql, $params );
        }
        return $_cache[$id];
    }

    static function getGrade( $id ) {
        static $_cache = array( );
        
        if ( ! array_key_exists( $id, $_cache ) ) {
            $sql = "
SELECT grade
FROM   civicrm_value_school_information
WHERE  entity_id = %1
";
            $params = array( 1 => array( $id, 'Integer' ) );
            $_cache[$id] = CRM_Core_DAO::singleValueQuery( $sql, $params );
        }
        return $_cache[$id];
    }

    static function isCurrentlyEnrolled( $id ) {
        static $_cache = array( );
        
        if ( ! array_key_exists( $id, $_cache ) ) {
            $sql = "
SELECT is_currently_enrolled
FROM   civicrm_value_school_information
WHERE  entity_id = %1
";
            $params = array( 1 => array( $id, 'Integer' ) );
            $_cache[$id] = CRM_Core_DAO::singleValueQuery( $sql, $params );
        }
        return $_cache[$id];
    }

    static function &getStudentsByGrade( $extendedCareOnly = false, $splitByGrade = true, $useDisplayName = true, $prefix = '' ) {
        $sql = "
SELECT     c.id, c.sort_name, c.display_name, sis.grade 
FROM       civicrm_contact c
INNER JOIN civicrm_value_school_information sis ON sis.entity_id = c.id
WHERE      sis.is_currently_enrolled = 1
";

        if ( $extendedCareOnly ) {
            $sql .= " AND sis.grade_sis > 0";
        }
        if ( $splitByGrade ) {
            $sql .= " ORDER BY sis.grade_sis DESC";
            if ( $useDisplayName ) {
                $sql .= ", display_name";
            } else {
                $sql .= ", sort_name";
            }
        } else {
            if ( $useDisplayName ) {
                $sql .= " ORDER BY display_name";
            } else {
                $sql .= " ORDER BY sort_name";
            }
        }

        $dao = CRM_Core_DAO::executeQuery( $sql );

        $students = array( );

        while ( $dao->fetch( ) ) {
            $key = "{$prefix}{$dao->id}";
            if ( $splitByGrade ) {
                if ( ! array_key_exists( $dao->grade, $students ) ) {
                    $students[$dao->grade] = array( );
                }
                $students[$dao->grade][$key] = $useDisplayName ? $dao->display_name : $dao->sort_name;
            } else {
                $students[$key]  = $useDisplayName ? $dao->display_name : $dao->sort_name;
                $students[$key] .= " (Grade {$dao->grade})";
            }
        }
        return $students;
    }

    static function getNameAndEmail( $id ) {
        $sql = "
SELECT    c.nick_name, c.display_name, e.email
FROM      civicrm_contact c
LEFT JOIN civicrm_email e ON ( e.contact_id = c.id )
WHERE     c.id = %1
ORDER BY  e.is_primary desc
";
        $params = array( 1 => array( $id, 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        if ( $dao->fetch( ) ) {
            $advisorName = $dao->nick_name ? $dao->nick_name : $dao->display_name;
            return array( $advisorName, $dao->email );
        }
        return array( null, null );
    }

    static function getClasses( $name = null ) {
        $sql = "
SELECT DISTINCT( name )
FROM   sfschool_extended_care_source
WHERE  is_active = 1
AND    term = %1
";
        if ( $name ) {
            $name = CRM_Utils_Type::escape( $name );
            $sql .= " AND name like '$name%'";
        }

        $sql .= " ORDER BY name";

        require_once 'SFS/Utils/ExtendedCare.php';
        $params = array( 1 => array( SFS_Utils_ExtendedCare::getTerm( ), 'String' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );

        $classes = array( );
        while ( $dao->fetch( ) ) {
            $classes[$dao->name] = $dao->name;
        }
        return $classes;
    }
    
    /**
     * Get students ajax widget
     */
    static function getStudents( ) {
        $name = CRM_Utils_Type::escape( $_GET['s'], 'String' );

        $limit = '25';
        if ( CRM_Utils_Array::value( 'limit', $_GET) ) {
            $limit = CRM_Utils_Type::escape( $_GET['limit'], 'Positive' );
        }

        $sql = "
SELECT c.id, c.display_name, s.grade
FROM   civicrm_contact c,
       civicrm_value_school_information s
WHERE  s.entity_id = c.id
AND    s.grade_sis >= 1
AND    s.subtype = 'Student'
AND    ( c.sort_name LIKE '$name%' 
 OR      c.display_name LIKE '$name%' )
ORDER BY sort_name
LIMIT 0, {$limit}
";
        $dao = CRM_Core_DAO::executeQuery( $sql );
        $contactList = null;
        while ( $dao->fetch( ) ) {
            echo "{$dao->display_name} (Grade {$dao->grade})|{$dao->id}\n";
        }
        exit();        
    }

}
