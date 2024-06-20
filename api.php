<?php

include 'connection.php';
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


if($_REQUEST['action'] == "get_features"){
    if(isset($_REQUEST['camera_id']) && !empty($_REQUEST['camera_id'])){
        
        $db->where("cf.cam_id", $_REQUEST['camera_id']);
        $db->join("alert_types at", "at.alert_type_id = cf.alert_type_id","LEFT");
        //$db->where("user_id", $_REQUEST['user_id']);    
        $feature_list = $db->get("camera_features cf", null, "cf.cam_id, cf.alert_type_id, at.alert_name, cf.status");
       
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
        
        $db->where("ca.cam_id", $_REQUEST['camera_id']);        
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
        $db->where("camera_id", $cam_id);
        $db->where("alert_type", $alert_type_id);
        $db->orderBy("alert_id","DESC");
        $last_alert = $db->getOne("app1_camera_alerts", "camera_id, alert_type, status");
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
else if($_REQUEST['action'] == "update_status"){
    if((isset($_REQUEST['camera_id']) && !empty($_REQUEST['camera_id'])) && (isset($_REQUEST['alert_type_id']) && !empty($_REQUEST['alert_type_id'])) ){
        
        $cam_id = $db->escape($_REQUEST['camera_id']);
        $alert_type_id = $db->escape($_REQUEST['alert_type_id']);
        $db->where("cam_id", $cam_id);
        $db->where("alert_type_id", $alert_type_id);
        $last_alert = $db->getOne("camera_alerts", "cam_id, alert_type_id, status");
        $new_status = 0;
        // var_dump($db->getLastQuery());
        if(!empty($last_alert))
        {
            if(isset($_REQUEST['undo']) && $_REQUEST['undo'] == 1)
            {
                $new_status = (int)$last_alert['status'] - 1;
            }
            else
            {
                $new_status = (int)$last_alert['status'] + 1;
            }

            $data = array(
                'status' => $new_status
                );
            
            $db->where("cam_id", $cam_id);
            $db->where("alert_type_id", $alert_type_id);
            if($db->update('camera_alerts',$data))
                $fields = array('status'=>'success');
            else    
                $fields = array('status'=>'error');
            
        }
        else
            $fields = array('status'=>'error');
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
            "alert_type" => intval($alert_type_id),
            "camera_id" => intval($cam_id)
        );
        
        //var_dump($data);
        // var_dump($db->getLastQuery());
        $new_id = $db->rawQuery("insert into app1_camera_alerts(`alert_type`,`camera_id`,`status`,`date_time`) values(".$alert_type_id.",".$cam_id.",1,'".date('Y-m-d H:i:s')."')");
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
// else if($_REQUEST['action'] == "create_alert"){
//     if((isset($_REQUEST['camera_id']) && !empty($_REQUEST['camera_id'])) && (isset($_REQUEST['alert_type_id']) && !empty($_REQUEST['alert_type_id'])) ){

//         $cam_id = $db->escape($_REQUEST['camera_id']);
//         $alert_type_id = $db->escape($_REQUEST['alert_type_id']);

//         // Check if the camera_id exists
//         $db->where("id", $cam_id);
//         $camera_exists = $db->getOne("app1_camera", "id");

//         if ($camera_exists) {
//             $data = array(
//                 "alert_type_id" => intval($alert_type_id),
//                 "camera_id" => intval($cam_id)
//             );

//             $status = 1; // assuming status is set to '1' for a new alert
//             $date_time = date('Y-m-d H:i:s');

//             // Insert query using prepared statements to prevent SQL injection
//             $insert_query = "INSERT INTO app1_camera_alerts (`alert_type`, `camera_id`, `status`, `date_time`) VALUES (?, ?, ?, ?)";

//             $result = $db->rawQuery($insert_query, array($alert_type_id, $cam_id, $status, $date_time));

//             // Check if insert was successful
//             if($result) {
//                 $fields = array('status'=> 'success');
//             } else {
//                 // Retrieve and log the MySQL error message
//                 $error_message = $db->getLastError();
//                 $fields = array('status'=>'error', 'message' => $error_message);
//                 error_log("MySQL Error: " . $error_message);
//             }
//         } else {
//             $fields = array('status'=>'error', 'message' => 'Invalid camera_id');
//         }
//     } else {
//         $fields = array('status'=>'missing ids');
//     }
//     header('Content-Type: application/json');
//     echo json_encode($fields);
// }

?>