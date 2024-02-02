<?php
  // define variables and set to empty values
  $content = $filename = "";

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $content = $_POST["content"];
    $filename = $_POST["filename"];
    $dir = 'data';

    // create new directory with 744 permissions if it does not exist yet
    // owner will be the user/group the PHP script is run under
    if ( !file_exists($dir) ) {
         mkdir ($dir, 0744);
    }

    unlink($dir.'/'.$filename, $content);
    file_put_contents($dir.'/'.$filename, $content);
  }
?>