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

class SCH_Utils_EConsent {
    static function sendReminderEmail( ) {
        require_once 'SCH/Utils/Mail.php';

        $sql = "
SELECT     c.id as student_id, p.id as parent_id,
           p.display_name as parent_name
FROM       civicrm_contact p
INNER JOIN civicrm_relationship r ON r.contact_id_b = p.id
INNER JOIN civicrm_contact c ON r.contact_id_a = c.id
INNER JOIN civicrm_value_school_information s ON s.entity_id = c.id
LEFT  JOIN civicrm_value_parent_relationship_data rd ON rd.entity_id = r.id
WHERE      r.relationship_type_id = 1
AND        r.is_active    = 1
AND        r.is_permission_b_a = 1
AND        s.is_currently_enrolled = 1
AND        rd.econsent_signed IS NULL
ORDER BY   c.id
";

        $dao = CRM_Core_DAO::executeQuery( $sql );

        $currentEntityID = null;
        $templateVars    = array( );
        $parentNames     = array( );
        while ( $dao->fetch( ) ) {
            if ( $dao->student_id != $currentEntityID &&
                 $currentEntityID != null ) {

                $templateVars['parentNames'] = $parentNames;

                // now send a message to the parents about what they did
                SCH_Utils_Mail::sendMailToParents( $currentEntityID,
                                                   'SCH/Mail/EConsent/EConsentSubject.tpl',
                                                   'SCH/Mail/EConsent/EConsentMessage.tpl',
                                                   $templateVars );
                $parentNames = array( );
            }

            $parentNames[] = $dao->parent_name;
            $currentEntityID = $dao->student_id;
        }

        if ( $currentEntityID ) {
            $templateVars['parentNames'] = $parentNames;

            // now send a message to the parents about what they did
            SCH_Utils_Mail::sendMailToParents( $currentEntityID,
                                               'SCH/Mail/EConsent/EConsentSubject.tpl',
                                               'SCH/Mail/EConsent/EConsentMessage.tpl',
                                               $templateVars );
        }

    }

    static function sendOnlineFormEmail( ) {
        require_once 'SCH/Utils/Mail.php';

        $sql = "
SELECT     c.id as student_id, p.id as parent_id,
           p.display_name as parent_name
FROM       civicrm_contact p
INNER JOIN civicrm_relationship r ON r.contact_id_b = p.id
INNER JOIN civicrm_contact c ON r.contact_id_a = c.id
INNER JOIN civicrm_value_school_information s ON s.entity_id = c.id
LEFT  JOIN civicrm_value_parent_relationship_data rd ON rd.entity_id = r.id
WHERE      r.relationship_type_id = 1
AND        r.is_active    = 1
AND        r.is_permission_b_a = 1
AND        s.is_currently_enrolled = 1
AND        s.updated_by IS NULL
AND        rd.econsent_signed = 1
ORDER BY   c.id"
;

        $dao = CRM_Core_DAO::executeQuery( $sql );

        $currentEntityID = null;
        $templateVars    = array( );
        $parentNames     = array( );
        while ( $dao->fetch( ) ) {
            if ( $dao->student_id != $currentEntityID &&
                 $currentEntityID != null ) {

                $templateVars['parentNames'] = $parentNames;

                // now send a message to the parents about what they did
                SCH_Utils_Mail::sendMailToParents( $currentEntityID,
                                                   'SCH/Mail/EConsent/OnlineFormSubject.tpl',
                                                   'SCH/Mail/EConsent/OnlineFormMessage.tpl',
                                                   $templateVars );
                $parentNames = array( );
            }

            $parentNames[] = $dao->parent_name;
            $currentEntityID = $dao->student_id;
        }

        if ( $currentEntityID ) {
            $templateVars['parentNames'] = $parentNames;

            // now send a message to the parents about what they did
            SCH_Utils_Mail::sendMailToParents( $currentEntityID,
                                               'SCH/Mail/EConsent/OnlineFormSubject.tpl',
                                               'SCH/Mail/EConsent/OnlineFormMessage.tpl',
                                               $templateVars );
        }
    }

    static function genOnlineFormPDF( ) {
        $sql = "
SELECT     c.id as student_id,
           c.display_name as student_name,
           c.birth_date as student_birth,
           s.grade as student_grade,
           s.grade_sis as student_grade_sis
FROM       civicrm_contact c
INNER JOIN civicrm_value_school_information s ON s.entity_id = c.id
AND        s.subtype = 'Student'
AND        s.is_currently_enrolled = 1
ORDER BY   s.grade, c.last_name
";

        require_once 'CRM/Utils/PDF/Utils.php';

        $dao      =  CRM_Core_DAO::executeQuery( $sql );
        $template =& CRM_Core_Smarty::singleton( );

        $content = array( );

        require_once 'SCH/Page/Family.php';
        require_once 'CRM/Utils/String.php';
        $config =& CRM_Core_Config::singleton( );

        $currentGrade = null;
        while ( $dao->fetch( ) ) {
            if ( $dao->student_grade != $currentGrade ) {
                if ( $currentGrade != null ) {
                    CRM_Core_Error::debug( "Storing PDF for $currentGrade" );
                    $string = CRM_Utils_PDF_Utils::domlib( $content, null, true );
                    $grade = CRM_Utils_String::munge( $currentGrade );
                    $fileName = "Grade_{$grade}_Info.pdf";
                    file_put_contents( $config->configAndLogDir . $fileName, $string );
                }
                $content = array( );
                $currentGrade = $dao->student_grade;
            }

            $page = new SCH_Page_Family( );
            $page->commonRun( $dao->student_id );

            $template->assign( 'tplFile', 'SCH/Page/FamilyPDF.tpl' );

            $template->assign( 'childName' , $dao->student_name  );
            $template->assign( 'childGrade', $dao->student_grade );
            $template->assign( 'childBirth',
                               CRM_Utils_Date::customFormat( $dao->student_birth,
                                                             '%b %E%f, %Y' ) );
            $message = array( );
            $message['html'] = $template->fetch( 'CRM/common/print.tpl' );
            $content[] = $message;
        }

        if ( ! empty( $content ) ) {
            $string = CRM_Utils_PDF_Utils::domlib( $content, null, true );
            $grade = CRM_Utils_String::munge( $currentGrade );
            $fileName = "Grade_{$grade}_Info.pdf";
            file_put_contents( $config->configAndLogDir . $fileName, $string );
        }
    }

