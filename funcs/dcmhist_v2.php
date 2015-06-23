<?php
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

	if( $_GET['timeLimit'] && $_GET['timeType'] ) {
		$timelimit = $_GET['timeLimit']; 
		$timetype = $_GET['timeType'];
		//if ( $timetype == "WEEK" ) {
		//	$timelimit = $timelimit * 7;
		//	$timetype = "DAY";
		//}	
	}

    date_default_timezone_set('America/New_York');
	$fromdate = date('Y-m-d',time() - (30 * 24 * 60 * 60));
	$dbobj = new DatabaseBean;

/*
$sql = "Select ScannerData.HDR_StudyDate, ScannerData.HDR_StudyID, ScannerData.HDR_SeriesNumber, ScannerData.NumberImages,
IF(ScannerData.NumberImages=DICOMHeaderInfo.NumberImages,\"Success\",\"Not Sent\") As TransferStatus
FROM ScannerData
LEFT JOIN DICOMHeaderInfo USING ( HDR_StudyID,HDR_SeriesNumber )
WHERE ScannerData.HDR_GEMSSuiteID = '$scannerID' AND ScannerData.Current = '1'
ORDER BY ScannerData.HDR_StudyDate DESC, ScannerData.HDR_StudyID DESC, ScannerData.HDR_SeriesNumber DESC $limit $offset"; 
*/

/*
$sql = "SELECT ScannerData.HDR_StudyDate, ScannerData.HDR_StudyID, ScannerData.HDR_SeriesNumber, ScannerData.NumberImages, IF( ScannerData.NumberImages = temphdinfo.NumberImages, \"Success\", \"Not Sent\" ) AS TransferStatus
FROM ScannerData
LEFT JOIN ( SELECT * FROM DICOMHeaderInfo WHERE HDR_StudyID IN ( SELECT DISTINCT HDR_StudyID FROM ScannerData )) AS temphdinfo
USING ( HDR_StudyID, HDR_SeriesNumber ) WHERE ScannerData.HDR_GEMSSuiteID = '$scannerID' AND ScannerData.Current = '1' 
ORDER BY ScannerData.HDR_StudyDate DESC, ScannerData.HDR_StudyID DESC, ScannerData.HDR_SeriesNumber DESC $limit $offset"; 
*/

//WHERE ScannerData.HDR_StudyDate BETWEEN DATE_SUB( curdate(), INTERVAL $timelimit $timetype ) AND CURDATE() 

$sql = sprintf("SELECT ScannerData.HDR_StudyDate as StudyDate,
	ScannerData.HDR_StudyID as StudyID,
	ScannerData.HDR_SeriesNumber as SeriesNumber,
	ScannerData.NumberImages,
CASE WHEN ( ScannerData.NumberImages = temphdinfo.NumberImages ) THEN 'Success' ELSE 'Not Sent' END AS TransferStatus,
CASE WHEN ( ScannerData.Current = '1' ) THEN 'Yes' ELSE 'No' END AS Current 
FROM ScannerData
LEFT JOIN ( SELECT * FROM DICOMHeaderInfo WHERE HDR_StudyID IN ( SELECT DISTINCT HDR_StudyID FROM ScannerData )) AS temphdinfo
USING ( HDR_StudyID, HDR_SeriesNumber )
WHERE ScannerData.HDR_StudyDate > CURRENT_DATE - INTERVAL '%s %s' AND ScannerData.HDR_StudyDate <= CURRENT_DATE
AND ScannerData.HDR_GEMSSuiteID = '%s' 
ORDER BY ScannerData.HDR_StudyDate DESC, ScannerData.HDR_StudyID DESC, ScannerData.HDR_SeriesNumber DESC",pg_escape_string($timelimit),pg_escape_string($timetype),pg_escape_string($scannerID) );

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
