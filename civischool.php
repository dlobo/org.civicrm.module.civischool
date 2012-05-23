<?php

define( 'CIVICRM_PROFILE_PARENT_ID' , 3    );
define( 'CIVICRM_PROFILE_STUDENT_ID', 4    );
define( 'CIVICRM_CUSTOM_STUDENT_ID' , 2    );
define( 'CIVICRM_SCHOOL_YEAR'       , 2011 );

function civischool_civicrm_config( &$config ) {
    $template =& CRM_Core_Smarty::singleton( );

    $schoolRoot =
        dirname( __FILE__ ) . DIRECTORY_SEPARATOR .
        '..'                . DIRECTORY_SEPARATOR .
        '..'                . DIRECTORY_SEPARATOR;

    $schoolDir = $schoolRoot . 'templates';

    if ( is_array( $template->template_dir ) ) {
        array_unshift( $template->template_dir, $schoolDir );
    } else {
        $template->template_dir = array( $schoolDir, $template->template_dir );
    }

    // also fix php include path
    $include_path = $schoolRoot . PATH_SEPARATOR . get_include_path( );
    set_include_path( $include_path );

    // assign the profile ids
    $template->assign( 'parentProfileID' , CIVICRM_PROFILE_PARENT_ID  );
    $template->assign( 'studentProfileID', CIVICRM_PROFILE_STUDENT_ID );

    // set the timezone
    date_default_timezone_set('America/Los_Angeles');
}

function civischool_civicrm_pageRun( &$page ) {
    $name = $page->getVar( '_name' );
    if ( $name == 'CRM_Profile_Page_Dynamic' ) {
        $gid = $page->getVar( '_gid' );
        switch ( $gid ) {
        case CIVICRM_PROFILE_PARENT_ID:
            return _civischool_civicrm_pageRun_Profile_Page_Dynamic_3( $page, $gid );
        case CIVICRM_PROFILE_STUDENT_ID:
            return _civischool_civicrm_pageRun_Profile_Page_Dynamic_4( $page, $gid );
        }
    } else if ( $name == 'CRM_Contact_Page_View_CustomData' ) {
        if ( $page->getVar( '_groupId' ) != CIVICRM_CUSTOM_STUDENT_ID ) {
            return;
        }

        // get the details from smarty
        $smarty  =& CRM_Core_Smarty::singleton( );
        $details =& $smarty->get_template_vars( 'viewCustomData' );

        require_once 'SCH/Utils/ExtendedCare.php';
         SCH_Utils_ExtendedCare::sortDetails( $details );

         // CRM_Core_Error::debug( 'POST', $details );
        $smarty->assign_by_ref( 'viewCustomData', $details );
    }

}

function _civischool_civicrm_pageRun_Profile_Page_Dynamic_3( &$page, $gid ) {
    $parentID = $page->getVar( '_id' );
    $values = array( );

    require_once 'SCH/Utils/Query.php';
    SCH_Utils_Query::checkSubType( $parentID, array( 'Parent', 'Staff' ) );

    require_once 'SCH/Utils/Relationship.php';
    SCH_Utils_Relationship::getChildren( $parentID,
                                         $values,
                                         true );
    $childrenIDs = array_keys( $values );

    require_once 'SCH/Utils/Conference.php';
    SCH_Utils_Conference::getValues( $childrenIDs, $values, false, $parentID );

    require_once 'SCH/Utils/ReportCard.php';
    SCH_Utils_ReportCard::getValues( $childrenIDs, $values );

    require_once 'SCH/Utils/ExtendedCare.php';
    SCH_Utils_ExtendedCare::getValues( $childrenIDs, $values, $parentID );

    foreach ( $childrenIDs as $childID ) {
        $values[$childID]['familyURL'] =
            CRM_Utils_System::url( "civicrm/school/family/household",
                                   "reset=1&cid={$childID}&pid={$parentID}" );
    }

    $page->assign( 'childrenInfo', $values );

    $subType = SCH_Utils_Query::getSubType( $parentID );
    if ( $subType == 'Staff' ) {
        $ptcValues = array( );
        SCH_Utils_Conference::getPTCValuesOccupied( $parentID, $ptcValues );

        $page->assign( 'ptcValues', $ptcValues );
    }

}

