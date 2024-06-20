<?php 
define('TIMEZONE', 'Asia/Kolkata');
date_default_timezone_set(TIMEZONE);
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);
//header('content-type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
//header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "OPTIONS") {
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
    header("HTTP/1.1 200 OK");
    die();
}

include 'connection_gb.php';

//exit;
if(isset($_REQUEST['winb']) && !empty($_REQUEST['winb']))
{
    $data = explode(",",$_REQUEST['winb']);
    if($data[0] != '?' && is_numeric($data[0])){
    $data1 = array (
		'device_id' => '1',
		'volume' => ($data[0]/5),
		'total_volume' => $data[1],
		'security' => $data[2],
		'added_date' => date('Y-m-d H:i:s')
	
        );
        
    $water_id = $db->insert('device_data', $data1);
    }
    
    if($data[0] == '?'){
        $data5 = array(
                'alert_type' => '3',
                'status' => '1',
                'added_date' => date('Y-m-d H:i:s')
            );
            
    	$alert_id = $db->insert('alerts', $data5);
    }
    if(!empty($data[2])){
        $data5 = array(
                'alert_type' => '2',
                'status' => '1',
                'added_date' => date('Y-m-d H:i:s')
            );
            
    	$alert_id = $db->insert('alerts', $data5);
    	$data5 = array();
    }
	if(empty($data[0]) || $data[0] == "0.00")
	{
	    
	    $db->where("cast(added_date as DATE)", date('Y-m-d'));
	    $db->orderBy("data_id","desc");
	    $checksql = $db->get("device_data", 3, "data_id, volume, total_volume");
	    $i = 0;
	    foreach($checksql as $single)
        {
            if($single['volme'] == '0.00' || empty($single['volme']))
            {
                $i++;
            }
        }
        if($i == 3)
        {
            $data4 = array (
    		'alert_type' => '1',
    		'status' => '1',
    		'added_date' => date('Y-m-d H:i:s')
    	
            );
            $alert = $db->insert('alerts', $data4);
        }
	}
	
    if ($water_id)
        $msg = "s";
    else
        $msg = "e"; 
	echo $msg;
}
else if(isset($_REQUEST['wink']) && !empty($_REQUEST['wink']))
{
    $data = explode(",",$_REQUEST['wink']);
    if($data[0] != '?' && is_numeric($data[0])){
    $data1 = array (
		'device_id' => '1',
		'volume' => ($data[0]/4),
		'total_volume' => $data[1],
		'security' => $data[2],
		'added_date' => date('Y-m-d H:i:s')
	
        );
    $water_id = $db->insert('device_data_kurdu', $data1);
    }
    if(!empty($data[2])){
        $data5 = array(
                'alert_type' => '2',
                'status' => '1',
                'added_date' => date('Y-m-d H:i:s')
            );
            
    	$alert_id = $db->insert('alerts_k', $data5);
    }
	
	if(empty($data[0]) || $data[0] == "0.00")
	{
	    
	    $db->where("cast(added_date as DATE)", date('Y-m-d'));
	    $db->orderBy("data_id","desc");
	    $checksql = $db->get("device_data_kurdu", 3, "data_id, volume, total_volume");
	    $i = 0;
	    foreach($checksql as $single)
        {
            if($single['volme'] == '0.00' || empty($single['volme']))
            {
                $i++;
            }
        }
        if($i == 3)
        {
            $data4 = array (
    		'alert_type' => '1',
    		'status' => '1',
    		'added_date' => date('Y-m-d H:i:s')
    	
            );
            $alert = $db->insert('alerts_k', $data4);
        }
	}
	
    if ($water_id)
        $msg = "kanda mahag ahe";
    else
        $msg = "e"; 
	echo $msg;
}
else if($_REQUEST['action'] == "save_token"){
    if(isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) && isset($_REQUEST['token']) && !empty($_REQUEST['token'])){
        $data = array(
            'device_token' => $_REQUEST['token'],
            'modified_date' => date('Y-m-d H:i:s')
            );
        $db->where("user_id", $_REQUEST['user_id']);    
        $token_id = $db->update("user", $data);
       
        if ($token_id)
        {
            $fields = array('status'=>'success');
        }
        else
        {
            $fields = array('status'=>'error');
        }
    }
    else
    {
        $fields = array('status'=>'missing');
    }
    header('Content-Type: application/json');
    echo json_encode($fields);
}
else if($_REQUEST['action'] == "mark_read"){
    if(isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) && isset($_REQUEST['notif_id']) && !empty($_REQUEST['notif_id'])){
        $data = array(
            'notif_id' => $_REQUEST['notif_id'],
            'user_id' => $_REQUEST['user_id'],
            'date_read' => date('Y-m-d H:i:s')
            );
        //$db->where("user_id", $_REQUEST['user_id']);    
        $token_id = $db->insert("read_notifications", $data);
       
        if ($token_id)
        {
            $fields = array('status'=>'success');
        }
        else
        {
            $fields = array('status'=>'error');
        }
    }
    else
    {
        $fields = array('status'=>'missing');
    }
    header('Content-Type: application/json');
    echo json_encode($fields);
}
else if($_REQUEST['action'] == "get_notifications"){
    if(isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) && isset($_REQUEST['user_level']) && !empty($_REQUEST['user_level'])){
        
        $db->orderBy("notif_id", "DESC");
        $db->groupBy("date(added_date)");
        //$db->where('date(opening_date)', date('Y-m-d'), ">");
        if($_REQUEST['user_level'] == 1)
        {
            $db->where("topic", array("common","important","critical"), "IN");
        }
        else if($_REQUEST['user_level'] == 2)
        {
            $db->where("topic", array("common","important"), "IN");
        }
        else
        {
            $db->where("topic", "common");
        }
        //$db->where('user_id', $_REQUEST['user_id']);
        $dates_list = $db->get("dashboard_app_notification", 3, "DATE(added_date) as added_date");            
        //var_dump($db->getLastQuery());
        $final_list = array();
        
        if(!empty($dates_list))
        {
            $i = 0;
            foreach($dates_list as $date)
            {
                $db->orderBy("notif_id", "DESC");
                $db->where('date(added_date)', $date['added_date']);
                if($_REQUEST['user_level'] == 1)
                {
                    $db->where("topic", array("common","important","critical"), "IN");
                }
                else if($_REQUEST['user_level'] == 2)
                {
                    $db->where("topic", array("common","important"), "IN");
                }
                else
                {
                    $db->where("topic", "common");
                }
                
                $valve_list = $db->get("dashboard_app_notification ", null, "notif_id, topic, title, body, image_url, added_date, DATE_FORMAT(added_date,'%H:%i:%s') as added_time");
                if(!empty($valve_list))
                {
                    $j = 0;
                    foreach($valve_list as $valve){
                        $db->where("user_id",$_REQUEST['user_id']);
                        $db->where("notif_id",$valve['notif_id']);
                        $check_read = $db->getOne("read_notifications");
                        if(!empty($check_read))
                            $valve_list[$j]['read'] = "#ececec";
                        else
                            $valve_list[$j]['read'] = "#fff";
                        $j++;
                    }
                }
                //var_dump($db->getLastQuery());
                $final_list[$i]['date']= $date['added_date'];
                $final_list[$i]['notif_list'] = $valve_list;
                $i++;
            }
            $fields = array('status'=>'success', "notifs" => $final_list);
        }
        else
        {
            $fields = array('status'=>'error');
        }
        
    }
    else
    {
        $fields = array('status'=>'missing');
    }
    header('Content-Type: application/json');
    echo json_encode($fields);
}




?>