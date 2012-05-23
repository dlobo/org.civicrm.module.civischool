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

require_once 'CRM/Report/Form.php';

class SCH_Report_Form_Econsent extends CRM_Report_Form {

    protected $_customTable_parentRel  = 'civicrm_value_parent_relationship_data';

    protected $_customTable_schoolInfo = 'civicrm_value_school_information';

    function __construct( ) {

        $this->_columns =
            array(
                  'civicrm_contact' =>
                  array( 'dao'       => 'CRM_Contact_DAO_Contact',
                         'fields'    =>
                         array( 'display_name' =>
                                array(
                                      'required'   => true,
                                      'title'      => ts('Student'),
                                      'no_repeat'  => true
                                      ),
                                'id' =>
                                array(
                                      'no_display' => true,
                                      'required'   => true,
                                      ),
                                ),
                         'filters'   =>
                         array( 'sort_name' =>
                                array('title' => ts('Student Name')
                                      ) ) ),
              'civicrm_parent_contact' =>
                  array( 'dao'       => 'CRM_Contact_DAO_Contact',
                         'fields'    =>
                         array( 'parent_name' =>
                                array(
                                      'title'      => ts('Parent'),
                                      'required'   => true,
                                      'name'       => 'display_name'
                                  ),
                                'id' =>
                                array(
                                      'no_display' => true,
                                      'required'   => true,
                                      ),
                                ),
                     ),
               );

        $this->_columns[$this->_customTable_parentRel] = array(
                                                               'dao'       => 'CRM_Contact_DAO_Contact',
                                                               'alias'     => 'parent_relationship',
                                                               'fields'    =>
                                                               array(
                                                                     'econsent_signed' =>
                                                                     array(
                                                                           'title'      => ts('Econsent Signed?'),
                                                                           'required'   => true,
                                                                            ),
                                                                     'econsent_signed_date' =>
                                                                     array(
                                                                           'title'        => ts('Econsent Signed Date'),
                                                                           'operatorType' => CRM_Report_Form::OP_DATE,
                                                                           'type'         => CRM_Utils_Type::T_DATE,
                                                                           'required'     => true,

                                                                           ),
                                                                ),
                                                               'filters'   =>
                                                               array( 'econsent_signed_date' =>
                                                                      array(
                                                                            'title' => ts('Signed Date'),
                                                                            'operatorType' => CRM_Report_Form::OP_DATE,
                                                                            'type'         => CRM_Utils_Type::T_DATE
                                                                            ) )
                                                                );

        $this->_options = array( 'econsent_unsigned' => array( 'title'   => ts('Display only parents who have not signed Econsent.'),
                                                               'type'    => 'checkbox' ) );

        parent::__construct( );
    }

    function select(  ) {
        $select = $this->_columnHeaders =  array( );
        foreach ( $this->_columns as $tableName => $table ) {

           if ( array_key_exists('fields', $table) ) {
                foreach ( $table['fields'] as $fieldName => $field ) {
                    if ( CRM_Utils_Array::value( 'required', $field ) ||
                         CRM_Utils_Array::value( $fieldName, $this->_params['fields'] ) ) {

                            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
                            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type']  = CRM_Utils_Array::value( 'type', $field );
                            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];

                    }
                }
            }
        }

