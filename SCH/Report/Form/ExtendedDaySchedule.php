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

class SFS_Report_Form_ExtendedDaySchedule extends CRM_Report_Form {
    
    // set custom table name
    protected $_customTable          = 'sfschool_extended_care_source';  
   
    // node id to be display bellow the report  
    protected $_nodeId               = 15;

    function __construct( ) {
        
        $this->_columns[$this->_customTable] = array( 'dao'     => 'CRM_Contact_DAO_Contact',
                                                      'fields'  => 
                                                      array( 'day_of_week' => array( 'required'   => true,
                                                                                     'no_display' => true ),
                                                             'session'     => array( 'required'   => true,
                                                                                     'no_display' => true ),
                                                             'name'        => array( 'required'   => true,
                                                                                     'no_display' => true ),   
                                                             'instructor'  => array( 'required'   => true,
                                                                                     'no_display' => true ), 
                                                             'min_grade'   => array( 'required'   => true,
                                                                                     'no_display' => true ),
                                                             'max_grade'   => array( 'required'   => true,
                                                                                     'no_display' => true ),
                                                             'fee_block'   => array( 'required'   => true,
                                                                                     'no_display' => true ),
                                                             'start_date'  => array( 'required'   => true,
                                                                                     'no_display' => true ),
                                                             'end_date'    => array( 'required'   => true,
                                                                                     'no_display' => true ),
                                                             'location'    => array( 'required'   => true,
                                                                                     'no_display' => true ),
                                                             ),
                                                      );
        parent::__construct( );
    }

    function preProcess( ) {
        $this->_csvSupported = false;
        $this->_add2groupSupported = false;
        parent::preProcess( );
    }
    
    function select(  ) {
        $alias  = $this->_aliases[$this->_customTable];
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
        $alias = $this->_aliases[$this->_customTable];
        $this->_from = "FROM $this->_customTable $alias ";

    }
    
    function where( ) { 
        $alias = $this->_aliases[$this->_customTable];
        $this->_where = "WHERE $alias.is_active=1"; 
    }
    
    function groupBy( ) {
        $alias = $this->_aliases[$this->_customTable];
        $this->_groupBy = " GROUP BY $alias.day_of_week, $alias.name";
    }


    function postProcess( ) {
        require_once 'SFS/Utils/ExtendedCare.php';

        $this->beginPostProcess( ); 
        
        $this->assign('node', node_load( $this->_nodeId ) );

        $sql = $this->buildQuery( false);

        $daysOfWeek = SFS_Utils_ExtendedCare::daysOfWeek();
        $dao  = CRM_Core_DAO::executeQuery( $sql );
        $activityFree = $activityPaid = array( );
        $feeAlias = $this->_customTable.'_fee_block';
        $dayAlias = $this->_customTable.'_day_of_week';
        
        while( $dao->fetch( ) ) {

            $row = array( );
            foreach ( $this->_columnHeaders as $key => $value ) {
                if ( property_exists( $dao, $key ) ) {
                        $row[$key] = $dao->$key;
                }
            }
            if( $dao->$feeAlias) {
                $activityPaid[$dao->$dayAlias][] = $row;
             } else {
                $activityFree[$dao->$dayAlias][] = $row;
            }
        }

        $maxRows = 0;
        $day     = 'Monday';
        foreach( $activityPaid  as $key =>  $row ) {
            if( $maxRows <  count($row) ) {
                $maxRows =  count($row);
                $day = $key;
            }
        }
        $paidRows = $activityPaid[$day];
        $this->assign( 'paidCount', $maxRows);

        $maxRows = 0;
        $day     = 'Monday';
        foreach( $activityFree as $key =>  $row ) {
            if( $maxRows <  count($row) ) {
                $maxRows =  count($row);
                $day = $key;
            }
        }
        $freeRows = $activityFree[$day];
        $this->assign( 'freeCount', $maxRows);

        $this->assign( 'activityPaid', $activityPaid );
        $this->assign( 'activityFree', $activityFree );
        $this->assign( 'paidRows', $paidRows  );
        $this->assign( 'freeRows', $freeRows  );

        $this->formatDisplay( $daysOfWeek,false );
        $this->doTemplateAssignment($daysOfWeek );
        $this->endPostProcess($daysOfWeek);

    }
    

}
