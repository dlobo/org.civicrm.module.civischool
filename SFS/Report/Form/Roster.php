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

class SFS_Report_Form_Roster extends CRM_Report_Form {
    
    // set custom table name
    protected $_schoolInfo          = 'civicrm_value_school_information';
    
    function __construct( ) {
        
        $fields = array( );
        $query  = "
SELECT column_name, label , option_group_id 
FROM   civicrm_custom_field 
WHERE  is_active = 1 
AND    column_name='grade' 
AND    custom_group_id = (
  SELECT id FROM civicrm_custom_group WHERE table_name='{$this->_schoolInfo}'
 )
";
        $dao_column = CRM_Core_DAO::executeQuery( $query );
        
        while ( $dao_column->fetch( ) ) {
            $fields[$dao_column->column_name] = array('required'   => true, 
                                                      'title'      => $dao_column->label,
                                                      'no_display' => true
                                                      );
            $op_group_id = $dao_column->option_group_id;
        }
        
        $filters = array( );
        // filter for Grade
        $options = array( );
        $query   = "SELECT label , value FROM civicrm_option_value WHERE option_group_id =".$op_group_id."  AND is_active=1";
        $dao     = CRM_Core_DAO::executeQuery( $query );
        
        while( $dao->fetch( ) ) {
            $options[$dao->value] = $dao->label; 
        }
        $filters['grade'] = array( 'title'        => ts('Grade'),
                                   'operatorType' => CRM_Report_Form::OP_SELECT,
                                   'options'      => array( '' => '-select-' ) + $options ,
                                   'type'         => CRM_Utils_Type::T_STRING
                                   );
        
        $this->_columns = 
            array( 
                  'civicrm_contact' =>
                  array( 'dao'       => 'CRM_Contact_DAO_Contact',
                         'fields'    => 
                         array( 'sort_name' =>
                                array(
                                      'no_display' => true,
                                      'required'   => true,
                                      'title'      => ts('STUDENT')
                                      ),
                                'id' =>
                                array(
                                      'no_display' => true,
                                      'required'   => true,
                                                    ),
                                ), 
                         'alias' => 'cs'
                         ),

                  $this->_schoolInfo =>
                                array( 'dao'     => 'CRM_Contact_DAO_Contact',
                                       'fields'  => $fields ,
                                       'filters' => $filters,
                                       'alias'   => 'school',
                                       ),

                  'civicrm_contact_parent1' =>
                  array( 'dao'       => 'CRM_Contact_DAO_Contact',
                         'fields'    => 
                         array( 'display_name' =>
                                array(
                                      'no_display' => true,
                                      'required'   => true,
                                      ),
                                ),
                         'alias' => 'cp1'
                         ),
                  
                  'civicrm_contact_parent2' =>
                  array( 'dao'       => 'CRM_Contact_DAO_Contact',
                         'fields'    => 
                         array( 'display_name' =>
                                array(
                                      'no_display' => true,
                                      'required'   => true,
                                      ),
                                ),
                         'alias' => 'cp2'
                         ),

                  'civicrm_email_parent1' =>
                  array( 'dao'       => 'CRM_Core_DAO_Email',
                         'fields'    => 
                         array( 'email' =>
                                array(
                                      'no_display' => true,
                                      'required'   => true,
                                      ),
                                ),
                         'alias' => 'cp1e'
                         ),

                  'civicrm_email_parent2' =>
                  array( 'dao'       => 'CRM_Core_DAO_Email',
                         'fields'    => 
                         array( 'email' =>
                                array(
                                      'no_display' => true,
                                      'required'   => true,
                                      ),
                                ),
                         'alias' => 'cp2e'
                         ),

                  'civicrm_phone_parent1' =>
                  array( 'dao'       => 'CRM_Core_DAO_Phone',
                         'fields'    => 
                         array( 'phone' =>
                                array(
                                      'no_display' => true,
                                      'required'   => true,
                                      ),
                                ),
                         'alias' => 'cp1phone'
                         ),

                  'civicrm_phone_parent2' =>
                  array( 'dao'       => 'CRM_Core_DAO_Phone',
                         'fields'    => 
                         array( 'phone' =>
                                array(
                                      'no_display' => true,
                                      'required'   => true,
                                      ),
                                ),
                         'alias' => 'cp2phone'
                         ),

                  'civicrm_address_parent1' =>
                   array( 'dao'       => 'CRM_Core_DAO_Address',
                          'fields'    =>
                          array( 'street_address'    => 
                                 array( 'no_display' => true,
                                        'required'   => true, ),
                                 'city'              => 
                                 array( 'no_display' => true,
                                        'required'   => true, ),
                                 'state_province_id' => 
                                 array( 'no_display' => true,
                                        'required'   => true, ),
                                 'postal_code' =>
                                 array( 'no_display' => true,
                                        'required'   => true, ),
                                 ),
                          'alias' => 'cp1add'
                          ),

                  'civicrm_address_parent2' =>
                   array( 'dao'       => 'CRM_Core_DAO_Address',
                          'fields'    =>
                          array( 'street_address'    => 
                                 array( 'no_display' => true,
                                        'required'   => true, ),
                                 'city'              => 
                                 array( 'no_display' => true,
                                        'required'   => true, ),
                                 'state_province_id' => 
                                 array( 'no_display' => true,
                                      'required'   => true, ),
                                 'postal_code' =>
                                 array( 'no_display' => true,
                                        'required'   => true, ),
                                 ),
                          'alias' => 'cp2add'
                          ),
                   );
    
        parent::__construct( );
    }

