<!DOCTYPE html>
<html>
<head>
	<title> Dasher </title>
  <!-- Head - links and meta -->
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
    <button id="summary_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black w3-light-blue" style="padding-bottom: 0px;" onclick="openDasherTab(event,'summary_tab', this)"><b><i class="fa fa-dashboard"></i> Summary </b></button>
    <button id="RWMon_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event,'RWMon_tab', this)"><b><i class="fa fa-dashboard"></i> RWMon </b></button>
    <button id="TrainMon5_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event,'TrainMon5_tab', this)"><i class="fa fa-plane"></i> TrainMon5 </button>
    <button id="ATCSMon_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event,'ATCSMon_tab', this)"><i class="fa fa-train"></i> ATCSMon </button>
  </div>

<div id="summary_tab" class="w3-cell-row dasherTab" style="display:block;">
  Summary
</div>

<div id="RWMon_tab" class="w3-cell-row dasherTab" style="display:none;">
  <iframe id="RWMon_iframe" src="http://<?php echo $_SERVER['SERVER_ADDR']; ?>/cgi-bin/rwconfig2.cgi" style="width:100%;height:1000px;"></iframe>
</div>

<div id="TrainMon5_tab" class="w3-cell-row dasherTab" style="display:none;">
  User: talker365 / Password: OzR0Qd83sgZ6#M82gPdJ
  <iframe id ="TrainMon5_iframe" src="http://trainmon5.com/Application/Railroads/Divisions/Monitor/Default.aspx?railroadDivisionLayoutId=14" style="width:100%;height:1250px;"></iframe>
</div>

<div id="ATCSMon_tab" class="w3-cell-row dasherTab" style="display:none;">
</div>


<div class="w3-panel">
  <div class="w3-panel">
    <hr>
  </div>
  <!-- Footer -->
  <?php include "footer.php"; ?>
  <!-- End page content -->
</div>



<script type="text/javascript">
  var objNavbar;
  objNavbar = document.getElementById("navbar_atcs");
  objNavbar.className += " w3-light-blue";

  setIFrameHeight("RWMon_iframe");
  setIFrameHeight("TrainMon5_iframe");

</script>

</body>
</html>