        $this->_select = "SELECT " . implode( ",\n", $select ) . " ";
   }


    function from( ) {
        $schoolInfoAlias = 'school_info';
        $parentRelAlias  =  $this->_aliases[$this->_customTable_parentRel];
        $this->_from = "FROM civicrm_contact {$this->_aliases['civicrm_contact']} {$this->_aclFrom}
                 INNER JOIN  {$this->_customTable_schoolInfo} {$schoolInfoAlias} ON ({$this->_aliases['civicrm_contact']}.id = {$schoolInfoAlias}.entity_id AND {$schoolInfoAlias}.subtype = 'Student' )
                 LEFT JOIN civicrm_relationship relationship ON (relationship.contact_id_a = {$this->_aliases['civicrm_contact']}.id AND relationship.relationship_type_id = 1 )
                 LEFT JOIN {$this->_customTable_parentRel} {$parentRelAlias} ON ( {$parentRelAlias}.entity_id = relationship.id )
                 LEFT JOIN civicrm_contact {$this->_aliases['civicrm_parent_contact']} ON {$this->_aliases['civicrm_parent_contact']}.id = relationship.contact_id_b
                ";

    }

    function where( ) {
        $parentRelAlias  = $this->_aliases[$this->_customTable_parentRel];
        $whereClauses[ ] = "school_info.grade_sis != 8";
        $whereClauses[ ] = "school_info.is_currently_enrolled = 1";

        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('filters', $table) ) {
                foreach ( $table['filters'] as $fieldName => $field ) {
                    $clause = null;
                    if ( CRM_Utils_Array::value( 'type', $field ) & CRM_Utils_Type::T_DATE ) {
                        $relative = CRM_Utils_Array::value( "{$fieldName}_relative", $this->_params );
                        $from     = CRM_Utils_Array::value( "{$fieldName}_from"    , $this->_params );
                        $to       = CRM_Utils_Array::value( "{$fieldName}_to"      , $this->_params );

                        $clause = $this->dateClause( $field['name'], $relative, $from, $to, $field['type'] );
                    } else {
                        $op = CRM_Utils_Array::value( "{$fieldName}_op", $this->_params );
                        if ( $op ) {
                            $clause =
                                $this->whereClause( $field,
                                                    $op,
                                                    CRM_Utils_Array::value( "{$fieldName}_value", $this->_params ),
                                                    CRM_Utils_Array::value( "{$fieldName}_min", $this->_params ),
                                                    CRM_Utils_Array::value( "{$fieldName}_max", $this->_params ) );
                        }
                    }

                    if ( ! empty( $clause ) ) {
                        $whereClauses[] = $clause;
                    }
                }
            }
        }

        if ( isset($this->_params['options']) &&
             CRM_Utils_Array::value( 'econsent_unsigned', $this->_params['options'] ) ) {
            $whereClauses[] = "({$parentRelAlias}.econsent_signed IS NULL OR {$parentRelAlias}.econsent_signed = 0)";
        }

        if ( empty( $whereClauses ) ) {
            $this->_where = "WHERE ( 1 ) ";
        } else {
            $this->_where = "WHERE " . implode( ' AND ', $whereClauses );
        }

        if ( $this->_aclWhere ) {
            $this->_where .= " AND {$this->_aclWhere} ";
        }

    }

    function postProcess( ) {
        parent::postProcess( );
    }

     function alterDisplay( &$rows ) {

         foreach ( $rows as $rowNum => $row ) {

             if ( array_key_exists('civicrm_contact_id', $row ) ) {
                 if ( $cid =  $row['civicrm_contact_id'] ) {
                     if ( $rowNum == 0 ) {
                         $prev_cid = $cid;
                     } else {
                         if( $prev_cid == $cid ) {
                             $display_flag = 1;
                             $prev_cid = $cid;
                         } else {
                             $display_flag = 0;
                             $prev_cid = $cid;
                         }
                     }

                     if ( $display_flag ) {
                         foreach ( $row as $colName => $colVal ) {
                             if ( in_array($colName, $this->_noRepeats) ) {
                                 unset($rows[$rowNum][$colName]);
                             }
                            }
                     }
                     $entryFound = true;
                 }
             }

             // convert display name to links
             if ( array_key_exists('civicrm_contact_display_name', $row) &&
                  array_key_exists('civicrm_contact_id', $row) ) {
                 $url = CRM_Utils_System::url( "civicrm/contact/view",
                                                   'reset=1&cid='
                                               . $row['civicrm_contact_id'] );
                 $rows[$rowNum]['civicrm_contact_display_name_link' ] = $url;
                 $rows[$rowNum]['civicrm_contact_display_name_hover'] =
                     ts("View contact summary");
                 $entryFound = true;
             }

             // convert display name to links
             if ( array_key_exists('civicrm_parent_contact_parent_name', $row) &&
                  array_key_exists('civicrm_parent_contact_id', $row) ) {
                 $url = CRM_Utils_System::url( "civicrm/contact/view",
                                                   'reset=1&cid='
                                               . $row['civicrm_parent_contact_id'] );
                 $rows[$rowNum]['civicrm_parent_contact_parent_name_link' ] = $url;
                 $rows[$rowNum]['civicrm_parent_contact_parent_name_hover'] =
                     ts("View contact summary");
                 $entryFound = true;
             }

             if ( array_key_exists('civicrm_value_parent_relationship_data_econsent_signed', $row ) ) {
                 if ( $row['civicrm_value_parent_relationship_data_econsent_signed'] == 1 ) {
                     $rows[$rowNum]['civicrm_value_parent_relationship_data_econsent_signed'] = ts('Yes');
                 } else {
                     $rows[$rowNum]['civicrm_value_parent_relationship_data_econsent_signed'] = ts('No');
                 }
             }

         }
     }

}
