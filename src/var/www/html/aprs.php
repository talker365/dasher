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
  const MAX_SUMMARY_RECORDS_TO_DISPLAY = 100;
  var refreshRate = 15000;
  var arrAPRS = [];
  var arrMarkers = [];
  var arrSummaryAPRS = [];
  var arrOutnetAPRS = [];
  var arrKnownCallsigns = [];
  var json_request = [];
  var aprsFilter = "";
  var outnetFilter = "";
  var aprsMap;

  var qthMaidenhead = new Maidenhead(_config.qthLatitude, _config.qthLongitude, 2 /*precision*/);


  setInterval(function() {
    if (!json_request["aprs"]) {
      json_request["aprs"] = true;
        //console.log("Calling for updates");
      getJSON("aprs");
    }
    if (!json_request["outnet"]) {
      json_request["outnet"] = true;
        //console.log("Calling for updates");
      getJSON("outnet");
    }
  }, refreshRate);

  function getJSON(strDataset) { /*  */
    var xhttp = new XMLHttpRequest();
    var strUrl  = host;

    switch (strDataset){
      case "aprs":
        strUrl += "/bin/getData.php?feed=aprs&records=100"
        break;
      case "outnet":
        strUrl += "/bin/getData.php?feed=outnet&records=100"
        break;
    }

    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        json_request[strDataset] = false;
        //console.log("Processing results");
        var strJSON = this.responseText;
        switch (strDataset) {
          case "aprs":
          case "outnet":
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



            var myObj = JSON.parse(s);

            var arrCurrentAPRS = myObj.records;

            switch (strDataset) {
              case "aprs":
                arrAPRS = arrCurrentAPRS;
                //console.log("getJSON(\"" + strDataset + "\"): arrAPRS = arrCurrentAPRS;");
                break;
              case "outnet":
                arrOutnetAPRS = arrCurrentAPRS;
                //console.log("getJSON(\"" + strDataset + "\"): arrOutnetAPRS = arrCurrentAPRS;");
                break;
            }
            updateArrSummaryAPRS(arrCurrentAPRS);
            //console.log("getJSON(\"" + strDataset + "\"): updateArrSummaryAPRS(arrCurrentAPRS);");

            // Clear Known Callsigns Array...
            arrKnownCallsigns = [];

            // Update UI...
            updateUI(strDataset);
            //console.log("getJSON(\"" + strDataset + "\"): updateUI(strDataset);");

            break;
        }
      }
    };
    xhttp.open("GET", strUrl, true);
    xhttp.send();   
  }

  function updateUI(strDataset) {
    //console.log("APRS Array: " + Array.isArray(arrAPRS));
    var d = new Date();
    switch (strDataset) {
      case "aprs":
        updateAprsFeed("aprs");
        document.getElementById("lastUpdatedAprs").innerHTML = d.toString();
        document.getElementById("lastUpdatedSummary").innerHTML = d.toString();
        break;
      case "outnet":
        updateAprsFeed("outnet");
        document.getElementById("lastUpdatedOutnet").innerHTML = d.toString();
        document.getElementById("lastUpdatedSummary").innerHTML = d.toString();
        break;
    }
    updateAprsFeed("summary");
  }

  function updateArrSummaryAPRS(arrCurrentAPRS) {
    var newArr = [];
    // add new records
    //newArr = arrSummaryAPRS.concat(arrCurrentAPRS);
    newArr = newArr.concat(arrAPRS, arrOutnetAPRS);

    // sort by time
    newArr.sort(compareAprsRecords);

    // trim array to 50 records
    while (newArr.length > MAX_SUMMARY_RECORDS_TO_DISPLAY) {
      newArr.pop();
    }

    arrSummaryAPRS = newArr;
  }

  function compareAprsRecords(a, b) {
    // Use toUpperCase() to ignore character casing
    const recordA = convertAprsDateTime(a.time);
    const recordB = convertAprsDateTime(b.time);

    let comparison = 0;
    if (recordA > recordB) {
      comparison = -1;
    } else if (recordA < recordB) {
      comparison = 1;
    }
    return comparison;
  }

  function updateAprsFeed(feedName) {
    var strTable = ""; // Set below...
    switch (feedName) {
      case "aprs":
        strTable = "recentAprsTable";
        break;
      case "outnet":
        strTable = "outnetAprsTable";
        break;
      case "summary":
        strTable = "summaryAprsTable";
        break;
    }
    //console.log("updateAprsFeed(): feedName=" + feedName + ", strTable=" + strTable);
    var t = document.getElementById(strTable);
    var i = t.rows.length;
    //console.log("APRS Table length = " + i);

    var tempArr = []; // Assigned below...
    switch (feedName) {
      case "aprs":
        var tempArr = tempArr.concat(arrAPRS);
        break;
      case "outnet":
        var tempArr = tempArr.concat(arrOutnetAPRS);
        break;
      case "summary":
        var tempArr = tempArr.concat(arrSummaryAPRS);
        break;
    }
    var strRows = "";
    while (tempArr.length > 0) {
      var record = tempArr.shift();
      var strRow
      switch (feedName) {
        case "aprs":
        case "outnet":
          var strRow = generateAprsTableRow(record, feedName);
          break;
        case "summary":
          var strRow = generateSummaryAprsTableRow(record);
          break;
      }
      strRows += strRow;
    }
    document.getElementById(strTable).tBodies.item(0).innerHTML = strRows;

    //console.log(strRows);
  }

  function findCallsign(record) {
    var boolFound = false;

    for (var i = 0; i < arrKnownCallsigns.length; i++) {
      if (arrKnownCallsigns[i].from == record.from) {
        boolFound = true;
        break;
      }
    }
    return boolFound;
  }

  function generateAprsTableRow(value, feedName) {
    var strFilter = "";
    switch (feedName) {
      case "aprs":
        strFilter = aprsFilter;
        break;
      case "outnet":
        strFilter = outnetFilter;
        break;
    }

    var strRow = "";


    //if ((strFilter != "" && strFilter.toUpperCase() == value.from.toUpperCase()) || strFilter == "") {
    if ((strFilter != "" && value.from.toUpperCase().startsWith(strFilter.toUpperCase())) || strFilter == "") {
      var chrSymbol = "";
      if (value.symbol == "error") {
        chrSymbol = ".".charCodeAt(0);
      } else {
        chrSymbol = value.symbol.charCodeAt(0);
      }

      //strRow = "<tr onclick='showJsonData(" + JSON.stringify(value) + ");'>";
      strRow = "<tr>";
      strRow += "<td>" + value.time + " (" + getTimeDifference(convertAprsDateTime(value.time)) + ")</td>";
      strRow += "<td>" + value.from + "</td>";
      strRow += "<td>" + value.to + "</td>";
      strRow += "<td>" + value.latitude.substr(0,9) + "</td>";
      strRow += "<td>" + value.longitude.substr(0,10)  + "</td>";
      strRow += "<td>" + value.comment + "</td>";
      strRow += "<td>" + value.via + "</td>";
      strRow += "<td><img src='images/aprs/svg/" + chrSymbol + "-1.svg' alt='" + value.symbol + "(" + chrSymbol + ")' /></td>";
      strRow += "<!--<td>" + value.raw + "</td>-->";
      strRow += "<td>" + value.path + "</td>";
      strRow += "</tr>";
    }


    return strRow;
  }

  function generateSummaryAprsTableRow(value) {
    var strRow = "";
    var strStationLocator = "";
    var strDistance = "";
    var strBearing = "";

    if (!findCallsign(value)) {
      arrKnownCallsigns.unshift(value);   

      var chrSymbol = "";
      if (value.symbol == "error") {
        chrSymbol = ".".charCodeAt(0);
      } else {
        chrSymbol = value.symbol.charCodeAt(0);
      }

      //strRow = "<tr onclick='showJsonData(" + JSON.stringify(value) + ");'>";
      strRow = "<tr>";
      strRow += "<td><img src='images/aprs/svg/" + chrSymbol + "-1.svg' alt='" + value.symbol + "(" + chrSymbol + ")' /></td>";
      strRow += "<td>" + value.from;
      if (value.source == "RF") {
        strRow += " <i class=\"fa fa-bullseye\"></i> ";
      } else {
        strRow += " <img src=\"images/icons/Goes.png\" alt=\"GOES\" height=\"20\" width=\"20\"> ";
      }
      strRow += "</td>";
      strRow += "<td>" + getTimeDifference(convertAprsDateTime(value.time)) + "</td>";

      if (value.latitude.substr(0,9) != "error" && value.longitude.substr(0,10) != "error") {

        var precision = 2;         //optional defaults to 5
        var station = new Maidenhead(value.latitude.substr(0,9), value.longitude.substr(0,10), precision);
        strStationLocator = station.locator;
        strDistance = qthMaidenhead.distanceTo(station);
        strBearing = qthMaidenhead.bearingTo(station);
      }


      strRow += "<td>" + strStationLocator + "</td>";
      strRow += "<td>" + convertMetersToFeet(strDistance*0.190291262135922) + " mi</td>";
      strRow += "<td>" + strBearing + "º</td>";




      strRow += "</tr>";

      if (value.latitude.substr(0,9) != "error" && value.longitude.substr(0,10) != "error") {
        //addMarkerToMap(value.latitude.substr(0,9), value.longitude.substr(0,10));
        // Target's GPS coordinates.
        var target = L.latLng(value.latitude.substr(0,9), value.longitude.substr(0,10));

        // Place a marker on the same location.
        //L.marker(target).addTo(aprsMap);
        //addMarkerToMap(value.latitude.substr(0,9), value.longitude.substr(0,10), value.from, chrSymbol);
        addMarkerToMap(value, chrSymbol);
      }


    }
    return strRow;
  }

  function convertAprsDateTime(heardDate) {
    //    APRS heardDate format:  Tue Mar 3 03:58:08 2020
    //  OUTNET heardDate format:  Fri Mar  6 18:53:38 2020
    heardDate = heardDate.replace("  ", " ");
    var arrDate = heardDate.split(" ");
    var arrTime = arrDate[3].split(":");
    var heardYear = arrDate[4];
    var heardMonth;
    switch(arrDate[1]) {
      case "Jan":
        heardMonth = 0;
        break;
      case "Feb":
        heardMonth = 1;
        break;
      case "Mar":
        heardMonth = 2;
        break;
      case "Apr":
        heardMonth = 3;
        break;
      case "May":
        heardMonth = 4;
        break;
      case "Jun":
        heardMonth = 5;
        break;
      case "Jul":
        heardMonth = 6;
        break;
      case "Aug":
        heardMonth = 7;
        break;
      case "Sep":
        heardMonth = 8;
        break;
      case "Oct":
        heardMonth = 9;
        break;
      case "Nov":
        heardMonth = 10;
        break;
      case "Dec":
        heardMonth = 11;
        break;
      default:
        heardMonth = 0;
    }
    var heardDay = arrDate[2];
    var heardHour = arrTime[0];
    var heardMinute = arrTime[1];
    var heardSecond = arrTime[2];

    //  7 numbers specify year, month, day, hour, minute, second, and millisecond (in that order):
    var dateHeard = new Date(heardYear, heardMonth, heardDay, heardHour, heardMinute, heardSecond, 0);

    return dateHeard;
  }

  function getTimeDifference(dateHeard) {
    //console.log("getTimeDifference("+heardDate+")");
    var dateNow = new Date();
    dateNow = Date.now();

    //Offset for UTC:
    var n = dateHeard.getTimezoneOffset();
    dateNow = dateNow + (n * 60 * 1000);


    //console.log("heardDate=" + heardDate + ", dateHeard=" + dateHeard + ", dateNow=" + dateNow + ", dateHeard=" + dateHeard);

    var dateDiff = dateNow - dateHeard;
    //console.log("dateDiff=" + dateDiff);
    dateDiff = dateDiff / 1000; // Turn milliseconds into seconds...

    var intSeconds = Math.floor(dateDiff % 60);
    var intMinutes = Math.floor((dateDiff / 60) % 60);
    var intHours = Math.floor((dateDiff / 60 / 60) % 24);
    var intDays = Math.floor((dateDiff / 60 / 60 / 24));

    //console.log("intDays=" + intDays + ", intHours=" + intHours + ", intMinutes=" + intMinutes + ",intSeconds=" + intSeconds);
    var strReturn = "";

    if (intDays > 0) {
      strReturn += intDays + "d ";
    }

    if (intHours > 0 || strReturn.length > 0) {
      strReturn += intHours + "h ";
    }

    if (intMinutes > 0 || strReturn.length > 0) {
      strReturn += intMinutes + "m ";
    }

    if (intSeconds > 0 || strReturn.length > 0) {
      strReturn += intSeconds + "s";
    }

    return strReturn;
  }

  function initializeMap() {
    // Where you want to render the map.
    var element = document.getElementById('osm-map');

    // Height has to be set. You can do this in CSS too.
    element.style = 'height:750px;';

    // Create Leaflet map on map element.
    //var aprsMap = L.map(element);
    aprsMap = L.map(element);
    
    /*
    // Add OSM tile layer to the Leaflet map.
    L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(aprsMap);
    */


    // Create layers for the map...
    const basemaps = {
      StreetView: L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png',   {attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'}),
      Topography: L.tileLayer.wms('http://ows.mundialis.de/services/service?',   {layers: 'TOPO-WMS'}),
      Places: L.tileLayer.wms('http://ows.mundialis.de/services/service?', {layers: 'OSM-Overlay-WMS'})
    };


    // Load all layers to map...
    L.control.layers(basemaps).addTo(aprsMap);

    // Set default map layer to display...
    basemaps.StreetView.addTo(aprsMap);



    // Target's GPS coordinates.
    var mapQth = L.latLng(_config.qthLatitude, _config.qthLongitude);

    // Set map's center to target with zoom 10...
    var zoom = 10;
    if (_config.aprsZoom != undefined) {
      zoom = _config.aprsZoom;
    }
    aprsMap.setView(mapQth, zoom);

    // Place a marker on the same location.
    L.marker(mapQth).addTo(aprsMap)
                    .bindPopup("QTH" + "<br /> Lat/Lon: " + _config.aprsLatitude + ", " + _config.aprsLongitude + "<br /> " + "My QTH")
                    .bindTooltip("QTH");
  }

  function addMarkerToMap(value, chrSymbol) {
    var latitude = value.latitude.substr(0,9);
    var longitude = value.longitude.substr(0,10);
    var callsign = value.from;
    var comment = value.comment;

    // Is the callsign already in the arrMarkers array?
    if (arrMarkers[callsign] != undefined) {
      // Remove existing marker for callsign...
      removeMarkerFromMap(callsign);
    }

    // Create icon for the marker...
    var aprsIcon = L.icon({
      iconUrl:  "images/aprs/svg/" + chrSymbol + "-1.svg",
      iconSize: [40, 40],
    });

    // Create a marker and save in array...
    var target = L.latLng(latitude, longitude);
    arrMarkers[callsign] = new L.Marker(target, {icon: aprsIcon});

    // Add callsign to icon...
    //var strPopup = callsign + "<br /> Lat/Lon: " + latitude + ", " + longitude + "<br /> " + comment;


    var strPopup = '<div class="w3-cell-row">';
    strPopup    += '  <div class="w3-half w3-left-align">';
    strPopup    += '    <h4> ' + callsign + ' </h4>';
    strPopup    += '  </div>';
    strPopup    += '  <div class="w3-half w3-right-align">';
    strPopup    += '    <h6> ' + value.source + ' </h6>';
    strPopup    += '  </div>';
    strPopup    += '</div>';
    strPopup    += '<div class="w3-cell-row">';
    strPopup    += '  <div class="w3-whole" style="text-overflow:ellipsis;">';
    strPopup    += '    Lat/Lon: ' + latitude + ', ' + longitude + '';
    strPopup    += '  </div>';
    strPopup    += '</div>';
    strPopup    += '<div class="w3-cell-row">';
    strPopup    += '  <div class="w3-whole" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">';
    strPopup    += '    Lat/Lon: ' + comment + '';
    strPopup    += '  </div>';
    strPopup    += '</div>';



    arrMarkers[callsign].bindPopup(strPopup);
    arrMarkers[callsign].bindTooltip(callsign);

    // Place marker on the map...
    arrMarkers[callsign].addTo(aprsMap);
  }

  function removeMarkerFromMap(callsign) {
    // Remove marker
    aprsMap.removeLayer(arrMarkers[callsign]);
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
    <button id="summary_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black w3-light-blue" style="padding-bottom: 0px;" onclick="openDasherTab(event,'summary_tab', this)"><b><i class="fa fa-dashboard"></i> Summary </b></button>
    <button id="activity_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event,'activity_tab', this)"><b><i class="fa fa-bullseye"></i> Activity </b></button>
    <button id="aprsFi_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event,'aprsFi_tab', this)"><i class="fa fa-car"></i> aprs.fi </button>
    <button id="outnet_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event,'outnet_tab', this)"><img src="images/icons/Goes.png" alt="GOES" height="20" width="20">Outnet </button>
  </div>

<div id="summary_tab" class="w3-cell-row dasherTab" style="display:block;">
  Summary
  <div class="w3-row-padding" style="margin:0 -16px">
    <div class="w3-third">
      <h5>
        Feeds
        <span class="w3-tiny">
          [last updated <span id="lastUpdatedSummary"></span>]
        </span>
      </h5>
      <table id="summaryAprsTable" class="w3-table w3-striped w3-white">
        <tbody></tbody>
      </table>
    </div>
    <div class="w3-twothird">
      <h5>APRS Nearby</h5>
      <div id="osm-map"></div>
    </div>
  </div>
</div>

<div id="activity_tab" class="w3-cell-row dasherTab" style="display:none;">
  <h1>
    Recent APRS Stations
    <span class="w3-tiny">
    [last updated <span id="lastUpdatedAprs"></span>]
    </span>
 </h1>
  Filter by Callsign <input type="text" name="aprsCallsign" id="aprsCallsign" />
  <button class="w3-button w3-white w3-border w3-border-blue w3-round-large" style="width: 10ch;" onclick="aprsFilter = document.getElementById('aprsCallsign').value;"> Filter </button>
  <button class="w3-button w3-white w3-border w3-border-red w3-round-large" style="width: 10ch;" onclick="aprsFilter = ''; document.getElementById('aprsCallsign').value = '';"> Reset </button>
  <table id="recentAprsTable" class="w3-table w3-striped w3-white">
    <thead>
      <td> Time </td>
      <td> From </td>
      <td> To </td>
      <td> Latitude </td>
      <td> Longitude </td>
      <td> Comment </td>
      <td> Via </td>
      <td> Symbol </td>
      <!--<td> Raw </td>-->
      <td> Path </td>
    </thead>
    <tbody></tbody>
  </table>
</div>

<div id="aprsFi_tab" class="w3-cell-row dasherTab" style="display:none;">
  <iframe id="aprsFi_iframe" src="https://aprs.fi.REMOVEPOPUPREDIRECT/#!lat=38.4025&lng=-78.6321" style="width:100%;height:800px;"></iframe>
</div>

<div id="outnet_tab" class="w3-cell-row dasherTab" style="display:none;">
  <h1>
    Outnet APRS Messages
    <span class="w3-tiny">
    [last updated <span id="lastUpdatedOutnet"></span>]
    </span>
  </h1>
  Filter by Callsign <input type="text" name="outnetCallsign" id="outnetCallsign" />
  <button class="w3-button w3-white w3-border w3-border-blue w3-round-large" style="width: 10ch;" onclick="outnetFilter = document.getElementById('outnetCallsign').value;"> Filter </button>
  <button class="w3-button w3-white w3-border w3-border-red w3-round-large" style="width: 10ch;" onclick="outnetFilter = ''; document.getElementById('outnetCallsign').value = '';"> Reset </button>
  <table id="outnetAprsTable" class="w3-table w3-striped w3-white">
    <thead>
      <td> Time </td>
      <td> From </td>
      <td> To </td>
      <td> Latitude </td>
      <td> Longitude </td>
      <td> Comment </td>
      <td> Via </td>
      <td> Symbol </td>
      <!--<td> Raw </td>-->
      <td> Path </td>
    </thead>
    <tbody></tbody>
  </table>
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
  objNavbar = document.getElementById("navbar_aprs");
  objNavbar.className += " w3-light-blue";

  setIFrameHeight("aprsFi_iframe");

</script>

<!-- Setup APRS Map -->
<script>
  initializeMap();
</script>

<!-- Kickstart getting data -->
<script>
  json_request["aprs"] = true;
  getJSON("aprs");
  json_request["outnet"] = true;
  getJSON("outnet");
</script>

</body>
</html>

