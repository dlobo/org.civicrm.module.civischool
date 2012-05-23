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

class SFS_Form_Apply_Additional extends SFS_Form_Apply {

    const
        
        CUSTOM_TABLE = 'civicrm_value_family_information';

    function buildQuickForm( ) {

        $sql = "
SELECT     f.id, f. label, f.column_name
FROM       civicrm_custom_field f
INNER JOIN civicrm_custom_group g ON f.custom_group_id = g.id
WHERE      g.table_name = '".self::CUSTOM_TABLE."'
AND        f.column_name like 'file_path%'
";
        $dao = CRM_Core_DAO::executeQuery( $sql );

        require_once 'CRM/Core/BAO/CustomField.php';
        $fieldNames = array( );
        while ( $dao->fetch( ) ) {
            $fieldsNames[] = $dao->column_name;           
            $test = CRM_Core_BAO_CustomField::addQuickFormElement( $this,
                                                           $dao->column_name,
                                                           $dao->id );
            
        }
        
        $this->assign( 'fieldNames', $fieldsNames );

        $this->add( 'text', 'how_do_hear', ts('How do you hear about IDEAL?') );
        $this->add( 'text', 'ideal_refer', ts('Did any IDEAL families refer you to us(Family name)?') );

        parent::buildQuickForm( );
    }

    function postProcess() 
    {
        parent::endPostProcess( );
    }
}
