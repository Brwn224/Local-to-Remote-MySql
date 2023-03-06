<?php
if(isset($_POST['pwd']) && isset($_POST['s64']) && isset($_POST['db'])){
    $post_data = true;
}
if(isset($_GET['pwd']) || isset($_POST['pwd'])){
    if($post_data == true){
        $pwd = htmlspecialchars($_POST['pwd']);
    } else{
        $pwd = htmlspecialchars($_GET['pwd']);
    }
    if($pwd == 0000){ //Define a password here

        //set headers
        header('Access-Control-Allow-Origin: *');
        header("Content-type: application/json; charset=utf-8");

        //defining variables
        $json = array();

        if($post_data == true){                         //If post request
            $str = htmlspecialchars($_POST['s64']);
            $database = htmlspecialchars($_POST['db']);
        } else {                                        //If get request
            $str = htmlspecialchars($_GET['s64']);
            $database = htmlspecialchars($_GET['db']);
        }

        $str = base64_decode($str);
        $sql = $str;

        //Connection data / Fill out your connection details
        $servername = "localhost";
        $username = "root";
        $password = '';
        $dbname = $database;
        $port = "3306";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname, $port);

        // Check connection
        if ($conn->connect_error) {
            $json['response_status'] = "Unable to connect the database";
            $json['response_message'] = $conn->connect_error;
        }

        //set connection charset
        mysqli_set_charset($conn, "utf8");
        $conn->set_charset("utf8");

        //query
        $result = $conn->query($sql);
        if(str_contains($sql, "SELECT")){ //Select data
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $json[] = $row;
                }
            } else{
                $json['row_nums'] = 0;
            }
            //response
            printf(json_encode($json, JSON_UNESCAPED_UNICODE));

        } else { //Upload data
            if($result){
                $json['response_status'] = 1;
                $json['http_response_code'] = 200;
                $json['response_message'] = "Query run successfully";
            } else{
                $json['response_status'] = 0;
                $json['response_message'] = json_encode($result, JSON_UNESCAPED_UNICODE);
            }
        }
      
        //close the connection
        $conn->close();
    }
} else { //If no pwd
    http_response_code(403);
    die('Forbidden');
}
?>
