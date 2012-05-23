<?php

define( 'HOME_LOCATION_TYPE_ID', 1 );
define( 'HOME_PHONE_TYPE_ID', 1 );
define( 'CELL_PHONE_TYPE_ID', 2 );
define( 'WORK_PHONE_TYPE_ID', 4 );

function &parsePSFile( &$studentInfo ) {

    $fdRead  = fopen( '/Users/lobo/SCH/PowerSchool/export/StudentInfo_080910.csv', 'r' );

    if ( ! $fdRead ) {
        echo "Could not read file\n";
        exit( );
    }

    // read first line
    $fields = fgetcsv( $fdRead );

    $studentInfo = array( );

    $count = 0;
    while ( $fields = fgetcsv( $fdRead ) ) {
        $count++;
        // print_r( $fields );
        parseRow( $fields, $studentInfo );
    }

    fclose( $fdRead  );
}

function parseRow( &$fields, &$studentInfo ) {
    if ( array_key_exists( $fields[0], $studentInfo ) ) {
        CRM_Core_Error::fatal( );
    }

    if ( empty( $fields[11] ) ) {
        CRM_Core_Error::fatal( 'First parent name in household 1 cannot be empty' );
    }

    if ( $fields[4] > 1 ) {
        return;
    }

    $studentInfo[$fields[0]] =
        array( 'student_id'  => $fields[0],
               'first_name'  => $fields[1],
               'middle_name' => $fields[2],
               'last_name'   => $fields[3],
               'birth_date'  => $fields[6] );

    $studentInfo[$fields[0]]['grade']     =
        $studentInfo[$fields[0]]['grade_sis'] = $fields[4];

    // fix for pre K to include north / south
    if ( $fields[4] < 1 ) {
        $grade = null;
        if ( $fields[4] == -2 ) {
            $grade = 'PK3 ';
        } else if ( $fields[4] == -1 ) {
            $grade = 'PK4 ';
        } else {
            $grade = 'K ';
        }

        $grade .= $fields[5];
        $studentInfo[$fields[0]]['grade'] = $grade;
    }

}

function initialize( ) {
    require_once '/Users/lobo/public_html/drupal6/sites/sfschool/civicrm.settings.php';

    require_once 'CRM/Core/Config.php';
    $config =& CRM_Core_Config::singleton( );

    require_once 'CRM/Core/Error.php';
}

function fixGradeSQL( &$studentInfo, &$errors ) {
    foreach ( $studentInfo as $studentID => &$student ) {
        // first get student id
        $query = "
SELECT     c.id, s.grade
FROM       civicrm_contact c
INNER JOIN civicrm_value_school_information s ON c.id = s.entity_id
WHERE      c.external_identifier = %1
";
        $params = array( 1 => array( "Student-{$studentID}", 'String' ) );
        $dao = CRM_Core_DAO::executeQuery( $query, $params );
        $dao->fetch( );
        if ( ! $dao->id ) {
            $errors['No Contact Info Student'][] = "$studentID, {$student['first_name']}, {$student['last_name']}";
            continue;
        }

        echo "UPDATE civicrm_value_school_information SET grade = '{$student['grade']}', grade_sis ={$student['grade_sis']} WHERE entity_id = {$dao->id};\n";
    }

}

function checkAndSaveParentInfo( &$student, &$parent, &$errors ) {
    checkAndSaveParentAddress( $student, $parent, $errors );
    checkAndSaveParentEmail  ( $student, $parent, $errors );
    checkAndSaveParentPhone  ( $student, $parent, $errors );

    updateRelationshipCustomData( $parent );
}

function updateRelationshipCustomData( &$parent ) {
    $params = array( 1 => array( $parent['relationship_id'], 'Integer' ),
                     2 => array( $parent['parent_index']   , 'Integer' ) );

    $query = "
SELECT id
FROM   civicrm_value_parent_relationship_data
WHERE  entity_id = %1
";
    $customID = CRM_Core_DAO::singleValueQuery( $query, $params );

    if ( $customID ) {
        $query = "
UPDATE civicrm_value_parent_relationship_data
SET    parent_index = %2
WHERE  entity_id = %1
";
    } else {
        $query = "
INSERT INTO civicrm_value_parent_relationship_data
  ( entity_id, parent_index )
VALUES
  ( %1, %2 )
";
    }

    CRM_Core_DAO::executeQuery( $query, $params );
}

