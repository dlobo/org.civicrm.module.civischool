<?php

function &readContactCSV( ) {
    $fdRead  = fopen( '/Users/lobo/tmp/SFS/csv/Students.csv', 'r' );

    $contactIDValues = array( );
    while ( $fields = fgetcsv( $fdRead ) ) {
        $contactIDValues[$fields[1]][$fields[2]][$fields[3]] = $fields[0];
    }
    return $contactIDValues;
}

function getContactID( &$contactIDValues, $grade, $last, $first ) {
    $grade = trim( $grade );
    $first = trim( $first );
    $last  = trim( $last  );

    if ( isset( $contactIDValues[$grade][$first][$last] ) ) {
        return $contactIDValues[$grade][$first][$last];
    } else {
        return null;
    }
}

function &readClassFile( &$fdWrite, &$contactIDValues, &$globalID ) {
    $fdRead  = fopen( '/Users/lobo/tmp/SFS/csv/Fall2009_ClassBlocks.csv', 'r' );

    if ( ! $fdRead ) {
        echo "Could not read input file\n";
        exit( );
    }
    
    // read first line
    $header = fgetcsv( $fdRead );

    $count  = 0;
    while ( $fields = fgetcsv( $fdRead ) ) {
        if ( ! is_numeric( $fields[0] ) ) {
            continue;
        }

        for ( $i = 10; $i <= 30; $i++ ) {
            $val = trim( $fields[$i] );
            if ( ! is_numeric( $val ) ) {
                continue;
            }

            if ( (float ) $val > 0 ) {
                $contactID = getContactID( $contactIDValues, $fields[0], $fields[1], $fields[2] );
                if ( ! $contactID ) {
                    echo "Could not find contact record for:  $fields[0], $fields[1], $fields[2]\n";
                    exit( );
                } else {
                    $output = array( $globalID++,
                                     $contactID,
                                     "Charge",
                                     "Fall 2009 - $header[$i]",
                                     "2009-12-31",
                                     trim($fields[$i]),
                                     "Activity Fee" );
                    fputcsv( $fdWrite, $output );
                }
            }
        }

        $count++;
    }

    fclose( $fdRead  );
}

function &readBlockFile( &$fdWrite, &$contactIDValues, &$globalID ) {
    $fdRead  = fopen( '/Users/lobo/tmp/SFS/csv/Fall2009_SeptNovBlocks.csv', 'r' );

    if ( ! $fdRead ) {
        echo "Could not read input file\n";
        exit( );
    }
    
    // read first line
    $header = fgetcsv( $fdRead );

    $count  = 0;
    while ( $fields = fgetcsv( $fdRead ) ) {
        if ( ! is_numeric( $fields[0] ) ) {
            continue;
        }

        $monthIndices = array( 'Sep 2009' => 30,
                               'Oct 2009' => 52,
                               'Nov 2009' => 70 );
        foreach ( $monthIndices as $month => $index ) {
            $val = trim( $fields[$index] );
            if ( ! is_numeric( $val ) ) {
                continue;
            }

            if ( (float ) $val > 0 ) {
                $contactID = getContactID( $contactIDValues, $fields[0], $fields[1], $fields[2] );
                if ( ! $contactID ) {
                    echo "Could not find contact record for:  $fields[0], $fields[1], $fields[2]\n";
                    exit( );
                } else {
                    $output = array( $globalID++,
                                     $contactID,
                                     "Charge",
                                     "Fall 2009 - $month",
                                     "2009-12-31",
                                     trim($val),
                                     "Standard Fee" );
                    fputcsv( $fdWrite, $output );
                }
            }
        }


        $paidIndices = array( 'Paid Blocks' => 6,
                              'Free Blocks' => 7,
                              'Carry Over Blocks'  => 8 );
        foreach ( $paidIndices as $title => $index ) {
            $val = trim( $fields[$index] );
            if ( ! is_numeric( $val ) ) {
                continue;
            }

            if ( (float ) $val > 0 ) {
                $contactID = getContactID( $contactIDValues, $fields[0], $fields[1], $fields[2] );
                if ( ! $contactID ) {
                    echo "Could not find contact record for:  $fields[0], $fields[1], $fields[2]\n";
                    exit( );
                } else {
                    $output = array( $globalID++,
                                     $contactID,
                                     "Payment",
                                     "Fall 2009 - $title",
                                     "2009-12-31",
                                     trim($val),
                                     $title );
                    fputcsv( $fdWrite, $output );
                }
            }
        }

        $count++;
    }

    fclose( $fdRead  );
}

function run( ) {
    $contactIDValues =& readContactCSV( );

    $fdWrite = fopen( '/Users/lobo/tmp/SFS/csv/Fall2009_FeeDetails.csv' , 'w' );

    if ( ! $fdWrite ) {
        echo "Could not write output file\n";
        exit( );
    }

    $globalID = 1;
    readBlockFile( $fdWrite, $contactIDValues, $globalID );

    readClassFile( $fdWrite, $contactIDValues, $globalID );

    fclose( $fdWrite );
}

run( );


