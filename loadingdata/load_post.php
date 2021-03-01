<?php
require "../dbconnect/connection.php";


$dir = $_SERVER['DOCUMENT_ROOT']."/uploads/post_images/"; // $_SERVER['DOCUMENT_ROOT'] => /var/www/html
$content = $_POST["content"];
$tag =$_POST["tag"];
$index = $_POST["index"]; //user_id
$imgSrc = "/uploads/post_images/";

//DB insert
if ($conn)
{

    //$sql_load = "select id, user_id, text_content, img_src, hashtag, hit_view, hit_like, created_date from diary_page where user_id = $index";

   // $sql_load = "select * from diary_page where post_status = 1";
    $sql_load = "SELECT user.user_profile, dp.id, dp.user_id, dp.img_src, dp.created_date 
                    FROM user JOIN diary_page dp
                    ON user.user_id = dp.user_id
                    WHERE post_status = 1";
    /*
     String no= jsonObject.getString("id"); //no가 문자열이라서 바꿔야함.
                        String name=jsonObject.getString("user_id");
                        //String msg=jsonObject.getString("text_content");
                        String imgPath=jsonObject.getString("img_src");
                        String date=jsonObject.getString("created_date");
    */
    $result = mysqli_query($conn, $sql_load);


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
