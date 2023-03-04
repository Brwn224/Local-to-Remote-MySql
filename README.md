# Remote MySQL

## About this project
In this solution I solved the problem of remote access to a local database on a server. 

## How to use it
This only works if you have a rented hosting and a database. If your database is only available locally on the server, but you want to query and upload data remotely, this project might be able to help you.

## How 'remote_mysql.php' works?
'remote_mysql.php' works on a simple principle. With the GET method you get the text of the query embedded in a URL with base64 decoding. It decodes this and then contacts the database. Runs the query and prints the response message embedded in a JSON structure.

## What parameters does it need?
First parameter is: **s64** : This is the base64 decoded query string.
Second parameter is: **db**: In this parameter we need to specify the name of the database from which we want to retrieve the data.
Third parameter is: **pwd**: This parameter must contain the password predefined in 'remote_mysql.php'.

## Example
Suppose you upload this php file to the root folder of your hosting.

> You have this query: **SELECT * FROM users WHERE 1** In Base64 format: **U0VMRUNUICogRlJPTSB1c2VycyBXSEVSRSAx**
> You have this database name: exampleDb1
> Your domain: example.com
> Your password: 1234

The URL is: **https://example.com/remote_mysql.php?s64=U0VMRUNUICogRlJPTSB1c2VycyBXSEVSRSAx&db=exampleDb1&pwd=1234**

## How to send query to this file:
-  Build the sql query and save it to a string variable
-  Decode the query string to Base64 format
-  Add the encoded query to the s64 parameter
-  Call the finished URL

# PHP URL call example
```
$data="";
$sql = "SELECT * FROM users WHERE 1";
$s64 = base64_encode($sql);
$url = "https://dev.horvath-barna.hu/remote_mysql.php?s64=".$s64."&db=exampledb1&pwd=0000";
$data = file_get_contents($url);
$data = json_decode($data); //To array
```

# remote_mysql.php
```
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
    if($pwd == 0000){ //define here a password

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

        //Define here your connection data
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
        if(str_contains($sql, "SELECT")){ //Download data
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
```
