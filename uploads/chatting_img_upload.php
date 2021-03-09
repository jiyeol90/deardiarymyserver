<?php
require "../dbconnect/connection.php";


$dir = $_SERVER['DOCUMENT_ROOT']."/uploads/chatting_images/"; // $_SERVER['DOCUMENT_ROOT'] => /var/www/html
$userId = $_POST["userId"];
$imgSrc = "/uploads/chatting_images/";

$file_size = $_FILES['image']['size'];

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
                //파일의 width , height 를 알려주자
                $file_info = getimagesize($file);

                $arr = array(
                    'status' => 1,
                    'message' => "File Uploaded",
                    'file_name' => $file_name,
                    'file_path' => $imgSrc,
                    'fileUploa_dId' => $userId,
                    'file_size' => $file_size,
                    'file_width' => $file_info[0],
                    'file_height' => $file_info[1]
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

}


//DB insert
// if ($conn)
// {

//         $sql_post = "INSERT INTO diary_page (user_id, text_content, img_src, hashtag, hit_view, hit_like, created_date) VALUES('$userId','$content', '$imgSrc', '$tag', 0, 0, now())";

//         if (mysqli_query($conn, $sql_post))
//         {
//             $arr = array(
//                 'status' => 1,
//                 'message' => $content
//             );
//         }
//         else
//         {
//             $arr = array(
//                 'status' => 0,
//                 'message' => "DB Fail"
//             );
//         }
  

// }
// else
// {
//     $arr = array(
//         'status' => 0,
//         'message' => "DB Connection Error"
//     );
// }

print_r(json_encode($arr, JSON_PRETTY_PRINT));

//mysqli_close($conn);
?>