<?php
require "../dbconnect/connection.php";

$userId = $_POST["userId"];
$friendId = $_POST["friendId"];

//DB insert
if ($conn)
{

    $sql_friend_or_not = "SELECT count(*)friendOrNot FROM follow WHERE user_id = '$userId' AND friend_id = '$friendId'";
    $result = mysqli_query($conn, $sql_friend_or_not);
    
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

    if($row['friendOrNot'] === '0') {
        $sql_friend_upload = "INSERT INTO follow (user_id, friend_id, follow_status, created_date) VALUE ('$userId', '$friendId', 1, now())";
       
        $result = mysqli_query($conn, $sql_friend_upload);
    
    
        if ($result)
        {
          echo "success";
        }
        else
        {
           echo "insertion fail";
        }
    } else {

        $sql_friend_upload = "UPDATE follow SET follow_status = 1, created_date = now() WHERE user_id = '$userId' AND friend_id = '$friendId'";
        $result = mysqli_query($conn, $sql_friend_upload);


        if ($result)
        {
          echo "success";
        }
        else
        {
           echo "upload fail";
        }
    
    }


  


}

else {
        echo "DB Fail";
}

mysqli_close($conn);
?>