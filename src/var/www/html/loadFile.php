<?php
  $dir = 'data';
  $filename = $_GET["filename"];

  $filename = $dir . "/" . $filename;

  if ( file_exists($filename) ) {
    $myfile = fopen($filename, "r") or die("Unable to open file!");
    echo fread($myfile,filesize($filename));
    fclose($myfile);
  } else {
    echo "No such file! (" . $filename . ")";
  }
?>