function _civischool_civicrm_pageRun_Profile_Page_Dynamic_4( &$page, $gid ) {
    $childID = $page->getVar( '_id' );

    $term =  CRM_Utils_Request::retrieve( 'term', 'String',
                                          $page, false, null );

    require_once 'SCH/Utils/Query.php';
    SCH_Utils_Query::checkSubType( $childID, 'Student' );

    $values = array( );
    $values[$childID] =
        array('name'    => CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                                        $childID,
                                                        'display_name' ),
              'grade'   => SCH_Utils_Query::getGrade( $childID ),
              'parents' => array( ) );

    require_once 'SCH/Utils/Relationship.php';
    SCH_Utils_Relationship::getParents( $childID,
                                        $values[$childID]['parents'],
                                        false );

    require_once 'CRM/Core/Permission.php';

    require_once 'SCH/Utils/ReportCard.php';
    SCH_Utils_ReportCard::getValues( $childID, $values );

    require_once 'SCH/Utils/Conference.php';
    SCH_Utils_Conference::getValues( $childID, $values );

    require_once 'SCH/Utils/ExtendedCare.php';
    SCH_Utils_ExtendedCare::getValues( $childID, $values, null, $term );

    // use the first parent by default (since we are admin)
    $parentIDs = array_keys( $values[$childID]['parents'] );
    if ( empty( $parentIDs ) ) {
        CRM_Core_Error::fatal( );
    }

    // require_once 'SCH/Utils/ReportCard.php';
    // SCH_Utils_ReportCard::getValues( $childID, $values, CIVICRM_SCHOOL_YEAR );

    $values[$childID]['familyURL'] =
        CRM_Utils_System::url( "civicrm/school/family/household",
                               "reset=1&cid={$childID}&pid={$parentIDs[0]}" );

    $page->assign( 'childInfo', $values[$childID] );
}

function civischool_civicrm_buildForm( $formName, &$form ) {
    if ( $formName == 'CRM_Profile_Form_Edit' ) {
        $gid = $form->getVar( '_gid' );
        switch ( $gid ) {
        case 3:
            return _civischool_civicrm_buildForm_CRM_Profile_Form_Edit_3( $formName, $form, $gid );
        case 4:
            return _civischool_civicrm_buildForm_CRM_Profile_Form_Edit_4( $formName, $form, $gid );
        }
    } else if ( $formName == 'CRM_Contact_Form_Merge' &&
                empty( $_POST ) ) {
        // do this only for GET requests on the merge form
        $cid = CRM_Utils_Array::value( 'cid', $_GET );
        $oid = CRM_Utils_Array::value( 'oid', $_GET );
        if ( ! $cid || !$oid ) {
            return;
        }

        // check if $oid has a drupal user, if so set a warning
        $sql = "
SELECT id
FROM   civicrm_uf_match
WHERE  contact_id = %1
";
        $params = array( 1 => array( $oid, 'Integer' ) );
        $ufID = CRM_Core_DAO::singleValueQuery( $sql, $params );
        if ( $ufID ) {
            $session =& CRM_Core_Session::singleton( );
            $session->setStatus( ts( 'The contact that will be deleted has a user record (%1) associated with it',
                                     array( 1 => $ufID ) ) );
        }
    }
}

function civischool_civicrm_validate( $formName, &$fields, &$files, &$form ) {
    if ( $formName == 'CRM_Profile_Form_Edit' ) {
        $gid = $form->getVar( '_gid' );
        if ( $gid = 3 ) {
            require_once 'SCH/Utils/Conference.php';
            return SCH_Utils_Conference::validatePTCForm( $form, $fields );
        }
    }
    return null;
}

function civischool_civicrm_postProcess( $class, &$form ) {
    if ( is_a( $form, 'CRM_Profile_Form_Edit' ) ) {
        $gid = $form->getVar( '_gid' );
        switch ( $gid ) {
        case 3:
            return civischool_civicrm_postProcess_CRM_Profile_Form_Edit_3( $class, $form, $gid );
        case 4:
            return civischool_civicrm_postProcess_CRM_Profile_Form_Edit_4( $class, $form, $gid );
        }
    }
}

function _civischool_civicrm_buildForm_CRM_Profile_Form_Edit_3( $formName, &$form, $gid ) {
    $staffID   = $form->getVar( '_id' );

    // freeze first name, last name and grade
    $elementList = array( 'first_name', 'last_name', 'email-Primary', 'phone-Primary' );
    $form->freeze( $elementList );

    require_once 'SCH/Utils/Conference.php';
    SCH_Utils_Conference::buildPTCForm( $form, $staffID );
}

function _civischool_civicrm_buildForm_CRM_Profile_Form_Edit_4( $formName, &$form, $gid ) {
    // get the custom field if for grade
    require_once 'CRM/Core/BAO/CustomField.php';
    $gradeFieldID = CRM_Core_BAO_CustomField::getCustomFieldID('Grade', 'School Information');

    // freeze first name, last name and grade
    $elementList = array( 'first_name', 'last_name', "custom_{$gradeFieldID}" );
    $form->freeze( $elementList );

    $childID   = $form->getVar( '_id' );

    require_once 'SCH/Utils/Conference.php';
    SCH_Utils_Conference::buildForm( $form, $childID );

    $term =  CRM_Utils_Request::retrieve( 'term', 'String',
                                          $form, false, null );

    require_once 'SCH/Utils/ExtendedCare.php';
    SCH_Utils_ExtendedCare::buildForm( $form, $childID, $term );
}

