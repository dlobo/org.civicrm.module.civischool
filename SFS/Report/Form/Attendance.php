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

class SFS_Report_Form_Attendance extends CRM_Report_Form {

    // set custom table name
    protected $_customTable   = 'civicrm_value_extended_care';
    
    // col mapper
    protected $_colMapper = array ( 'dayOfWeek'    => 'day_of_week',
                                    'sessionName'  => 'name',
                                    'sessionOrder' => 'session',
                                    'isCancelled'  => 'has_cancelled',
                                    );
    
    //set time for Sign out columns
    protected $_sesionOrder = array( 'first'  => '3.30pm- 4.30pm',
                                     'second' => '4.30pm- 5.15pm',
                                     'third'  => '5.15pm- 6.15pm',
                                     );
    
    function __construct( ) {
        $this->_columns = array( );
        
        $query  = "
SELECT column_name, label , option_group_id
FROM civicrm_custom_field
WHERE is_active = 1 AND column_name = '{$this->_colMapper['dayOfWeek']}'";
        $dao_column = CRM_Core_DAO::executeQuery( $query );
        $this->_optionFields = array( );
        while ( $dao_column->fetch( ) ) {
            if( $dao_column->option_group_id ) {
                $query   = "
SELECT label , value
FROM   civicrm_option_value 
WHERE  option_group_id = {$dao_column->option_group_id}
AND    is_active = 1
";
                $dao     = CRM_Core_DAO::executeQuery( $query );
                while( $dao->fetch() ) {
                    $this->_optionFields[$dao_column->column_name][$dao->value] = $dao->label;
                }
            }
        }

        $query   = "
SELECT distinct {$this->_colMapper['sessionName']} as session_name 
FROM   sfschool_extended_care_source value_extended_care_2_civireport
WHERE  is_active = 1
AND    term = %1
";
        require_once 'SFS/Utils/ExtendedCare.php';
        $params = array( 1 => array( SFS_Utils_ExtendedCare::getTerm( ), 'String' ) );
        $dao      = CRM_Core_DAO::executeQuery( $query, $params );

        $sOptions = array( );
        while( $dao->fetch( ) ) {
            $sOptions[$dao->session_name] = $dao->session_name;
        }

        $this->_columns[$this->_customTable] = 
            array( 'dao'     => 'CRM_Contact_DAO_Contact',
                   'filters' =>             
                   array( 
                         'weekday'       => 
                         array( 'title'   => ts( 'Day Of Week' ),
                                'operatorType' => CRM_Report_Form::OP_SELECT,
                                'options'      => $this->_optionFields[$this->_colMapper['dayOfWeek']] ),
                          ),
                   );
        parent::__construct( );
    }

    function preProcess( ) {
        parent::preProcess( );
        if ( !$this->_id ) {
            if ( CRM_Utils_Array::value( 'title', $_POST ) ) {
                $this->assign('reportTitle', $_POST['title']);
            } else if ( CRM_Utils_Array::value( 'weekday_value', $_POST ) ) {
                $this->assign('reportTitle', "EXTENDED CARE FOR " . strtoupper($_POST['weekday_value']));
            }
        }

    }
    
