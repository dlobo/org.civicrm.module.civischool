<?php

function &fixSFSFile( &$northSouthInfo ) {
    
    $fdRead  = fopen( 'SFS_0910.csv', 'r' );
    $fdWrite = fopen( 'SFS_0910_FIX.csv', 'w' );

    if ( ! $fdRead ) {
        echo "Could not read file\n";
        exit( );
    }
    
    // read first line
    $fields = fgetcsv( $fdRead );

    $studentInfo = array( );

    array_splice( $fields,  4, 0, array( 'Contact SubType' ) );
    array_splice( $fields,  7, 0, array( 'Contact SubType' ) );
    array_splice( $fields, 10, 0, array( 'Contact SubType' ) );
    $fields[] = 'Grade SIS';

    fputcsv( $fdWrite, $fields );
    
    $fixFields = array( 4, 5, 6, 7 );
    
    $validSIDs = array( 100030, 100033, 201820, 201513, 100019, 201610, 201421, 201121, 201811, 201812, 201722 );

    while ( $fields = fgetcsv( $fdRead ) ) {
        // print_r( $fields );
        
        if ( $fields[2] <= 0 ) {
            if ( ! array_key_exists( $fields[3], $northSouthInfo ) ) {
                echo 'FATAL: ' . implode( ',', $fields ) . "\n";
                exit( );
            }

            // save the SIS grade for sorting purposes
            $fields[]  = $fields[2];
            $fields[2] = $northSouthInfo[$fields[3]];
        } else {
            $fields[]  = $fields[2];
        }
        
        foreach ( $fixFields as $fix ) {
            if ( empty( $fields[$fix] ) ) {
                continue;
            }

            $name = splitName( $fields[$fix] );
            
            $fields[$fix] = ( $fix == 4 || $fix == 6 ) ? $name[1] : $name[0];
        }

        // also add Student and Parent sub type fields
        array_splice( $fields, 4, 0, array( 'Student' ) );
        if ( ! empty( $fields[5] ) ) {
            array_splice( $fields, 7, 0, array( 'Parent' ) );
        } else {
            array_splice( $fields, 7, 0, array( '' ) );
        }
        
        if ( ! empty( $fields[8] ) ) {
            array_splice( $fields, 10, 0, array( 'Parent' ) );
        } else {
            array_splice( $fields, 10, 0, array( '' ) );
        }
        
        /*******
        // scramble the fields
        if ( in_array( $fields[3], $validSIDs ) === false &&
             $fields[2] != 6 ) {
            scrambleFields( $fields );
        }
        *********/

        $studentInfo[$fields[3]] = array( 'first_name' => $fields[0],
                                          'last_name'  => $fields[1],
                                          'grade'      => $fields[2] );

        $fields[3] = 'Student-' . $fields[3];

        fputcsv( $fdWrite, $fields );
        // print_r( $fields );
        // exit( );
    }

    fclose( $fdRead  );
    fclose( $fdWrite );

    return $studentInfo;
}

function scrambleFields( &$fields ) {
    static $skipFields = array( 2, 3, 4, 7, 10 );
    for ( $i = 0; $i <= 13; $i++ ) {
        if ( in_array( $i, $skipFields ) !== false || empty( $fields[$i] ) ) {
            continue;
        } else if ( $i < 12 ) {
            $fields[$i] = substr( md5( $fields[$i] ), 0, 16 );
        } else {
            // email address
            $fields[$i] = substr( md5( $fields[$i] ), 0, 16 ) . '@example.com';
        }
    }
}

function &readNorthSouthFile( ) {
    $fdRead  = fopen( '0910_northsouth.csv', 'r' );

    if ( ! $fdRead ) {
        echo "Could not read file\n";
        exit( );
    }
    
    // read and ignore first line
    $fields = fgetcsv( $fdRead );

    $northSouthInfo = array( );

    while ( $fields = fgetcsv( $fdRead ) ) {
        $grade = '';
        if ( $fields[0] == -2 ) {
            $grade = 'PK3 ';
        } else if ( $fields[0] == -1 ) {
            $grade = 'PK4 ';
        } else if ( $fields[0] == 0 ) {
            $grade = 'K ';
        }

        $grade .= $fields[2];
        $northSouthInfo[$fields[1]] = $grade;
    }

    fclose( $fdRead );
    return $northSouthInfo;
}

