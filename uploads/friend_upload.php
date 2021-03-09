<?php
require "../dbconnect/connection.php";

$userId = $_POST["userId"];
$friendId = $_POST["friendId"];

//DB insert
if ($conn)
{

    $sql_friend_upload = "INSERT INTO follow (user_id, friend_user_id, friend_status, created_date) VALUE ('$userId', '$friendId', 1, now())";
    /*
     String no= jsonObject.getString("id"); //no가 문자열이라서 바꿔야함.
                        String name=jsonObject.getString("user_id");
                        //String msg=jsonObject.getString("text_content");
                        String imgPath=jsonObject.getString("img_src");
                        String date=jsonObject.getString("created_date");
    */
    $result = mysqli_query($conn, $sql_friend_upload);


    if ($result)
    {
      echo "success";
    }
    else
    {
       echo "insertion fail";
    }


}

else {
        echo "DB Fail";
}

mysqli_close($conn);
?>