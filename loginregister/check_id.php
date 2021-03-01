<?php
 require "../dbconnect/connection.php";

 $userId = $_POST["userId"];


 if ($conn) {
        /* 
          데이터베이스에서 쿼리를 수행합니다. 만약 실패할 시 FALSE를 반환합니다. 
          성공적으로 SELECT, SHOW, DESCRIBE, EXPLAIN 쿼리를 수행했다면 mysqli_result object를 반환합니다. 
          다른 쿼리를 성공적으로 수행했다면 TRUE를 반환합니다.
        */

        $sqlCheckUserId= "SELECT * FROM user WHERE user_id LIKE '$userId'";
        $usernameQuery = mysqli_query($conn, $sqlCheckUserId);

        if (mysqli_num_rows($usernameQuery) > 0) {
            echo "The ID is already used, type another one";
        } else {
            echo "This ID is Available";
        }
    }
 else {
     echo "Connection Error";
 }

 
?>