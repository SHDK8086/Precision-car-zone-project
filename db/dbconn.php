<?php
$servername="localhost";
$username="root";
$password="";
$dbname="service_center";

try{
$conn= new mysqli($servername,$username,$password,$dbname);

if($conn->connect_error){
    die("connection failed".$conn->connect_error);
}
else{
    echo"";
}
}
catch(Exception $e){
    echo"Message: ".$e->getMessage();
}
?>