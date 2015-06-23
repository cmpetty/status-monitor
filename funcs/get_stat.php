<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    date_default_timezone_set('America/New_York');

    include_once '../src/database.inc.php';
    
    $db = new DatabaseBean;
    
    //$db = new SQLite3('aims.db');
    
    $data = array();
    $sql = NULL;
    $table = NULL;

    //print_r($_GET);
    
    if(isset($_GET['stat'])){
        $table = "statusmonitor";
        #$sql = "select strftime('%Y/%m/%d %H:%M:%S',ts) as ts,host,procname,pid,started,running from status";
        #$sql = "select ts,host,procname,pid,started,running from statusmonitor order by host";
        $sql = "select to_char(ts,'YYYY/MM/DD HH24:MI:SS') as ts,host,procname,pid,started,running from statusmonitor order by host";

    }elseif (isset($_GET['disk'])){
        $table = "diskmonitor";
        #$sql = "select strftime('%Y/%m/%d %H:%M:%S',ts) as ts,host,display,usage from diskmonitor";
        #$sql = "select ts,host,display,usage from diskmonitor order by host";
        $sql = "select to_char(ts,'YYYY/MM/DD HH24:MI:SS') as ts,host,display,usage from diskmonitor order by host";

    }

    if (isset($sql) AND $db->doquery($sql)) {
        #$sql = sprintf("SELECT * FROM %s",$db->escapeString($table));
        #$results = $db->doquery($sql);
        #while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        #    array_push($data,$row);
        #}
        
        $hosts=array();
        
        while ( $row = $db->getrow() ){
            if (!array_key_exists($row['host'],$hosts)){
                $hosts[$row['host']]=array();
            }

            if ($table == "diskmonitor"){
                array_push($hosts[$row['host']],array('ts'=>$row['ts'],'display'=>$row['display'],'usage'=>$row['usage']));
            } else if ($table == "statusmonitor") {
                array_push($hosts[$row['host']],array('ts'=>$row['ts'],'procname'=>$row['procname'],'started'=>$row['started'],'running'=>$row['running']));
            }
        }
    }
    
    header("Content-type: text/json");
    echo json_encode(array("data"=>$hosts, "sql"=>$sql, "table"=>$table));

/*
    echo "<pre>";
    print_r($data);
    echo "</br>";
    print "$sql";
    echo "</pre>";
 */
?>
