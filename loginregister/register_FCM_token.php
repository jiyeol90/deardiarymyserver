<?php
 require "../dbconnect/connection.php";

 $myId = $_POST["myId"];
 $token = $_POST["token"];


 if ($conn) {
        /* 
          데이터베이스에서 쿼리를 수행합니다. 만약 실패할 시 FALSE를 반환합니다. 
          성공적으로 SELECT, SHOW, DESCRIBE, EXPLAIN 쿼리를 수행했다면 mysqli_result object를 반환합니다. 
          다른 쿼리를 성공적으로 수행했다면 TRUE를 반환합니다.
        */

        $sqlUpdateToken= "UPDATE user SET fcm_token = '$token' WHERE user_id = '$myId'";
        $userTokenQuery = mysqli_query($conn, $sqlUpdateToken);

        if ($userTokenQuery){
            echo "Token Success";
        } else {
            echo "Token Failed";
        }
    }
 else {
     echo "Connection Error";
 }

 
?>