    function preProcess( ) {
        $this->_csvSupported = false;
        parent::preProcess( );
    }
    
    function select(  ) {
    
        $fieldArray = array( 'civicrm_contact',$this->_customTable );
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
        
        $alias = $this->_aliases[$this->_schoolInfo];
        
        $this->_from = "FROM 
                         civicrm_contact {$this->_aliases['civicrm_contact']}

                         INNER JOIN civicrm_value_school_information $alias ON 
                                {$this->_aliases['civicrm_contact']}.id = $alias.entity_id

                         INNER JOIN civicrm_relationship r1 ON 
                                r1.contact_id_a = {$this->_aliases['civicrm_contact']}.id

                         INNER JOIN civicrm_relationship r2 ON 
                                r2.contact_id_a = {$this->_aliases['civicrm_contact']}.id

                         LEFT  JOIN civicrm_contact {$this->_aliases['civicrm_contact_parent1']} ON 
                               {$this->_aliases['civicrm_contact_parent1']}.id = r1.contact_id_b

                         LEFT  JOIN civicrm_email {$this->_aliases['civicrm_email_parent1']}  ON 
                               {$this->_aliases['civicrm_email_parent1']}.contact_id = {$this->_aliases['civicrm_contact_parent1']}.id AND {$this->_aliases['civicrm_email_parent1']}.is_primary = 1

                         LEFT  JOIN civicrm_phone {$this->_aliases['civicrm_phone_parent1']} ON 
                               {$this->_aliases['civicrm_phone_parent1']}.contact_id = {$this->_aliases['civicrm_contact_parent1']}.id AND {$this->_aliases['civicrm_phone_parent1']}.is_primary = 1
                         
                         LEFT  JOIN civicrm_address {$this->_aliases['civicrm_address_parent1']} ON
                                {$this->_aliases['civicrm_address_parent1']}.contact_id = {$this->_aliases['civicrm_contact_parent1']}.id AND {$this->_aliases['civicrm_address_parent1']}.is_primary=1

                         LEFT  JOIN civicrm_contact {$this->_aliases['civicrm_contact_parent2']} ON 
                               {$this->_aliases['civicrm_contact_parent2']}.id = r2.contact_id_b

                         LEFT  JOIN civicrm_email {$this->_aliases['civicrm_email_parent2']}  ON 
                               {$this->_aliases['civicrm_email_parent2']}.contact_id = {$this->_aliases['civicrm_contact_parent2']}.id AND {$this->_aliases['civicrm_email_parent2']}.is_primary = 1

                         LEFT  JOIN civicrm_phone {$this->_aliases['civicrm_phone_parent2']} ON 
                               {$this->_aliases['civicrm_phone_parent2']}.contact_id = {$this->_aliases['civicrm_contact_parent2']}.id AND {$this->_aliases['civicrm_phone_parent2']}.is_primary = 1

                         LEFT  JOIN civicrm_address {$this->_aliases['civicrm_address_parent2']} ON
                                {$this->_aliases['civicrm_address_parent2']}.contact_id = {$this->_aliases['civicrm_contact_parent2']}.id AND {$this->_aliases['civicrm_address_parent2']}.is_primary=1

 ";

    }
    
