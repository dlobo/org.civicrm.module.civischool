<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

/**
 * Helper class to build navigation links
 */
class SFS_Form_Apply_TabHeader {

    static function build( &$form ) {
        $tabs = $form->get( 'tabHeader' );
        if ( !$tabs || !CRM_Utils_Array::value('reset', $_GET) ) {
            $tabs =& self::process( $form );
            $form->set( 'tabHeader', $tabs );
        }
        $form->assign_by_ref( 'tabHeader', $tabs );
        $form->assign_by_ref( 'selectedTab', self::getCurrentTab($tabs) );
        return $tabs;
    }

    static function process( &$form ) {
        $tabs = array(
                      'Applicant'        => array( 'title'  => ts( 'Applicant Info' ),
                                                   'link'   => null,
                                                   'valid'  => false,
                                                   'active' => false,
                                                   'current' => false,
                                                   ),
                      'School'           => array( 'title' => ts( 'School Info' ),
                                                   'link'   => null,
                                                   'valid' => false,
                                                   'active' => false,
                                                   'current' => false,
                                                   ),
                      'Family'           => array( 'title' => ts( 'Family Info' ),
                                                   'link'   => null,
                                                   'valid' => false,
                                                   'active' => false,
                                                   'current' => false,
                                                   ),
                      'Children'         => array( 'title' => ts( 'Other Children' ),
                                                   'link'   => null,
                                                   'valid' => false,
                                                   'active' => false,
                                                   'current' => false,
                                                   ),
                      'Additional'       => array( 'title' => ts( 'Additional Info' ),
                                                   'link'   => null,
                                                   'valid' => false,
                                                   'active' => false,
                                                   'current' => false,
                                                   ),
                      );

        $fullName  = $form->getVar( '_name' );
        $className = CRM_Utils_String::getClassName( $fullName );

        if ( array_key_exists( $className, $tabs ) ) {
            $tabs[$className]['current'] = true;
        }

        $reset = CRM_Utils_Array::value('reset', $_GET) ? 'reset=1&' : '';
        $qfKey = empty($reset) ? "&qfKey={$form->controller->_key}" : '';

        foreach ( $tabs as $key => $value ) {
            $tabs[$key]['link'] = CRM_Utils_System::url( 'civicrm/sfschool/apply/' . strtolower($key),
                                                         "{$reset}snippet=4{$qfKey}" );
            $tabs[$key]['active'] = $tabs[$key]['valid'] = true;
        }

        return $tabs;
    }

    static function reset( &$form ) {
        $tabs =& self::process( $form );
        $form->set( 'tabHeader', $tabs );
    }

    static function getNextSubPage( $form, $currentSubPage = 'Applicant' ) {
        $tabs = self::build( $form );
        $flag = false;

        if ( is_array($tabs) ) {
            foreach ( $tabs as $subPage => $pageVal ) {
                if ( $flag && $pageVal['valid'] ) {
                    return $subPage;
                }
                if ( $subPage == $currentSubPage ) {
                    $flag = true;
                }
            }
        }
        return 'Applicant';
    }

    static function getSubPageInfo( $form, $subPage, $info = 'title' ) {
        $tabs = self::build( $form );

        if ( is_array($tabs[$subPage]) && array_key_exists($info, $tabs[$subPage]) ) {
            return $tabs[$subPage][$info];
        }
        return false;
    }

    static function getCurrentTab( $tabs ) {
        static $current = false;

        if ( $current ) {
            return $current;
        }
        
        if ( is_array($tabs) ) {
            foreach ( $tabs as $subPage => $pageVal ) {
                if ( $pageVal['current'] === true ) {
                    $current = $subPage;
                    break;
                }
            }
        }
        
        $current = $current ? $current : 'Applicant';
        return $current;
    }
}
