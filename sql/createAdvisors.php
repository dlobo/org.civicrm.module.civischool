<?php

function getContactID( $lastName, $firstName, $subType ) {
    $sql = "
SELECT     c.id, c.display_name
FROM       civicrm_contact c
INNER JOIN civicrm_value_school_information s ON c.id = s.entity_id
WHERE      c.first_name = %1
AND        c.last_name  = %2
AND        s.subtype IN ( $subType )   
";
    $params = array( 1 => array( trim( $firstName ), 'String' ),
                     2 => array( trim( $lastName ), 'String' ) );
    $dao = CRM_Core_DAO::executeQuery( $sql, $params );
    while ( $dao->fetch( ) ) {
        if ( $dao->N > 1 ) {
            print_r( $dao );
            echo "More than one contact ID for $lastName, $firstName\n";
            exit( );
        }
        return $dao->id;
    }

    echo "Could not find contact ID for $lastName, $firstName\n";
    exit( );
}

function createAdvisors( ) {
    $fdRead  = fopen( '/Users/lobo/SFS/PowerSchool/export/MS_Advisors.csv', 'r' );

    if ( ! $fdRead ) {
        echo "Could not read file\n";
        exit( );
    }


    $values = array( );
    while ( $fields = fgetcsv( $fdRead ) ) {
        list( $studentFirst, $studentLast, $advisorFirst, $advisorLast) = $fields;

        $studentID = getContactID( $fields[0], $fields[1], "'Student'" );
        $advisorID = getContactID( $fields[2], $fields[3], "'Parent', 'Staff'" );

        $values[] = "( $advisorID, $studentID, 10, 1 )";
    }

    echo "
INSERT INTO civicrm_relationship (contact_id_a, contact_id_b, relationship_type_id, is_active )
VALUES
" .
        implode( ",\n", $values ) .
        ";
";

    fclose( $fdRead  );
}

function initialize( ) {
    require_once '/Users/lobo/public_html/drupal6/sites/sfschool/civicrm.settings.php';

    require_once 'CRM/Core/Config.php';
    $config =& CRM_Core_Config::singleton( );

    require_once 'CRM/Core/Error.php';
}

function run( ) {
    initialize( );

    createAdvisors( );
}

run( );