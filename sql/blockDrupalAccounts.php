<?php

function initialize( ) {
    require_once '/Users/lobo/public_html/drupal6/sites/sfschool/civicrm.settings.php';

    require_once 'CRM/Core/Config.php';
    $config =& CRM_Core_Config::singleton( );

    require_once 'CRM/Core/Error.php';
}

function blockDrupalAccounts( ) {
    // first find all the parent ids and their children ids
    // and current status
    $sql = "
SELECT     p.id as parent_id, p.display_name as parent_name,
           uf.uf_id as user_id,
           c.id as child_id , c.display_name as child_name ,
           s.is_currently_enrolled 
FROM       civicrm_contact p
INNER JOIN civicrm_relationship r  ON r.contact_id_b = p.id
INNER JOIN civicrm_contact      c  ON r.contact_id_a = c.id
INNER JOIN civicrm_uf_match     uf ON r.contact_id_b = uf.contact_id
INNER JOIN civicrm_value_school_information s ON c.id = s.entity_id
WHERE      r.relationship_type_id = 1
AND        r.is_active    = 1
AND        r.contact_id_b = p.id
";

    $dao = CRM_Core_DAO::executeQuery( $sql );
    $parentInfo = array( );
    while ( $dao->fetch( ) ) {
        if ( ! array_key_exists( $dao->parent_id, $parentInfo ) ) {
            $parentInfo[$dao->parent_id] = array( 'id'       => $dao->parent_id,
                                                  'name'     => $dao->parent_name,
                                                  'user_id'  => $dao->user_id,
                                                  'children' => array( ) );
        }
        $parentInfo[$dao->parent_id]['children'][] = 
            array( 'id'   => $dao->child_id,
                   'name' => $dao->child_name,
                   'is_currently_enrolled' => $dao->is_currently_enrolled ? 1 : 0 );
    }

    $config = CRM_Core_Config::singleton( );
    $db_cms = DB::connect($config->userFrameworkDSN);
    if ( DB::isError( $db_cms ) ) { 
        die( "Cannot connect to UF db via $dsn, " . $db_cms->getMessage( ) ); 
    }
    
    // now we have all the parent / child info
    // keep all the parents who have no enrolled children
    foreach ( $parentInfo as $parentID => $parent ) {
        $keepAccount = false;
        foreach ( $parent['children'] as $child ) {
            if ( $child['is_currently_enrolled'] ) {
                $keepAccount = true;
                break;
            }
        }
        if ( ! $keepAccount ) {
            $sql = "
UPDATE {$config->userFrameworkUsersTableName}
SET    status = 0
WHERE  uid = {$parent['user_id']}
";
            $db_cms->query( $sql );
            echo "Blocking account for {$parent['name']}, {$parent['user_id']}\n";
        }
    }

}

function run( ) {
    initialize( );

    blockDrupalAccounts( );
}

run( );