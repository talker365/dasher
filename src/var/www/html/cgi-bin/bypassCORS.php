<?php header('Access-Control-Allow-Origin: *'); ?>
<?php 
# Filename:  bypassCORS.php
# Created:   16-MAR-2022
# Author:    N4LDR & WD4VA
#
# Description:  This PHP file will be called by AJAX calls, targetting the data on servers that
#               otherwise block CORS.
#
# Parameters:  target - escaped URL of datasource (REQUIRED)
#



# Instantiating variables...
$strUrl = "";
$strReturn = "";
$boolDebug = false;

$strUrl = isset($_GET["target"]) ? htmlspecialchars($_GET["target"]) : "";
$boolDebug = isset($_GET["debug"]) ? true : false;

$contents = file_get_contents($strUrl);
echo $contents;

?>
