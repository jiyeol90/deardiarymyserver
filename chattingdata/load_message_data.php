<?php
require "../dbconnect/connection.php";


$roomId = $_POST['roomId'];

//DB insert
if ($conn)
{

    
    $sql_message_load = "SELECT msg.user_id, user.user_profile, msg.content_type, msg.content, msg.created_date 
                         FROM  message  msg JOIN user
                         ON msg.user_id = user.user_id
                         WHERE room_id = '$roomId'
                         ORDER BY msg.created_date";
   
    $result = mysqli_query($conn, $sql_message_load);


    if($result) {

        $rowCnt = mysqli_num_rows($result);

        $arr = array();

        for($i = 0; $i < $rowCnt; $i++) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

            $arr[$i] = $row;
        }

        print_r(json_encode($arr, JSON_PRETTY_PRINT));
        
    }

}

else {
        echo "DB Fail";
}

mysqli_close($conn);
?>