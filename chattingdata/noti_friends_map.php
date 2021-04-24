<?php
require "../dbconnect/connection.php";

//$_POST 는 헤더에 포함된 POST data를 파싱한 결과를 가지지만,

$friends = $_POST['friends'];

$friendsMap = array();

//친구리스트에 2명이상 있을경우 '/'로 구분되어 있는 아이디를 분리해준다.
if(strpos($friends, '/') !== false) {
    $friendsMap = explode('/', $friends);
} else {
    //친구리스트에 한명이 있는경우는 바로 배열에 넣어준다.
    array_push($friendsMap, $friends);
}


//DB insert
if ($conn)
{
    $profileMap = array();
    
    for($i = 0; $i < count($friendsMap); $i++) {

        $sql_user_profile = "SELECT user_id, user_profile FROM user where user_id = '$friendsMap[$i]'";

        $result = mysqli_query($conn, $sql_user_profile);

        $row = mysqli_fetch_assoc($result);

        if(!isset($row['user_profile'])) { //DB의 NULL값을 가져오면 
            $row['user_profile'] = "default";
        }

        array_push($profileMap,$row);

    }
    print_r(json_encode($profileMap, JSON_PRETTY_PRINT));
    // print_r($profileMap);
    // exit();
}
    
else {
        
        echo "DB Fail";
}

mysqli_close($conn);
?>/