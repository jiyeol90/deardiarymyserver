<?php
 require "../dbconnect/connection.php";

 $userId = $_POST["userId"];
 $password = $_POST["psw"];
 $userName = $_POST["userName"];
 $email = $_POST["email"];
 $gender = $_POST["gender"];

//  아래는 미리 박아둔 데이터들을 써서 앱이 잘 작동하는지 확인하려고 임의로 만든 변수들이다.
//  $username = "test";
//  $email = "test@naver.com";
//  $password = "1234567";
//  $mobile = "011000000";
//  $gender = "Male";

//이메일 주소를 입력받았을 때 입력받은 이메일이 이메일 주소 형식에 맞게 입력했는지 확인하기 위해서 사용하는 함수
 $isValidEmail = filter_var($email, FILTER_VALIDATE_EMAIL); 

 if ($conn) {
     
    if ($isValidEmail === false) {

        echo "This Email is not valid";
    }
    else {
        /* 
          데이터베이스에서 쿼리를 수행합니다. 만약 실패할 시 FALSE를 반환합니다. 
          성공적으로 SELECT, SHOW, DESCRIBE, EXPLAIN 쿼리를 수행했다면 mysqli_result object를 반환합니다. 
          다른 쿼리를 성공적으로 수행했다면 TRUE를 반환합니다.
        */

        $sqlCheckEmail = "SELECT * FROM user WHERE email LIKE '$email'";
        $userEmailQuery = mysqli_query($conn, $sqlCheckEmail);

        if (mysqli_num_rows($userEmailQuery) > 0) {
            echo "This email is already registered, type another Email";
        }

        else {

            $sql_register = "INSERT INTO user (user_id, password, user_name, email, gender, user_status, join_date) VALUES('$userId', MD5('$password'), '$userName', '$email', '$gender', 1, now())";

            if (mysqli_query($conn, $sql_register)) {
                echo "Successfully Registered";
            } else {
                echo "Failed to register";
            }

        }

    }

 }
 else {
     echo "Connection Error";
 }



?>