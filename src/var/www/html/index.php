<!DOCTYPE html>
<html>
<head>
  <title> Dasher </title>
  <!-- Head - links and meta -->
  <?php include "head.php"; ?>
  <?php
    // define variables and set to empty values
    $config_goes = $config_dreamcatcher = $config_gps = $config_echolink = $config_aprsLatitude = $config_aprsLongitude = $config_aprsZoom = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $config_dreamcatcher = test_input($_POST["dreamCatcherAddress"]);
      $config_goes = test_input($_POST["goesAddress"]);
      $config_gps = test_input($_POST["gpsAddress"]);
      $config_echolink = test_input($_POST["echolinkAddress"]);
      $config_qthLatitude = test_input($_POST["qthLatitude"]);
      $config_qthLongitude = test_input($_POST["qthLongitude"]);
      $config_qthAltitude = test_input($_POST["qthAltitude"]);
      $config_aprsZoom = test_input($_POST["aprsZoom"]);

      $config_json = '{"dreamCatcherAddress":"'.$config_dreamcatcher.'","goesAddress":"'.$config_goes.'","gpsAddress":"'.$config_gps.'","echolinkAddress":"'.$config_echolink.'","qthLatitude":"'.$config_qthLatitude.'","qthLongitude":"'.$config_qthLongitude.'","qthAltitude":"'.$config_qthAltitude.'","aprsZoom":"'.$config_aprsZoom.'"}';

      $dir = 'data';

      // create new directory with 744 permissions if it does not exist yet
      // owner will be the user/group the PHP script is run under
      if ( !file_exists($dir) ) {
           mkdir ($dir, 0744);
      }

      file_put_contents ($dir.'/config.json', $config_json);
    }

    function test_input($data) {
      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data);
      if ($data == '') {
        $data = $_SERVER['SERVER_ADDR'];
      }
      return $data;
    }
  ?>
  <?php include "dasher_js.php"; ?>
