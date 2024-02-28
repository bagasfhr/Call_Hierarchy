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


if ($val==1) {

              $sqlcg = "SELECT a.* FROM cc_telecollection_cust_data a 
              LEFT JOIN cc_telecollection_whitelist c
              ON a.agrement_no=c.`list` AND c.`status`=1
              WHERE 1=1 AND c.id IS NULL AND a.call_status=0 AND a.flag_telecol=0";
                        // echo "string $sqlcg </br></br>";
              $rescg = mysqli_query($condb,$sqlcg);
              while($reccg = mysqli_fetch_array($rescg)){
                  $agrement_no  = $reccg['agrement_no'];
                  $angke        = $reccg['angke'];
                  $dpd_real     = $reccg['dpd_real'];
                  $no =1;
              

                  $hist_id=0;
                  $sqlmp = "SELECT 
                            a.*
                            FROM 
                            cc_telecollection_history_call a 
                            WHERE a.aggrement_no='$agrement_no' AND DATE(a.create_time)=CURDATE()";
                  $resmp = mysqli_query($condb,$sqlmp);
                  if($recmp = mysqli_fetch_array($resmp)){
                      $hist_id                               = $recmp['id'];
                  }
                  if ($hist_id==0) {
                      $sqlhist = "INSERT INTO cc_telecollection_history_call SET 
                                      aggrement_no       = '$agrement_no', 
                                      tgl_jatuhtempo     = '$xxx',
                                      angsuran_ke        = '$angke',
                                      dpd                = '$dpd_real',
                                      phone_no           = '',
                                      call_result        = '0',
                                      call_result_sub    = '0',
                                      kategory_konsumen  = '',
                                      contacted_person   = '',
                                      ptp_date           = '',
                                      ptp_status         = '',
                                      remark_desc        = '',
                                      create_name        = '',";
                      $sqlhist .= "create_by          ='',
                                  create_time         =now()";//echo "$sqlhist";
                      // mysqli_query($dbopen,$sqlhist);
                      if($reshist = mysqli_query($condb,$sqlhist)){
                        $suc1 += $no;
                      }else{
                        $err1 += $no;
                      }
                  }
                  
              }


        $sqlupdt = "UPDATE cc_telecollection_cust_data
                   SET flag_log=flag_telecol";
        $resupdt = mysqli_query($condb,$sqlupdt);

}

// //log 
 $sqllog = "INSERT INTO cc_log_sync_data SET 
                sync_desc       ='JOB_TELECOLL_EOD',
                sync_success    ='$suc1',
                sync_error      ='$err1',
                sync_time       ='$dateexe',
                sync_end_time   = now() ";
 mysqli_query($condb,$sqllog);

echo "success";
disconnectDB($condb);

?>