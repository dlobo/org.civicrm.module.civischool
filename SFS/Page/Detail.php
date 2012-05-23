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

class SFS_Page_Detail extends CRM_Core_Page {

    function run( ) {
        $name    = CRM_Utils_Request::retrieve('name',
                                            'String',
                                            $this,
                                            true );
        $day     = CRM_Utils_Request::retrieve('day',
                                            'String',
                                            $this,
                                            true );
        $sess = CRM_Utils_Request::retrieve('sess',
                                               'String',
                                               $this,
                                               true );

        $sql = "
SELECT c.display_name, c.id
FROM   civicrm_contact c,
       civicrm_value_extended_care s
WHERE  s.name = %1
AND    s.term = %2
AND    s.day_of_week = %3
AND    s.session = %4
AND    s.has_cancelled = 0
AND    s.entity_id = c.id
";
        require_once 'SFS/Utils/ExtendedCare.php';
        $params = array( 1 => array( $name, 'String' ),
                         2 => array( SFS_Utils_ExtendedCare::getTerm( ),
                                     'String' ),
                         3 => array( $day, 'String' ),
                         4 => array( $sess, 'String' ) );

        $dao = CRM_Core_DAO::executeQuery( $sql, $params );

        $values = array( );

        while ( $dao->fetch( ) ) {
            $values[] = array( 'contact_id'   => $dao->id,
                               'display_name' => $dao->display_name,
                               'url'          => CRM_Utils_System::url( 'civicrm/profile/view',
                                                                        "reset=1&gid=4&id={$dao->id}" ) );
        }

        $this->assign_by_ref( 'values', $values );

        $classDetails = array( 'name' => $name,
                               'time' => SFS_Utils_ExtendedCare::getTime( $sess ),
                               'day'  => $day );
        $this->assign( $classDetails );

        parent::run( );
    }

}