function checkAndSaveParentAddress( &$student, &$parent ) {
    $params = array( 1 => array( $parent['contact_id'], 'Integer' ),
                     2 => array( HOME_LOCATION_TYPE_ID, 'Integer' ) );

    if ( ! CRM_Utils_Array::value( 'street_address', $parent ) ) {
        // delete existing address
        $query = "
DELETE
FROM   civicrm_address
WHERE  contact_id = %1
AND    location_type_id = %2
";
        CRM_Core_DAO::executeQuery( $query, $params );
        return;
    }

    // check if address already exists, if so reuse that
    $query = "
SELECT id
FROM   civicrm_address
WHERE  contact_id = %1
AND    location_type_id = %2
";
    $addressID = CRM_Core_DAO::singleValueQuery( $query, $params );

    $params[3] = array( trim( $parent['street_address'] ), 'String' );
    $city = CRM_Utils_Array::value( 'city', $parent, 'San Francisco' );
    $params[4] = array( trim( $city ), 'String' );

    $postalCode = CRM_Utils_Array::value( 'postal_code', $parent, 'San Francisco' );
    if ( $postalCode ) {
        $params[5] = array( $postalCode, 'String' );
    }

    $stateProvinceID = 1004;
    $params[6] = array( $stateProvinceID, 'Integer' );

    $countryID = 1228;
    $params[7] = array( $countryID, 'Integer' );

    if ( $addressID ) {
        $params[8] = array( $addressID, 'Integer' );
        $query = "
UPDATE civicrm_address
SET    street_address    = %3,
       city              = %4,
       postal_code       = %5,
       state_province_id = %6,
       country_id        = %7,
       is_primary        = 1
WHERE  id = %8
";
    } else {
        $query = "
INSERT INTO civicrm_address
  ( contact_id, location_type_id, street_address, city, postal_code, state_province_id, country_id, is_primary )
VALUES
  ( %1, %2, %3, %4, %5, %6, %7, 1 )
";
    }

    CRM_Core_DAO::executeQuery( $query, $params );
}

function checkAndSaveParentEmail( &$student, &$parent, &$errors ) {
    if ( ! CRM_Utils_Array::value( 'email', $parent ) ) {
        return;
    }

    $newEmail = strtolower( trim( $parent['email'] ) );

    // first check if email exists and matches
    $query = "
SELECT email
FROM   civicrm_email
WHERE  contact_id = %1
";
    $params = array( 1 => array( $parent['contact_id'], 'Integer' ) );
    $email = CRM_Core_DAO::singleValueQuery( $query, $params );
    $email = strtolower( trim($email) );

    if ( ! empty( $email ) ) {
        // check if user has logged into drupal account
        $query = "
SELECT uid, login
FROM   drupal_sfs.users
WHERE  ( name = %1 OR mail = %1 )
";
        $params = array( 1 => array( $email, 'String' ) );
        $user = CRM_Core_DAO::executeQuery( $query, $params );
        $user->fetch( );
        if ( ! $user->uid ) {
            $errors['Drupal Account does not exist'][] =
                "$studentID, {$student['first_name']}, {$student['last_name']}, {$parent['first_name']}, {$parent['last_name']}, {$parent['name']}, {$parent['email']}, CiviCRM Email: $email";
        }
    }

    if ( $email != $newEmail ) {
        if ( empty( $email ) ) {
            $errors['No Email in CiviCRM'][] =
                "$studentID, {$student['first_name']}, {$student['last_name']}, {$parent['first_name']}, {$parent['last_name']}, {$parent['name']}, {$parent['email']}";
        } else if ( strpos( $newEmail, '/' ) !== false ) {
            $errors['Multiple Emails in PowerSchool'][] =
                "Email does not match for parent of {$student['contact_id']}, {$student['first_name']}, {$student['last_name']}, {$parent['name']}: Drupal Email: $email, PowerSchool Email: $newEmail";
        } else {
            $loginMessage = $user->login ? 'User has logged in' : 'User has not logged in';
            $errors['Email MisMatch'][] =
                "Email does not match for parent of {$student['contact_id']}, {$student['first_name']}, {$student['last_name']}, {$parent['name']}: Drupal Email: $email, PowerSchool Email: $newEmail. {$loginMessage}";
        }
    }
}

function checkAndSaveParentPhone( &$student, &$parent ) {
    $primary = 1;
    // add phone numbers
    addPhone( $parent['contact_id'],
              HOME_LOCATION_TYPE_ID,
              CRM_Utils_Array::value( 'phone_home', $parent ),
              HOME_PHONE_TYPE_ID,
              $primary );
    addPhone( $parent['contact_id'],
              HOME_LOCATION_TYPE_ID,
              CRM_Utils_Array::value( 'phone_cell', $parent ),
              CELL_PHONE_TYPE_ID,
              $primary );
    addPhone( $parent['contact_id'],
              HOME_LOCATION_TYPE_ID,
              CRM_Utils_Array::value( 'phone_work', $parent ),
              WORK_PHONE_TYPE_ID,
              $primary );
}

