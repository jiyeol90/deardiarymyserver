<?php
require "../dbconnect/connection.php";


$roomId = $_POST['roomId'];
$myId = $_POST['myId'];
$friendId = $_POST['friendId'];
$contentType = $_POST['contentType'];
$content = $_POST['content'];

$result_message;
$user_status;
$target_token;

//DB insert
if ($conn)
{
   
    
    $sql_message_upload = "INSERT INTO message(room_id, user_id, content_type, content, read_status, created_date) VALUES ('$roomId', '$myId', '$contentType', '$content', 0, now())";

   
    $result = mysqli_query($conn, $sql_message_upload);

    if($result) {

      $result_message = $contentType." 저장이 완료되었습니다.";
    } else {
        $result_message = $contentType." 저장 실패";
    }

    $fcm_message_status = "SELECT pi.user_status, user.fcm_token FROM participate_in pi JOIN user 
                                                                 ON pi.user_id = user.user_id  
                                                                 WHERE pi.room_id = '$roomId' AND pi.user_id = '$friendId'";

    $status_result = mysqli_query($conn, $fcm_message_status);

    $row = mysqli_fetch_array($status_result, MYSQLI_ASSOC);

    // $user_status = $row['user_status'];

   
    if($row['user_status'] === '1') {
        $user_status = '부재중';
        $target_token = $row['fcm_token'];

        $ch = curl_init("https://fcm.googleapis.com/fcm/send");
        $header = array("Content-Type:application/json", 
        "Authorization:key=AAAA2ngioU0:APA91bF4SlN0aHNap2nt9QWm6s5kdoZYUynVVQmQ6gSvZ7SiS9XKpWf5B2tUkfTqDs97Gg9mPuH7oNqgqXxsaasLO9i0sHhc1lTlCRb8Ai6b_Xo085j73SGXYIo5OkV--GPrqp0KiZyl");
        $data = json_encode(array(
            "to" => $target_token,
            "data" => array(
                "title"   => $myId,
                "message" => $content)
                ));

        print_r($data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_exec($ch);

        //echo $result_message.'/'.$user_status.'/'.$target_token;
        exit();
    }
    

    echo $result_messge;
   
}
    
else {
        
        echo "DB Fail";
}

mysqli_close($conn);
?>