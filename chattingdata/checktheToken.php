<?php
 require "../dbconnect/connection.php";

 $userId = $_POST["userId"];
 



 if ($conn) {
         
        $sqlCheckId = "SELECT fcm_token FROM user WHERE user_id = '$userId'";
        $userIdQuery = mysqli_query($conn, $sqlCheckId);

       

        if ($userIdQuery) {
     
                    $row = mysqli_fetch_array($userIdQuery, MYSQLI_ASSOC);

                    $result = $row["fcm_token"];
                   
                

                echo $result;
            } else {
                echo "Wrong Password";
            }

     
 } else {
     echo "Connection Error";
 }


?>