<?php header('Access-Control-Allow-Origin: *'); ?>
<!DOCTYPE html>
<html>
<head>
	<title> Dasher </title>
  <!-- Head - links and meta -->
  <?php include "head.php"; ?>
  <?php include "dasher_js.php"; ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/satellite.js/4.0.0/satellite.min.js"></script>
  <!--<script src="https://cesium.com/downloads/cesiumjs/releases/1.81/Build/Cesium/Cesium.js"></script>-->
  <script src="Cesium/Build/Cesium/Cesium.js"></script>
  <link href="https://cesium.com/downloads/cesiumjs/releases/1.81/Build/Cesium/Widgets/widgets.css" rel="stylesheet">
  <script src="jspredict.js"></script>
</head>
<body class="w3-light-grey">
<script type="text/javascript">
  //const host = "http://<?php echo $_SERVER['SERVER_ADDR']; ?>";
  const host = "http://192.168.1.231";
  var arrAPRS = [];
  var json_request = [];
  var aprsFilter = "";
  var outnetFilter = "";
  var aprsMap;
  var json_artifacts = [];
  var json_modes = [];
  var json_satellites = [];
  var json_transmitters = [];
  var json_telemetry = [];
  var json_tle = [];
  var arrSatellites = [];
  var arrFavorites = [];
  var arrTelemetry = [];
  var arrTLEs = [];
  var strTLEs = "";
  var apiKeyN2YO = "XEB68E-E484UY-Y2BJNA-4LJ7";

  var qthMaidenhead = new Maidenhead(_config.qthLatitude, _config.qthLongitude, 2 /*precision*/);

  function initialize(strLoadType) {
    loadFile("sat_favorites.json");

    switch (strLoadType) {
      case "disk":
        loadFile("tle.txt");
        loadFile("sat_master.json");
        loadFile("sat_modes.json");
        loadFile("sat_satellites.json");
        loadFile("sat_transmitters.json");

        setTimeout(function() {
          if (arrSatellites.length >= 1) {
            generateAllSats();
            getJSON("update_modes");
          } else {
            getJSON("download_modes");
          }
        }, 100);

        break;

      case "web":
        getJSON("download_modes");
        break;
    }
  }

  function getJSON(strDataset) { /*  */
    var xhttp = new XMLHttpRequest();
    var strUrl;

    switch (strDataset){
      case "download_artifacts":
      case "update_artifacts":
        strUrl = bypassCORS("https://db.satnogs.org/api/artifacts/?format=json");
        break;
      case "download_modes":
      case "update_modes":
        strUrl = bypassCORS("https://db.satnogs.org/api/modes/?format=json");
        break;
      case "download_satellites":
      case "update_satellites":
        strUrl = bypassCORS("https://db.satnogs.org/api/satellites/?format=json");
        break;
      case "download_transmitters":
      case "update_transmitters":
        strUrl = bypassCORS("https://db.satnogs.org/api/transmitters/?format=json");
        break;
      case "download_telemetry":
      case "update_telemetry":
        strUrl = bypassCORS("https://db.satnogs.org/api/telemetry/?format=json");
        break;
      case "download_tle":
      case "update_tle":
        strUrl = bypassCORS("https://db.satnogs.org/api/tle/?format=json");
        break;
    }

    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        json_request[strDataset] = false;
        //console.log("Processing results");
        var strJSON = this.responseText;
        var s = strJSON;

        // remove non-printable and other non-valid JSON chars
        s = s.replace(/[\u0000-\u001F]+/g,""); 
        s = s.replace(/[\u0000-\u001F\u007F-\u009F]/g, "");

        var myObj = JSON.parse(s);

        switch (strDataset) {
          case "download_artifacts":
          case "update_artifacts":
            json_artifacts = myObj;
            break;
          case "download_modes":
          case "update_modes":
            json_modes = myObj;
            break;
          case "download_satellites":
          case "update_satellites":
            json_satellites = myObj;
            break;
          case "download_transmitters":
          case "update_transmitters":
            json_transmitters = myObj;
            break;
          case "download_telemetry":
          case "update_telemetry":
            json_telemetry = myObj;
            break;
          case "download_tle":
          case "update_tle":
            json_tle = myObj;
            break;
        }

        // Update UI...
        updateUI(strDataset);
      }
    };
    xhttp.open("GET", strUrl, true);
    xhttp.send();   
  }

  function updateUI(strDataset) {
    var d = new Date();
    switch (strDataset) {
      case "download_artifacts":
        break;
      case "update_artifacts":
        break;
      case "download_modes":
        saveFile("sat_modes.json", JSON.stringify(json_modes));
        downloadTLEs("satnogs");
        getJSON("download_transmitters");
        break;
      case "update_modes":
        saveFile("sat_modes.json", JSON.stringify(json_modes));
        downloadTLEs("satnogs");
        getJSON("update_transmitters");
        break;
      case "download_transmitters":
        saveFile("sat_transmitters.json", JSON.stringify(json_transmitters));
        getJSON("download_satellites");
        break;
      case "update_transmitters":
        saveFile("sat_transmitters.json", JSON.stringify(json_transmitters));
        getJSON("update_satellites");
        break;
      case "download_satellites":
        saveFile("sat_satellites.json", JSON.stringify(json_satellites));
        buildSatellitesArray();
        buildFavoritesArray();
        generateAllSats();
        break;
      case "update_satellites":
        saveFile("sat_satellites.json", JSON.stringify(json_satellites));
        buildSatellitesArray();
        buildFavoritesArray();
        generateAllSats();
        break;
      case "download_telemetry":
        saveFile("sat_telemetry.json", JSON.stringify(json_telemetry));
        updateTelemetry();
        break;
      case "update_telemetry":
        saveFile("sat_telemetry.json", JSON.stringify(json_telemetry));
        updateTelemetry();
        break;
      case "update_tle":
      case "download_tle":
        saveFile("sat_tle.json", JSON.stringify(json_tle));
        updateTLEs("satnogs");
        break;
      case "tle":
        break;
    }
  }

  function generateAllSats() {
    /*
    var div = document.getElementById("divAllSats");
    var tempArr = []; // Assigned below...
    //var tempArr = tempArr.concat(json_satellites);
    var tempArr = tempArr.concat(arrSatellites);

    var strHtml = "";

    while (tempArr.length > 0) {
      var sat = tempArr.shift();
      strHtml += generateSatelliteRow(sat);
    }

    div.innerHTML = strHtml;
    */
    updateFilterAllSats();
  }

  function updateFilterAllSats() {
    var boolFavorite = false;
    var boolMode = false;
    var boolBand = false;
    var boolDirection = false;
    var strMode = "";
    var strBand = "";
    var strDirection = "";
    var eFavorite = document.getElementById("allSats_Filter_Favorite");
    var eMode = document.getElementById("allSats_Filter_Mode");
    var eBand = document.getElementById("allSats_Filter_Band");
    var eDirection = document.getElementById("allSats_Filter_Direction");

    if (eFavorite.className == "fa fa-heart w3-text-red") {
      boolFavorite = true;
    }

    strMode = eMode.options[eMode.selectedIndex].text;
    strBand = eBand.options[eBand.selectedIndex].text;
    strDirection = eDirection.options[eDirection.selectedIndex].value;

    var div = document.getElementById("divAllSats");
    var tempArr = []; // Assigned below...
    var tempArr = tempArr.concat(arrSatellites);

    var strHtml = "";


    // Determine filter conditions
    if (strMode != "All") boolMode = true;
    if (strBand != "All") boolBand = true;
    if (strDirection != "All") boolDirection = true;


    while (tempArr.length > 0) {
      var satellite = tempArr.shift();
      var boolInclude = true;
      var boolFavoriteMatch = false;
      var boolTransmitterModeMatch = false;
      var boolTransmitterBandMatch = false;
      var boolTransmitterDirectionMatch = false;

      if (boolFavorite) boolFavoriteMatch = satellite.summary.favorite;

      if (boolMode) {
        var tempArrModes = [];
        var tempArrModes = tempArrModes.concat(satellite.transmitters);
        for (var i = 0; i < tempArrModes.length; i++) {
          if (strMode == tempArrModes[i].mode) boolTransmitterModeMatch = true;
        }
      }
      if (boolBand) {
        const arrBands = satellite.summary.bands.split(" ");
        for (var i = 0; i < arrBands.length; i++) {
          if (strBand == arrBands[i]) boolTransmitterBandMatch = true;
        }
      }
      if (boolDirection) {
        const arrDirections = satellite.summary.filterDirections.split(" ");
        for (var i = 0; i < arrDirections.length; i++) {
          if (strDirection == arrDirections[i]) boolTransmitterDirectionMatch = true;
        }
      }

      if (boolFavorite) boolInclude = boolInclude && boolFavoriteMatch;
      if (boolMode) boolInclude = boolInclude && boolTransmitterModeMatch;
      if (boolBand) boolInclude = boolInclude && boolTransmitterBandMatch;
      if (boolDirection) boolInclude = boolInclude && boolTransmitterDirectionMatch;

      if (boolInclude) {
        strHtml += generateSatelliteRow(satellite);
      }
    }

    div.innerHTML = strHtml;
  }

  function resetFilters() {
    var eFavorite = document.getElementById("allSats_Filter_Favorite");
    var eMode = document.getElementById("allSats_Filter_Mode");
    var eBand = document.getElementById("allSats_Filter_Band");
    var eDirection = document.getElementById("allSats_Filter_Direction");


    if (eFavorite.className == "fa fa-heart w3-text-red") {
      eFavorite.className = "fa fa-heart-o w3-text-black";
    }
    eMode.selectedIndex = 0;
    eBand.selectedIndex = 0;
    eDirection.selectedIndex = 0;
    updateFilterAllSats();
  }

  function toggleFavoritesFilter() {
    var element = document.getElementById("allSats_Filter_Favorite");

    if (element.className == "fa fa-heart-o w3-text-black") {
      element.className = "fa fa-heart w3-text-red";
    } else {
      element.className = "fa fa-heart-o w3-text-black";
    }
  }

  function generateSatelliteRow(satellite) {
    var strReturn = "";

    strReturn += "<li class=\"w3-bar\">";
    strReturn += "<span onclick=\"this.parentElement.style.display='none'\" ";
    strReturn += "class=\"w3-bar-item w3-button w3-xlarge w3-right\">&times;</span>";
    strReturn += "<img src=\"";
    if (satellite.image != "") {
      strReturn += "https://db.satnogs.org/media/" + satellite.image;
    } else {
      strReturn += "images/icons/Goes.png";
    }
    strReturn += "\" class=\"w3-bar-item w3-circle\" style=\"width:85px\">";
    strReturn += "<div class=\"w3-bar-item\">";
    strReturn += "<span class=\"w3-large\">" + satellite.name + "</span> ";
    if (satellite.names != undefined && satellite.names != "") {
      strReturn += "<span class=\"\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[ " + satellite.names + " ]</span>";
    }
    strReturn += "<br />";

    // Summary Row
    strReturn += "<span class=\"\">" + generateSatSummaryRow(satellite) + "</span><br>";

    // Modes button...
    strReturn += "<button onclick=\"toggleDisplay('AllSats_Transmitters_" + satellite.norad_cat_id + "')\" class=\"w3-button w3-block w3-left-align\"> Transmitters </button>";

    // Get Transmitters Expansion
    strReturn += "<div id=\"AllSats_Transmitters_" + satellite.norad_cat_id + "\" class=\"w3-container w3-hide\">";
    strReturn += generateTransmitters(satellite);
    strReturn += "</div>";



    strReturn += "</div>";
    strReturn += "</li>";

    return strReturn;
  }

  function getTransmittersArray(satellite) {
    var returnArr = []; //Array to return...
    var tempArr = []; // Assigned below...
    var tempArr = tempArr.concat(json_transmitters);

    while (tempArr.length > 0) {
      var transmitter = tempArr.shift();
      if (satellite.norad_cat_id == transmitter.norad_cat_id) {
        returnArr.push(transmitter);
      }
    }

    return returnArr;
  }

  function generateTransmitters(satellite) {
    var div = document.getElementById("divAllSats");
    var tempArr = []; // Assigned below...
    var tempArr = tempArr.concat(json_transmitters);

    var strHtml = "";
    var boolFirst = true;

    while (tempArr.length > 0) {
      var transmitter = tempArr.shift();
      if (satellite.norad_cat_id == transmitter.norad_cat_id) {
        if (boolFirst) {
          boolFirst = false;
        } else {
          strHtml += "<br />"; 
        }
        strHtml += generateTransmitterRow(transmitter);
      }
    }

    return strHtml;
  }
  
  function generateTransmitterRow(transmitter) {
    var strReturn = "";
    if (transmitter.uplink_low != undefined) strReturn += "<span><b>Uplink (MHz):</b> " + formatFrequency(transmitter.uplink_low) + "</span><br />";
    if (transmitter.downlink_low != undefined) strReturn += "<span><b>Downlink (MHz):</b> " + formatFrequency(transmitter.downlink_low) + "</span><br />";
    if (transmitter.type != undefined) strReturn += "<span><b>Type:</b> " + transmitter.type + "</span><br />";
    if (transmitter.mode != undefined) strReturn += "<span><b>Mode:</b> " + transmitter.mode + "</span><br />";
    if (transmitter.description != undefined) strReturn += "<span><b>Description:</b> " + transmitter.description + "</span><br />";
    if (transmitter.status != undefined) {
    strReturn += "<span><b>Status:</b> " + transmitter.status + "</span><br />";

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

    return strReturn;
  }
  
  function generateSatSummaryRow(satellite) {
    var strReturn = "";
    var boolFavorite = getIsFavorite(satellite);
    var strService = getServices(satellite);
    var strModes = getModes(satellite);
    var strBands = getBands(satellite);
    var strUpDown = getUpDown(satellite);
    var strActive = getActive(satellite);
    var strFavoriteIcon = "";
    var strFavoriteIconAfterClick = "";

    // Favorite...
    if (boolFavorite) {
      strFavoriteIcon = "fa fa-heart w3-text-red";
      strFavoriteIconAfterClick = "fa fa-heart-o w3-text-black";
    } else {
      strFavoriteIcon = "fa fa-heart-o w3-text-black";
      strFavoriteIconAfterClick = "fa fa-heart w3-text-red";
    }
    //element.classList.remove("mystyle");
    strReturn += "<div class=\"w3-container w3-cell w3-border-right\"><i class=\"" + strFavoriteIcon + "\" style=\"font-size:20px;\" onclick=\"setIsFavorite(" + satellite.norad_cat_id + "); this.className='" + strFavoriteIconAfterClick + "';\"></i></div>";

    // Services... class="w3-container w3-red w3-cell"
    strReturn += "<div class=\"w3-container w3-cell w3-border-right\"><b>Services:</b> " + strService + "</div>";

    // FM/CW/SSB...
    strReturn += "<div class=\"w3-container w3-cell w3-border-right\"><b>Modes:</b> " + strModes + "</div>";

    // 2m/70cm/10m/etc...
    strReturn += "<div class=\"w3-container w3-cell w3-border-right\"><b>Bands:</b> " + strBands + "</div>";

    // uplink / downlink...
    strReturn += "<div class=\"w3-container w3-cell w3-border-right\"><b>Direction:</b> " + strUpDown + "</div>";

    // active...
    strReturn += "<div class=\"w3-container w3-cell\"><b>Status:</b> " + strActive + "</div>";

    return strReturn;
  }

  function getIsFavorite(satellite) {
    var boolReturn = false;

    for (var i = 0; i < arrFavorites.length; i++) {
      if (satellite.norad_cat_id == arrFavorites[i].norad_cat_id) {
        boolReturn = true;
        break;
      }
    }

    /*
    if (satellite.summary.favorite != undefined) {
      boolReturn = satellite.summary.favorite;
    }
    */

    return boolReturn;
  }

  function setIsFavorite(norad_cat_id) {
    for (var i = 0; i < arrSatellites.length; i++) {
      if (arrSatellites[i].norad_cat_id == norad_cat_id) {
        arrSatellites[i].summary.favorite = !arrSatellites[i].summary.favorite;
        break;
      }
    }
    saveFile("sat_master.json", JSON.stringify(arrSatellites));
    buildFavoritesArray();
  }

  function getModes(satellite) {
    var strReturn = "";
    var boolFirst = true;
    var condensedModeArr = [];
    //var transmitterArr = getTransmittersArray(satellite);
    var transmitterArr = [].concat(satellite.transmitters);

    // Loop thru all transmitters...
    while (transmitterArr.length > 0) {
      var transmitter = transmitterArr.shift();
      var modeArr = []; // Assigned below...
      var modeArr = modeArr.concat(json_modes);

      while (modeArr.length > 0) {
        var mode = modeArr.shift();

        if (transmitter.mode_id == mode.id) {
          var boolFoundMode = false;
          for (let i = 0; i < condensedModeArr.length; i++) {
            if (mode.name == condensedModeArr[i]) {
              boolFoundMode = true;
              break;
            }            
          }

          if (!boolFoundMode) {
            condensedModeArr.push(mode.name);
          }
        }
      }
    }

    while (condensedModeArr.length > 0) {
      var strMode = condensedModeArr.shift();

      if (boolFirst) {
        boolFirst = false;
      } else {
        strReturn += ", "; 
      }
      strReturn += strMode;
    }

    return strReturn;
  }

  function getBands(satellite) {
    var strReturn = "";
    if (satellite.transmitters != undefined) {
      var transmitterArr = [].concat(satellite.transmitters);
    } else {
      var transmitterArr = getTransmittersArray(satellite);
    }
    var bool10m = false;
    var bool2m = false;
    var bool70cm = false;
    var bool33cm = false;
    var bool23cm = false;
    var bool13cm = false;
    var bool3cm = false;
    var bool1_25cm = false;

    // Loop thru all transmitters...
    while (transmitterArr.length > 0) {
      var transmitter = transmitterArr.shift();

      if (transmitter.uplink_low != undefined) {
        if (28000000 <= transmitter.uplink_low && transmitter.uplink_low <= 30000000) {
          bool10m = true;
        } else if (144000000 <= transmitter.uplink_low && transmitter.uplink_low <= 148000000) {
          bool2m = true;
        } else if (420000000 <= transmitter.uplink_low && transmitter.uplink_low <= 450000000) {
          bool70cm = true;
        } else if (902000000 <= transmitter.uplink_low && transmitter.uplink_low <= 928000000) {
          bool33cm = true;
        } else if (1240000000 <= transmitter.uplink_low && transmitter.uplink_low <= 1300000000) {
          bool23cm = true;
        } else if (2300000000 <= transmitter.uplink_low && transmitter.uplink_low <= 2450000000) {
          bool13cm = true;
        } else if (10000000000 <= transmitter.uplink_low && transmitter.uplink_low <= 10500000000) {
          bool3cm = true;
        } else if (24000000000 <= transmitter.uplink_low && transmitter.uplink_low <= 24250000000) {
          bool1_25cm = true;
        }
      }

      if (transmitter.downlink_low != undefined) {
        if (28000000 <= transmitter.downlink_low && transmitter.downlink_low <= 30000000) {
          bool10m = true;
        } else if (144000000 <= transmitter.downlink_low && transmitter.downlink_low <= 148000000) {
          bool2m = true;
        } else if (420000000 <= transmitter.downlink_low && transmitter.downlink_low <= 450000000) {
          bool70cm = true;
        } else if (902000000 <= transmitter.downlink_low && transmitter.downlink_low <= 928000000) {
          bool33cm = true;
        } else if (1240000000 <= transmitter.downlink_low && transmitter.downlink_low <= 1300000000) {
          bool23cm = true;
        } else if (2300000000 <= transmitter.downlink_low && transmitter.downlink_low <= 2450000000) {
          bool13cm = true;
        } else if (10000000000 <= transmitter.downlink_low && transmitter.downlink_low <= 10500000000) {
          bool3cm = true;
        } else if (24000000000 <= transmitter.downlink_low && transmitter.downlink_low <= 24250000000) {
          bool1_25cm = true;
        }
      }
    }

    if (bool10m) {
      strReturn += "10m ";
    }
    if (bool2m) {
      strReturn += "2m ";
    }
    if (bool70cm) {
      strReturn += "70cm ";
    }
    if (bool33cm) {
      strReturn += "33cm ";
    }
    if (bool23cm) {
      strReturn += "23cm ";
    }
    if (bool13cm) {
      strReturn += "13cm ";
    }
    if (bool3cm) {
      strReturn += "3cm ";
    }
    if (bool1_25cm) {
      strReturn += "1.25cm ";
    }

    return strReturn;
  }

  function getUpDown(satellite, boolReturnHtml = true) {
    var strReturn = "";
    var boolUplink = false;
    var boolDownlink = false;
    var transmitterArr = getTransmittersArray(satellite);
    var boolUplinkActive = false;
    var boolUplinkInactive = false;
    var boolDownlinkActive = false;
    var boolDownlinkInactive = false;

    // Loop thru all transmitters...
    while (transmitterArr.length > 0) {
      var transmitter = transmitterArr.shift();

      if (transmitter.uplink_low != undefined && transmitter.uplink_low != "") {
        boolUplink = true;
        if (transmitter.status == "active") {
          boolUplinkActive = true;
        }
        if (transmitter.status == "inactive") {
          boolUplinkInactive = true;
        }
      }
      if (transmitter.downlink_low != undefined && transmitter.downlink_low != "") {
        boolDownlink = true;
        if (transmitter.status == "active") {
          boolDownlinkActive = true;
        }
        if (transmitter.status == "inactive") {
          boolDownlinkInactive = true;
        }
      }
    }

    if (boolUplink) {
      if (boolUplinkActive && !boolUplinkInactive) {
        strReturn += "<span class=\"\"><i class=\"fa fa-arrow-up w3-green\" style=\"font-size:20px;\"></i></span>";
      } else if (boolUplinkActive && boolUplinkInactive) {
        strReturn += "<span class=\"\"><i class=\"fa fa-arrow-up w3-yellow\" style=\"font-size:20px;\"></i></span>";
      } else if (!boolUplinkActive && boolUplinkInactive) {
        strReturn += "<span class=\"\"><i class=\"fa fa-arrow-up w3-red\" style=\"font-size:20px;\"></i></span>";
      } else {
        strReturn += "<span class=\"\"><i class=\"fa fa-arrow-up\" style=\"font-size:20px;\"></i></span>";
      }
    }
    if (boolDownlink) {
      if (boolDownlinkActive && !boolDownlinkInactive) {
        strReturn += "<span class=\"\"><i class=\"fa fa-arrow-down w3-green\" style=\"font-size:20px;\"></i></span>";
      } else if (boolDownlinkActive && boolDownlinkInactive) {
        strReturn += "<span class=\"\"><i class=\"fa fa-arrow-down w3-yellow\" style=\"font-size:20px;\"></i></span>";
      } else if (!boolDownlinkActive && boolDownlinkInactive) {
        strReturn += "<span class=\"\"><i class=\"fa fa-arrow-down w3-red\" style=\"font-size:20px;\"></i></span>";
      } else {
        strReturn += "<span class=\"\"><i class=\"fa fa-arrow-down\" style=\"font-size:20px;\"></i></span>";
      }
    }

    if (boolReturnHtml) {
      return strReturn;
    } else {
      strReturn = "";
      if (boolUplink) strReturn += "up";
      if (boolUplink && boolDownlink) strReturn += " ";
      if (boolDownlink) strReturn += "down";
      return strReturn;
    }
  }

  function getActive(satellite) {
    var strReturn = "";
    var boolActive = false;
    var boolInactive = false;
    var transmitterArr = getTransmittersArray(satellite);

    // Loop thru all transmitters...
    while (transmitterArr.length > 0) {
      var transmitter = transmitterArr.shift();

      if (transmitter.status == "active") {
        boolActive = true;
      }
      if (transmitter.status == "inactive") {
        boolInactive = true;
      }
    }

    if (boolActive && !boolInactive) {
      strReturn += "<span class=\"w3-text-green\"><i class=\"fa fa-check-circle\" style=\"font-size:20px;\"></i></span>";
    }
    if (boolActive && boolInactive) {
      strReturn += "<span class=\"w3-text-yellow\"><i class=\"fa fa-exclamation-circle\" style=\"font-size:20px;\"></i></span>";
    }
    if (!boolActive && boolInactive) {
      strReturn += "<span class=\"w3-text-red\"><i class=\"fa fa-times-circle\" style=\"font-size:20px;\"></i></span>";
    }

    return strReturn;
  }

  function getServices(satellite) {
    var strReturn = "";
    var transmitterArr = getTransmittersArray(satellite);
    var condensedServiceArr = [];
    var boolFirst = true;

    while (transmitterArr.length > 0) {
      var transmitter = transmitterArr.shift();

      var boolFoundService = false;
      if (transmitter.service == "Unknown") {
        boolFoundService = true;
      } else {
        for (let i = 0; i < condensedServiceArr.length; i++) {
          if (transmitter.service == condensedServiceArr[i]) {
            boolFoundService = true;
            break;
          }            
        }
      }

      if (!boolFoundService) {
        condensedServiceArr.push(transmitter.service);
      }
    }

    while (condensedServiceArr.length > 0) {
      var strService = condensedServiceArr.shift();

      if (boolFirst) {
        boolFirst = false;
      } else {
        strReturn += ", "; 
      }
      strReturn += strService;
    }

    return strReturn;
  }

  function buildSatellitesArray() {
    var jSatellitesArr = []; // Assigned below...
    var jSatellitesArr = jSatellitesArr.concat(json_satellites);

    // clear arrSatellites...
    while (arrSatellites.length > 0) {
      arrSatellites.pop();
    }

    while (jSatellitesArr.length > 0) {
      var satellite = jSatellitesArr.shift();
      var jTransmitterArr = getTransmittersArray(satellite);
      satellite.transmitters = jTransmitterArr;

      var TLEs = getTLEs(satellite.norad_cat_id);
      satellite.tle = TLEs;

      var summary = {
        favorite:getIsFavorite(satellite),
        services:getServices(satellite),
        modes:getModes(satellite),
        bands:getBands(satellite),
        directions:getUpDown(satellite, true),
        filterDirections:getUpDown(satellite, false),
        active:getActive(satellite)
      };
      satellite.summary = summary;

      arrSatellites.push(satellite);
    }

    saveFile("sat_master.json", JSON.stringify(arrSatellites));
  }

  function refreshSatellitesArrayTLEs() {
    for (var i = 0; i < arrTLEs.length; i++) {
      for (var j= 0; j < arrSatellites.length; j++) {
        if (arrTLEs[i].norad_cat_id == arrSatellites[j].norad_cat_id) {
          arrSatellites[j].tle = arrTLEs[i];
          break;
        }
      }
    }
  }

  function buildFavoritesArray() {
    // clear arrFavorites...
    while (arrFavorites.length > 0) {
      arrFavorites.pop();
    }

    for (var i = 0; i < arrSatellites.length; i++) {
      if (arrSatellites[i].summary.favorite) {
        var favorite = {
          norad_cat_id: arrSatellites[i].norad_cat_id,
          tle_line1: arrSatellites[i].tle.line1,
          tle_line2: arrSatellites[i].tle.line2
        };
        arrFavorites.push(favorite);
      }
    }

    saveFile("sat_favorites.json", JSON.stringify(arrFavorites));
  }

  function getTLEs(norad_cat_id) {
    var returnTLE; //TLE to return...
    var tempArr = []; // Assigned below...
    var tempArr = tempArr.concat(arrTLEs);
    var boolFound = false;

    while (tempArr.length > 0 && !boolFound) {
      var tle = tempArr.shift();
      if (norad_cat_id.valueOf() == new Number(tle.norad_cat_id).valueOf()) {
        boolFound = true;
        returnTLE = tle;
        break;
      }
    }

    if (!boolFound) {
      // Is this a favorite?
      var tempArr = []; // Assigned below...
      var tempArr = tempArr.concat(arrFavorites);
      var boolFavorite = false;

      while (tempArr.length > 0 && !boolFavorite) {
        var favorite = tempArr.shift();
        if (norad_cat_id == favorite.norad_cat_id) {
          boolFavorite = true;
          break;
        }
      }

      if (boolFavorite) {
        var xhttp = new XMLHttpRequest();
        var strUrl = bypassCORS("https://api.n2yo.com/rest/v1/satellite/tle/" + norad_cat_id + "?apiKey=" + apiKeyN2YO);

        xhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
            console.log("getTLEs(" + norad_cat_id + "):  Downloading from N2YO...");
            strTLEs = this.responseText;
            strTLEs = strTLEs.replace(/[\u0000-\u001F]+/g,""); 
            strTLEs = strTLEs.replace(/[\u0000-\u001F\u007F-\u009F]/g, "");

            var myObj = JSON.parse(strTLEs);

            var objTLE = {
              norad_cat_id: myObj.info.satid,
              name: myObj.info.satname,
              line1: myObj.tle.split("\r\n", 2)[0],
              line2: myObj.tle.split("\r\n", 2)[1]
            }

            arrTLEs.push(objTLE);


            updateTLEs("n2yo", norad_cat_id);
          }
        };

        xhttp.open("GET", strUrl, true);
        xhttp.send();   



      }

    }

    if (!boolFound) {
      var returnTLE = {
        line1: "",
        line2: "",
        name: "",
        norad_cat_id: norad_cat_id
      }
    }

    return returnTLE;
  }

  function updateTelemetry() {
    var jTelemetryArr = []; // Assigned below...
    var jTelemetryArr = jTelemetryArr.concat(json_telemetry);

    // clear arrSatellites...
    while (arrTelemetry.length > 0) {
      arrTelemetry.pop();
    }

    while (jTelemetryArr.length > 0) {
      var telemetry = jTelemetryArr.shift();

      arrTelemetry.push(telemetry);
    }

    saveFile("sat_telemetry.json", JSON.stringify(arrTelemetry));
  }

  function downloadTLEs(strSource) {
    var xhttp = new XMLHttpRequest();
    var strUrl;

    switch (strSource) {
      case "celestrak":
        var xhttp = new XMLHttpRequest();
        var strUrl = bypassCORS("http://celestrak.com/NORAD/elements/gp.php?GROUP=amateur&FORMAT=tle");

        xhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
            console.log("downloadTle('celestrak'):  Downloading " + strUrl);
            strTLEs = this.responseText;
            saveFile("tle.txt", strTLEs)
            updateTLEs(strSource);
          }
        };

        xhttp.open("GET", strUrl, true);
        xhttp.send();   
        break;

      case "satnogs":
        getJSON("download_tle");
        break;
    }
  }

  function updateTLEs(strSource, norad_cat_id) {
    switch (strSource) {
      case "celestrak":
        var arrTemp = strTLEs.split("\r\n");

        // clear arrTLEs...
        while (arrTLEs.length > 0) {
          arrTLEs.pop();
        }

        while (arrTemp.length > 0) {
          var tle_0 = arrTemp.shift();
          if (tle_0.substr(0,2) != "1 " && tle_0.substr(0,2) != "2 " && tle_0 != "") {
            var tle_1 = arrTemp.shift();
            var tle_2 = arrTemp.shift();

            var objTLE = {
              norad_cat_id: tle_1.substr(2,5),
              name: tle_0,
              line1: tle_1,
              line2: tle_2
            }

            arrTLEs.push(objTLE);
          }
        }
        break;

      case "satnogs":
        var arrTemp = [];
        var arrTemp = arrTemp.concat(json_tle);

        // clear arrTLEs...
        while (arrTLEs.length > 0) {
          arrTLEs.pop();
        }

        while (arrTemp.length > 0) {
          var tle = arrTemp.shift();
          var objTLE = {
            norad_cat_id: tle.norad_cat_id,
            name: tle.tle0,
            line1: tle.tle1,
            line2: tle.tle2
          }

          arrTLEs.push(objTLE);
        }
        break;

      case "n2yo":
        for (var i = 0; i < arrTLEs.length; i++) {
          if (arrTLEs[i].norad_cat_id == norad_cat_id) {
            // update arrSatellites if exists
            for (var j = 0; j < arrSatellites.length; j++) {
              if (arrTLEs[i].norad_cat_id == arrSatellites[j].norad_cat_id) {
                arrSatellites[j].tle.line1 = arrTLEs[i].line1;
                arrSatellites[j].tle.line2 = arrTLEs[i].line2;
                arrSatellites[j].tle.name = arrTLEs[i].name;
                j = arrSatellites.length; // stop the loop
              }
            }

            // update arrFavorites if exists
            for (var j = 0; j < arrFavorites.length; j++) {
              if (arrTLEs[i].norad_cat_id == arrFavorites[j].norad_cat_id) {
                arrFavorites[j].tle_line1 = arrTLEs[i].line1;
                arrFavorites[j].tle_line2 = arrTLEs[i].line2;
                j = arrFavorites.length; // stop the loop
              }
            }
            i = arrTLEs.length; // stop the loop
          }
  
        }
        saveFile("sat_master.json", JSON.stringify(arrSatellites));
        saveFile("sat_favorites.json", JSON.stringify(arrFavorites));
        break;
    }
    console.log("finished running updateTLEs('" + strSource + "')");
  }

  function toggleDisplay(id) {
    var x = document.getElementById(id);
    if (x.className.indexOf("w3-show") == -1) {
      x.className += " w3-show";
    } else { 
      x.className = x.className.replace(" w3-show", "");
    }
  }

  function formatFrequency(frequency) {
    var strReturn = "";
    var strFreq = frequency + "";
    for (let i = 0; i < strFreq.length - 3; i++) {
      var strDigit = strFreq.substr(i, 1)

      if (i == strFreq.length - 9 && strFreq.length > 9) strReturn += ","
      if (i == strFreq.length - 6) strReturn += "."
      strReturn += strDigit;
    }

    return strReturn;
  }

  function generateCurrentPositions() {
    var div = document.getElementById("divCurrentSatPositions");
    var tempArr = []; // Assigned below...
    var tempArr = tempArr.concat(arrSatellites);

    var strHtml = "<table class='w3-table w3-striped w3-hoverable'>";
    strHtml += "<tr>";
    strHtml += "<th> <b> Satellite </b> </th>";
    strHtml += "<th> AZ </th>";
    strHtml += "<th> EL </th>";
    strHtml += "<th> Range </th>";
    strHtml += "<th> Next AOS </th>";
    strHtml += "<th> Next LOS </th>";
    strHtml += "<th> Footprint </th>";
    strHtml += "<th> Altitude </th>";
    strHtml += "<th> Velocity </th>";
    strHtml += "<th> Doppler </th>";
    strHtml += "</tr>";

    // take favorites
    while (tempArr.length > 0) {
      var satellite = tempArr.shift();

      if (satellite.summary.favorite) {
        // for each favorite, get current position with observe()
        var tle;
        try {
          tle = satellite.tle.name + "\r\n" + satellite.tle.line1 + "\r\n" + satellite.tle.line2;
        } catch(err) {
          tle = "";
        }
        var jp = new jspredict();
        var pstart = new Date();
        var qth = [];
        qth[0] = _config.qthLatitude;
        qth[1] = _config.qthLongitude;
        qth[2] = _config.qthAltitude / 3; // ~ 1200'

        var objObserve = jp.observe(tle, qth, pstart);

        // build table row for display
        strHtml += generateCurrentSatellitePositionRow(satellite, objObserve);
      }
    }
    strHtml += "</table>";

    div.innerHTML = strHtml;
  }

  function generateCurrentSatellitePositionRow(satellite, objObserve) {
    var strHtml = "";

    strHtml += "<tr id='trPos_" + satellite.norad_cat_id + "' onclick='selectSat(this);'>";
    strHtml += "<td>" + satellite.name + "</td>";
    try {
      strHtml += "<td>" + objObserve.azimuth.toFixed(2) + "</td>";
    } catch(err) {
      strHtml += "<td><i>error</i></td>";
    }
    try {
      strHtml += "<td>" + objObserve.elevation.toFixed(2) + "</td>";
    } catch(err) {
      strHtml += "<td><i>error</i></td>";
    }
    try {
      strHtml += "<td>" + objObserve.rangeSat.toFixed(0) + "</td>";
    } catch(err) {
      strHtml += "<td><i>error</i></td>";
    }
    strHtml += "<td>" + "" + "</td>";
    strHtml += "<td>" + "" + "</td>";
    try {
      strHtml += "<td>" + objObserve.footprint.toFixed(0) + "</td>";
    } catch(err) {
      strHtml += "<td><i>error</i></td>";
    }
    try {
      strHtml += "<td>" + objObserve.altitude.toFixed(0) + "</td>";
    } catch(err) {
      strHtml += "<td><i>error</i></td>";
    }
    strHtml += "<td>" + "" + "</td>";
    try {
      strHtml += "<td>" + objObserve.doppler + "</td>";
    } catch(err) {
      strHtml += "<td><i>error</i></td>";
    }
    strHtml += "<tr>";

    return strHtml;
  }

  function selectSat(row) {
    var norad_cat_id = row.id.replace("trPos_", "");
    //alert(row.id);
    trackInViewer(norad_cat_id);
  }

  function saveFile(filename, content) {
    var xhttp = new XMLHttpRequest();
    var strUrl = "saveFile.php";

    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        console.log("saveFile(): filename = '" + filename + "' - Successful");
      }
    };
    xhttp.open("POST", strUrl, true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("filename=" + filename + "&content=" + encodeURIComponent(content));   
  }

  function loadFile(filename) {
    var xhttp = new XMLHttpRequest();
    var strUrl = "loadFile.php?filename=" + filename;
    var strReturn = "";

    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        console.log("loadFile(): filename = '" + filename + "' - Successful");
        switch (filename) {
          case "tle.txt":
            strTLEs = this.responseText;
            updateTLEs("celestrak");
            break;
          case "sat_favorites.json":
            try {
              arrFavorites = JSON.parse(this.responseText);
            } catch(err) {

            }
            break;
          case "sat_master.json":
            arrSatellites = JSON.parse(this.responseText);
            break;
          case "sat_modes.json":
            json_modes = JSON.parse(this.responseText);
            break;
          case "sat_satellites.json":
            json_satellites = JSON.parse(this.responseText);
            break;
          case "sat_transmitters.json":
            json_transmitters = JSON.parse(this.responseText);
            break;
        }
      }
    };

    xhttp.open("GET", strUrl, true);
    xhttp.send();   
  }

  function track() {
    const ISS_TLE = 
      `1 25544U 98067A   21122.75616700  .00027980  00000-0  51432-3 0  9994
       2 25544  51.6442 207.4449 0002769 310.1189 193.6568 15.48993527281553`;
    // Initialize the satellite record with this TLE
    const satrec = satellite.twoline2satrec(
      ISS_TLE.split('\n')[0].trim(), 
      ISS_TLE.split('\n')[1].trim()
    );
    // Get the position of the satellite at the given date
    const date = new Date();
    const positionAndVelocity = satellite.propagate(satrec, date);
    const gmst = satellite.gstime(date);
    const position = satellite.eciToGeodetic(positionAndVelocity.position, gmst);

    console.log(position.longitude);// in radians
    console.log(position.latitude);// in radians
    console.log(position.height);// in km

    // Initialize the Cesium viewer.
    const viewer = new Cesium.Viewer('cesiumContainer', {
      imageryProvider: new Cesium.TileMapServiceImageryProvider({
        url: Cesium.buildModuleUrl("Assets/Textures/NaturalEarthII"),
      }),
      baseLayerPicker: false, geocoder: false, homeButton: false, infoBox: false,
      navigationHelpButton: false, sceneModePicker: false
    });
    viewer.scene.globe.enableLighting = true;

    const satellitePoint = viewer.entities.add({
      position: Cesium.Cartesian3.fromRadians(
        position.longitude, position.latitude, position.height * 1000
      ),
      point: { pixelSize: 5, color: Cesium.Color.RED }
    });
  }

  function findPass() {
    // These 2 lines are published by NORAD and allow us to predict where
    // the ISS is at any given moment. They are regularly updated.
    // Get the latest from: https://celestrak.com/satcat/tle.php?CATNR=25544. 
    const ISS_TLE = 
    `1 25544U 98067A   21121.52590485  .00001448  00000-0  34473-4 0  9997
    2 25544  51.6435 213.5204 0002719 305.2287 173.7124 15.48967392281368`;
    const satrec = satellite.twoline2satrec(
      ISS_TLE.split('\n')[0].trim(), 
      ISS_TLE.split('\n')[1].trim()
    );
    // Give SatelliteJS the TLE's and a specific time.
    // Get back a longitude, latitude, height (km).
    // We're going to generate a position every 10 seconds from now until 6 seconds from now. 
    const totalSeconds = 60 * 60 * 6;
    const timestepInSeconds = 10;
    const start = Cesium.JulianDate.fromDate(new Date());
    const stop = Cesium.JulianDate.addSeconds(start, totalSeconds, new Cesium.JulianDate());
    viewer.clock.startTime = start.clone();
    viewer.clock.stopTime = stop.clone();
    viewer.clock.currentTime = start.clone();
    viewer.timeline.zoomTo(start, stop);
    viewer.clock.multiplier = 40;
    viewer.clock.clockRange = Cesium.ClockRange.LOOP_STOP;
    
    const positionsOverTime = new Cesium.SampledPositionProperty();
    for (let i = 0; i < totalSeconds; i+= timestepInSeconds) {
      const time = Cesium.JulianDate.addSeconds(start, i, new Cesium.JulianDate());
      const jsDate = Cesium.JulianDate.toDate(time);

      const positionAndVelocity = satellite.propagate(satrec, jsDate);
      const gmst = satellite.gstime(jsDate);
      const p   = satellite.eciToGeodetic(positionAndVelocity.position, gmst);


      const position = Cesium.Cartesian3.fromRadians(p.longitude, p.latitude, p.height * 1000);
      positionsOverTime.addSample(time, position);
    } 
  }

  function initializeCesiumViewer() {
    document.getElementById('cesiumContainer').innerHTML = "";
    // Initialize the Cesium viewer.
    const viewer = new Cesium.Viewer('cesiumContainer', {
      imageryProvider: new Cesium.TileMapServiceImageryProvider({
        url: Cesium.buildModuleUrl("Assets/Textures/NaturalEarthII"),
      }),
      baseLayerPicker: false, geocoder: false, homeButton: false, infoBox: false,
      navigationHelpButton: false, sceneModePicker: false
    });

    // This causes a bug on android, see: https://github.com/CesiumGS/cesium/issues/7871
    // viewer.scene.globe.enableLighting = true;
    // These 2 lines are published by NORAD and allow us to predict where
    // the ISS is at any given moment. They are regularly updated.
    // Get the latest from: https://celestrak.com/satcat/tle.php?CATNR=25544. 
    var satrec = [];


    for (let i = 0; i < arrFavorites.length; i++) {

      const objTLE = getTLEs(arrFavorites[i].norad_cat_id);    
      console.log(objTLE);
      console.log("A - getTLEs(): [" + objTLE.line1 + "]")
      console.log("A - getTLEs(): [" + objTLE.line2 + "]")
      console.log("B - arrFavorites[]: [" + arrFavorites[i].tle_line1.trim() + "]");
      console.log("B - arrFavorites[]: [" + arrFavorites[i].tle_line2.trim() + "]");

      satrec[i] = satellite.twoline2satrec(
        arrFavorites[i].tle_line1.trim(), 
        arrFavorites[i].tle_line2.trim()
      );
    }

    // Give SatelliteJS the TLE's and a specific time.
    // Get back a longitude, latitude, height (km).
    // We're going to generate a position every 10 seconds from now until 6 hours from now. 
    const totalSeconds = 60 * 60 * 6;
    const timestepInSeconds = 10;
    const start = Cesium.JulianDate.fromDate(new Date());
    const stop = Cesium.JulianDate.addSeconds(start, totalSeconds, new Cesium.JulianDate());
    viewer.clock.startTime = start.clone();
    viewer.clock.stopTime = stop.clone();
    viewer.clock.currentTime = start.clone();
    viewer.timeline.zoomTo(start, stop);
    viewer.clock.multiplier = 40;
    viewer.clock.clockRange = Cesium.ClockRange.LOOP_STOP;

console.log("initializeCesiumViewer(): Start getting positionsOverTime...");

    var positionsOverTime = [];
    for (let n = 0; n < arrFavorites.length; n++) {
      positionsOverTime[n] = new Cesium.SampledPositionProperty();
      for (let i = 0; i < totalSeconds; i+= timestepInSeconds) {
        try {
          const time = Cesium.JulianDate.addSeconds(start, i, new Cesium.JulianDate());
          const jsDate = Cesium.JulianDate.toDate(time);

          const positionAndVelocity = satellite.propagate(satrec[n], jsDate);
          const gmst = satellite.gstime(jsDate);
          const p   = satellite.eciToGeodetic(positionAndVelocity.position, gmst);

          const position = Cesium.Cartesian3.fromRadians(p.longitude, p.latitude, p.height * 1000);
          positionsOverTime[n].addSample(time, position);
        } catch (err) {
          console.log("positionsOverTime[" + n + "]");
          //console.log(err);
          const time = Cesium.JulianDate.addSeconds(start, i, new Cesium.JulianDate());
          const jsDate = Cesium.JulianDate.toDate(time);

          const positionAndVelocity = satellite.propagate(satrec[0], jsDate);
          const gmst = satellite.gstime(jsDate);
          const p   = satellite.eciToGeodetic(positionAndVelocity.position, gmst);

          const position = Cesium.Cartesian3.fromRadians(p.longitude, p.latitude, p.height * 1000);
          positionsOverTime[n].addSample(time, position);
        }


      }
    }

    // Visualize the satellite with a red dot.
    var satellitePoint = [];
    for (let i = 0; i < arrFavorites.length; i++) {
      satellitePoint[i] = viewer.entities.add({
        position: positionsOverTime[i],
        point: { pixelSize: 5, color: Cesium.Color.YELLOW }
      });
    }

    // Set the camera to follow the satellite 
//    viewer.trackedEntity = satellitePoint[0];
    // Wait for globe to load then zoom out     
    let initialized = false;
    viewer.scene.globe.tileLoadProgressEvent.addEventListener(() => {
      if (!initialized && viewer.scene.globe.tilesLoaded === true) {
        viewer.clock.shouldAnimate = true;
        initialized = true;
        viewer.scene.camera.zoomOut(7000000);
        //document.querySelector("#loading").classList.toggle('disappear', true)
      }
    });
  }

  function trackInViewer(norad_cat_id) {
    document.getElementById('cesiumContainer').innerHTML = "";
    // Initialize the Cesium viewer.
    const viewer = new Cesium.Viewer('cesiumContainer', {
      imageryProvider: new Cesium.TileMapServiceImageryProvider({
        url: Cesium.buildModuleUrl("Assets/Textures/NaturalEarthII"),
      }),
      baseLayerPicker: false, geocoder: false, homeButton: false, infoBox: false,
      navigationHelpButton: false, sceneModePicker: false
    });

    // This causes a bug on android, see: https://github.com/CesiumGS/cesium/issues/7871
    // viewer.scene.globe.enableLighting = true;
    // These 2 lines are published by NORAD and allow us to predict where
    // the ISS is at any given moment. They are regularly updated.
    // Get the latest from: https://celestrak.com/satcat/tle.php?CATNR=25544. 
    /**/
    const ISS_TLE = 
    `1 25544U 98067A   21121.52590485  .00001448  00000-0  34473-4 0  9997
    2 25544  51.6435 213.5204 0002719 305.2287 173.7124 15.48967392281368`;
    /**/
    const objTLE = getTLEs(norad_cat_id);    
    console.log(objTLE);
    console.log("A [" + objTLE.line1 + "]")
    console.log("A [" + objTLE.line2 + "]")
    console.log("B [" + ISS_TLE.split('\n')[0].trim() + "]");
    console.log("B [" + ISS_TLE.split('\n')[1].trim() + "]");
    //const ISS_TLE = objTLE.line1 + "\r\n" + objTLE.line2;
/*
    const satrec = satellite.twoline2satrec(
      ISS_TLE.split('\n')[0].trim(), 
      ISS_TLE.split('\n')[1].trim()
    );
*/
    const satrec = satellite.twoline2satrec(
      objTLE.line1.trim(), 
      objTLE.line2.trim()
    );
/**/
    // Give SatelliteJS the TLE's and a specific time.
    // Get back a longitude, latitude, height (km).
    // We're going to generate a position every 10 seconds from now until 6 hours from now. 
    const totalSeconds = 60 * 60 * 6;
    const timestepInSeconds = 10;
    const start = Cesium.JulianDate.fromDate(new Date());
    const stop = Cesium.JulianDate.addSeconds(start, totalSeconds, new Cesium.JulianDate());
    viewer.clock.startTime = start.clone();
    viewer.clock.stopTime = stop.clone();
    viewer.clock.currentTime = start.clone();
    viewer.timeline.zoomTo(start, stop);
    viewer.clock.multiplier = 40;
    viewer.clock.clockRange = Cesium.ClockRange.LOOP_STOP;
    
    const positionsOverTime = new Cesium.SampledPositionProperty();
    for (let i = 0; i < totalSeconds; i+= timestepInSeconds) {
      const time = Cesium.JulianDate.addSeconds(start, i, new Cesium.JulianDate());
      const jsDate = Cesium.JulianDate.toDate(time);

      const positionAndVelocity = satellite.propagate(satrec, jsDate);
      const gmst = satellite.gstime(jsDate);
      const p   = satellite.eciToGeodetic(positionAndVelocity.position, gmst);

      //console.log(p);
      //console.log("Longitude: " + p.longitude / Math.PI * 180 + ", Latitude: " + p.latitude / Math.PI * 180);

      const position = Cesium.Cartesian3.fromRadians(p.longitude, p.latitude, p.height * 1000);

      positionsOverTime.addSample(time, position);
    }
    
    // Visualize the satellite with a red dot.
    const satellitePoint = viewer.entities.add({
      position: positionsOverTime,
      point: { pixelSize: 5, color: Cesium.Color.RED }
    });

    // Set the camera to follow the satellite 
    viewer.trackedEntity = satellitePoint;
    // Wait for globe to load then zoom out     
    let initialized = false;
    viewer.scene.globe.tileLoadProgressEvent.addEventListener(() => {
      if (!initialized && viewer.scene.globe.tilesLoaded === true) {
        viewer.clock.shouldAnimate = true;
        initialized = true;
        viewer.scene.camera.zoomOut(7000000);
        //document.querySelector("#loading").classList.toggle('disappear', true)
      }
    });
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
    <button id="groundControl_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black w3-light-blue" style="padding-bottom: 0px;" onclick="openDasherTab(event,'groundControl_tab', this);initializeCesiumViewer();"><b><img src="images/icons/DreamCatcher.png" alt="DreamCatcher" height="20" width="20"> Ground Control </b></button>
    <button id="allSats_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event,'allSats_tab', this)"><img src="images/icons/Goes.png" alt="GOES" height="20" width="20"> Satellites </button>
    <button id="predictions_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event,'predictions_tab', this)"><img src="images/icons/gps.png" alt="GPS" height="20" width="20"> Predictions </button>
    <button id="status_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event,'status_tab', this)"><img src="images/icons/DreamCatcher.png" alt="DreamCatcher" height="20" width="20"> Status </button>
  </div>

<div id="groundControl_tab" class="w3-cell-row dasherTab" style="display:block;">
  Summary
  <div class="w3-row-padding" style="margin:0 -16px">
  </div>
  <div id="cesiumContainer" style="width: 99%; height: 50%"></div>


  <div id="divCurrentSatPositions">
    Loading data...
  </div>

</div>

<div id="allSats_tab" class="w3-cell-row dasherTab" style="display:none;">
  <div style="padding-left: 1ch;">
    Filters
    <i id="allSats_Filter_Favorite" class="fa fa-heart-o w3-text-black" style="font-size:20px;" onclick="toggleFavoritesFilter();updateFilterAllSats();"></i>
    <label for="allSats_Filter_Mode">Mode:</label>
    <select id="allSats_Filter_Mode" name="allSats_Filter_Mode" onchange="updateFilterAllSats()">
      <option value="">All</option>
      <option value="FM">FM</option>
      <option value="FMN">FMN</option>
      <option value="USB">USB</option>
      <option value="LSB">LSB</option>
      <option value="CW">CW</option>
      <option value="SSTV">SSTV</option>
      <option value="PSK31">PSK31</option>
      <option value="FSK AX.25 G3RUH">FSK AX.25 G3RUH</option>
      <option value="AFSK">AFSK</option>
      <option value="AFSK TUBiX10">AFSK TUBiX10</option>AFSK TUBiX10
      <option value="BPSK">BPSK</option>
      <option value="FPSK">FPSK</option>
      <option value="QPSK">QPSK</option>
      <option value="QPSK31">QPSK31</option>
      <option value="GFSK">GFSK</option>
      <option value="GMSK">GMSK</option>
      <option value="MSK">MSK</option>
      <option value="FSK">FSK</option>
      <option value="PSK">PSK</option>
      <option value="AHRPT">AHRPT</option>
      <option value="HRPT">HRPT</option>
      <option value="LRPT">LRPT</option>
      <option value="APT">APT</option>
      <option value="BPSK PMT-A3">BPSK PMT-A3</option>
      <option value="CERTO">CERTO</option>
      <option value="DUV">DUV</option>
      <option value="DSTAR">DSTAR</option>
      <option value="OFDM">OFDM</option>
      <option value="FSK AX.100 Mode 5">FSK AX.100 Mode 5</option>
      <option value="MSK AX.100 Mode 5">MSK AX.100 Mode 5</option>
    </select>

    <label for="allSats_Filter_Band">Band:</label>
    <select id="allSats_Filter_Band" name="allSats_Filter_Band" onchange="updateFilterAllSats()">
      <option value="">All</option>
      <option value="10M">10m</option>
      <option value="2M">2m</option>
      <option value="70cm">70cm</option>
      <option value="33cm">33cm</option>
      <option value="23cm">23cm</option>
      <option value="13cm">13cm</option>
      <option value="3cm">3cm</option>
      <option value="1.25cm">1.25cm</option>
    </select>

    <label for="allSats_Filter_Direction">Direction:</label>
    <select id="allSats_Filter_Direction" name="allSats_Filter_Direction" onchange="updateFilterAllSats()">
      <option value="All">All</option>
      <option value="down">Downlink</option>
      <option value="up">Uplink</option>
    </select>

    <button class="w3-btn w3-white w3-border w3-border-blue w3-round-large w3-padding-small" onclick="resetFilters()">Reset</button>
  </div>
  <div id="divAllSats">
    Loading data...
  </div>
</div>

<div id="predictions_tab" class="w3-cell-row dasherTab" style="display:none;">
</div>

<div id="status_tab" class="w3-cell-row dasherTab" style="display:none;">
  <iframe id="status_iframe" src="/bin/bypassCORS.php?target=<?php echo rawurlencode("https://www.amsat.org/status/") ?>" style="width:100%;height:800px;"></iframe>
</div>

<div id="json-modal" class="w3-modal">
  <div class="w3-modal-content w3-dark-gray" style="padding: 2px;">
    <div class="w3-container w3-black">
      <span onclick="document.getElementById('json-modal').style.display='none'" class="w3-button w3-display-topright w3-xlarge" style="margin-top: 2px; margin-right: 2px;">&times;</span>
        <div id="json-details"></div>
    </div>
  </div>
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
  objNavbar = document.getElementById("navbar_keps");
  objNavbar.className += " w3-light-blue";

  setIFrameHeight("status_iframe");

  initialize("disk");

  var tle = `OSCAR 7 (AO-7)          
1  7530U 74089B   22106.50575287 -.00000046  00000-0  62345-6 0  9990
2  7530 101.9006  86.1910 0012316 158.9820 314.1098 12.53654208169946`;
  var jp = new jspredict();
  var pstart = new Date();
  var pend = new Date();
  pend.setDate(pstart.getDate() + 1);
  var minElevation = 0;
  var qth = [];
  qth[0] = 38.378597;
  qth[1] = -78.735183;
  qth[2] = 390; // ~ 1280'

  qth[0] = _config.qthLatitude;
  qth[1] = _config.qthLongitude;
  qth[2] = _config.qthAltitude / 3.281;

  var objObserve = jp.observe(tle, qth, pstart);
  var objTransits = jp.transits(tle, qth, pstart, pend, minElevation/*, maxTransits*/);

  for (var i = 0; i < objTransits.length; i++) {
    console.log("Pass #" + (i + 1));
    var dtStart = new Date(objTransits[i].start);
    var dtEnd = new Date(objTransits[i].end);
    console.log(" - AOS = " + dtStart);
    console.log(" - LOS = " + dtEnd);
  }

  var objObserves = jp.observes(tle, qth, pstart, pend, 120);



  console.log("jspredict: " + objObserve);

  setInterval(function() {
    if (arrSatellites.length >= 1) {
      generateCurrentPositions();
    }
  }, 1000);


  setTimeout(function() {
    initializeCesiumViewer();
  }, 5000);
//  initializeCesiumViewer();
</script>


</body>
</html>

