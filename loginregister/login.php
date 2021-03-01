<?php
 require "../dbconnect/connection.php";

 $userId = $_POST["userId"];
 $password = $_POST["psw"];



 if ($conn) {
         
        $sqlCheckId = "SELECT * FROM user WHERE user_id = '$userId'";
        $userIdQuery = mysqli_query($conn, $sqlCheckId);

        if (mysqli_num_rows($userIdQuery) > 0) {
            
            $sqlLogin = "SELECT * FROM user WHERE user_id = '$userId' AND password = MD5('$password')";
            $loginQuery = mysqli_query($conn, $sqlLogin);

            if (mysqli_num_rows($loginQuery) > 0) {

                $rowCnt = mysqli_num_rows($loginQuery);
                $arr = array();

                for($i = 0; $i < $rowCnt; $i++) {
                    $row = mysqli_fetch_array($loginQuery, MYSQLI_ASSOC);

                    $arr[$i]["id"] = $row["id"];
                    $arr[$i]["user_id"] = $row["user_id"];
                    $arr[$i]["user_name"] = $row["user_name"];
                }

                print_r(json_encode($arr, JSON_PRETTY_PRINT));
            } else {
                echo "Wrong Password";
            }

        } else {
            echo "This ID is not registered";
        }

     
 } else {
     echo "Connection Error";
 }


?>