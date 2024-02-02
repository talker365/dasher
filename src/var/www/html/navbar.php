<!-- Sidebar/menu -->
<style>
  span.d_navSpan {
    display:    inline-block;
    text-align: center;
    width:      50px;
  }
</style>
<div class="w3-sidebar w3-collapse w3-white w3-animate-left" style="z-index:3;width:300px;" id="mySidebar"><br>
  <div class="w3-container w3-row">
    <div class="w3-col s4">
      <!--<img src="/images/avatar.png" class="w3-circle w3-margin-right" style="width:46px">-->
      <img src="images/micro_sdr.svg" class="w3-margin-right" style="width:90%">
    </div>
    <div class="w3-col s8 w3-bar">
      <span><?php echo shell_exec("get_boardname"); ?></span><br>
      <a href="#" class="w3-bar-item w3-button" title="Shutdown"><i class="fa fa-power-off w3-xlarge"></i></a>
      <a href="#" class="w3-bar-item w3-button" title="Restart"><i class="material-icons w3-xlarge">loop</i></a>
      <a href="#" class="w3-bar-item w3-button" title="Lock TX"><i class="fa fa-lock w3-xlarge"></i></a>
    </div>
  </div>
  <hr>
  <div class="w3-container">
    <h5>Dashboard</h5>
  </div>
  <div class="w3-bar-block" style="font-size:16pt;">
    <a href="#" class="w3-bar-item w3-button w3-padding-16 dasher-hide-large w3-dark-grey w3-hover-black" onclick="w3_close()" title="close menu"><i class="fa fa-remove fa-fw"></i>  Close Menu</a>
    <a href="index.php" id="navbar_overview" class="w3-bar-item w3-button w3-padding navbar">
      <!--<i class="fa fa-users fa-fw"></i>-->
      <span class="d_navSpan"><img src="images/icons/Overview.png" alt="Overview" height="32" width="32"></span>
      Overview 
    </a>
    <a href="adsb.php" id="navbar_adsb" class="w3-bar-item w3-button w3-padding navbar">
      <span class="d_navSpan"><i class="fa fa-plane fa-fw" style="font-size:32px;"></i></span>
      ADS-B
    </a>
    <a href="aprs.php" id="navbar_aprs" class="w3-bar-item w3-button w3-padding navbar">
      <span class="d_navSpan"><i class="fa fa-automobile fa-fw" style="font-size:32px;"></i></span>
      APRS
    </a>
    <a href="atcs.php" id="navbar_atcs" class="w3-bar-item w3-button w3-padding navbar">
      <!--<i class="fa fa-train fa-fw" style="font-size:32px;"></i>-->
      <span class="d_navSpan"><img src="images/icons/locomotive-2.jpeg" alt="ATCS" height="32" width="32"></span>
      ATCS
    </a>
    <a href="dreamcatcher.php" id="navbar_dreamcatcher" class="w3-bar-item w3-button w3-padding navbar">
      <!--<i class="fa fa-history fa-fw"></i>-->
      <span class="d_navSpan"><img src="images/icons/DreamCatcher.png" alt="DreamCatcher" height="32" width="32"></span>
      DreamCatcher
    </a>
    <a href="echolink.php" id="navbar_echolink" class="w3-bar-item w3-button w3-padding navbar">
      <!--<i class="fa fa-diamond fa-fw"></i>-->
      <span class="d_navSpan"><img src="images/icons/Echolink-2.png" alt="Echolink" height="32" width="32"></span>
      Echolink
    </a>
    <a href="goes.php" id="navbar_goes" class="w3-bar-item w3-button w3-padding navbar">
      <!--<i class="fa fa-cog fa-fw"></i>-->
      <span class="d_navSpan"><img src="images/icons/Goes.png" alt="GOES" height="32" width="32"></span>
      GOES
    </a>
    <a href="gps.php" id="navbar_gps" class="w3-bar-item w3-button w3-padding navbar">
      <!--<i class="fa fa-bullseye fa-fw"></i>-->
      <span class="d_navSpan"><img src="images/icons/gps.png" alt="GPS" height="32" width="32"></span>
      GPS
    </a>
    <a href="noaa.php" id="navbar_noaa" class="w3-bar-item w3-button w3-padding navbar">
      <!--<i class="fa fa-bullseye fa-fw"></i>-->
      <span class="d_navSpan"><img src="images/icons/Weather.png" alt="NOAA" height="32" width="32"></span>
      NOAA
    <a href="packet.php" id="navbar_packet" class="w3-bar-item w3-button w3-padding navbar">
      <!--<i class="fa fa-eye fa-fw"></i>-->
      <span class="d_navSpan"><img src="images/icons/Packet-2.png" alt="Packet" height="32" width="32"></span>
      Packet
    </a>
    <a href="keps.php" id="navbar_keps" class="w3-bar-item w3-button w3-padding navbar">
      <span class="d_navSpan"><img src="images/icons/Goes.png" alt="keps" height="32" width="32"></span>
      Sat Tracker
    </a>
    <a href="weather.php" id="navbar_weather" class="w3-bar-item w3-button w3-padding navbar">
      <!--<i class="fa fa-cog fa-fw"></i>-->
      <span class="d_navSpan"><img src="images/icons/Weather.png" alt="Weather" height="32" width="32"></span>
      Weather
    </a>
    <a href="TPMS.php" id="navbar_tpms" class="w3-bar-item w3-button w3-padding navbar">
      <span class="d_navSpan"><img src="images/icons/TPMS.svg" alt="TPMS" height="32" width="32"></span>
      TPMS
    </a>
  </div>
  <br /><br /><br /><br />
</div>

<!-- Overlay effect when opening sidebar on small screens -->
<div class="w3-overlay dasher-hide-large w3-animate-opacity" onclick="w3_close()" style="cursor:pointer" title="close side menu" id="myOverlay"></div>