</head>
<body id="dasher_body" class="w3-light-grey" onresize="global_dasherBodyResize();resizeDashboardTiles();">
<script type="text/javascript">
  const host = "http://<?php echo $_SERVER['SERVER_ADDR']; ?>";
  var refreshRate = 60000;
  var arrDasher = [];
  var json_request = [];
  var json_modules_master = [];
  var json_modules_local = [];

  setInterval(function() {
    if (!json_request["dasher"]) {
      json_request["dasher"] = true;
        //console.log("Calling for updates");
      getJSON("dasher");
    }
  }, refreshRate);

  function initialize() {
    loadFile("modules_master.json");
    loadFile("modules_local.json");
  }

  function getJSON(strDataset) { /*  */
    var xhttp = new XMLHttpRequest();
    var strUrl  = host;

    switch (strDataset){
      case "dasher":
        strUrl += "/bin/getData.php?feed=dasher"
        break;
    }

    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        json_request[strDataset] = false;
        //console.log("Processing results");
        var strJSON = this.responseText;
        switch (strDataset) {
          case "dasher":
            strJSON = strJSON.replace("},    matches]", "}]");
            strJSON = strJSON.replace(/[^\x0F-\xFF]/g, "");
            strJSON = strJSON.replace("},]}", "}]}");

            var s = strJSON;

            // remove non-printable and other non-valid JSON chars
            s = s.replace(/[\u0000-\u001F]+/g,""); 
            s = s.replace(/[\u0000-\u001F\u007F-\u009F]/g, "");

            /*
            s = s.replace(/\\n/g, "\\n")  
                 .replace(/\\'/g, "\\'")
                 .replace(/\\"/g, '\\"')
                 .replace(/\\&/g, "\\&")
                 .replace(/\\r/g, "\\r")
                 .replace(/\\t/g, "\\t")
                 .replace(/\\b/g, "\\b")
                 .replace(/\\f/g, "\\f");
            */


            /*
            // remove some odd escape characters
            s = s.replace("\\&", "\\\\&");
            //s = s.replace("\\l", "\\\\l");
            s = s.replace("\\l", "l");
            s = s.replace("\\l", "l");
            s = s.replace("\\W", "W");
            */


            // remove '"\ '
            s = s.replace("\":\"\\\",\"", "\":\"\",\"");


            s = s.replace("\"\"\"", "\"\"");

            s = s.replace("\\\\ ", "/ ");
            s = s.replace("\\\\R", "/R");
            s = s.replace("\\R", "/R");



            try {
              var myObj = JSON.parse(s);

              switch (strDataset) {
                case "dasher":
                  arrDasher = myObj.records[0];
                  //console.log("getJSON(\"" + strDataset + "\"): arrAPRS = arrCurrentAPRS;");
                  break;
              }

              // Update UI...
              updateUI(strDataset);
              //console.log("getJSON(\"" + strDataset + "\"): updateUI(strDataset);");
            }
            catch (err) {
              console.log(err);
            }

            break;
        }
      }
    };
    xhttp.open("GET", strUrl, true);
    xhttp.send();   
  }

  function updateUI(strDataset) {
    //var d = new Date();
    switch (strDataset) {
      case "dasher":
        updateDasher();
        //document.getElementById("lastUpdatedAprs").innerHTML = d.toString();
        //document.getElementById("lastUpdatedSummary").innerHTML = d.toString();
        break;
    }
  }

  function updateDasher() {
    // Storage...
    var strStorage = "";
    for (let i = 0; i < arrDasher.storage.length; i++) {
      if (arrDasher.storage[i].mounted == "/") {
        strStorage += "<table><tr><td>"
        strStorage += arrDasher.storage[i].used;
        strStorage += " Used</td></tr><tr><td>" ;
        strStorage += arrDasher.storage[i].free;
        strStorage += " Available</td></tr></table>";
      }
      //strStorage += arrDasher.storage[i].device.replace("/dev/", "");
      //strStorage += " - " + arrDasher.storage[i].total ;
      //strStorage += " (" + arrDasher.storage[i].free + " free)";
      //strStorage += "<br />";
    } 
    document.getElementById("dashboard_hdd").innerHTML = strStorage;

    // Memory...
    var strMemory = "";
    strMemory += "<table><tr><td>";
    strMemory += arrDasher.memory.used + "M";
    strMemory += " Used</td></tr><tr><td>" ;
    strMemory += arrDasher.memory.free + "M";
    strMemory += " Available</td></tr></table>";
    document.getElementById("dashboard_mem").innerHTML = strMemory;

    // Temperatures...
    var strTempCPU = "";
    var strTempGPU = "";
    var strTempHDD = "";

    if (arrDasher.temperature.cpu != "") {
      strTempCPU = arrDasher.temperature.cpu;
    }

    if (arrDasher.temperature.gpu != "") {
      strTempGPU = arrDasher.temperature.gpu;
    }

    if (arrDasher.temperature.nvme != "") {
      //strTempHDD = arrDasher.temperature.nvme.replace("+", "") + "ºF";
      strTempHDD = arrDasher.temperature.nvme;
    }

    var strTemperature = "";
    strTemperature += "<table><tr><td> CPU ";
    strTemperature += strTempCPU;
    //strTemperature += "</td></tr><tr><td> GPU " ;
    //strTemperature += strTempGPU;
    if (strTempHDD != "") {
      strTemperature += "</td></tr><tr><td> SSD " ;
      strTemperature += strTempHDD;
    }
    strTemperature += "</td></tr></table>";
    document.getElementById("dashboard_temp").innerHTML = strTemperature;

    // Network...
    var strNetworkLan = "";
    var strNetworkWlan = "";
    for (let i = 0; i < arrDasher.network.length; i++) {
      if (arrDasher.network[i].type == "LAN") {
        strNetworkLan = "LAN " + arrDasher.network[i].ip;
      }
      if (arrDasher.network[i].type == "WLAN") {
        strNetworkWlan = "WLAN " + arrDasher.network[i].ip;
      }
    }
    document.getElementById("dashboard_lan").innerHTML = strNetworkLan;
    document.getElementById("dashboard_wlan").innerHTML = strNetworkWlan;

    // UPS...
    var upsBatteryVoltage = "";
    upsBatteryVoltage = arrDasher.ups.battery_voltage + " VDC";
    upsBatteryVoltage += "<br />";
    upsBatteryVoltage += arrDasher.ups.status;

    document.getElementById("dashboard_ups").innerHTML = upsBatteryVoltage;


    // SDR...


    document.getElementById("dashboard_sdr").innerHTML = arrDasher.sdr.length;





    resizeDashboardTiles();
  }

  function openTab(tabName, element) { /*  */
    console.log("Calling openTab('" + tabName + "', '[" + element.innerHTML + "]');");
    var i;
    var x;
    var boolAlreadyOpen = false;

    boolAlreadyOpen = (document.getElementById(tabName).className.indexOf("w3-show") >= 0);

    x = document.getElementsByClassName("tab");
    for (i = 0; i < x.length; i++) {
        x[i].className = x[i].className.replace(" w3-show", " w3-hide");
    }
    x = document.getElementsByClassName("tab-button");
    for (i = 0; i < x.length; i++) {
        x[i].className = x[i].className.replace(" w3-border-blue", " w3-border-black");
        x[i].style.fontWeight = "normal";
    }

    if (!boolAlreadyOpen) {
      document.getElementById(tabName).className = document.getElementById(tabName).className.replace(" w3-hide", " w3-show");
      element.className = element.className.replace(" w3-border-black", " w3-border-blue");
      element.style.fontWeight = "bold";
    }
  }

  function resizeDashboardTiles(){ /*  */
    if (screen.availWidth > 600) {
      var divHeight = document.getElementById("dashboard_tile_div").clientHeight + "px";

      document.getElementById("dashboard_tile_hdd").style.height = divHeight;
      document.getElementById("dashboard_tile_net").style.height = divHeight;
      document.getElementById("dashboard_tile_temp").style.height = divHeight;
      document.getElementById("dashboard_tile_mem").style.height = divHeight;
      document.getElementById("dashboard_tile_ups").style.height = divHeight;
      document.getElementById("dashboard_tile_sdr").style.height = divHeight;
    }
  }

  function loadFile(filename) {
    var xhttp = new XMLHttpRequest();
    var strUrl = "loadFile.php?filename=" + filename;
    var strReturn = "";

    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        try {
          switch (filename) {
            case "modules_master.json":
              json_modules_master = JSON.parse(this.responseText);
              break;
            case "modules_local.json":
              json_modules_local = JSON.parse(this.responseText);
              document.getElementById("divModules").innerHTML = generateModules();
              break;
          }
          console.log("loadFile(): filename = '" + filename + "' - Successful");
        } catch (err) {
          console.log("loadFile(): filename = '" + filename + "' - Failed");
          console.log(err);
        }
      }
    };

    xhttp.open("GET", strUrl, true);
    xhttp.send();   
  }

  function generateModules() {  /* Create Modules table Modules Tab */
    var div = document.getElementById("divAllSats");
    var tempArr = []; // Assigned below...
    var tempArr = tempArr.concat(json_modules_local);

    var strHtml = "";
    var boolFirst = true;

    while (tempArr.length > 0) {
      var objModule = tempArr.shift();
      if (boolFirst) {
        boolFirst = false;
      } else {
        strHtml += "<br />"; 
      }
      strHtml += generateModuleRow(objModule);
    }

    return strHtml;
  }
  
  function generateModuleRow(objModule) {



    // Find a <table> element with id="myTable":
    var table = document.getElementById("tblModules");

    // Create an empty <tr> element and add it to the 1st position of the table:
    var row = table.insertRow(-1);

    // Insert new cells (<td> elements) at the 1st and 2nd position of the "new" <tr> element:
    var cell1 = row.insertCell(0);
    var cell2 = row.insertCell(1);
    var cell3 = row.insertCell(2);
    var cell4 = row.insertCell(3);
    var cell5 = row.insertCell(4);
    var cell6 = row.insertCell(5);

    // Add some text to the new cells:
    if (objModule.order != undefined) cell1.innerHTML = objModule.order;
    if (objModule.name != undefined) cell2.innerHTML = objModule.name;
    if (objModule.version_installed != undefined) cell3.innerHTML = objModule.version_installed;
    if (objModule.active != undefined) {
      var html = "<span class=\"d_navSpan\"><i class=\"";
      if (objModule.active == "true") {
        html += "fa fa-toggle-on";
      } else {
        html += "fa fa-toggle-off";
      }
      html += "\"></i></span>";
      cell4.innerHTML = html;
    }
    if (objModule.visible != undefined) {
      var html = "<span class=\"d_navSpan\"><i class=\"";
      if (objModule.visible == "true") {
        html += "fa fa-toggle-on";
      } else {
        html += "fa fa-toggle-off";
      }
      html += "\"></i></span>";
      cell5.innerHTML = html;
    }
    if (objModule.installed != undefined) {
      var html = "<button class=\"w3-button w3-round-xlarge w3-teal\">";
      if (objModule.installed == "true") {
        html += "Uninstall";
      } else {
        html += "Install";
      }
      html += "</button>";
      cell6.innerHTML = html;
    }




    var strReturn = "";
    if (objModule.name != undefined) strReturn += "<span><b>Name:</b> " + objModule.name + "</span><br />";
    if (objModule.installer != undefined) strReturn += "<span><b>Installer:</b> " + objModule.installer + "</span><br />";
    if (objModule.version_installed != undefined) strReturn += "<span><b>Version Installed:</b> " + objModule.version_installed + "</span><br />";
    if (objModule.version_available != undefined) strReturn += "<span><b>Version Available:</b> " + objModule.version_available + "</span><br />";
    if (objModule.installed != undefined) strReturn += "<span><b>Installed:</b> " + objModule.installed + "</span><br />";
    if (objModule.active != undefined) strReturn += "<span><b>Active:</b> " + objModule.active + "</span><br />";
    if (objModule.description != undefined) strReturn += "<span><b>Description:</b> " + objModule.description + "</span><br />";
    if (objModule.order != undefined) strReturn += "<span><b>Order:</b> " + objModule.order + "</span><br />";

/*
    if (transmitter.status != undefined) {
    strReturn += "<span><b>Status:</b> " + transmitter.status + "</span><br />";
    } else {
    strReturn += "<span><b>Status:</b> ";
    if (transmitter.status == "inactive") {
      strReturn += "<span class=\"w3-red\">";
    } else if (transmitter.status == "active") {
      strReturn += "<span class=\"w3-green\">";
    } else {
      strReturn += "<span class=\"\">";
    }
    strReturn += "&nbsp;" + transmitter.status + "&nbsp;</span><br />";

    }
*/
    return strReturn;
  }





