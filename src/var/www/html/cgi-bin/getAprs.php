<?php 
# Filename:  getData.php
# Created:   28-FEB-2020
# Author:    N4LDR & WD4VA
#
# Description:  This PHP file will be called by AJAX calls, Mango, etc to return
#               data in a defined format.
#
# Parameters:  feed - desired data feed (REQUIRED)
#              format - output format (OPTIONAL - default JSON)
#              flatten - [true/false] makes data flat for Mango (OPTIONAL - default false)
#              sensor_model - model name for Mango (OPTIONAL)
#              sensor_id_alpha - id for Mango (OPTIONAL)
#              sensor_id_numeric - id for Mango (OPTIONAL)
#              sensor_channel_alpha - channel for Mango (OPTIONAL)
#              sensor_channel_numeric - channel for Mango (OPTIONAL)
#              records - number of records to return (OPTIONAL - 0 = ALL)
#              condense - [true/false] reduces returned data to one row, using the most recent values (OPTIONAL)
#



# Instantiating variables...
$strCommand = "";
$strReturn = "";
$strFlags = "";
$boolDebug = false;

$strFeed = isset($_GET["feed"]) ? htmlspecialchars($_GET["feed"]) : "";
$strModel = isset($_GET["sensor_model"]) ? htmlspecialchars($_GET["sensor_model"]) : "";
$strIdAlpha = isset($_GET["sensor_id_alpha"]) ? htmlspecialchars($_GET["sensor_id_alpha"]) : "";
$strIdNumeric = isset($_GET["sensor_id_numeric"]) ? htmlspecialchars($_GET["sensor_id_numeric"]) : "";
$strChannelAlpha = isset($_GET["sensor_channel_alpha"]) ? htmlspecialchars($_GET["sensor_channel_alpha"]) : "";
$strChannelNumeric = isset($_GET["sensor_channel_numeric"]) ? htmlspecialchars($_GET["sensor_channel_numeric"]) : "";
$strFlatten = isset($_GET["flatten"]) ? htmlspecialchars($_GET["flatten"]) : "";
$strRecords = isset($_GET["records"]) ? htmlspecialchars($_GET["records"]) : "";
$strCondense = isset($_GET["condense"]) ? htmlspecialchars($_GET["condense"]) : "";
$boolDebug = isset($_GET["debug"]) ? true : false;


if (strlen($strIdAlpha) > 0) {
	$strIdAlpha = "\\\"" . $strIdAlpha . "\\\"";
}
$strId = $strIdAlpha . $strIdNumeric;

if (strlen($strChannelAlpha) > 0) {
	$strChannelAlpha = "\\\"" . $strChannelAlpha . "\\\"";
}
$strChannel = $strChannelAlpha . $strChannelNumeric;


# Set flags...
$strFlags .= " --feed " . $strFeed;

if (strlen($strRecords) > 0) {
	$strFlags .= " --number " . $strRecords;
}

if (strlen($strModel) > 0) {
	$strFlags .= " --sensor_model " . $strModel;
}

if (strlen($strId) > 0) {
	$strFlags .= " --sensor_id " . $strId;
}

if (strlen($strChannel) > 0) {
	$strFlags .= " --sensor_channel " . $strChannel;
}



# Determine how to grab the data from the data source...
if (strlen($strFeed) > 0) {
	$strCommand = "/var/www/html/bin/./getJson " . $strFlags;
} else {
	$strCommand = "echo \"error in php (no feed specified): " . $strFlags . "\"";
}

if ($boolDebug == true) {
	print "\$strCommand = " . $strCommand . "<br />";
}
$strJSON = shell_exec($strCommand);




# Post Processing...
$strJSON = preg_replace( "/\r|\n/", "", $strJSON); // Remove line breaks
$strJSON = str_replace(',]}', ']}', $strJSON);  // Remove trailing commas in record arrays

if ($strCondense == "true") {
	/*
	$strJSON = '
	{"records":[
		{
			"time" : "2020-03-21 02:01:38", 
			"model" : "Acurite-5n1", 
			"subtype" : 49, 
			"id" : 2159, 
			"channel" : "A", 
			"sequence_num" : 2, 
			"battery_ok" : 1, 
			"wind_avg_km_h" : 2.656, 
			"wind_dir_deg" : 270.000, 
			"rain_in" : 112.410, 
			"mic" : "CHECKSUM"
		},
		{
			"time" : "2020-03-21 02:01:38", 
			"model" : "Acurite-5n1", 
			"subtype" : 49, 
			"id" : 2159, 
			"channel" : "A", 
			"sequence_num" : 1, 
			"battery_ok" : 1, 
			"wind_avg_km_h" : 2.656, 
			"wind_dir_deg" : 270.000, 
			"rain_in" : 112.410, 
			"mic" : "CHECKSUM"
		},
		{
			"time" : "2020-03-21 02:01:38", 
			"model" : "Acurite-5n1", 
			"subtype" : 49, 
			"id" : 2159, 
			"channel" : "A", 
			"sequence_num" : 0, 
			"battery_ok" : 1, 
			"wind_avg_km_h" : 2.656, 
			"wind_dir_deg" : 270.000, 
			"rain_in" : 112.410, 
			"mic" : "CHECKSUM"
		},
		{
			"time" : "2020-03-21 02:01:57", 
			"model" : "Acurite-5n1", 
			"subtype" : 56, 
			"id" : 2159, 
			"channel" : "A", 
			"sequence_num" : 2, 
			"battery_ok" : 1, 
			"wind_avg_km_h" : 5.967, 
			"temperature_F" : 69.300, 
			"humidity" : 99, 
			"mic" : "CHECKSUM"
		},
		{
			"time" : "2020-03-21 02:01:57", 
			"model" : "Acurite-5n1", 
			"subtype" : 56, 
			"id" : 2159, 
			"channel" : "A", 
			"sequence_num" : 1, 
			"battery_ok" : 1, 
			"wind_avg_km_h" : 5.967, 
			"temperature_F" : 69.300, 
			"humidity" : 99, 
			"mic" : "CHECKSUM"
		},
		{
			"time" : "2020-03-21 02:01:57", 
			"model" : "Acurite-5n1", 
			"subtype" : 56, 
			"id" : 2159, 
			"channel" : "A", 
			"sequence_num" : 0, 
			"battery_ok" : 1, 
			"wind_avg_km_h" : 5.967, 
			"temperature_F" : 69.300, 
			"humidity" : 99, 
			"mic" : "CHECKSUM"
		}
	]}';

	*/
	$objJSON = json_decode($strJSON);
	
	$objCondensedJSON=null;  // Condensed JSON Object...

	foreach($objJSON as $key => $records) {
		$arrlength = count($records);
		for($x = $arrlength - 1; $x >= 0; $x--) {
			if ($x == $arrlength - 1) {
				// Assign initial record to condensed object...
				$objCondensedJSON = $records[$x];
			} else {
				// Examine objCondensedFields to see if field exists
				foreach($records[$x] as $a => $b) {
					$objCondensedJSON->$a = $b;
				}
			}
		}
	}
	$strJSON = json_encode($objCondensedJSON);
}


$strReturn = $strJSON;
print $strReturn;
?>
