<?php

function getContactID( $grade, $last, $first ) {
    $grade = trim( $grade );
    $first = trim( $first );
    $last  = trim( $last  );

        $query = "
SELECT     c.id
FROM       civicrm_contact c
INNER JOIN civicrm_value_school_information s ON c.id = s.entity_id
WHERE      s.grade = %1
AND        c.first_name = %2
AND        c.last_name  = %3
";
        $params = array( 1 => array( $grade, 'Integer' ),
                         2 => array( $first, 'String'  ),
                         3 => array( $last , 'String'  ),
                         );
        return CRM_Core_DAO::singleValueQuery( $query, $params );
}

function setUnlimitedStatus( $contactID ) {
    return "UPDATE civicrm_value_school_information SET extended_care_status_2011 = 'Staff' WHERE  entity_id = $contactID;\n";
}

function readPaymentFile( $readFile, $writeFile, $globalID ) {
    $fdRead  = fopen( $readFile, "r" );
    if ( ! $fdRead ) {
        echo "Could not read input file: $readFile\n";
        exit( );
    }
    
    $fdWrite  = fopen( $writeFile, "w" );
    if ( ! $fdWrite ) {
        echo "Could not write output file: $writeFile\n";
        exit( );
    }

    // read first line
    $header = fgetcsv( $fdRead );

    $count  = 0;
    while ( $fields = fgetcsv( $fdRead ) ) {

        // get contact id
        $contactID = getContactID( $fields[0], $fields[1], $fields[2] );
        if ( ! $contactID ) {
            echo "Could not retrieve valid Contact ID for: " . implode( ", ", $fields ) . "\n";
            continue;
        }

        $output = array( $globalID++,
                         $contactID,
                         'Credit',
                         'Free Block Credit for 2011-2012',
                         '20110901', 30,
                         'Free Blocks' );
        fputcsv ( $fdWrite, $output );
    }

    fclose( $fdWrite );
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

    readPaymentFile( '/Users/lobo/SFS/SFS/FreeBlocks2011.csv',
                     '/Users/lobo/SFS/SFS/FreeBlocks2011.sql',
                     1645 );
}

run( );


