<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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

require_once 'SFS/Form/Apply.php';
require_once 'CRM/Core/PseudoConstant.php';

class SFS_Form_Apply_Children extends SFS_Form_Apply {

    const
        
        CUSTOM_TABLE = 'civicrm_value_family_information';
    

    function buildQuickForm( ) {

        $sql = "
SELECT     f.id, f. label, f.column_name
FROM       civicrm_custom_field f
INNER JOIN civicrm_custom_group g ON f.custom_group_id = g.id
WHERE      g.table_name = '".self::CUSTOM_TABLE."'
AND column_name IN ('child_age','child_school','is_child_applying')
";
        require_once 'CRM/Core/BAO/CustomField.php';
        $fieldNames = array( );
        for($count = 1; $count <= 3; $count++) {
            $name = 'child_name_'.$count;
            $this->add('text', $name, ts('Name'));
            $dao = CRM_Core_DAO::executeQuery( $sql );
            while ( $dao->fetch( ) ) {
                $colName = $dao->column_name."_".$count;
                $fieldsNames[$count][] = $colName;
                CRM_Core_BAO_CustomField::addQuickFormElement( $this,
                                                               $colName,
                                                               $dao->id );
            }
        }
        $this->assign( 'fieldNames', $fieldsNames );  

        parent::buildQuickForm( );
    }

    function postProcess() 
    {
        parent::endPostProcess( );
    }
}