</script>


<!-- Top container -->
<?php include "header.php"; ?>

<!-- Sidebar/menu -->
<?php include "navbar.php"; ?>

<!-- !PAGE CONTENT! -->
<div class="w3-main" style="margin-left:300px;margin-top:43px;">

  <!-- Header -->

  <div class="w3-bar w3-border-top"> <!-- Tab Navigation -->
    <button id="Dashboard_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black w3-light-blue" style="padding-bottom: 0px;" onclick="openDasherTab(event, 'Dashboard_tab', this)"><b><i class="fa fa-dashboard"></i> Dashboard </b></button>
    <button id="Settings_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event, 'Modules_tab', this)"><i class="fa fa-wrench"></i> Modules </button>
    <button id="Settings_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event, 'Settings_tab', this)"><i class="fa fa-wrench"></i> Settings </button>
    <button id="Webmin_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event, 'Webmin_tab', this)"><i class="fa fa-gear"></i> Webmin </button>
  </div>

<div id="Dashboard_tab" class="w3-cell-row dasherTab"> <!-- Dashboard Tab -->
  <div id="dashboard_tile_div" class="w3-cell-row">
    <div class="w3-half">
      <div class="w3-third">
        <div id="dashboard_tile_hdd" class="w3-container w3-red w3-padding-16" style="max-height: 250px;">
          <div class="w3-left">
            <i class="fa fa-hdd-o w3-xxxlarge"></i>
            <h4>HDD</h4>
          </div>
          <div class="w3-right">
            <h4 id="dashboard_hdd">
              <?php echo shell_exec("dasher_get disk_free"); ?> 
              /
              <?php echo shell_exec("dasher_get disk_total"); ?> Free
            </h4>
          </div>
        </div>
      </div>
      <div class="w3-third">
        <div id="dashboard_tile_net" class="w3-container w3-blue w3-padding-16" style="max-height: 250px;">
          <div class="w3-left">
            <i class="fa fa-wifi w3-xxxlarge"></i>
            <!--<i class="material-icons">settings_input_hdmi</i>-->
            <h4>Network</h4>
          </div>
          <div class="w3-right">
            <h5 id="dashboard_lan">eth0: <?php echo shell_exec("dasher_get ip_lan"); ?></h5>
            <h5 id="dashboard_wlan">wlan0: <?php echo shell_exec("dasher_get ip_wifi"); ?></h5>
          </div>
          <div class="w3-clear"></div>
        <!--<h4>Network</h4>-->
        </div>
      </div>
      <div class="w3-third">
        <div id="dashboard_tile_temp" class="w3-container w3-teal w3-padding-16" style="max-height: 250px;">
          <div class="w3-left">
            <i class="fa fa-thermometer w3-xxxlarge"></i>
            <h4>Temp</h4>
          </div>
          <div class="w3-right">
            <h4 id="dashboard_temp"><?php echo shell_exec("dasher_get boardtemp"); ?></h4>
          </div>
          <div class="w3-clear"></div>
        </div>
      </div>
    </div>
    <div class="w3-half">
      <div class="w3-third">
        <div id="dashboard_tile_mem" class="w3-container w3-orange w3-text-white w3-padding-16" style="max-height: 250px;">
          <div class="w3-left">
            <i class="fa fa-microchip w3-xxxlarge"></i>
            <h4>Memory</h4>
          </div>
          <div class="w3-right">
            <h4 id="dashboard_mem"><?php echo shell_exec("dasher_get memfree"); ?></h4>
          </div>
          <div class="w3-clear"></div>
        </div>
      </div>
      <div class="w3-third">
        <div id="dashboard_tile_ups" class="w3-container w3-purple w3-text-white w3-padding-16" style="max-height: 250px;">
          <div class="w3-left">
            <i class="fa fa-battery-3 w3-xxxlarge"></i>
            <h4>UPS</h4>
          </div>
          <div class="w3-right">
            <h4 id="dashboard_ups"></h4>
          </div>
          <div class="w3-clear"></div>
        </div>
      </div>
      <div class="w3-third">
        <div id="dashboard_tile_sdr" class="w3-container w3-cyan w3-text-white w3-padding-16" style="max-height: 250px;">
          <div class="w3-left">
            <i class="fa fa-signal w3-xxxlarge"></i>
            <h4>SDR</h4>
          </div>
          <div class="w3-right">
            <h4 id="dashboard_sdr"></h4>
          </div>
          <div class="w3-clear"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="w3-cell-row">
    <div class="w3-half">
      <h4> Connected SDRs </h4>
      <?php
        print shell_exec("/usr/local/bin/rtl_test -l 2>&1 | /bin/grep -v 'Using device' | /bin/grep -v Found | /usr/bin/awk 'NF' - | /bin/sed 's/$/<br>/'");
      ?>
    </div>
    <div class="w3-half">
      <h4> Network Connections </h4>
      <?php
        print shell_exec("/sbin/ifconfig eth0 | grep 'inet ' | /bin/sed 's/^/<br>eth0/' | /bin/sed 's/$/<br>/'");
      ?>
    </div>
  </div>