function civischool_civicrm_postProcess_CRM_Profile_Form_Edit_3( $class, &$form, $gid ) {
    $staffID   = $form->getVar( '_id' );

    require_once 'SCH/Utils/Conference.php';
    SCH_Utils_Conference::postProcessPTC( $form, $staffID );
}


function civischool_civicrm_postProcess_CRM_Profile_Form_Edit_4( $class, &$form, $gid ) {
    require_once 'SCH/Utils/Conference.php';
    SCH_Utils_Conference::postProcess( $class, $form, $gid );

    $term =  CRM_Utils_Request::retrieve( 'term', 'String',
                                          $form, false, null );

    require_once 'SCH/Utils/ExtendedCare.php';
    SCH_Utils_ExtendedCare::postProcess( $class, $form, $gid, $term );
}

function civischool_civicrm_tabs( &$tabs, $contactID ) {
    require_once 'SCH/Utils/Query.php';
    $subType = SCH_Utils_Query::getSubType( $contactID );

    // if subType is not student then hide the extended care tab
    if ( $subType == 'Student' ) {
        return;
    }

    foreach ( $tabs as $tabID => $tabValue ) {
        if ( $tabValue['title'] == 'Extended Care' ||
             $tabValue['title'] == 'Extended Care Signout' ) {
            unset( $tabs[$tabID] );
        }
    }
}

function civischool_civicrm_xmlMenu( &$files ) {
    $files[] =
        dirname( __FILE__ ) . DIRECTORY_SEPARATOR .
        '..'                . DIRECTORY_SEPARATOR .
        '..'                . DIRECTORY_SEPARATOR .
        'SCH'               . DIRECTORY_SEPARATOR .
        'xml'               . DIRECTORY_SEPARATOR .
        'Menu'              . DIRECTORY_SEPARATOR .
        'school.xml';
}

