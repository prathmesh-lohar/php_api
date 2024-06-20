<?php

include 'connection.php';

header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


if($_REQUEST['action'] == "get_features"){
    if(isset($_REQUEST['camera_id']) && !empty($_REQUEST['camera_id'])){
        
        $db->where("cam_id", $_REQUEST['camera_id']);
        
        //$db->where("user_id", $_REQUEST['user_id']);    
        $feature_list = $db->get("camera_features", null, "cam_id, alert_type_id, status");
       
        if(count($feature_list) > 0)
        {
            $fields = array('status'=>'success', "feature_list" => $feature_list);
        }
        else
        {
            $fields = array('status'=>'error');
        }
    }
    else
    {
        $fields = array('status'=>'missing id');
    }
    header('Content-Type: application/json');
    echo json_encode($fields);
}
else if($_REQUEST['action'] == "set_features"){
    if(isset($_REQUEST['camera_id']) && !empty($_REQUEST['camera_id']) && isset($_REQUEST['alert_type_id']) && !empty($_REQUEST['alert_type_id'])){
        $cam_id = $db->escape($_REQUEST['camera_id']);
        $alert_type_id = $db->escape($_REQUEST['alert_type_id']);
        $db->where("cam_id", $cam_id);
        $db->where("alert_type_id", $alert_type_id);
        //$db->where("user_id", $_REQUEST['user_id']);    
        $feature = $db->getOne("camera_features", null, "cam_id, alert_type_id, status");
       
        if(!empty($feature))
        {
            $db->where("feature_id", $feature['feature_id']);
            if($db->delete('camera_features'))
                $fields = array('status'=>'off');
            else    
                $fields = array('status'=>'error');
        }
        else
        {
            // $data = array(
            //     "cam_id" => intval($_REQUEST['camera_id']),
            //     "alert_type_id" => intval($_REQUEST['alert_type_id'])                
            // );
            // $new_id = $db->insert("camera_features",$data);
            // if(!empty($new_id))
            // {
            //     $fields = array('status'=> 'success');            
            // }
            // else
            //     $fields = array('status'=>'error');
            $new_id = $db->rawQuery("insert into camera_features(`cam_id`,`alert_type_id`,`status`,`date_time`) values(".$cam_id.",".$alert_type_id.",1,'".date('Y-m-d H:i:s')."')");
            $fields = array('status'=> 'on');
        }
    }
    else
    {
        $fields = array('status'=>'missing id');
    }
    header('Content-Type: application/json');
    echo json_encode($fields);
}
else if($_REQUEST['action'] == "get_alerts"){
    if(isset($_REQUEST['camera_id']) && !empty($_REQUEST['camera_id'])){
        
        $db->where("cam_id", $_REQUEST['camera_id']);        
        $db->join("alert_types at", "at.alert_type_id = ca.alert_type_id","LEFT");        
            
        $feature_list = $db->get("camera_alerts ca", null, "ca.cam_id, ca.alert_type_id, at.alert_name, ca.status");
       
        if(count($feature_list) > 0)
        {
            $fields = array('status'=>'success', "alerts_list" => $feature_list);
        }
        else
        {
            $fields = array('status'=>'error');
        }
    }
    else
    {
        $fields = array('status'=>'missing id');
    }
    header('Content-Type: application/json');
    echo json_encode($fields);
}
else if($_REQUEST['action'] == "get_last_detected"){
    if((isset($_REQUEST['camera_id']) && !empty($_REQUEST['camera_id'])) && (isset($_REQUEST['alert_type_id']) && !empty($_REQUEST['alert_type_id'])) ){
        
        $cam_id = $db->escape($_REQUEST['camera_id']);
        $alert_type_id = $db->escape($_REQUEST['alert_type_id']);
        $db->where("cam_id", $cam_id);
        $db->where("alert_type_id", $alert_type_id);
        $last_alert = $db->getOne("camera_alerts", "cam_id, alert_type_id, status");
        // var_dump($db->getLastQuery());
        if(!empty($last_alert))
        {
            if($last_alert['status'] == 1)
                $fields = array('status'=>1);
            else
                $fields = array('status'=>2);
        }
        else
            $fields = array('status'=>2);
    }
    else
    {
        $fields = array('status'=>'missing ids');
    }
    header('Content-Type: application/json');
    echo json_encode($fields);
}
else if($_REQUEST['action'] == "create_alert"){
    if((isset($_REQUEST['camera_id']) && !empty($_REQUEST['camera_id'])) && (isset($_REQUEST['alert_type_id']) && !empty($_REQUEST['alert_type_id'])) ){
        
        $cam_id = $db->escape($_REQUEST['camera_id']);
        $alert_type_id = $db->escape($_REQUEST['alert_type_id']);
        $data = array(
            "alert_type_id" => intval($alert_type_id),
            "cam_id" => intval($cam_id)
        );
        
        //var_dump($data);
        // var_dump($db->getLastQuery());
        $new_id = $db->rawQuery("insert into camera_alerts(`alert_type_id`,`cam_id`,`status`,`date_time`) values(".$alert_type_id.",".$cam_id.",1,'".date('Y-m-d H:i:s')."')");
        // $new_id = $db->insert("camera_alerts",$data);
        //var_dump($db->getLastQuery());
       // var_dump($new_id);
        // if(!empty($new_id))
        // {
            $fields = array('status'=> 'success');            
        // }
        // else
        //     $fields = array('status'=>'error');
    }
    else
    {
        $fields = array('status'=>'missing ids');
    }
    header('Content-Type: application/json');
    echo json_encode($fields);
}
?>