function addPhone( $contactID,
                   $locationTypeID,
                   $phone,
                   $phoneTypeID,
                   &$primary ) {
    $params = array( 1 => array( $contactID     , 'Integer' ),
                     2 => array( $locationTypeID, 'Integer' ),
                     3 => array( $phoneTypeID   , 'Integer' ),
                     4 => array( trim($phone)   , 'String'  ),
                     5 => array( $primary       , 'Integer' ) );

    if ( empty( $phone ) ) {
        $query = "
DELETE
FROM   civicrm_phone
WHERE  contact_id = %1
AND    location_type_id = %2
AND    phone_type_id = %3
";
        CRM_Core_DAO::executeQuery( $query, $params );
    } else {
        $query = "
SELECT id
FROM   civicrm_phone
WHERE  contact_id = %1
AND    location_type_id = %2
AND    phone_type_id = %3
";
        $phoneID = CRM_Core_DAO::singleValueQuery( $query, $params );
        if ( $phoneID ) {
            $query = "
UPDATE civicrm_phone
SET    phone = %4,
       is_primary = %5
WHERE  id = %6
";
            $params[6] = array( $phoneID, 'Integer' );
        } else {
            $query = "
INSERT INTO civicrm_phone ( contact_id, location_type_id, phone_type_id, phone, is_primary )
VALUES ( %1, %2, %3, %4, %5 )
";
        }
        CRM_Core_DAO::executeQuery( $query, $params );
        $primary = 0;
    }
}

function matchName( $name ) {
    static $names = array(
                          'Lee, Mike' => 'Lee, Michael',
                          );

    $name = trim( $name );
    return CRM_Utils_Array::value( $name, $names, $name );
}

function splitName( $name, $separator = ',' ) {
    $name = matchName( $name );

    $names = explode( $separator, $name, 2 );
    return array( trim( $names[0] ),
                  trim( $names[1] ) );
}

function createStudent( &$student ) {
    // create student contact record
    $contactData = array( 'first_name'          => $student['first_name'],
                          'last_name'           => $student['last_name'] ,
                          'external_identifier' => "Student-{$student['student_id']}",
                          'contact_type'        => 'Individual',
                          'custom_1'            => 'Student',
                          'custom_2'            => $student['grade'],
                          'custom_14'           => $student['grade_sis'],
                          'custom_24'           => 'Regular',
                          );

    require_once 'api/v2/Contact.php';
    civicrm_contact_create( $contactData );
}

function createParent( &$student, &$parent ) {
    // first checka and create parent record
    $query = "
SELECT     p.id as parent_id
FROM       civicrm_contact p
INNER JOIN civicrm_value_school_information s ON p.id = s.entity_id
WHERE      ( ( p.first_name = %1 AND p.last_name = %2 ) OR
             ( p.sort_name LIKE %3 ) )
AND        ( ( s.subtype = 'Parent' ) OR ( s.subtype = 'Staff' ) )
";
    $params = array( 1 => array( $parent['first_name'], 'String' ),
                     2 => array( $parent['last_name'], 'String' ),
                     3 => array( "{$parent['name']}%", 'String' ) );
    $dao = CRM_Core_DAO::executeQuery( $query, $params );
    $dao->fetch( );
    if ( ! $dao->parent_id ) {
        $contactData = array( 'first_name'          => $parent['first_name'],
                              'last_name'           => $parent['last_name'] ,
                              'email'               => $parent['email'],
                              'contact_type'        => 'Individual',
                              'custom_1'            => 'Parent',
                              );

        require_once 'api/v2/Contact.php';
        $result = civicrm_contact_create( $contactData );
        $parentID = $result['contact_id'];
    } else {
        $parentID = $dao->parent_id;
    }

    // next create relationship between parent and student
    $sql = "
INSERT INTO civicrm_relationship
  ( contact_id_a, contact_id_b, relationship_type_id, is_active, is_permission_a_b, is_permission_b_a )
VALUES
  ( %1, %2, 1, 1, 0, 1 )
";
    $params = array( 1 => array( $student['contact_id'], 'Integer' ),
                     2 => array( $parentID             , 'Integer' ) );
    CRM_Core_DAO::executeQuery( $sql, $params );
}

function run( ) {
    initialize( );

    $studentInfo = null;
    parsePSFile( $studentInfo );

    // print_r( $studentInfo );

    fixGradeSQL( $studentInfo, $errors );

}

run( );