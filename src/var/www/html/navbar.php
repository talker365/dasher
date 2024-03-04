<!-- Sidebar/menu -->
<script type="text/javascript">
  
</script>
<style>
  span.d_navSpan {
    display:    inline-block;
    text-align: center;
    width:      50px;
  }
</style>
<div class="w3-sidebar w3-collapse w3-white w3-animate-left" style="z-index:3;width:300px;padding-top: 0px; margin-top: 0px;" id="mySidebar">
  <div class="w3-container w3-row" style="font-family: 'Nasalization', sans-serif; font-size: 7ch; background-color: #DEE8EF; color: #F70608; letter-spacing: -0.15ch; padding-top: 0px; margin-top: 0px; padding-bottom: 0px; margin-bottom: 0px; text-shadow: 1px 1px; top: -0.6ch; position: relative;">
    <!--<img src="images/dasher_logo.png" class="w3-margin-right" style="width:90%">-->
      DASHER
  </div>
  <hr style="margin: 0px;">
  <div id="divNavbar" class="w3-bar-block" style="font-size:16pt;">
    <a href="#" class="w3-bar-item w3-button w3-padding-16 dasher-hide-large w3-dark-grey w3-hover-black" onclick="w3_close()" title="close menu"><i class="fa fa-remove fa-fw"></i>  Close Menu</a>
    <a href="index.php" id="navbar_overview" class="w3-bar-item w3-button w3-padding navbar">
      <!--<i class="fa fa-users fa-fw"></i>-->
      <span class="d_navSpan"><img src="images/icons/Overview.png" alt="Overview" height="32" width="32"></span>
      Overview 
    </a>


    <!--
    <a href="adsb.php" id="navbar_adsb" class="w3-bar-item w3-button w3-padding navbar">
      <span class="d_navSpan"><i class="fa fa-plane fa-fw" style="font-size:32px;"></i></span>
      ADS-B
    </a>
    <a href="aprs.php" id="navbar_aprs" class="w3-bar-item w3-button w3-padding navbar">
      <span class="d_navSpan"><i class="fa fa-automobile fa-fw" style="font-size:32px;"></i></span>
      APRS
    </a>
    <a href="atcs.php" id="navbar_atcs" class="w3-bar-item w3-button w3-padding navbar">
      <span class="d_navSpan"><img src="images/icons/locomotive-2.jpeg" alt="ATCS" height="32" width="32"></span>
      ATCS
    </a>
    <a href="dreamcatcher.php" id="navbar_dreamcatcher" class="w3-bar-item w3-button w3-padding navbar">
      <span class="d_navSpan"><img src="images/icons/DreamCatcher.png" alt="DreamCatcher" height="32" width="32"></span>
      DreamCatcher
    </a>
    <a href="echolink.php" id="navbar_echolink" class="w3-bar-item w3-button w3-padding navbar">
      <span class="d_navSpan"><img src="images/icons/Echolink-2.png" alt="Echolink" height="32" width="32"></span>
      Echolink
    </a>
    <a href="goes.php" id="navbar_goes" class="w3-bar-item w3-button w3-padding navbar">
      <span class="d_navSpan"><img src="images/icons/Goes.png" alt="GOES" height="32" width="32"></span>
      GOES
    </a>
    <a href="gps.php" id="navbar_gps" class="w3-bar-item w3-button w3-padding navbar">
      <span class="d_navSpan"><img src="images/icons/gps.png" alt="GPS" height="32" width="32"></span>
      GPS
    </a>
    <a href="noaa.php" id="navbar_noaa" class="w3-bar-item w3-button w3-padding navbar">
      <span class="d_navSpan"><img src="images/icons/Weather.png" alt="NOAA" height="32" width="32"></span>
      NOAA
    <a href="packet.php" id="navbar_packet" class="w3-bar-item w3-button w3-padding navbar">
      <span class="d_navSpan"><img src="images/icons/Packet-2.png" alt="Packet" height="32" width="32"></span>
      Packet
    </a>
    <a href="keps.php" id="navbar_keps" class="w3-bar-item w3-button w3-padding navbar">
      <span class="d_navSpan"><img src="images/icons/Goes.png" alt="keps" height="32" width="32"></span>
      Sat Tracker
    </a>
    <a href="weather.php" id="navbar_weather" class="w3-bar-item w3-button w3-padding navbar">
      <span class="d_navSpan"><img src="images/icons/Weather.png" alt="Weather" height="32" width="32"></span>
      Weather
    </a>
    <a href="TPMS.php" id="navbar_tpms" class="w3-bar-item w3-button w3-padding navbar">
      <span class="d_navSpan"><img src="images/icons/TPMS.svg" alt="TPMS" height="32" width="32"></span>
      TPMS
    </a>
    -->
  </div>


  <br /><br /><br /><br />
</div>

<!-- Overlay effect when opening sidebar on small screens -->
<div class="w3-overlay dasher-hide-large w3-animate-opacity" onclick="w3_close()" style="cursor:pointer" title="close side menu" id="myOverlay"></div>



<script type="text/javascript">
  
  var _navbar_json_modules_local = [];

  function _navbar_loadFile() { /* loads local json file */
    var xhttp = new XMLHttpRequest();
    var strUrl = "loadFile.php?filename=modules_local.json";
    var strReturn = "";

    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        //try {
            _navbar_json_modules_local = JSON.parse(this.responseText);
            _navbar_populate();
        //} catch (err) {
        //  console.log("_navbar_loadFile() - Failed");
        //  console.log(err);
        //}
      }
    };

    xhttp.open("GET", strUrl, true);
    xhttp.send();   
  }

  function _navbar_populate() {
    var objNavbar = document.getElementById("divNavbar");

    var numModules = _navbar_json_modules_local.length;


    for (var i = 0; i < numModules; i++) {
      var objRemove = document.getElementById(_navbar_json_modules_local[i].navbar.id);
      if (objRemove != null) objRemove.remove();
    }


    for (var i = 0; i < numModules; i++) {
      for (var j = 0; j < numModules; j++) {
        if (_navbar_json_modules_local[j].order == i + 1 && _navbar_json_modules_local[j].visible == "true") {
          var objA = document.createElement("A");
          objA.href = _navbar_json_modules_local[j].navbar.href;
          objA.id = _navbar_json_modules_local[j].navbar.id;
          objA.className = "w3-bar-item w3-button w3-padding navbar";

          var objS = document.createElement("SPAN");
          objS.className = "d_navSpan";
          objS.innerHTML = _navbar_json_modules_local[j].navbar.icon;

          var objN = document.createElement("SPAN");
          objN.innerHTML = _navbar_json_modules_local[j].navbar.name;

          objA.appendChild(objS);
          objA.appendChild(objN);
          objNavbar.appendChild(objA);
        }
      }
    }
  }

  _navbar_loadFile();
</script>




