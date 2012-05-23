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

class SFS_Report_Form_AttendeeInfo extends CRM_Report_Form {
    
    // set custom table name
    protected $_extentedCareTable = 'civicrm_value_extended_care';  
    
    function __construct( ) {
        require_once 'CRM/Core/OptionGroup.php';
        require_once 'SFS/Utils/ExtendedCare.php';

        $sql = "SELECT column_name,option_group_id FROM civicrm_custom_field WHERE column_name IN('term', 'day_of_week')";
        $dao = CRM_Core_DAO::executeQuery( $sql );
        $options = array( );
        while( $dao->fetch( ) ) {
            $options[$dao->column_name] = CRM_Core_OptionGroup::valuesByID($dao->option_group_id);
        }

        $sql = " SELECT DISTINCT( name ) as class
FROM   sfschool_extended_care_source
WHERE  is_active = 1";
        
        $dao = CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch( ) ) {
            $options['class'][$dao->class] = $dao->class;
        }
        
        $this->_columns = array(
                                $this->_extentedCareTable   =>
                                array( 'dao'     => 'CRM_Contact_DAO_Contact',
                                       'filters'  => 
                                       array( 'term' => array( 'title'        => ts('Term'),
                                                               'operatorType' => CRM_Report_Form::OP_SELECT,
                                                               'type'         => CRM_Utils_Type::T_STRING, 
                                                               'options'      => $options['term'],
                                                               'default'      => SFS_Utils_ExtendedCare::getTerm()
                                                               ),
                                              'day_of_week' => array( 'title'        => ts('Day Of Week'), 
                                                                      'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                                      'type'         => CRM_Utils_Type::T_STRING, 
                                                                      'options'      =>  $options['day_of_week'],
                                                               ),
                                              'name' => array( 'title'        => ts('Class'), 
                                                               'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                               'type'         => CRM_Utils_Type::T_STRING, 
                                                               'options'      =>  $options['class'],
                                                               ),
                                              ) ),

                                'civicrm_student' =>
                                array( 'dao'       => 'CRM_Contact_DAO_Contact',
                                       'fields'    => 
                                       array( 'display_name' =>
                                              array( 'no_display' => true,
                                                     'required'   => true,
                                                     'title'      => ts('Student')
                                                     ),
                                              'id' => 
                                              array( 'no_display' => true,
                                                     'no_repeat'  => true,
                                                     'required'   => true ) ) ),
                                'civicrm_parent' =>
                                array( 'dao'       => 'CRM_Contact_DAO_Contact',
                                       'fields'    =>
                                       array( 'display_name' =>
                                              array( 'no_display' => true,
                                                     'required'   => true,
                                                     'title'      => ts('Parent') ),
                                              'id' =>
                                              array( 'no_display' => true,
                                                     'required'   => true ) ) ),
                                      
                                'civicrm_email'   =>
                                array( 'dao'       => 'CRM_Core_DAO_Email',
                                       'fields'    =>
                                       array( 'email' => 
                                              array( 'title'      => ts( 'Email' ),
                                                     'no_display' => true,
                                                     'required'   => true
                                                     ) ) ),
                                'civicrm_phone' => 
                                array( 'dao'       => 'CRM_Core_DAO_Phone',
                                       'fields'    =>
                                       array( 'phone'  => array( 'title'      => ts( 'Phone' ),
                                                                 'no_display' => true,
                                                                 'required'   => true
                                                                 ) ) )
                                
                                );
        