</div>







<div id="Modules_tab" class="w3-cell-row dasherTab" style="display: none;"> <!-- Settings Tab -->
  <div class="w3-border w3-border-gray w3-margin w3-padding-16">
    <h2> Modules </h2>

    <table id="tblModules">
      <tr>
        <th> Order </th>
        <th> Name </th>
        <th> Version </th>
        <th> Active </th>
        <th> Visible </th>
        <th> Manage </th>
      </tr>
    </table>
  </div>




  <div id="divModules">
  </div>





</div>













<div id="Settings_tab" class="w3-cell-row dasherTab" style="display: none;"> <!-- Settings Tab -->
  <form action="index.php" method="post">
    <div class="w3-cell-row">
      <div class="w3-half">
        <div class="w3-border w3-border-gray w3-margin w3-padding-16">
          <h2> DreamCatcher Setup </h2>
          Network Address <input type="text" name="dreamCatcherAddress" id="dreamCatcherAddress" />
        </div>
      </div>
      <div class="w3-half">
        <div class="w3-border w3-border-gray w3-margin w3-padding-16">
          <h2> GOES Setup </h2>
          Network Address <input type="text" name="goesAddress" id="goesAddress" />
        </div>
      </div>
    </div>
    <div class="w3-cell-row">
      <div class="w3-half">
        <div class="w3-border w3-border-gray w3-margin w3-padding-16">
          <h2> GPS Setup </h2>
          Network Address <input type="text" name="gpsAddress" id="gpsAddress" />
        </div>
      </div>
      <div class="w3-half">
        <div class="w3-border w3-border-gray w3-margin w3-padding-16">
          <h2> Echolink Setup </h2>
          Network Address <input type="text" name="echolinkAddress" id="echolinkAddress" />
        </div>
      </div>
    </div>
    <div class="w3-cell-row">
      <div class="w3-half">
        <div class="w3-border w3-border-gray w3-margin w3-padding-16">
          <h2> QTH Setup </h2>
          <div>
            This is used for APRS and satellite tracking.
          </div>
          QTH Latitude <input type="text" name="qthLatitude" id="qthLatitude" /> <br />
          QTH Longitude <input type="text" name="qthLongitude" id="qthLongitude" /> <br />
          QTH Altitude <input type="text" name="qthAltitude" id="qthAltitude" /> (feet) <br />
          Default Map Zoom <input type="text" name="aprsZoom" id="aprsZoom" /> (default = 10)
        </div>
      </div>
      <div class="w3-half">
      </div>
    </div>
    <div class="w3-cell-row">
      <div class="w3-whole w3-center">
        <hr />
        <button class="w3-button w3-white w3-border w3-border-red w3-round-large" style="width: 10ch;" onclick=""> Cancel </button>
        <input type="submit" value="Save" class="w3-button w3-white w3-border w3-border-blue w3-round-large" style="width: 10ch;" onclick="" />
      </div>
    </div>
  </form>
