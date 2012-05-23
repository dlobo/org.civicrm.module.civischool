<?php


function initialize( ) {
    require_once '/Users/lobo/public_html/drupal6/sites/sfschool/civicrm.settings.php';

    require_once 'CRM/Core/Config.php';
    $config =& CRM_Core_Config::singleton( );

    require_once 'CRM/Core/Error.php';
    require_once 'CRM/Utils/String.php';
}

function loadFiles( $inputDir, $year, $term, &$files ) {
    $dir =
        $inputDir . DIRECTORY_SEPARATOR .
        $year     . DIRECTORY_SEPARATOR .
        $term;

    $outDir =
        $inputDir     . DIRECTORY_SEPARATOR .
        'reportCards' . DIRECTORY_SEPARATOR .
        $year         . DIRECTORY_SEPARATOR .
        $term;
    CRM_Utils_File::createDir( $outDir );

    $grades = scandir( $dir );
    $files = array( );

    foreach ( $grades as $grade ) {
        if ( ! is_numeric( $grade ) ) {
            continue;
        }

        $gradeDir = $dir . DIRECTORY_SEPARATOR . $grade;
        $gradeFiles = scandir( $gradeDir );
        if ( ! empty( $gradeFiles ) ) {
            foreach ( $gradeFiles as $reportCard ) {
                $fileInfo = pathInfo( $reportCard );
                if ( ! is_numeric( $fileInfo['filename'] ) ||
                     $fileInfo['extension'] != 'pdf' ) {
                    continue;
                }

                $path    = $gradeDir . DIRECTORY_SEPARATOR . $reportCard;
                $newGradeDir   = $outDir . DIRECTORY_SEPARATOR . $grade;
                CRM_Utils_File::createDir( $newGradeDir );

                list( $contactID, $firstName, $lastName, $gradeDB ) = getContactInfo( $fileInfo['filename'] );
                if ( empty( $contactID ) ) {
                    echo "Could not find matching student record for reportFile: $path\n";
                    continue;
                }

                $cleanFirst = CRM_Utils_String::munge( $firstName );
                $cleanLast  = CRM_Utils_String::munge( $lastName  );
                $newReportCard = "{$cleanFirst}_{$cleanLast}_{$fileInfo['filename']}_" . md5( uniqid( rand( ), true ) ) . ".pdf";
                $newPath = $newGradeDir . DIRECTORY_SEPARATOR . $newReportCard;
                if ( ! copy( $path, $newPath ) ) {
                    echo "Could not copy $path to $newPath\n";
                    continue;
                }

                // fix newPath so we remove the inputDir offset
                $newPath = str_replace( $inputDir . DIRECTORY_SEPARATOR,
                                        '',
                                        $newPath );

                $files[] = array( 'grade'         => $grade,
                                  'studentNumber' => $fileInfo['filename'],
                                  'fileName'      => $reportCard,
                                  'path'          => $path,
                                  'newPath'       => $newPath,
                                  'contactID'     => $contactID,
                                  'firstName'     => $firstName,
                                  'lastName'      => $lastName,
                                  'gradeDB'       => $gradeDB,
                                  'isValid'       => 1 );
            }
        }
    }
}

function getContactInfo( $studentNumber ) {
    $sql = "
SELECT     c.id as contact_id, c.first_name, c.last_name, s.grade
FROM       civicrm_contact c
INNER JOIN civicrm_value_school_information s ON s.entity_id = c.id
WHERE      c.external_identifier = 'Student-$studentNumber'
AND        s.is_currently_enrolled = 1
";
    $dao = CRM_Core_DAO::executeQuery( $sql );
    if ( $dao->fetch( ) ) {
        return array( $dao->contact_id, $dao->first_name, $dao->last_name, $dao->grade );
    } else {
        return array( null, null, null, null );
    }
}

function validateFiles( &$files, $year, $term ) {
    $sql = "
SELECT id
FROM   civicrm_value_report_cards
WHERE  entity_id = %1
AND    report_year = %2
AND    report_grade = %3
AND    report_term = %4
";
    $params = array( 1 => array( 0    , 'Integer' ),
                     2 => array( $year, 'String'  ),
                     3 => array( '0'  , 'String'  ),
                     4 => array( $term, 'String'  ) );

    foreach ( $files as $idx =>& $file ) {
        echo "Validating {$file['firstName']}, {$file['lastName']}, {$file['gradeDB']}\n";

        // check various params
        if ( empty( $file['contactID'] ) ) {
            echo "Could not find matching student record for reportFile: {$file['path']}\n";
            $file['isValid'] = 0;
            continue;
        }

        if ( $file['gradeDB'] != $file['grade'] ) {
            echo "Grades do not match for {$file['path']}, {$file['grade']}, DB Grade: {$file['gradeDB']}\n";
            $file['isValid'] = 0;
            continue;
        }

        // check for first name last name in file using mdfind
        $name = strtolower( "{$file['firstName']} {$file['lastName']}" );
        $fileName = exec( "/usr/bin/mdfind -onlyin {$file['path']} \"$name\"", $dontCare );
        if ( trim( $fileName ) != trim( $file['path'] ) ) {
            echo "Name: $name does not exist in file {$file['path']}\n";
            $file['isValid'] = 0;
            continue;
        }

        // check if this entry already exists, if so move on
        $params[1][0] = $file['contactID'];
        $params[3][0] = $file['grade'];
        if ( CRM_Core_DAO::singleValueQuery( $sql, $params ) ) {
            echo "Name: $name already has a report card {$file['path']} attached.\n";
            $file['isValid'] = 0;
            continue;
        }
    }
}

function generateSQL( &$files, $year, $term,
                      $customValueCounter,
                      $entityFileCounter,
                      $fileCounter ) {
    $customValueSQL = $entityFileSQL = $fileSQL = array( );
    $now = date( 'Y-m-d h:i:s' );

    foreach ( $files as $idx =>& $file ) {
        if ( ! $file['isValid'] ) {
            continue;
        }

        $customValueSQL[] = "( $customValueCounter, {$file['contactID']}, '$year', '$term', '{$file['grade']}', $entityFileCounter )";
        $entityFileSQL[]  = "( $entityFileCounter, 'civicrm_value_report_cards_13', {$file['contactID']}, $fileCounter )";
        $fileSQL[]        = "( $fileCounter, 'application/pdf', '{$file['newPath']}', '$now' )";

        $customValueCounter++;
        $entityFileCounter++;
        $fileCounter++;
    }

    $sql  = null;
    $sql .= "
INSERT INTO civicrm_file ( id, mime_type, uri, upload_date )
VALUES
" . implode( ",\n", $fileSQL ) . ";\n";

    $sql .= "
INSERT INTO civicrm_entity_file ( id, entity_table, entity_id, file_id )
VALUES
" . implode( ",\n", $entityFileSQL ) . ";\n";

    $sql .= "
INSERT INTO civicrm_value_report_cards ( id, entity_id, report_year, report_term, report_grade, report_pdf_76 )
VALUES
" . implode( ",\n", $customValueSQL ) . ";\n";

    return $sql;
}

function run( $inputDir, $year, $term ) {
    initialize( );

    $files = array( );
    loadFiles( $inputDir, $year, $term, $files );

    validateFiles( $files, $year, $term );

    // at this point all the files in validateFiles are valid, so now lets generate the sql
    $sql = generateSQL( $files, $year, $term, 96, 96, 96 );

    print_r( $sql );
}

run( '/Users/lobo/SCH/Reports',
     '2011-2012',
     'S1' );

