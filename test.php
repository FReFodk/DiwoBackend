<?php 
$to = "chirag.logixbuilt@gmail.com";
$subject = "My subject";
$txt = "Hello world!";
$headers = "From: webmaster@example.com" . "\r\n" .
"CC: somebodyelse@example.com";



$result = mail($to,$subject,$txt,$headers);
if(!$result) {   
     echo "Error";   
} else {
    echo "Success";
}
 ?>