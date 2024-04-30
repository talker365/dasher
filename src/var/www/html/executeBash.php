<?php

  // define variables and set to empty values
  $command = "";

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $command = $_POST["command"];
    $dir = '/var/www/html/cgi-bin/installers/';


    //echo 'bash '.$dir.$command;

    $output = shell_exec('sudo bash '.$dir.$command);
    echo $output;
 
 

  }
?>