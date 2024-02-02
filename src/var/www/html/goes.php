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
    <button id="Browse_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event,'Browse_tab', this)"><b><i class="fa fa-dashboard"></i> Browse </b></button>
    <button id="Today_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event,'Today_tab', this)"><b><i class="fa fa-dashboard"></i> Today </b></button>
  </div>

<div id="summary_tab" class="w3-cell-row dasherTab" style="display:block;">
  <?php 
    $goesDate = strval(date("Y")) . "-" . strval(date("m")) . "-" . strval(date("d"));
    $goesFolder = "?path=goes16/fd/fc/" . $goesDate;
  ?>
  <div class="w3-row-padding" style="margin:0 -16px" style="display:none;">
    <div class="w3-half">
      <h5>Animated</h5>
      <iframe id="animated_iframe" style="width:100%;height:500px;"></iframe>
    </div>
    <div class="w3-half">
      <h5>Full Disk</h5>
      <iframe id="fullDisk_iframe" style="width:100%;height:500px;"></iframe>
    </div>
  </div>
</div>

<div id="Browse_tab" class="w3-cell-row dasherTab" style="display:none;">
  <iframe id="Browse_iframe" style="width:100%;height:1000px;"></iframe>
</div>

<div id="Today_tab" class="w3-cell-row dasherTab" style="display:none;">
  <iframe id="Today_iframe" style="width:100%;height:1000px;"></iframe>
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
  objNavbar = document.getElementById("navbar_goes");
  objNavbar.className += " w3-light-blue";

  setIFrameHeight("Browse_iframe");
  setIFrameHeight("Today_iframe");

  document.getElementById("animated_iframe").src = "http://" + addresses.goesAddress + "/?path=animations/&file=INDCIRUS.gif";
  document.getElementById("fullDisk_iframe").src = "http://" + addresses.goesAddress + "/?path=animations&file=GOES16_FD_FC.jpg";
  document.getElementById("Browse_iframe").src = "http://" + addresses.goesAddress + "/";
  <?php 
    $goesDate = strval(date("Y")) . "-" . strval(date("m")) . "-" . strval(date("d"));
    $goesFolder = "?path=goes16/fd/fc/" . $goesDate;
  ?>
  document.getElementById("Today_iframe").src = "http://" + addresses.goesAddress + "/<?php echo $goesFolder ?>";
</script>

</body>
</html>

