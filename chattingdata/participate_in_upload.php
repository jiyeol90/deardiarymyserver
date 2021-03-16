<?php
require "../dbconnect/connection.php";

//$_POST 는 헤더에 포함된 POST data를 파싱한 결과를 가지지만,

$roomId = $_POST['roomId'];
$myId = $_POST['myId'];
$contentType = $_POST['contentType'];
$content = $_POST['content'];

$result_message;
//DB insert
if ($conn)
{
    //이미 참여했던 방인지 조회한다.
    $sql_search_room = "SELECT * FROM participate_in WHERE room_id = '$roomId' AND user_id = '$myId'";
   
    $result = mysqli_query($conn, $sql_search_room);

    if(mysqli_num_rows($result) == 0) {

        $sql_participate = "INSERT INTO participate_in(room_id, user_id, invited_date, user_status) VALUES ('$roomId', '$myId', now(), 0)";
    
        if(mysqli_query($conn, $sql_participate)) {

            $result_message = $contentType.' 을 성공하였습니다.';
           
        
        }else {
            $result_message = $contentType.' 을 실패 하였습니다.';
            
           
           
        }
    } else {
        $result_message = $contentType.' 들어왔던 방 입니다.';
    }

    echo $result_message;
}
    
else {
        
        echo "DB Fail";
}

mysqli_close($conn);
?>