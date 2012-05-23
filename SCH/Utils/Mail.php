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

class SCH_Utils_Mail {

    const
        SCH_FROM_EMAIL = "SCH Parent Portal <info.portal@sfschool.org>",
        SCH_BCC_EMAIL  = "SCH Parent Archival <archive.portal@sfschool.org>";

    static function sendMailToParents( $childID,
                                       $subjectTPL,
                                       $messageTPL,
                                       $templateVars,
                                       $additionalCC = null,
                                       $onlyEnrolled = true ) {

        // make sure that the child is currently enrolled
        require_once 'SCH/Utils/Query.php';
        if ( $onlyEnrolled &&
             ! SCH_Utils_Query::isCurrentlyEnrolled( $childID ) ) {
            return;
        }

        require_once 'SCH/Utils/Relationship.php';
        $parentInfo = array( );
        SCH_Utils_Relationship::getParents( $childID, $parentInfo, false );

        // make sure we unset the older parents
        for ( $count = 1 ; $count < 5; $count++ ) {
            $templateVars["parent_{$count}_Name"] = null;
        }

        $count = 1;
        $toDisplayName = $toEmail = $cc = null;
        foreach ( $parentInfo as $parent ) {
            $templateVars["parent_{$count}_Name"] = $parent['name'];
            if ( $parent['email'] ) {
                if ( ! $toEmail ) {
                    $toDisplayName = $parent['name'];
                    $toEmail       = $parent['email'];
                } else {
                    if ( ! empty( $cc ) ) {
                        $cc .= ", ";
                    }
                    $cc .= $parent['email'];
                }
            }
            $count++;
        }

        if ( $additionalCC ) {
            if ( ! empty( $cc ) ) {
                $cc .= ", ";
            }
            $cc .= $additionalCC;
        }

        // return if we dont have a toEmail
        if ( ! $toEmail ) {
            return;
        }

        require_once 'SCH/Utils/Query.php';
        list( $templateVars['childName'],
              $templateVars['childEmail'] ) = SCH_Utils_Query::getNameAndEmail( $childID );

        $template = CRM_Core_Smarty::singleton( );
        $template->assign( $templateVars );

        require_once 'CRM/Utils/Mail.php';
        require_once 'CRM/Utils/String.php';

        $params = array( 'from'    => self::SCH_FROM_EMAIL,
                         'toName'  => $toDisplayName,
                         'toEmail' => $toEmail,
                         'subject' => $template->fetch( $subjectTPL ),
                         'text'    => $template->fetch( $messageTPL ),
                         'cc'      => $cc,
                         'bcc'     => self::SCH_BCC_EMAIL );
        CRM_Utils_Mail::send( $params );
    }

}
