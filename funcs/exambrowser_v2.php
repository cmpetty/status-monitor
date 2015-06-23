<?php	
    require_once '../src/database.inc.php';

        if($_GET['sc']) {
            if (is_string($_GET['sc'])){
                if (preg_match('/^(bia\d{1})$/',$_GET['sc'])){
                        $scanner=$_GET['sc'];
                } else {
                        break;
                }
            } else {
                break;
            }
        }

	#$fromdate = date('Y-m-d',time() - (30 * 24 * 60 * 60));
    $dbobj = new DatabaseBean;

    //print_r($dbobj);

$sql =  sprintf("Select AllQueryable.Exam_Number as Exam,
	AllQueryable.Exam_Date as Date,
	AllQueryable.Series_Number as Series,
	AllQueryable.Run_Number as Run,
	AllQueryable.DICOM_GEMSSuiteID as D_SuiteID,
	AllQueryable.Pfile_GEMSSuiteID as P_SuiteID,
	AllQueryable.DICOM_SeriesDescription as D_Desc,
	AllQueryable.Pfile_SeriesDescription as P_Desc
FROM AllQueryable
WHERE CURRENT_DATE - INTERVAL '%s DAYS' <= Exam_Date
AND ( DICOM_GEMSSuiteID = '%s' OR Pfile_GEMSSuiteID = '%s' )
ORDER BY Exam_Date DESC, Exam_Number DESC, Exam_Date DESC, Series_Number DESC, Run_Number DESC", pg_escape_string(30),pg_escape_string($scanner),pg_escape_string($scanner));

	//print_r($sql);

	$dbobj->doquery($sql);

    //print_r($dbobj);

	$cols = array();
	$results = array();
	$exams = array();	
	$idx = 0;

	while($row = $dbobj->getRow()) {
		//get the columns
		if ( $idx == 0 ) {
            next($row);
		}
		++$idx;
	
		$subj = array();
		foreach ( $row as $key => $val ) {
			if ( $key == "exam" ) {
				$exam = $val;
				if ( in_array($exam,$exams) ) {
					next($row);
				} else {
					array_push($exams,$exam);
				}
            } else if ( preg_match('/(suiteid)$/',$key) ) {
                next($row);
			} else {
                if ( $val != null ){
    				array_push( $subj,$val );
                }
			}
		}
		array_push($results,array($exam => $subj));
	}
	
	$return_json = array( 'columns' => array("Date","Series","Run","Description"), 'exams' => $exams, 'data' => $results, 'sql' => $sql );

	header("Content-type: text/json");
	echo json_encode($return_json);

?>

