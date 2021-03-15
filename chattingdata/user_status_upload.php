<?php
require "../dbconnect/connection.php";

//$_POST 는 헤더에 포함된 POST data를 파싱한 결과를 가지지만,

$myId = $_POST['myId'];
$roomId = $_POST['roomId'];


//DB insert
if ($conn)
{
    //이미 참여했던 방인지 조회한다.
    $sql_participate_in_status_update = "UPDATE participate_in SET user_status = 1 WHERE user_id = '$myId' AND room_id = '$roomId'";
    $participateResult = mysqli_query($conn, $sql_participate_in_status_update);

    if($participateResult) {

        echo "user_status_success";

    } else {
       
        echo "user_status_error";
    }

}
    
else {
        
        echo "DB Fail";
}

mysqli_close($conn);
?>