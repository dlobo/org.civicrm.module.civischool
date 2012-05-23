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

class SFS_Report_Form_ParentTeacherConference extends CRM_Report_Form {
    
    // set custom table name
    protected $_customTable = 'civicrm_value_school_information';  
    
    protected $_typeField   = array( 'column_name'  => 'subtype',
                                     'value'        => 'Student' );
    protected $_gradeField  = array( 'column_name'  => 'grade' );
    
    protected $_actvityTypeId = 20;
    
    protected $_teacherRelationship = 10;
    protected $_teacherRelContact   = 'contact_id_a';

    function __construct( ) {

        $fields = array( );
        $query  = " 
                   SELECT column_name, label , option_group_id 
                   FROM civicrm_custom_field 
                   WHERE is_active = 1 AND column_name='".$this->_gradeField['column_name']."' AND custom_group_id = ( SELECT id FROM civicrm_custom_group WHERE table_name='{$this->_customTable}' ) " ;
        $dao_column = CRM_Core_DAO::executeQuery( $query );
        
        $this->_optionFields = $this->_textFields = array( );

        while ( $dao_column->fetch( ) ) {
            $fields[$dao_column->column_name] = array('required'   => true, 
                                                      'title'      => $dao_column->label,
                                                      'no_display' => true
                                                        );
                $this->_gradeField['op_group_id'] = $dao_column->option_group_id;
        }
        
        $filters = array( );
        // filter for Grade
        $options = array( );
        $query   = "SELECT label , value FROM civicrm_option_value WHERE option_group_id =".$this->_gradeField['op_group_id']."  AND is_active=1";
        $dao     = CRM_Core_DAO::executeQuery( $query );
        
        while( $dao->fetch( ) ) {
            $options[$dao->value] = $dao->label; 
        }
        $filters[$this->_gradeField['column_name']] = array(
                                                            'title'        => ts('Grade'),
                                                            'operatorType' => CRM_Report_Form::OP_SELECT,
                                                            'options'      => array( '' => '-select-' ) + $options ,
                                                            'type'         => CRM_Utils_Type::T_STRING
                                                            );

        //get teacher with relatioship 
        $this->teacherOptions = array( );
        $query = " SELECT DISTINCT(contact.id) ,contact.display_name 
                   FROM civicrm_contact contact 
                   INNER JOIN civicrm_relationship relationship 
                         ON ( contact.id=relationship.{$this->_teacherRelContact} AND relationship.is_active=1 )  
                   INNER JOIN {$this->_customTable} school_information
                         ON ( school_information.entity_id = contact.id AND (school_information.subtype='Staff' OR school_information.subtype='Parent') )
                   WHERE relationship.relationship_type_id={$this->_teacherRelationship} ";
        $dao  = CRM_Core_DAO::executeQuery( $query );

        while( $dao->fetch( ) ) {
            $this->teacherOptions[$dao->id] = $dao->display_name;
        }

        $this->_columns = array(
                                'civicrm_activity'      =>
                                array( 'dao'     => 'CRM_Activity_DAO_Activity',
                                       'fields'  =>
                                       array( 'activity_date_time' => array( 'title'        => ts('Date'),
                                                                             'no_display'   => true, 
                                                                             'required'     => true,
                                                                             'type'         => CRM_Utils_Type::T_TIME,
                                                                             ),
                                             ), ),

                                $this->_customTable   =>
                                array( 'dao'     => 'CRM_Contact_DAO_Contact',
                                       'fields'  => $fields ,
                                       'filters' => $filters,
                                       ),

                                'civicrm_contact' =>
                                array( 'dao'       => 'CRM_Contact_DAO_Contact',
                                       'fields'    => 
                                       array( 'display_name' =>
                                              array(
                                                    'no_display' => true,
                                                    'required'   => true,
                                                    'title'      => ts('Advisor')
                                                    ),
                                              'id' =>
                                              array(
                                                    'no_display' => true,
                                                    'required'   => true,
                                                    ),
                                              ),
                                       'filters'   =>
                                       array( 'id_teacher' =>
                                              array('name'         => 'id',
                                                    'title'        => ts('Advisor'),
                                                    'operatorType' => CRM_Report_Form::OP_SELECT,
                                                    'options'      => array( '' => '-select-' ) + $this->teacherOptions ,
                                                    'type'         => CRM_Utils_Type::T_INT,
                                                    ) ) ),
                                                                
                                 'civicrm_contact_student' =>
                                array( 'dao'       => 'CRM_Contact_DAO_Contact',
                                       'fields'   =>
                                       array( 'display_name' =>
                                              array(
                                                    'no_display' => true,
                                                    'required'   => true,
                                                    'title'      => ts('Student')
                                                    ),
                                              'id' =>
                                              array(
                                                    'no_display' => true,
                                                    'required'   => true,
                                                    ), ),
                                       ),

                                'civicrm_contact_parent' =>
                                array( 'dao'       => 'CRM_Contact_DAO_Contact',
                                       'fields'   =>
                                       array( 'display_name' =>
                                              array(
                                                    'no_display' => true,
                                                    'required'   => true,
                                                    'title'      => ts('Parent')
                                                    ),
                                              'id' =>
                                              array(
                                                    'no_display' => true,
                                                    'required'   => true,
                                                    ),
                                              ) ),
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
                        if( $tableName == 'civicrm_contact_parent' ) {
                            $select[] = "GROUP_CONCAT(DISTINCT {$field['dbAlias']}  ORDER BY {$field['dbAlias']} SEPARATOR ',<br/>' ) as {$tableName}_{$fieldName}";
                        } else {
                            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
                        }
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['type']  = CRM_Utils_Array::value( 'type', $field );
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
                    }
                }
            }
        }
        
        $this->_select = "SELECT " . implode( ', ', $select ) . " ";
    }
    

    function from(  ) {
        $alias = $this->_aliases[$this->_customTable];
        $this->_from = "FROM
                              civicrm_activity_assignment activity_assignment
                              INNER JOIN $this->_customTable sfschool
                                           ON sfschool.entity_id = activity_assignment.assignee_contact_id  AND (sfschool.subtype='Parent' OR sfschool.subtype='Staff')
                              INNER JOIN civicrm_contact {$this->_aliases['civicrm_contact']}
                                           ON {$this->_aliases['civicrm_contact']}.id = activity_assignment.assignee_contact_id
                              INNER  JOIN civicrm_activity {$this->_aliases['civicrm_activity']}
                                            ON ({$this->_aliases['civicrm_activity']}.id = activity_assignment.activity_id AND  {$this->_aliases['civicrm_activity']}.is_deleted=0 AND {$this->_aliases['civicrm_activity']}.is_test=0 AND {$this->_aliases['civicrm_activity']}.activity_type_id={$this->_actvityTypeId} )
                              INNER JOIN civicrm_activity_target activity_target 
                                            ON {$this->_aliases['civicrm_activity']}.id = activity_target.activity_id
                              INNER JOIN civicrm_contact  {$this->_aliases['civicrm_contact_student']}
                                            ON {$this->_aliases['civicrm_contact_student']}.id = activity_target.target_contact_id
                              INNER JOIN $this->_customTable {$alias} 
                                            ON ({$alias}.entity_id={$this->_aliases['civicrm_contact_student']}.id AND {$alias}.subtype='Student')
                              LEFT JOIN civicrm_relationship relationship
                                             ON (relationship.relationship_type_id = 1 AND relationship.contact_id_a = {$this->_aliases['civicrm_contact_student']}.id AND relationship.is_active=1)
                              LEFT JOIN civicrm_contact  {$this->_aliases['civicrm_contact_parent']}
                                             ON {$this->_aliases['civicrm_contact_parent']}.id =  relationship.contact_id_b ";
    }
    
    function where( ) {
        $alias = $this->_aliases[$this->_customTable];
        $clauses = array( "{$this->_aliases['civicrm_activity']}.status_id = 1" );
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

    function orderBy( ) {
        $this->_orderBy = " ORDER BY civicrm_activity_activity_date_time asc ";
    }

    function groupBy( ) {
        $this->_groupBy = " GROUP BY {$this->_aliases['civicrm_contact']}.id,{$this->_aliases['civicrm_contact_student']}.id,{$this->_aliases['civicrm_activity']}.id";
    }
     
    function statistics( &$rows ) {
        $statistics =  parent::statistics( $rows );
        //replace Advisor id by name
        if ( CRM_Utils_Array::value( 'filters' , $statistics ) ) {
            foreach ( $statistics['filters'] as $key => $fields ) {
                if ( CRM_Utils_Array::value( 'title', $fields ) == 'Advisor' ) {
                    $statistics['filters'][$key]['value'] = 'Is equal to '. $this->teacherOptions[$this->_params['id_teacher_value']];
                }
            }
        }
       return $statistics;
    }

    function postProcess( ) {
        $this->beginPostProcess( );

        $removeHeaders = array ( 'civicrm_contact_id', 'civicrm_contact_parent_id', 'civicrm_contact_student_id');
        
        $sql = $this->buildQuery( );
        $this->buildRows ( $sql, $rows );

        // hide Advisor column if filter Advisor is selected
        if( CRM_Utils_Array::value( 'id_teacher_value' , $this->_params ) ) {
            unset( $this->_columnHeaders['civicrm_contact_display_name'] );
        }

        $tempHeaders = $this->_columnHeaders;

        foreach( $tempHeaders as $field => $header ) {
            if( in_array($field, $removeHeaders) ) {
                unset($tempHeaders[$field]);
            }
        }
       
        $this->formatDisplay($rows );
        $this->_columnHeaders = $tempHeaders;
        
        $this->doTemplateAssignment($rows );
        
        $this->endPostProcess($rows );

    } 
    
    function alterDisplay( &$rows ) {
        $entryFound = false;
       
      	$flag_teacher   = $flag_student = 0;
        
        foreach ( $rows as $rowNum => $row ) {
            if ( array_key_exists('civicrm_activity_activity_date_time', $row) ) {
                $rows[$rowNum]['civicrm_activity_activity_date_time'] = 
                    CRM_Utils_Date::customFormat( $row['civicrm_activity_activity_date_time'] );
                $entryFound = true;
            }
            
            // remove repeat for  Teacher
            if ( array_key_exists('civicrm_contact_display_name', $row) ) {
                if ( $value = $row['civicrm_contact_id'] ) {
                    if( $rowNum == 0 ) {
                        $privious_teacher = $value;
                    } else {
                        if( $privious_teacher == $value ) {
                            $flag_teacher     = 1;
                            $privious_teacher = $value;
                        } else { $flag_teacher =0; $privious_teacher=$value; }
                    }
                    
                    if(  $flag_teacher == 1 ) {
                        // hide columns for Teacher
                        $rows[$rowNum]['civicrm_contact_display_name'] = "";          
                    } else {
                        $url = CRM_Utils_System::url( "civicrm/contact/view",  
                                                      'reset=1&cid=' . $value );
                        $rows[$rowNum]['civicrm_contact_display_name_link' ] = $url;
                        $rows[$rowNum]['civicrm_contact_display_name_hover'] = 
                            ts("View Contact Summary for this Contact");
                    }
                    $entryFound = true;
                }
            }

            // remove repeat for Student
            if ( array_key_exists('civicrm_contact_student_display_name', $row) ) {
                if ( $value = $row['civicrm_contact_student_id'] ) {
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

                    if( $flag_teacher == 1 && $flag_student == 1 ) {
                        // hide columns for student
                        $rows[$rowNum]['civicrm_contact_student_display_name']     = "";
                        $rows[$rowNum]['civicrm_activity_activity_date_time']      = "";
                        $rows[$rowNum]['civicrm_value_school_information_grade']   = "";
                        $rows[$rowNum]['civicrm_activity_subject']                 = "";

                    } else {
                        $url = CRM_Utils_System::url( "civicrm/contact/view",  
                                                      'reset=1&cid=' . $value );
                        $rows[$rowNum]['civicrm_contact_student_display_name_link' ] = $url;
                        $rows[$rowNum]['civicrm_contact_student_display_name_hover'] = 
                            ts("View Contact Summary for this Contact");
                    }
                    $entryFound = true;
                }
            }

            
            // skip looking further in rows, if first row itself doesn't 
            // have the column we need
            if ( !$entryFound ) {
                break;
            }
        }
    }

    
}
