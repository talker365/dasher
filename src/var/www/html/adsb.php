<!DOCTYPE html>
<html>
<head>
	<title> Dasher </title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<link rel="stylesheet" href="w3.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	<style>
		html,body,h1,h2,h3,h4,h5 {font-family: "Raleway", sans-serif}
	</style>
  <!-- Icons:  https://www.w3schools.com/icons/icons_reference.asp -->



  <?php include "head.php"; ?>
  <?php include "dasher_js.php"; ?>
</head>
<body class="w3-light-grey">
<script type="text/javascript">
  const host = "http://<?php echo $_SERVER['SERVER_ADDR']; ?>";
</script>


<!-- Top container -->
<?php include "header.php"; ?>

<!-- Sidebar/menu -->
<?php include "navbar.php"; ?>

<!-- !PAGE CONTENT! -->
<div class="w3-main" style="margin-left:300px;margin-top:43px;">

  <!-- Header -->

  <div class="w3-bar w3-border-top"> <!-- Tab Navigation -->
    <button id="PiAware1090_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black w3-light-blue" style="padding-bottom: 0px;" onclick="openDasherTab(event,'PiAware1090_tab', this)"><b><i class="fa fa-rss"></i> PiAware 1090 </b></button>
    <button id="PiAware978_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event,'PiAware978_tab', this)"><i class="fa fa-rss"></i> PiAware 978 </button>
    <button id="PlaneFinder_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event,'PlaneFinder_tab', this)"><i class="fa fa-bullseye"></i> Plane Finder </button>
  </div>

<div id="PiAware1090_tab" class="w3-cell-row dasherTab">
  <iframe id="PiAware1090_iframe" src="http://<?php echo $_SERVER['SERVER_ADDR']; ?>:8080" style="width:100%;height:1000px;"></iframe>
</div>

<div id="PiAware978_tab" class="w3-cell-row dasherTab" style="display:none;">
  <iframe id="PiAware978_iframe" src="http://<?php echo $_SERVER['SERVER_ADDR']; ?>/skyaware978/" style="width:100%;height:1000px;"></iframe>
</div>

<div id="PlaneFinder_tab" class="w3-cell-row dasherTab" style="display:none;">
  <iframe id="PlaneFinder_iframe" src="http://<?php echo $_SERVER['SERVER_ADDR']; ?>:30053" style="width:100%;"></iframe>
</div>

<!--
<div id="FlightAware_tab" class="w3-cell-row dasherTab" style="display:none;">
  <iframe id="FlightAware_iframe" src="https://flightaware.com/adsb/stats/user/talker365" style="width:100%;height:1000px;"></iframe>
</div>
-->


  <!-- Footer -->
  <?php include "footer.php"; ?>

  <!-- End page content -->
</div>

<script type="text/javascript">
  var objNavbar;
  objNavbar = document.getElementById("navbar_adsb");
  objNavbar.className += " w3-light-blue";

  setIFrameHeight("PiAware1090_iframe");
  setIFrameHeight("PiAware978_iframe");
  setIFrameHeight("PlaneFinder_iframe");
  setIFrameHeight("FlightAware_iframe");
</script>

</body>
</html>

