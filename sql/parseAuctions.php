<?php

function &parseAuctionFile( &$auction ) {
    
    $fdRead  = fopen( '/Users/lobo/SFS/Auctions/2011_01.csv', 'r' );

    if ( ! $fdRead ) {
        echo "Could not read file\n";
        exit( );
    }
    
    // read first line
    $fields = fgetcsv( $fdRead );

    $auctions = array( );

    $count = 0;
    while ( $fields = fgetcsv( $fdRead ) ) {
        $count++;
        // print_r( $fields );
        parseRow( $fields, $auction );

    }

    fclose( $fdRead  );
}

function parseRow( &$fields, &$auctions ) {

    // ignore row if empty first col
    if ( empty( $fields[0] ) ) {
        return;
    }

    // valid values are: COMPLETED, PENDING, DECLINE
    $validValues = array( 'completed'    ,
                          'pending'      ,
                          'decline'      ,
                          'needs pick up' );

    if ( ! in_array( strtolower( $fields[0] ), $validValues ) ) {
        echo "Invalid Value: $fields[0] in Line: " , implode( ', ', $fields ) . "\n";
        return;
    }

    // get the organization id from the organization name
    $orgName = $fields[5];
    $orgID   = getOrgID( $orgName );
    if ( ! $orgID ) {
        echo "Invalid Org Name: $orgName in Line: " , implode( ', ', $fields ) . "\n";
        return;
    }

    if ( array_key_exists( $orgID, $auctions ) ) {
        echo "Already processed Org: $orgName in Line: " , implode( ', ', $fields ) . "\n";
        return;
    }

    $result = $status = '';
    $value  = 0;
    switch ( strtolower( $fields[0] ) ) {
    case 'completed':
        $result = 'Yes';
        $status = 'Received';
        $value  = (float ) str_replace( array( '$', ',' ), '', $fields[2] );
        break;

    case 'needs pick up':
        $result = 'Yes';
        $status = 'Volunteer will pick up';
        $value  = (float ) str_replace( array( '$', ',' ), '', $fields[2] );
        break;

    case 'decline':
        $result = 'No';
        break;

    case 'pending':
        $result = 'Pending';
        break;
    }

        
    $auctions[$orgID] = array( 'orgID'   => $orgID    ,
                               'status'  => $status   ,
                               'result'  => $result   ,
                               'details' => CRM_Utils_Array::value( 1, $fields, '' ),
                               'value'   => $value    ,
                               'notes'   => CRM_Utils_Array::value( 3, $fields, '' )
                               );
}

function initialize( ) {
    require_once '/Users/lobo/public_html/drupal6/sites/sfschool/civicrm.settings.php';

    require_once 'CRM/Core/Config.php';
    $config =& CRM_Core_Config::singleton( );

    require_once 'CRM/Core/Error.php';
}

function getOrgID( $orgName ) {
    $query = "
SELECT     c.id
FROM       civicrm_contact c
WHERE      c.organization_name = %1
AND        ( is_deleted = 0 OR is_deleted IS NULL )
";
    $params = array( 1 => array( $orgName, 'String' ) );
    return CRM_Core_DAO::singleValueQuery( $query, $params );
}

function createActivities( &$auctions ) {
    require_once 'CRM/Activity/DAO/Activity.php';
    require_once 'CRM/Activity/DAO/ActivityTarget.php';
    require_once 'CRM/Activity/DAO/ActivityAssignment.php';
    
    $params = array(
                    'source_contact_id'  => 745,
                    'source_record_id'   => 2,
                    'activity_type_id'   => 32,
                    'subject'            => 'SF Auction 2011 - Respondent Interview',
                    'activity_date_time' => date( 'Ymdhis' ),
                    'status_id'          => 1,
                    'priority_id'        => 2,
                    'is_deleted'         => 0,
                    );

    foreach ( $auctions as $key => $auction ) {
        createActivity( $auction, $params );
    }
}

function createActivity( &$auction, &$params ) {
    // first create activity record
    $activity = new CRM_Activity_DAO_Activity( );

    foreach ( $params as $key => $value ) {
        $activity->$key = $value;
    }
    $activity->details = $auction['notes'];
    $activity->result  = $auction['result'];

    $activity->save( );

    $activityTarget = new CRM_Activity_DAO_ActivityTarget( );
    $activityTarget->activity_id = $activity->id;
    $activityTarget->target_contact_id = $auction['orgID'];
    $activityTarget->save( );
    
    $activityAssignment = new CRM_Activity_DAO_ActivityAssignment( );
    $activityAssignment->activity_id = $activity->id;
    $activityAssignment->assignee_contact_id = $params['source_contact_id'];
    $activityAssignment->save( );

    // now save the custom data
    $sql = "
INSERT INTO civicrm_value_business_survey_12
( entity_id, description_of_donation_68, amount_of_donation_71, donation_status_70 )
VALUES
( %1, %2, %3, %4 )
";
    $sqlParams = array( 1 => array( $activity->id       , 'Integer' ),
                        2 => array( $auction['details'] , 'String'  ),
                        3 => array( $auction['value']   , 'Money'   ),
                        4 => array( $auction['status']  , 'String'  ),
                        );
    CRM_Core_DAO::executeQuery( $sql, $sqlParams );
}


function run( ) {
    initialize( );

    $auction = array( );
    parseAuctionFile( $auction );

    createActivities( $auction );
}

run( );