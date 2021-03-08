<?php
require "../dbconnect/connection.php";


$roomId = $_POST['roomId'];
$userId = $_POST['userId'];
$contentType = $_POST['contentType'];
$content = $_POST['content'];

$result_message;
//DB insert
if ($conn)
{
   
    
    $sql_message_upload = "INSERT INTO message(room_id, user_id, content_type, content, read_status, created_date) VALUES ('$roomId', '$userId', '$contentType', '$content', 0, now())";

   
    $result = mysqli_query($conn, $sql_message_upload);

    if($result) {

      $result_message = $contentType." 저장이 완료되었습니다.";
    } else {
        $result_message = $contentType." 저장 실패";
    }

    echo $result_message;
}
    
else {
        
        echo "DB Fail";
}

mysqli_close($conn);
?>