</div>


<div id="Webmin_tab" class="w3-cell-row dasherTab" style="display: none;">
  <iframe src="https://<?php echo $_SERVER['SERVER_ADDR']; ?>:10000" style="width:100%;height:1000px;"></iframe>
</div>






  <!-- Footer -->
  <?php include "footer.php"; ?>

  <!-- End page content -->
</div>

<script type="text/javascript">
  var objNavbar;
  objNavbar = document.getElementById("navbar_overview");
  objNavbar.className += " w3-light-blue";
</script>

<script type="text/javascript">
  // Populate saved values
  document.getElementById("dreamCatcherAddress").value = _config.dreamCatcherAddress;
  document.getElementById("goesAddress").value = _config.goesAddress;
  document.getElementById("gpsAddress").value = _config.gpsAddress;
  document.getElementById("echolinkAddress").value = _config.echolinkAddress;
  document.getElementById("qthLatitude").value = _config.qthLatitude;
  document.getElementById("qthLongitude").value = _config.qthLongitude;
  document.getElementById("qthAltitude").value = _config.qthAltitude;
  document.getElementById("aprsZoom").value = _config.aprsZoom;

  json_request["dasher"] = true;
  getJSON("dasher");
</script>

<script type="text/javascript">
  initialize();
</script>

</body>
</html>