    static function genPowerSchoolExport( ) {
        $sql = "
SELECT     c.id as student_id,
           c.display_name as student_name,
           c.birth_date as student_birth,
           s.grade as student_grade,
           s.grade_sis as student_grade_sis
FROM       civicrm_contact c
INNER JOIN civicrm_value_school_information s ON s.entity_id = c.id
AND        s.subtype = 'Student'
AND        s.is_currently_enrolled = 1
AND        s.grade_sis = 2
AND        c.id = 169
ORDER BY   s.grade_sis, c.last_name
";

        $dao      =  CRM_Core_DAO::executeQuery( $sql );
        $template =& CRM_Core_Smarty::singleton( );

        $content = array( );

        require_once 'SCH/Page/Family.php';
        require_once 'CRM/Utils/String.php';
        $config =& CRM_Core_Config::singleton( );

        $currentGrade = null;
        while ( $dao->fetch( ) ) {
            $page = new SCH_Page_Family( );
            $page->commonRun( $dao->student_id );

            CRM_Core_Error::debug( $page->_values );
        }
    }

    static function checkAppCompleted( ) {
        $sql = "
SELECT     c.id as student_id, c.display_name as student_name, s.grade as student_grade
FROM       civicrm_contact c
INNER JOIN civicrm_value_school_information s ON s.entity_id = c.id
WHERE      s.is_currently_enrolled = 1
AND        s.subtype = 'Student'
ORDER BY   s.grade_sis, c.id
";

        $dao = CRM_Core_DAO::executeQuery( $sql );

        require_once 'SCH/Form/Family.php';
        $form =& new SCH_Form_Family( );
        while ( $dao->fetch( ) ) {
            $results = $form->isAppCompleted( $dao->student_id );
            if ( ! $results['is_completed'] ) {
                $sections = array( );
                foreach ( $results as $section => $done ) {
                    if ( $section == 'is_completed' || $section == 'diversity' ) {
                        continue;
                    }
                    if ( ! $done ) {
                        $sections[] = $section;
                    }
                }
                if ( ! empty( $sections ) ) {
                    echo "{$dao->student_name} (Grade: {$dao->student_grade}): " . implode( ', ', $sections ) . "<br/>";
                }
            }
        }
    }

    static function checkEmergencyContacts( ) {
        $sql = "
SELECT     c.id as student_id, c.display_name as student_name, s.grade as student_grade,
           p.id as parent_id, p.display_name as parent_name
FROM       civicrm_contact p
INNER JOIN civicrm_relationship r ON r.contact_id_b = p.id
INNER JOIN civicrm_contact c ON r.contact_id_a = c.id
INNER JOIN civicrm_value_school_information s ON s.entity_id = c.id
LEFT  JOIN civicrm_value_parent_relationship_data rd ON rd.entity_id = r.id
WHERE      r.relationship_type_id = 1
AND        r.is_active    = 1
AND        r.is_permission_b_a = 1
AND        s.is_currently_enrolled = 1
ORDER BY   grade_sis, c.id
";

        $dao = CRM_Core_DAO::executeQuery( $sql );

        $currentEntityID = null;
        $currentName     = null;
        $parentNames     = array( );
        $dao = CRM_Core_DAO::executeQuery( $sql );
        require_once 'api/v2/Relationship.php';

        while ( $dao->fetch( ) ) {
            if ( $dao->student_id != $currentEntityID &&
                 $currentEntityID != null ) {

                // we have parent names, lets get emergency contact names
                // and check
                $params  = array( 'contact_id' => $currentEntityID );
                $relationships = civicrm_get_relationships( $params, null, array('Emergency Contact Of' ) );
                if ( $relationships['is_error'] ) {
                    echo "$currentName has no emergency contacts<br/>";
                } else {
                    $rNames = array( );
                    foreach ( $relationships['result'] as $rid => $rValue ) {
                        $rNames[] = $rValue['display_name'];
                    }
                    $matches = array_intersect( $parentNames, $rNames );
                    if ( ! empty( $matches ) ) {
                        echo
                            $currentName .
                            " : " .
                            implode( ", ", $parentNames ) .
                            " : " .
                            implode( ", ", $matches ) .
                            "<br/>";
                    }
                }

                $parentNames = array( );
            }

            $parentNames[] = $dao->parent_name;
            $currentEntityID = $dao->student_id;
            $currentName     = "{$dao->student_name} (Grade: {$dao->student_grade})";
        }

        if ( $currentEntityID ) {
        }

    }

}
