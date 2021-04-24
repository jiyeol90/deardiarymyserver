<?php
require "../dbconnect/connection.php";

$userId = $_POST["userId"];

//DB insert
if ($conn)
{

    //$sql_load = "select id, user_id, text_content, img_src, hashtag, hit_view, hit_like, created_date from diary_page where user_id = $index";

    $sql_load_friend = "SELECT follow.friend_id, user.user_profile
                            From follow JOIN user
                            ON follow.friend_id = user.user_id
                            WHERE follow.user_id = '$userId' and follow.follow_status = 1";
    
    $result = mysqli_query($conn, $sql_load_friend);

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