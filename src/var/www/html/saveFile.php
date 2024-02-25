<?php
  // define variables and set to empty values
  $content = $filename = "";

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $content = $_POST["content"];
    $filename = $_POST["filename"];
    $dir = 'data';

    echo "filename = " . $filename;
    echo "content = " . $content;

    // create new directory with 744 permissions if it does not exist yet
    // owner will be the user/group the PHP script is run under
    if ( !file_exists($dir) ) {
         mkdir ($dir, 0744);
    }

    /*
    unlink($dir.'/'.$filename, $content);
    file_put_contents($dir.'/'.$filename, $content);
    */

    $myfile = fopen($dir.'/'.$filename, "w") or die("Unable to open file for writing!");
    fwrite($myfile, $content);
    fclose($myfile);
  }
?>