    function where( ) { 
        $alias = $this->_aliases[$this->_schoolInfo];
        $clauses    = array( );
        $clauses[]  = "$alias.is_currently_enrolled = 1";
        foreach ( $this->_columns as $tableName => $table ) {
         
            if ( array_key_exists('filters', $table) ) {
                foreach ( $table['filters'] as $fieldName => $field ) {
                    $clause = null;

                    //  if ( CRM_Utils_Array::value( 'type', $field ) & CRM_Utils_Type::T_DATE ) {
                     if ( $field['operatorType'] & CRM_Report_Form::OP_DATE ) { 
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
        
        $this->_where = $this->_where ." AND r1.relationship_type_id = 1 AND r2.relationship_type_id = 1 AND ( {$this->_aliases['civicrm_contact_parent1']}.id < {$this->_aliases['civicrm_contact_parent2']}.id OR {$this->_aliases['civicrm_contact_parent2']}.id IS NULL ) AND  $alias.subtype = 'Student'";

    }
    
    function orderBy( ) {
        $alias = $this->_aliases[$this->_schoolInfo];
        $this->_orderBy = " ORDER BY $alias.grade_sis, $alias.grade, {$this->_aliases['civicrm_contact']}.sort_name";
        
    }

    function postProcess( ) {
        $rows = array();
        $unsetHeaders = array( 'civicrm_contact_id','civicrm_contact_parent1_display_name','civicrm_contact_parent2_display_name','civicrm_email_parent1_email','civicrm_email_parent2_email','civicrm_address_parent1_street_address','civicrm_address_parent1_city','civicrm_address_parent1_state_province_id' ,'civicrm_address_parent2_street_address','civicrm_address_parent2_city','civicrm_address_parent2_state_province_id','civicrm_phone_parent1_phone', 'civicrm_phone_parent2_phone','civicrm_address_parent1_postal_code','civicrm_address_parent2_postal_code');

        $this->beginPostProcess( );

        $sql = $this->buildQuery( );

        $dao  = CRM_Core_DAO::executeQuery( $sql );
 
        while ( $dao->fetch( ) ) {
            $row = array( );
            foreach ( $this->_columnHeaders as $key => $value ) {
                if ( property_exists( $dao, $key ) ) {
                    $row[$key] = $dao->$key;
                }
            }
            $rows[] = $row;
        }

        foreach( $unsetHeaders as $header ) {
            unset($this->_columnHeaders[$header]);
        }
        
        $this->_columnHeaders['parent_names'] = array( 'type'  => 2 ,
                                                       'title' => 'PARENT' );
        $this->_columnHeaders['parent_info']  = array( 'type'  => 2 ,
                                                       'title' => 'ADDRESS' );
        $this->_columnHeaders['parent_phone'] = array( 'type'  => 2 ,
                                                       'title' => 'PHONE' );
        $this->_columnHeaders['civicrm_value_school_information_grade'] = array( 'type'  => 2 ,
                                                                                 'title' => 'GRADE' );
        $this->setPager( );
        $this->alterDisplay( $rows );

        $this->doTemplateAssignment( $rows );

        $this->endPostProcess( $rows );

    }
    
    
    function alterDisplay( &$rows ) {
        // custom code to alter rows
        $entryFound = false;
        $parents = array( 'parent1', 'parent2');
        
        foreach ( $rows as $rowNum => $row ) {
 
            if ( array_key_exists('civicrm_contact_id', $row) ) {
                
                $parentNames = '';
                $parentInfo  = '';
                $parentPhone = '';

                if ( $value = $row['civicrm_contact_sort_name'] ) {
                    $url = CRM_Utils_System::url( 'civicrm/contact/view', 
                                                  'reset=1&cid=' . $row['civicrm_contact_id'],
                                                  $this->_absoluteUrl );
                    $rows[$rowNum]['civicrm_contact_sort_name_link' ] = $url;
                    $rows[$rowNum]['civicrm_contact_sort_name_hover'] = ts("View Contact details for this contact.");
                }
                
                foreach( $parents as $k => $parent ) {

                    if ( $value = $row["civicrm_contact_{$parent}_display_name"] ) {
                        $parentNames = $parentNames."{$value} <br>";  
                    }
                    if ( $value = $row["civicrm_address_{$parent}_street_address"] ) {
                        $parentInfo = $parentInfo."{$value} &nbsp;";  
                    }
                    if ( $value = $row["civicrm_address_{$parent}_city"] ) {
                        $parentInfo = $parentInfo." {$value} &nbsp;";  
                    }
                    if ( $value = $row["civicrm_address_{$parent}_state_province_id"] ) {
                        $value = CRM_Core_PseudoConstant::stateProvince( $value, false );
                        $parentInfo = $parentInfo." {$value} &nbsp;";  
                    }
                    if ( $value = $row["civicrm_address_{$parent}_postal_code"] ) {
                        $parentInfo = $parentInfo." {$value}";  
                    }
                    if ( $value = $row["civicrm_email_{$parent}_email"] ) {
                        if( $parentInfo ) {
                            $parentInfo .= "<br>"; 
                        }
                        $parentInfo = $parentInfo." {$value}";  
                    }
                    if ( $value = $row["civicrm_phone_{$parent}_phone"] ) {
                        $parentPhone = $parentPhone." {$value} <br>";  
                    }

                    if( $k == 0 ) {
                        $parentInfo  .= "<br>";
                        $parentPhone .= "<br>";
                        $parentNames .= "<br>";
                    }
                }
                $rows[$rowNum]['parent_names'] = $parentNames;
                $rows[$rowNum]['parent_info']  = $parentInfo;
                $rows[$rowNum]['parent_phone'] = $parentPhone;

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
