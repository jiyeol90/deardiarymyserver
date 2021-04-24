<?php
require "../dbconnect/connection.php";

$roomId = $_POST['roomId'];
$myId = $_POST['myId'];
$friendId = $_POST['friendId'];
$contentType = $_POST['contentType'];
$content = $_POST['content'];
$time = $_POST['time'];

// $timestamp = date('Y-m-d H:i:s', strtotime($time)); 
// echo gettype($timestamp);
// exit();

$isOneonOne = true;

/*
roomId : 4
myId :james
friendId : jiyeol90/jar
contentType : txt, 
content : hey
*/

//친구리스트에 1명 이상의 아이디를 가지고 있는 경우
if (strpos($friendId, '/') !== false)
{

    $friendsList = explode('/', $friendId);

    // echo '친구리스트 명수 : '. count($friendsList).' => ';
    // for($i = 0; $i < count($friendsList); $i++) {
    //     echo '친구 ['.$i.'] :'.$friendsList[$i]. ' ';
    // }
    // print_r($friendsList);
    // exit();

    $isOneonOne = false;
}

$result_message;
$user_status;
$target_token;

//DB insert
if ($conn)
{

    $sql_message_upload = "INSERT INTO message(room_id, user_id, content_type, content, read_status, created_date) VALUES ('$roomId', '$myId', '$contentType', '$content', 0, STR_TO_DATE('$time', '%Y-%m-%d %H:%i:%s'))";

    $result = mysqli_query($conn, $sql_message_upload);

    if ($result)
    {

        $result_message = $contentType . " 저장이 완료되었습니다.";
    }
    else
    {
        $result_message = $contentType . " 저장 실패";
    }

    // echo $result_message;
    // exit();
    if ($isOneonOne)
    {
        $fcm_message_status = "SELECT pi.user_status, user.fcm_token FROM participate_in pi JOIN user 
                                                                 ON pi.user_id = user.user_id  
                                                                 WHERE pi.room_id = '$roomId' AND pi.user_id = '$friendId'";


        $status_result = mysqli_query($conn, $fcm_message_status);

        $row = mysqli_fetch_array($status_result, MYSQLI_ASSOC);

        // $user_status = $row['user_status'];
        

        if ($row['user_status'] === '1')
        {
            $user_status = '부재중';
            $target_token = $row['fcm_token'];

            $ch = curl_init("https://fcm.googleapis.com/fcm/send");
            $header = array(
                "Content-Type:application/json",
                "Authorization:key=AAAA2ngioU0:APA91bF4SlN0aHNap2nt9QWm6s5kdoZYUynVVQmQ6gSvZ7SiS9XKpWf5B2tUkfTqDs97Gg9mPuH7oNqgqXxsaasLO9i0sHhc1lTlCRb8Ai6b_Xo085j73SGXYIo5OkV--GPrqp0KiZyl"
            );
            $data = json_encode(array(
                "to" => $target_token,
                "data" => array(
                    "title" => $myId,
                    "message" => $content,
                    "contentType" => $contentType,
                    "roomId" => $roomId,
                    "receive" => $friendId,
                    "type" => '1'
                ) // 메시지를 받는 아이디
                
            ));

            //print_r($data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_exec($ch);

            //echo $result_message.'/'.$user_status.'/'.$target_token;
            //exit();
            
        }
    }
    else
    {
        /////////////////////////////////////////////////////////////////////////////단체방에서 FCM 보내기
         $fcm_message_status = "SELECT pi.user_id, pi.user_status, user.fcm_token FROM participate_in pi JOIN user 
                                                                 ON pi.user_id = user.user_id  
                                                                 WHERE pi.room_id = '$roomId' AND pi.user_id NOT IN ('$myId')";

/*
user_id     user_status     fcm_token
jar         1               cEbJq-YOS8Cyo1BYr_N6PY:APA91bEqa-4eHCqL0jqEIuvRVEU...
jiyeol90    1               f7qGK2UuR0a4J3PoRfBH_B:APA91bHRbwW-21N8pXAvUDdn4dj...

=> 한번에 여러명에게 보내는 방법이 있지만 그럴경우 받는 상대가 앱을 끌경우 제대로된 정보를 알 수 가 없다. 그러므로
   멀티캐스트로 한번에 보내지 말고 보낼 상대의 수만큼 나눠서 보내주도록 하자.
*/                                                                


        $status_result = mysqli_query($conn, $fcm_message_status);

        $multicast = array();

        while($row = mysqli_fetch_array($status_result, MYSQLI_ASSOC)) {
            array_push($multicast, $row);
        }
        // print_r($multicast);
        // echo count($multicast);
        // exit();
     
        for($i = 0; $i < count($multicast); $i++) {

            if($multicast[$i]['user_status'] === '1') {

            $user_status = '부재중';
            $target_token = $multicast[$i]['fcm_token'];

            $ch = curl_init("https://fcm.googleapis.com/fcm/send");
            $header = array(
                "Content-Type:application/json",
                "Authorization:key=AAAA2ngioU0:APA91bF4SlN0aHNap2nt9QWm6s5kdoZYUynVVQmQ6gSvZ7SiS9XKpWf5B2tUkfTqDs97Gg9mPuH7oNqgqXxsaasLO9i0sHhc1lTlCRb8Ai6b_Xo085j73SGXYIo5OkV--GPrqp0KiZyl"
            );
            $data = json_encode(array(
                "to" => $target_token,
                "data" => array(
                    "title" => $myId,
                    "message" => $content,
                    "contentType" => $contentType,
                    "receive" => $multicast[$i]['user_id'],
                    "roomId" => $roomId,
                    "type" => '2',
                    "friendList" => $friendId
                ) // 메시지를 받는 아이디
                
            ));

            print_r($data);
            // exit();
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_exec($ch);
            }
        }
       
        
    }

    echo $result_message;

}

else
{

    echo "DB Fail";
}

mysqli_close($conn);
?>