function civischool_civicrm_navigationMenu( &$params ) {
    //  Get the maximum key of $params
    $maxKey = ( max( array_keys($params) ) );

    $params[$maxKey+1] = array (
                       'attributes' => array (
                                              'label'      => 'School',
                                              'name'       => 'School',
                                              'url'        => null,
                                              'permission' => 'access CiviCRM',
                                              'operator'   => null,
                                              'separator'  => null,
                                              'parentID'   => null,
                                              'navID'      => $maxKey+1,
                                              'active'     => 1
                                              ),
                       'child' =>  array (
                                          '1' => array (
                                                        'attributes' => array (
                                                                               'label'      => 'Student Search',
                                                                               'name'       => 'Student Search',
                                                                               'url'        => CRM_Utils_System::url( 'civicrm/profile',
                                                                                                                      'reset=1&gid=' .
                                                                                                                      CIVICRM_PROFILE_STUDENT_ID, true,
                                                                                                                      null, false ),
                                                                               'permission' => 'access CiviCRM',
                                                                               'operator'   => null,
                                                                               'separator'  => 1,
                                                                               'parentID'   => $maxKey+1,
                                                                               'navID'      => 1,
                                                                               'active'     => 1
                                                                                ),
                                                        'child' => null
                                                        ),
                                          '2' => array (
                                                        'attributes' => array (
                                                                               'label'      => 'Extended Care Summary',
                                                                               'name'       => 'Extended Care Summary',
                                                                               'url'        => CRM_Utils_System::url( 'civicrm/school/extendedCareSummary',
                                                                                                                      'reset=1', true,
                                                                                                                      null, false ),
                                                                               'permission' => 'access CiviCRM',
                                                                               'operator'   => null,
                                                                               'separator'  => 1,
                                                                               'parentID'   => $maxKey+1,
                                                                               'navID'      => 1,
                                                                               'active'     => 1
                                                                                ),
                                                        'child' => null
                                                        ),
                                          '3' => array (
                                                        'attributes' => array (
                                                                               'label'      => 'Extended Care Class Listings',
                                                                               'name'       => 'Extended Care Class Listings',
                                                                               'url'        => CRM_Utils_System::url( 'civicrm/school/extended/class',
                                                                                                                      'reset=1', true,
                                                                                                                      null, false ),
                                                                               'permission' => 'access CiviCRM',
                                                                               'operator'   => null,
                                                                               'separator'  => 1,
                                                                               'parentID'   => $maxKey+1,
                                                                               'navID'      => 1,
                                                                               'active'     => 1
                                                                                ),
                                                        'child' => null
                                                        ),
                                          '4' => array (
                                                        'attributes' => array (
                                                                               'label'      => 'Extended Care Class Detail',
                                                                               'name'       => 'Extended Care Class Detail',
                                                                               'url'        => CRM_Utils_System::url( 'civicrm/report/school/extended/roster',
                                                                                                                      'reset=1', true,
                                                                                                                      null, false ),
                                                                               'permission' => 'access CiviCRM',
                                                                               'operator'   => null,
                                                                               'separator'  => 1,
                                                                               'parentID'   => $maxKey+1,
                                                                               'navID'      => 1,
                                                                               'active'     => 1
                                                                                ),
                                                        'child' => null
                                                        ),
                                          '5' => array (
                                                        'attributes' => array (
                                                                               'label'      => 'Reports',
                                                                               'name'       => 'Reports',
                                                                               'url'        => CRM_Utils_System::url( 'civicrm/report/list',
                                                                                                                      'reset=1', true,
                                                                                                                      null, false ),
                                                                               'permission' => 'access CiviCRM',
                                                                               'operator'   => null,
                                                                               'separator'  => 1,
                                                                               'parentID'   => $maxKey+1,
                                                                               'navID'      => 1,
                                                                               'active'     => 1
                                                                                ),
                                                        'child' => null
                                                        ),
                                          '6' => array (
                                                        'attributes' => array (
                                                                               'label'      => 'Afternoon Signout',
                                                                               'name'       => 'Afternoon Signout',
                                                                               'url'        => CRM_Utils_System::url( 'civicrm/school/signout',
                                                                                                                      'reset=1', true,
                                                                                                                      null, false ),
                                                                               'permission' => 'access CiviCRM',
                                                                               'operator'   => null,
                                                                               'separator'  => 1,
                                                                               'parentID'   => $maxKey+1,
                                                                               'navID'      => 1,
                                                                               'active'     => 1
                                                                                ),
                                                        'child' => null
                                                        ),
                                          '7' => array (
                                                        'attributes' => array (
                                                                               'label'      => 'Afternoon SignIn',
                                                                               'name'       => 'Afternoon SignIn',
                                                                               'url'        => CRM_Utils_System::url( 'civicrm/school/signin',
                                                                                                                      'reset=1', true,
                                                                                                                      null, false ),
                                                                               'permission' => 'access CiviCRM',
                                                                               'operator'   => null,
                                                                               'separator'  => 1,
                                                                               'parentID'   => $maxKey+1,
                                                                               'navID'      => 1,
                                                                               'active'     => 1
                                                                                ),
                                                        'child' => null
                                                        ),
                                          '8' => array (
                                                        'attributes' => array (
                                                                               'label'      => 'Morning SignIn',
                                                                               'name'       => 'Morning SignIn',
                                                                               'url'        => CRM_Utils_System::url( 'civicrm/school/morning',
                                                                                                                      'reset=1', true,
                                                                                                                      null, false ),
                                                                               'permission' => 'access CiviCRM',
                                                                               'operator'   => null,
                                                                               'separator'  => 1,
                                                                               'parentID'   => $maxKey+1,
                                                                               'navID'      => 1,
                                                                               'active'     => 1
                                                                                ),
                                                        'child' => null
                                                        ),
                                          '9' => array (
                                                        'attributes' => array (
                                                                               'label'      => 'Setup Parent teacher conference',
                                                                               'name'       => 'Setup Parent teacher conference',
                                                                               'url'        => CRM_Utils_System::url( 'civicrm/school/conference',
                                                                                                                      'reset=1', true,
                                                                                                                      null, false ),
                                                                               'permission' => 'access CiviCRM',
                                                                               'operator'   => null,
                                                                               'separator'  => 1,
                                                                               'parentID'   => $maxKey+1,
                                                                               'navID'      => 1,
                                                                               'active'     => 1
                                                                                ),
                                                        'child' => null
                                                        ),
                                          '10' => array (
                                                        'attributes' => array (
                                                                               'label'      => 'Batch Consent Form',
                                                                               'name'       => 'Batch Consent Form',
                                                                               'url'        => CRM_Utils_System::url( 'civicrm/school/batchConsent',
                                                                                                                      'reset=1', true,
                                                                                                                      null, false ),
                                                                               'permission' => 'access CiviCRM',
                                                                               'operator'   => null,
                                                                               'separator'  => 1,
                                                                               'parentID'   => $maxKey+1,
                                                                               'navID'      => 1,
                                                                               'active'     => 1
                                                                                ),
                                                        'child' => null
                                                         ),
                                           )
                                 );
}