        parent::__construct( );
    }

    function preProcess( ) {
        $this->_add2groupSupported = false;
        parent::preProcess( );
    }
  
    function select( ) { 
        $select = array( );
        $this->_columnHeaders = array( );
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
        
        $this->_select = "SELECT " . implode( ', ', $select ) . " ";
    }
    

    function from(  ) {
        $alias = $this->_aliases[$this->_extentedCareTable];
        $this->_from = " FROM
                              $this->_extentedCareTable $alias
                              INNER JOIN civicrm_contact {$this->_aliases['civicrm_student']}
                                           ON $alias.entity_id = {$this->_aliases['civicrm_student']}.id
                              LEFT JOIN civicrm_relationship relationship
                                           ON relationship.relationship_type_id = 1 AND relationship.contact_id_a = {$this->_aliases['civicrm_student']}.id AND relationship.is_active = 1      
                              LEFT JOIN civicrm_contact {$this->_aliases['civicrm_parent']}
                                           ON {$this->_aliases['civicrm_parent']}.id = relationship.contact_id_b
                              LEFT JOIN  civicrm_email {$this->_aliases['civicrm_email']} 
                                           ON ({$this->_aliases['civicrm_parent']}.id = {$this->_aliases['civicrm_email']}.contact_id AND {$this->_aliases['civicrm_email']}.is_primary = 1) 
                              LEFT JOIN civicrm_phone {$this->_aliases['civicrm_phone']} 
                                           ON {$this->_aliases['civicrm_parent']}.id = {$this->_aliases['civicrm_phone']}.contact_id AND {$this->_aliases['civicrm_phone']}.is_primary = 1 ";
    }
    
    function where( ) {
        $alias   = $this->_aliases[$this->_extentedCareTable];
        $clauses = array( );
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('filters', $table) ) {
                foreach ( $table['filters'] as $fieldName => $field ) {
                    $clause = null;
                    if ( $field['type'] & CRM_Utils_Type::T_DATE ) {
                        $relative = CRM_Utils_Array::value( "{$fieldName}_relative", $this->_params );
                        $from     = CRM_Utils_Array::value( "{$fieldName}_from"    , $this->_params );
                        $to       = CRM_Utils_Array::value( "{$fieldName}_to"      , $this->_params );
                        
                        $clause = $this->dateClause( $field['name'], $relative, $from, $to );
                    } else {
                        $op = CRM_Utils_Array::value( "{$fieldName}_op", $this->_params );
                        if ( $op ) {
                            
                            // hack for values type string
                            if ( $op == 'in' ) {
                                $value  = CRM_Utils_Array::value( "{$fieldName}_value", $this->_params );
                                if ( $value !== null && count( $value ) > 0 ) {
                                    $clause = "( {$field['dbAlias']} IN ('" . implode( '\',\'', $value ) . "' ) )";
                                }
                            } else {
                                $clause = 
                                    $this->whereClause( $field,
                                                        $op,
                                                        CRM_Utils_Array::value( "{$fieldName}_value", $this->_params ),
                                                        CRM_Utils_Array::value( "{$fieldName}_min", $this->_params ),
                                                        CRM_Utils_Array::value( "{$fieldName}_max", $this->_params ) );
                            }
                        }
                    }
                    
                    if ( ! empty( $clause ) ) {
                        $clauses[] = $clause;
                    }
                }
            }
        }
        if ( empty( $clauses ) ) {
            $this->_where = "WHERE ( 1 ) ";
        } else {
            $this->_where = "WHERE " . implode( ' AND ', $clauses );
        } 

    }

   function groupBy( ) {
        $this->_groupBy = " GROUP BY {$this->_aliases['civicrm_student']}.id,{$this->_aliases['civicrm_parent']}.id ";
    }
    
    function postProcess( ) {
        $this->beginPostProcess( );

        $sql = $this->buildQuery( );
        $this->buildRows ( $sql, $rows );

        $this->setPager( );
        $this->alterDisplay( $rows );

        unset( $this->_columnHeaders['civicrm_student_id'], $this->_columnHeaders['civicrm_parent_id'] );
        
        $this->doTemplateAssignment( $rows );

        $this->endPostProcess( $rows );  
    } 
    
    function alterDisplay( &$rows ) {
        $entryFound   = false;
        $flag_student = 0;
        
        foreach ( $rows as $rowNum => $row ) {
            // remove repeat for Student
            if ( array_key_exists('civicrm_student_display_name', $row) &&  
                 $value = $row['civicrm_student_id'] ) {
                    if ( $rowNum == 0 ) {
                        $privious_student = $value;
                    } else {
                        if( $privious_student == $value ) {
                            $flag_student     = 1;
                            $privious_student = $value;
                        } else { 
                            $flag_student     = 0;
                            $privious_student = $value;
                        }
                    }

                    if( $flag_student ) {
                        $rows[$rowNum]['civicrm_student_display_name'] = "";
                    } else {
                        $url = CRM_Utils_System::url( "civicrm/contact/view",  
                                                      'reset=1&cid=' . $value );
                        $rows[$rowNum]['civicrm_student_display_name_link' ] = $url;
                        $rows[$rowNum]['civicrm_student_display_name_hover'] = 
                            ts("View Contact Summary for this Contact");
                    }
                    $entryFound = true;
            }
            
            if ( array_key_exists('civicrm_parent_display_name', $row) &&  
                 $value = $row['civicrm_parent_id'] ) {
                $url = CRM_Utils_System::url( "civicrm/contact/view",  
                                              'reset=1&cid=' . $value );
                $rows[$rowNum]['civicrm_parent_display_name_link' ] = $url;
                $rows[$rowNum]['civicrm_parent_display_name_hover'] = 
                    ts("View Contact Summary for this Contact");
                $entryFound = true; 
            }
            
            // skip looking further in rows, if first row itself doesn't 
            // have the column we need
            if ( !$entryFound ) {
                        break;
            }
        }
    }
}