    function postProcess( ) {
        $this->beginPostProcess( );

        $sessionHeaders = array();
        foreach( $this->_sesionOrder as $value => $time ) {
            $sessionHeaders[$value] = array( 'title'=> $time ,
                                             'type' => 'signout' );
        }
        
        $sql  = "
SELECT distinct value_extended_care_2_civireport.{$this->_colMapper['sessionName']} as session_name, 
       value_extended_care_2_civireport.{$this->_colMapper['sessionOrder']} as session_order,  
       additional_rows as extra_rows, instructor, location
FROM   sfschool_extended_care_source value_extended_care_2_civireport
WHERE  is_active = 1
AND    term = %1
AND   {$this->_colMapper['dayOfWeek']} = '{$this->_params['weekday_value']}'
ORDER BY session, name, additional_rows
";
        $params = array( 1 => array( SFS_Utils_ExtendedCare::getTerm( 'Fall 2009' ), 'String' ) );
        $sname = CRM_Core_DAO::executeQuery( $sql, $params );
        $rows  = array( ); 

        while( $sname->fetch( ) ) {
            $sql  = "
SELECT contact_civireport.id as contact_civireport_id, 
       contact_civireport.display_name as contact_civireport_display_name, '' as SignIn, '' as SignOut, '' as parent_initial
FROM   civicrm_value_extended_care value_extended_care_civireport
INNER  JOIN civicrm_contact as contact_civireport ON value_extended_care_civireport.entity_id = contact_civireport.id
WHERE  value_extended_care_civireport.{$this->_colMapper['sessionName']} = '{$sname->session_name}' AND 
       value_extended_care_civireport.{$this->_colMapper['sessionOrder']} = '{$sname->session_order}' AND
       value_extended_care_civireport.{$this->_colMapper['dayOfWeek']} = '{$this->_params['weekday_value']}' AND
       value_extended_care_civireport.{$this->_colMapper['isCancelled']} != 1 AND
       term = %1
GROUP BY contact_civireport.id;
";

            $this->_columnHeaders = 
                array( 'contact_civireport_id' => array( 'no_display' => true ),
                       'contact_civireport_display_name' => array( 'title' => 'Name' ),
                       'SignIn'  => array( 'title' => 'Sign In', 'type' => 'signin' ),
                       'parent_initial' => array( 'title' => 'Parent<br/>Initial', 'type' => 'parent' ),
                       );
            $this->_columnHeaders = array_merge( $this->_columnHeaders, $sessionHeaders );

            $index = $sname->session_order . '_' . $sname->session_name;
            $rows[$index] = array( );

            $dao  = CRM_Core_DAO::executeQuery( $sql, $params );
            
            while( $dao->fetch( ) ) {
                if( property_exists( $dao, 'contact_civireport_id' ) ) {
                    if( !$dao->contact_civireport_id ) 
                         continue;
                }
                $row = array( );
                foreach ( $this->_columnHeaders as $key => $value ) {
                    if ( property_exists( $dao, $key ) ) {
                        $row[$key] = $dao->$key;
                    }
                }
                $rows[$index][] = $row;
            } 

            // add extra rows
            if( $sname->extra_rows ) {
                for ($i = 1; $i <= $sname->extra_rows ; $i++) {
                    $rows[$index][] = array('contact_civireport_display_name' => '&nbsp;');
                }
            }

            $classRowCount = count($rows[$index]);
            $rows[$classRowCount . '_' . $index] = $rows[$index];
            unset($rows[$index]);

            $sessionInfo[$classRowCount . '_' . $index] = array( );
            $sessionInfo[$classRowCount . '_' . $index]['title'   ] = $sname->session_name;
            $sessionInfo[$classRowCount . '_' . $index]['session' ] = $sname->session_order;
            $sessionInfo[$classRowCount . '_' . $index]['instRoom'] = ($sname->instructor && $sname->location) ? 
                $sname->instructor . ' - ' . $sname->location : 
                ($sname->instructor ? $sname->instructor : $sname->location);
        }

        // maximize space utilization
        // ksort($rows, SORT_NUMERIC);

        $this->formatDisplay( $rows );

        $this->assign_by_ref( 'sessionInfo', $sessionInfo );
        $this->doTemplateAssignment( $rows );

        $this->endPostProcess( $rows );
    }

    function alterDisplay( &$rows ) {
        foreach ( $rows as $name => $nrows ) {
            foreach ( $nrows as $rowNum => $row ) {
                // convert display name to links
                if ( array_key_exists('contact_civireport_display_name', $row) &&
                     array_key_exists('contact_civireport_id', $row) ) {
                    $url = CRM_Utils_System::url( "civicrm/contact/view",  
                                                  'reset=1&cid=' . $row['contact_civireport_id'] );                      
                    $rows[$name][$rowNum]['contact_civireport_display_name_link' ] = $url;
                    $rows[$name][$rowNum]['contact_civireport_display_name_hover'] =
                        ts("View contact summary");
                    $entryFound = true;
                }
            } // foreach ends
        }
    }
}