function &fixStaffFile( ) {
    $fdRead  = fopen( 'faculty.csv', 'r' );
    $fdWrite = fopen( 'faculty_FIX.csv', 'w' );

    if ( ! $fdRead ) {
        echo "Could not read file\n";
        exit( );
    }
    
    // read and ignore first line
    $fields = fgetcsv( $fdRead );

    $staffInfo = array( );
    while ( $fields = fgetcsv( $fdRead ) ) {
        $fields[2] = "Staff-{$fields[2]}";
        $fields[4] = "Staff";

        $staffInfo["{$fields[0]} {$fields[1]}"] =
            array( 'first_name' => $fields[0],
                   'last_name'  => $fields[1],
                   'id'         => $fields[2] );

        fputcsv( $fdWrite, $fields );
    }

    fclose( $fdRead );
    fclose( $fdWrite );

    return $staffInfo;
}

function fixAdvisorFile( &$studentInfo, &$staffInfo ) {
    $fdRead  = fopen( '0910_Advisors.csv', 'r' );
    $fdWrite = fopen( '0910_Advisors_FIX.csv', 'w' );

    if ( ! $fdRead ) {
        echo "Could not read file\n";
        exit( );
    }
    
    // read first line
    $fields = fgetcsv( $fdRead );

    while ( $fields = fgetcsv( $fdRead ) ) {
        if ( ! array_key_exists( $fields[1], $studentInfo ) ) {
            echo "FATAL STUDENT ID DOES NOT EXISTS: " . implode( ',', $fields ) . "\n";
            exit( );
        }

        if ( $studentInfo[$fields[1]]['grade'] != $fields[0] ) {
            echo "FATAL STUDENT ID GRADE DOES NOT MATCH: " . implode( ',', $fields ) . "\n";
            exit( );
        }

        if ( strpos( $fields[2], ' / ' ) !== false ) {
            $advisors = splitName( $fields[2], '/' );
            $advisor_1 = splitName( $advisors[0] );
            $advisor_2 = splitName( $advisors[1] );
        } else {
            $advisor_1 = splitName( $fields[2] );
            $advisor_2 = array( '', '' );
        }

        $advisor_1_id = $advisor_2_id = '';
        if ( ! array_key_exists( "{$advisor_1[1]} {$advisor_1[0]}", $staffInfo ) ) {
            echo "FATAL ADVISOR 1 INFO NOT FOUND: " . implode( ',', $fields ) . "\n";
            exit( );
        }
        $advisor_1_id = $staffInfo["{$advisor_1[1]} {$advisor_1[0]}"]['id'];

        if ( ! empty( $advisor_2[0] ) ) {
            if ( ! array_key_exists( "{$advisor_2[1]} {$advisor_2[0]}", $staffInfo ) ) {
                echo "FATAL ADVISOR 2 INFO NOT FOUND: " . implode( ',', $fields ) . "\n";
                exit( );
            }
            $advisor_2_id = $staffInfo["{$advisor_2[1]} {$advisor_2[0]}"]['id'];
        }


        $newFields = array( $studentInfo[$fields[1]]['first_name'],
                            $studentInfo[$fields[1]]['last_name'],
                            'Student-' . $fields[1],
                            $advisor_1[0], $advisor_1[1], $advisor_1_id,
                            $advisor_2[0], $advisor_2[1], $advisor_2_id );

        fputcsv( $fdWrite, $newFields );
    }

    fclose( $fdRead  );
    fclose( $fdWrite );
}

function splitName( $name, $separator = ',' ) {
    $names = explode( $separator, trim($name), 2 );
    return array( trim( $names[0] ),
                  trim( $names[1] ) );
}

$northSouthInfo =& readNorthSouthFile( );

$studentInfo =& fixSFSFile( $northSouthInfo );

$staffInfo =& fixStaffFile( );

fixAdvisorFile( $studentInfo, $staffInfo );


