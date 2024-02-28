<?php 
// include "../../sysconf/global_func.php";
// include "../../sysconf/db_config.php";
// include "../../sysconf/session.php";




$v_agentid                  = "1";
$v_agentname                = "system";
$id                         = $_GET['id']; 
$val                        = 1; 



  function date_long_to_yyyymmdd($inpdate) {
    if ($inpdate == "") {
       return "$inpdate";
    } 
    // 21 Apr 2015
    $arrdate = explode(" ", $inpdate);
    $yyyy = $arrdate[2];
    $dd   = $arrdate[0];
    if ($arrdate[1] == "Jan")
       $mm = "01";
    else if ($arrdate[1] == "Feb")
       $mm = "02";
    else if ($arrdate[1] == "Mar")
       $mm = "03";
    else if ($arrdate[1] == "Apr")
       $mm = "04";
    else if ($arrdate[1] == "May" || $arrdate[1] == "Mei")
       $mm = "05";
    else if ($arrdate[1] == "Jun")
       $mm = "06";
    else if ($arrdate[1] == "Jul")
       $mm = "07";
    else if ($arrdate[1] == "Aug" || $arrdate[1] == "Agu")
       $mm = "08";
    else if ($arrdate[1] == "Sep")
       $mm = "09";
    else if ($arrdate[1] == "Oct" || $arrdate[1] == "Okt")
       $mm = "10";
    else if ($arrdate[1] == "Nov")
       $mm = "11";
    else if ($arrdate[1] == "Dec" || $arrdate[1] == "Des")
       $mm = "12";
    
    return "$yyyy-$mm-$dd";    
  }

function connectDB() {

/* config mysql */ 
$conf_ip            = "10.1.49.224";  
$conf_user          = "es";
$conf_passwd        = "0218Galunggung";
$conf_db            = "db_wom";

    if (!$connect=mysqli_connect($conf_ip, $conf_user, $conf_passwd, $conf_db)) {
      $filename = __FILE__;
      $linename = __LINE__;
     exit();
    }
    return $connect;
}


function disconnectDB($db_connect) {
    mysqli_close($db_connect);
}


$condb = connectDB();

$dateexe = DATE("Y-m-d H:i:s");
$no =1;
$suc1=0;
$err1=0;


$sqlupdt = "UPDATE cc_telecollection_cust_data
           SET last_followup_result_log=NULL";
// $resupdt = mysqli_query($condb,$sqlupdt);
$resupdt = mysqli_query($condb,$sqlupdt);

if ($val==1) {
    
        $dateyear  = DATE("Y");
        $datemonth = DATE("m");

        $sqlcg = "SELECT * FROM cc_parameter_telecol_period a
                  WHERE a.status=1";
        $rescg = mysqli_query($condb,$sqlcg);
        if($reccg = mysqli_fetch_array($rescg)){
            $parm_value  = $reccg['parm_value'];
        }
        $datemonthnew = Date('m', strtotime("-$parm_value month", strtotime( DATE('Y-m-d') )));

        //
        $sqlcg1 = "SELECT * FROM cc_telecollection_hierarchy a
                  WHERE a.hierarchy_status=1
                  ORDER BY a.hierarchy_priority ASC";
        $rescg1 = mysqli_query($condb,$sqlcg1);
        while($reccg1 = mysqli_fetch_array($rescg1)){
            $hierarchy_status_id  = $reccg1['hierarchy_status_id'];
            $hierarchy_priority   = $reccg1['hierarchy_priority'];
            // echo"$hierarchy_priority </br>";
            if ($hierarchy_priority == '1') {
              $sqlcg = "SELECT * FROM 
                        ( SELECT * FROM cc_telecollection_history_call a
                        WHERE MONTH(create_time)>='$datemonthnew' AND YEAR(create_time)='$dateyear'
                        AND call_result = '$hierarchy_status_id'  
                        ORDER BY create_time DESC
                        ) AS table_cust_data GROUP BY aggrement_no";
              $rescg = mysqli_query($condb,$sqlcg);
              while($reccg = mysqli_fetch_array($rescg)){
                  $orderNumber  = $reccg['aggrement_no'];
                  $result  = $reccg['call_result'];
                  $sub_result  = $reccg['call_result_sub'];
                  $modify_time  = $reccg['create_time'];
                  $called_by  = $reccg['create_by'];
                  $no =1;

                  $sqlupdt = "UPDATE cc_telecollection_cust_data
                             SET last_followup_result_log='$result', last_followup_result='$result', last_followup_sub_result='$sub_result', last_followup_date='$modify_time', last_followup_by='$called_by'
                             WHERE agrement_no='$orderNumber'";
                  // $resupdt = mysqli_query($condb,$sqlupdt);
                  if($resupdt = mysqli_query($condb,$sqlupdt)){
                    $idin1 = mysqli_insert_id($dbopen);

                    $suc1 += $no;
                  }else{
                    $err1 += $no;
                  }
              }
            }else{
              $sqlcg = "SELECT * FROM 
                        ( SELECT * FROM cc_telecollection_history_call a
                        WHERE MONTH(create_time)>='$datemonthnew' AND YEAR(create_time)='$dateyear'
                        AND call_result = $hierarchy_status_id  
                        ORDER BY create_time DESC
                        ) AS table_cust_data GROUP BY aggrement_no";
                        // echo "string $sqlcg </br></br>";
              $rescg = mysqli_query($condb,$sqlcg);
              while($reccg = mysqli_fetch_array($rescg)){
                  $orderNumber  = $reccg['aggrement_no'];
                  $result  = $reccg['call_result'];
                  $sub_result  = $reccg['call_result_sub'];
                  $modify_time  = $reccg['create_time'];
                  $called_by  = $reccg['create_by'];
                  $no =1;
              

                  $sqlupdt = "UPDATE cc_telecollection_cust_data
                             SET last_followup_result_log='$result', last_followup_result='$result', last_followup_sub_result='$sub_result', last_followup_date='$modify_time', last_followup_by='$called_by'
                             WHERE agrement_no='$orderNumber' AND last_followup_result_log IS NULL ";
                  // $resupdt = mysqli_query($condb,$sqlupdt);
                  if($resupdt = mysqli_query($condb,$sqlupdt)){
                    $idin1 = mysqli_insert_id($dbopen);

                    $suc1 += $no;
                  }else{
                    $err1 += $no;
                  }
              }
            }
            

        }


        $sqlupdt = "UPDATE cc_telecollection_cust_data
                   SET flag_log=flag_telecol";
        $resupdt = mysqli_query($condb,$sqlupdt);

}

// //log 
 $sqllog = "INSERT INTO cc_log_sync_data SET 
                sync_desc       ='JOB_TELECOLL_hierarchy',
                sync_success    ='$suc1',
                sync_error      ='$err1',
                sync_time       ='$dateexe',
                sync_end_time   = now() ";
 mysqli_query($condb,$sqllog);

echo "success";
disconnectDB($condb);

?>