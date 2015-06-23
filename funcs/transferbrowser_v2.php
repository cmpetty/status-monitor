<?php
	error_reporting(-1);
	require_once '../src/database.inc.php';

	if($_GET['sc']) {
        if (is_string($_GET['sc'])){
            if (preg_match('/^(bia\d{1})$/',$_GET['sc'])){
                $scannerID=$_GET['sc'];
            } else {
                break;
            }
        } else {
	        break;
	    }
	}

	if ( $_GET['offset'] ) {
		$offset = "OFFSET ". $_GET['offset'];
	} else {
		$offset = "";
	}

	if ( $_GET['limit'] ) {
		$limit = "LIMIT ". $_GET['limit'];
	} else {
		$limit = "";
	}

    date_default_timezone_set('America/New_York');
	$fromdate = date('Y-m-d',time() - (30 * 24 * 60 * 60));
	$dbobj = new DatabaseBean;


$sql = "SELECT DISTINCT ScannerData.HDR_StudyDate as StudyDate, ScannerData.HDR_StudyID as StudyID, ScannerData.HDR_SeriesNumber as SeriesNumber, ScannerData.NumberImages, CASE WHEN ( ScannerData.NumberImages = temphdinfo.NumberImages ) THEN 'Success' ELSE 'Not Sent' END AS TransferStatus
FROM ScannerData LEFT JOIN ( SELECT * FROM DICOMHeaderInfo WHERE HDR_StudyID IN ( SELECT DISTINCT HDR_StudyID FROM ScannerData )) AS temphdinfo
USING ( HDR_StudyID, HDR_SeriesNumber ) WHERE ScannerData.HDR_GEMSSuiteID = '$scannerID' AND ScannerData.Current = '1' 
ORDER BY ScannerData.HDR_StudyDate DESC, ScannerData.HDR_StudyID DESC, ScannerData.HDR_SeriesNumber DESC $limit $offset"; 

   //print_r($sql);

    $dbobj->doquery($sql);
    
	$cols = array();
	$results = array();
	$idx = 0;

	while($row = $dbobj->getRow()) {        
/*
		echo "<pre>";
		print_r($row);
		echo "</pre>";
*/
		if ( $idx == 0 ) {
			foreach ( $row as $k => $v ) {
				array_push($cols,$k);
			}	
		}
		++$idx;

		$subj = array();
		foreach ( $row as $key => $val ) {
			array_push($subj,$val);
		}
		array_push($results,$subj);
	}

	//print_r( $results );

	$return_json = array( 'columns' => $cols, 'data' => $results, 'sql' => $sql );

	header("Content-type: text/json");
	echo json_encode($return_json);

?>
