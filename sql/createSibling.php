<?php

function initialize( ) {
    require_once '/Users/lobo/public_html/drupal6/sites/sfschool/civicrm.settings.php';

    require_once 'CRM/Core/Config.php';
    $config =& CRM_Core_Config::singleton( );

    require_once 'CRM/Core/Error.php';
}

function getRelationshipType( $name ) {
    $siblingRelationshipType = null;
    $query = "
SELECT id
FROM   civicrm_relationship_type
WHERE  name_a_b = %1
";
    $params = array( 1 => array( $name, 'String' ) );
    $type = CRM_Core_DAO::singleValueQuery( $query, $params );
    if ( ! $type ) {
        CRM_Core_Error::fatal( );
    }

    return $type;
}

function createSiblingRelationship( $contactID_a,
                                    $contactID_b,
                                    $relationshipTypeID ) {
    $query = "
SELECT id
FROM   civicrm_relationship
WHERE  relationship_type_id = %1
AND    ( ( contact_id_a = %2 AND contact_id_b = %3 ) 
OR       ( contact_id_a = %3 AND contact_id_b = %2 ) )
";
    $params = array( 1 => array( $relationshipTypeID, 'Integer' ),
                     2 => array( $contactID_a       , 'Integer' ),
                     3 => array( $contactID_b       , 'Integer' ) );
    $id = CRM_Core_DAO::singleValueQuery( $query, $params );
    if ( $id ) {
        return $id;
    }

    $dao = new CRM_Contact_DAO_Relationship( );
    $dao->relationship_type_id = $relationshipTypeID;
    $dao->contact_id_a = $contactID_a;
    $dao->contact_id_b = $contactID_b;
    $dao->is_active    = 1;
    $dao->save( );
    
    return $dao->id;
}

function createSiblingRelationships( ) {
    $parentRelationshipType  = getRelationshipType( 'Child of' );
    $siblingRelationshipType = getRelationshipType( 'Sibling of' );

    $query = "
SELECT     p.id as parent_id, p.display_name as parent_name, c.id as child_id, c.display_name as child_name
FROM       civicrm_contact p
INNER JOIN civicrm_relationship r ON r.contact_id_b = p.id
INNER JOIN civicrm_contact c      ON r.contact_id_a = c.id
WHERE      r.relationship_type_id = 1
AND        r.is_active    = 1
ORDER BY   p.id, c.id
";

    $dao = CRM_Core_DAO::executeQuery( $query );

    $parents = $name = array( );
    while ( $dao->fetch( ) ) {
        if ( ! array_key_exists( $dao->parent_id, $parents ) ) {
            $parents[$dao->parent_id] = array( 'name' => $dao->parent_name,
                                               'children' => array( ) );
        }
        $parents[$dao->parent_id]['children'][$dao->child_id] = $dao->child_name;
        $name[$dao->child_id] = $dao->child_name;
    }

    require_once 'CRM/Contact/DAO/Relationship.php';

    $alreadyCreated = array( );
    foreach ( $parents as $id => $info ) {
        if ( count( $parents[$id]['children'] ) < 1 ) {
            continue;
        }

        $childIDs = array_keys( $parents[$id]['children'] );
        foreach ( $childIDs as $lowerChildID ) {
            foreach ( $childIDs as $higherChildID ) {
                if ( $lowerChildID == $higherChildID ) {
                    continue;
                }

                if ( array_key_exists( "{$lowerChildID}_{$higherChildID}", $alreadyCreated ) ) {
                    continue;
                }

                $alreadyCreated["{$lowerChildID}_{$higherChildID}"] = 1;
                $alreadyCreated["{$higherChildID}_{$lowerChildID}"] = 1;
                echo "Creating a sibling relationship between {$name[$lowerChildID]} and {$name[$higherChildID]}\n";
                createSiblingRelationship( $lowerChildID,
                                           $higherChildID,
                                           $siblingRelationshipType );
            }
        }
    }

}

function run( ) {
    initialize( );

    createSiblingRelationships( );
}

run( );