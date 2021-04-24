<?php
require "../dbconnect/connection.php";


$dir = $_SERVER['DOCUMENT_ROOT']."/uploads/profile_images/"; // $_SERVER['DOCUMENT_ROOT'] => /var/www/html
$userText = $_POST['userText'];
$userId = $_POST['userId'];
$imgSrc = "/uploads/profile_images/";



if (isset($_FILES['image']['name']))

{

    //time() 은 1970년 1월 1일 0시 0분 0초부터 지금까지 지나온 초를 정수형태로 리턴해주는 함수
    //basename() 은 순수 파일 이름만 반환하는 함수 -> ex) '/uploadfile/sameimage.jpg' -> 'smpleimage.jpg'
    $file_name = time().basename($_FILES['image']['name']);
    //pathinfo : 파일 경로, 파일명, 파일 확장자, 파일 이름을 배열로
    $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $imgSrc = $imgSrc.$file_name;

    if ($extension == 'png' || $extension == 'jpg' || $extension == 'jpeg')
    {
        $file = $dir.$file_name;
            
           //파일업로드 성공시
            if (move_uploaded_file($_FILES['image']['tmp_name'], $file))
            
            {
                $arr = array(
                    'status' => 1,
                    'message' => "File Uploaded",
                    'file_name' => $file_name,
                    'file' => $file,
                );
            }

            else

            {
                $arr = array(
                    'status' => 0,
                    'error' => "Something Went Wrong Please Retry",
                    'file_name' => $file_name
                );
               
            }
    
    }

    else

    {
        $arr = array(
            'status' => 0,
            'error' => "Only .png, .jpg and .jpeg format are accepted"
        );
      
    }

}

else
{
    $arr = array(
        'status' => 1,
        'message' => "Please try Post Method"
    );

    exit();

}


//DB insert
if ($conn)
{
    $sql_post;

    if(isset($_FILES['image']['name'])) {
        if(isset($userText)) {
            //새로운 프로필 사진과 소개글을 업데이트
            $sql_post = "UPDATE user SET user_profile = '$imgSrc', user_text = '$userText' WHERE user_id = '$userId'";
            
        } else {
            //새로운 프로필 사진만 업데이트 
            $sql_post = "UPDATE user SET user_profile = '$imgSrc' WHERE user_id = '$userId'";
        }
    } else {
        if(isset($userText)) {
            //새로운 소개글만 업데이트
            $sql_post = "UPDATE user SET user_text = '$userText' WHERE user_id = '$userId'";
            
        } else {
            
            $arr = array(
                'status' => 1,
                'message' => "입력한 값이 없습니다."
            );

            print_r(json_encode($arr, JSON_PRETTY_PRINT));
            exit();
        }
    }

        if (mysqli_query($conn, $sql_post))
        {
            $arr = array(
                'status' => 1,
                'message' => "Insert Success"
            );
        }
        else
        {
            $arr = array(
                'status' => 0,
                'message' => "DB Fail"
            );
        }  

}
else
{
    $arr = array(
        'status' => 0,
        'message' => "DB Connection Error"
    );
}

print_r(json_encode($arr, JSON_PRETTY_PRINT));

mysqli_close($conn);
?>