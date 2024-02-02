<!DOCTYPE html>
<html>
<head>
	<title> Dasher </title>
  <!-- Head - links and meta -->
  <?php include "head.php"; ?>
  <?php include "dasher_js.php"; ?>
</head>
<body class="w3-light-grey">

<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
<script type="text/javascript" src='http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js'></script>
<script type="text/javascript" src="gnssradar/lib/highchartsv4/highcharts.js"></script>
<script type="text/javascript" src="gnssradar/lib/highchartsv4/modules/exporting.js"></script>
<script type="text/javascript" src="gnssradar/lib/highchartsv4/highcharts-more.js"></script>
<script type="text/javascript" src="gnssradar/lib/satellite/satellite.js"></script>
<script type="text/javascript" src="gnssradar/lib/sylvester/sylvester.js"></script>

<script type="text/javascript">
  const host = "http://" + _config.gpsAddress;
  var refreshRate = 1000;
  var arrGPS = [];
  var json_request = false;
  var countSatellites = 0;

  setInterval(function() {
    if (!json_request) {
      json_request = true;
        //console.log("Calling for updates");
      getJSON("gps");
    }
  }, refreshRate);

  function getJSON(strDataset) { /*  */
    var xhttp = new XMLHttpRequest();
    var strUrl  = host;

    switch (strDataset){
      case "gps":
        strUrl += "/getGps.php"
        break;
    }

    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        json_request = false;
        //console.log("Processing results");
        var strJSON = this.responseText;
        switch (strDataset) {
          case "gps":
            strJSON = strJSON.replace(/[^\x0F-\xFF]/g, "");
            strJSON = strJSON.replace("},]}", "}]}");

            var s = strJSON;
            s = s.replace(/\\n/g, "\\n")  
                 .replace(/\\'/g, "\\'")
                 .replace(/\\"/g, '\\"')
                 .replace(/\\&/g, "\\&")
                 .replace(/\\r/g, "\\r")
                 .replace(/\\t/g, "\\t")
                 .replace(/\\b/g, "\\b")
                 .replace(/\\f/g, "\\f");

            // remove non-printable and other non-valid JSON chars
            s = s.replace(/[\u0000-\u001F]+/g,""); 

            // remove some odd escape characters
            s = s.replace("\\&", "\\\\&");
            s = s.replace("\\l", "\\\\l");

            var myObj = JSON.parse(s);

            //var myObj = JSON.parse(strJSON);
            arrGPS = myObj.records;

            // Update UI...
            updateUI(strDataset);

            break;
        }
      }
    };
    xhttp.open("GET", strUrl, true);
    xhttp.send();   
  }

  function updateUI(strDataset) {
    arrGPS.forEach(displayGpsData);

    var strTable = ""; // Set below...
    switch (strDataset) {
      case "gps":
        strTable = "satelliteTable";
        break;
    }
    var t = document.getElementById(strTable);
    var i = t.rows.length;

    var tempArr = []; // Assigned below...
    switch (strDataset) {
      case "gps":
        var tempArr = tempArr.concat(arrGPS);
        break;
    }
    while (tempArr.length > 0) {
      var gpsRecord = tempArr.shift();
      switch (strDataset) {
        case "gps":
          if (gpsRecord.class == "SKY") {
            countSatellites = 0;
            //console.log("Resetting locked satellite count to zero, processing new JSON data...");
            var strRows = "";
            var arrSatellites = [];
            var arrSatellites = arrSatellites.concat(gpsRecord.satellites);
            while (arrSatellites.length > 0) {
              var satellite = arrSatellites.shift();
              var strRow = generateSatelliteTableRow(satellite);
              strRows += strRow;
            }
            document.getElementById(strTable).tBodies.item(0).innerHTML = strRows;
          }
          break;
      }
    }
  }

  function displayGpsData(gps) {
    switch (gps.class) {
      case "TPV":
        try {
          document.getElementById("cell_datetime").innerText = gps.time.replace("T", " ").replace(".000Z", " UTC");
        } catch (err) {
          document.getElementById("cell_datetime").innerText = err;
        }
        try {
          document.getElementById("cell_latitude").innerText = gps.lat;
        } catch (err) {
          document.getElementById("cell_latitude").innerText = err;
        }
        try {
          document.getElementById("cell_longitude").innerText = gps.lon;
        } catch (err) {
          document.getElementById("cell_longitude").innerText = err;
        }
        try {
          document.getElementById("cell_altitude").innerText = convertMetersToFeet(gps.alt) + " feet (" + gps.alt + "m)";
        } catch (err) {
          document.getElementById("cell_altitude").innerText = err;
        }
        /*
        try {
          document.getElementById("cell_heading").innerText = gps.track;
        } catch (err) {
          document.getElementById("cell_heading").innerText = err;
        }
        try {
          document.getElementById("cell_speed").innerText = gps.speed;
        } catch (err) {
          document.getElementById("cell_speed").innerText = err;
        }
        try {
          document.getElementById("cell_climb").innerText = gps.climb;
        } catch (err) {
          document.getElementById("cell_climb").innerText = err;
        }
        */
        try {
          document.getElementById("cell_status").innerText = countSatellites + " sats locked";
        } catch (err) {
          document.getElementById("cell_status").innerText = err;
        }
        try {
          document.getElementById("cell_timeoffset").innerText = getTimeDifference(convertGpsDateTime(gps.time));
          //console.log("getTimeDifference(convertGpsDateTime(gps.time)) = " + getTimeDifference(convertGpsDateTime(gps.time)));
        } catch (err) {
          document.getElementById("cell_timeoffset").innerText = err;
        }
        try {
          var latitude  = gps.lat; //mandatory
          var longitude = gps.lon;  //mandatory
          var precision = 3;         //optional defaults to 5

          var gridsquare     = new Maidenhead(latitude, longitude, precision);

          var maidenheadLocator = gridsquare.locator;
          //console.log('has valid locator: ' + Maidenhead.valid(maidenheadLocator));

          //console.log("latitude: %s, longitude: %s, Maidenhead: %s", gridsquare.lat, gridsquare.lon, maidenheadLocator);
          //console.log("[lat, lon] = ", Maidenhead.toLatLon(maidenheadLocator));

          document.getElementById("cell_gridsquare").innerText = maidenheadLocator;
        } catch (err) {
          document.getElementById("cell_gridsquare").innerText = err;
        }

        break;
      case "DEVICES":
        try {
          document.getElementById("cell_device").innerText = gps.devices[0].path;
        } catch (err) {
          document.getElementById("cell_device").innerText = err;
        }
        try {
          document.getElementById("cell_driver").innerText = gps.devices[0].driver;
        } catch (err) {
          document.getElementById("cell_driver").innerText = err;
        }
        try {
          document.getElementById("cell_activated").innerText = gps.devices[0].activated;
        } catch (err) {
          document.getElementById("cell_activated").innerText = err;
        }
        try {
          document.getElementById("cell_bps").innerText = gps.devices[0].bps;
        } catch (err) {
          document.getElementById("cell_bps").innerText = err;
        }
        try {
          document.getElementById("cell_parity").innerText = gps.devices[0].parity;
        } catch (err) {
          document.getElementById("cell_parity").innerText = err;
        }
        try {
          document.getElementById("cell_stopbits").innerText = gps.devices[0].stopbits;
        } catch (err) {
          document.getElementById("cell_stopbits").innerText = err;
        }
        try {
          document.getElementById("cell_cycle").innerText = gps.devices[0].cycle;
        } catch (err) {
          document.getElementById("cell_cycle").innerText = err;
        }
        try {
          document.getElementById("cell_mincycle").innerText = gps.devices[0].mincycle;
        } catch (err) {
          document.getElementById("cell_mincycle").innerText = err;
        }
        break;
    }
  }

  function generateSatelliteTableRow(satellite) {
    var strRow = "<tr";
    if (satellite.used) {
      strRow += " class=\"w3-blue\" style=\"text-shadow:1px 1px 0 #444\">";
    } else {
      strRow += ">";
    }

    strRow += "<td>";
    try {
      strRow += satellite.PRN;
    } catch (err) {
      strRow += err;
    }
    strRow += "</td>";

    strRow += "<td>";
    try {
      strRow += satellite.el;
    } catch (err) {
      strRow += err;
    }
    strRow += "</td>";

    strRow += "<td>";
    try {
      strRow += satellite.az;
    } catch (err) {
      strRow += err;
    }
    strRow += "</td>";

    strRow += "<td>";
    try {
      strRow += satellite.ss;
    } catch (err) {
      strRow += err;
    }
    strRow += "</td>";

    strRow += "<td>";
    try {
      strRow += satellite.used;
      if (satellite.used) {
        countSatellites++;
        //console.log("Locked onto satellite '" + satellite.PRN + "', locked satellite count is now " + countSatellites);
      } else {
        //console.log("No lock on satellite '" + satellite.PRN + "', locked satellite count is still " + countSatellites);
      }
    } catch (err) {
      strRow += err;
    }
    strRow += "</td>";

    strRow += "</tr>";

    return strRow;
  }

  function convertGpsDateTime(heardDate) {
    //    APRS heardDate format:  Tue Mar 3 03:58:08 2020
    //  OUTNET heardDate format:  Fri Mar  6 18:53:38 2020
    //     GPS heardDate format:  2020-04-12T03:42:58.000Z
    //console.log("heardDate = " + heardDate);
    heardDate = heardDate.replace("-", " ");
    heardDate = heardDate.replace("-", " ");
    heardDate = heardDate.replace("T", " ");
    heardDate = heardDate.replace(".000Z", "");
    //console.log("heardDate = " + heardDate);
    var arrDate = heardDate.split(" ");
    var arrTime = arrDate[3].split(":");
    var heardYear = arrDate[0];
    var heardMonth;
    switch(arrDate[1]) {
      case "01":
        heardMonth = 0;
        break;
      case "02":
        heardMonth = 1;
        break;
      case "03":
        heardMonth = 2;
        break;
      case "04":
        heardMonth = 3;
        break;
      case "05":
        heardMonth = 4;
        break;
      case "06":
        heardMonth = 5;
        break;
      case "07":
        heardMonth = 6;
        break;
      case "08":
        heardMonth = 7;
        break;
      case "09":
        heardMonth = 8;
        break;
      case "10":
        heardMonth = 9;
        break;
      case "11":
        heardMonth = 10;
        break;
      case "12":
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

    //console.log("dateHeard = " + dateHeard);
    return dateHeard;
  }

  function getTimeDifference(dateHeard) {
    //console.log("getTimeDifference("+dateHeard+")");
    var dateNow = new Date();
    dateNow = Date.now();

    //Offset for UTC:
    var n = dateHeard.getTimezoneOffset();
    dateNow = dateNow + (n * 60 * 1000);


    //console.log(" dateHeard=" + dateHeard + ", dateNow=" + dateNow);

    var dateDiff = dateNow - dateHeard;
    //console.log("dateDiff=" + dateDiff);
    dateDiff = Math.abs(dateDiff / 1000); // Turn milliseconds into seconds...

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

    if (intDays + intHours + intMinutes + intSeconds == 0) {
      strReturn += intSeconds + "s";
    }

    return strReturn;
  }


  //var Maidenhead = require('maidenhead');

  var latitude  = 50.879087; //mandatory
  var longitude = 4.701169;  //mandatory
  var precision = 6;         //optional defaults to 5

  var townHall     = new Maidenhead(latitude, longitude, precision);

  var maidenheadLocator = townHall.locator;
  console.log('has valid locator: ' + Maidenhead.valid(maidenheadLocator));

  console.log("latitude: %s, longitude: %s, Maidenhead: %s", townHall.lat, townHall.lon, maidenheadLocator);
  console.log("[lat, lon] = ", Maidenhead.toLatLon(maidenheadLocator));

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
  </div>

  <div id="summary_tab" class="w3-cell-row dasherTab" style="display:block;">
    <div class="w3-cell-row">
      <div class="w3-cell w3-third"> <!-- Current Data -->
        <h1> Current Data </h1>
        <table>
          <tr>
            <td> Time: </td>
            <td id="cell_datetime" />
          </tr>
          <tr>
            <td> Latitude: </td>
            <td id="cell_latitude" />
          </tr>
          <tr>
            <td> Longitude: </td>
            <td id="cell_longitude" />
          </tr>
          <tr>
            <td> Altitude: </td>
            <td id="cell_altitude" />
          </tr>
          <!--
          <tr>
            <td> Speed: </td>
            <td id="cell_speed" />
          </tr>
          <tr>
            <td> Heading: </td>
            <td id="cell_heading" />
          </tr>
          <tr>
            <td> Climb: </td>
            <td id="cell_climb" />
          </tr>
        -->
          <tr>
            <td> Status: </td>
            <td id="cell_status" />
          </tr>
          <tr>
            <td> Time offset: </td>
            <td id="cell_timeoffset" />
          </tr>
          <tr>
            <td> Grid Square: </td>
            <td id="cell_gridsquare" />
          </tr>
        </table> 
      </div>
      <div class="w3-cell w3-third"> <!-- Current Satellites -->
        <div class="w3-margin">
          <h1> Satellites </h1>
          <table id="satelliteTable" class="w3-table w3-striped w3-white">
            <thead>
              <th> PRN: </th>
              <th> Elev: </th>
              <th> Azim: </th>
              <th> SNR: </th>
              <th> Used: </th>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
      <div class="w3-cell w3-third"> <!-- Current Device -->
        <h1> Device </h1>
        <table>
          <tr>
            <td> Device: </td>
            <td id="cell_device" />
          </tr>
          <tr>
            <td> Driver: </td>
            <td id="cell_driver" />
          </tr>
          <tr>
            <td> Activated: </td>
            <td id="cell_activated" />
          </tr>
          <tr>
            <td> bps: </td>
            <td id="cell_bps" />
          </tr>
          <tr>
            <td> parity: </td>
            <td id="cell_parity" />
          </tr>
          <tr>
            <td> stopbits: </td>
            <td id="cell_stopbits" />
          </tr>
          <tr>
            <td> cycle: </td>
            <td id="cell_cycle" />
          </tr>
          <tr>
            <td> mincycle: </td>
            <td id="cell_mincycle" />
          </tr>
        </table> 
      </div>
    </div>
  </div>

<div class="w3-panel">
    <div id="box1">
        <div id="map"></div>
        <div id="box2">
            <div id="skychart"></div>
            <div id="nsatchart"></div>
        </div>
    </div>
    <script type="text/javascript">
        var map;
        var orglat, orglon, orgllh;
        var orgmarker;
        var elemask, offhr, dop;
        var tint;
        var maxntimes = 200;
        var ntimes;
        var tind = 0;
        var time, times = new Array(maxntimes);
        var utcoffset; // min.
        var gpsazels = new Array(maxntimes);
        var gloazels = new Array(maxntimes);
        var galazels = new Array(maxntimes);
        var bdsazels = new Array(maxntimes);
        var qzsazels = new Array(maxntimes);
        var sbsazels = new Array(maxntimes);
        var gpsllhs = new Array(maxntimes);
        var glollhs = new Array(maxntimes);
        var galllhs = new Array(maxntimes);
        var bdsllhs = new Array(maxntimes);
        var qzsllhs = new Array(maxntimes);
        var sbsllhs = new Array(maxntimes);
        var qzsmarkers = new Array(7);
        var gpsmarkers = new Array(32);
        var glomarkers = new Array(24);
        var galmarkers = new Array(27);
        var bdsmarkers = new Array(35);
        var sbsmarkers = new Array(23);
        var qzstles = new Array(7);
        var gpstles = new Array(32);
        var glotles = new Array(24);
        var galtles = new Array(27);
        var bdstles = new Array(35);
        var sbstles = new Array(23);
        var iconqz, icongps, icongal, iconglo, iconbds, iconsbs, icon;
        var visstate = { "GPS": true, "GLONASS": true, "Galileo": true, "BeiDou": true, "QZSS": true, "SBAS": true };
        var skyplts = {};
        var nsatplts = {};
        var skychart,nsatchart;
        var D2R = Math.PI / 180;
        var R2D = 180 / Math.PI;

        // get TLE from celestrak.com
        var xmlHttp = new XMLHttpRequest();
        xmlHttp.open("GET", "gnssradar/celestrak.txt", false);
        xmlHttp.send(null);
        var tledl = xmlHttp.responseText;

        // get satellite list from taroz.net
        var xmlHttp = new XMLHttpRequest();
        xmlHttp.open("GET", "gnssradar/satlist.txt", false);
        xmlHttp.send(null);
        var satlistdl = xmlHttp.responseText;
        var sats = satlistdl.split("\n");
        var nsats = sats.length;

        // parse TLE
        var tles = parse_tle(tledl);
            
        // sort TLE
        for (var i = 0; i < nsats; i++) {
            var fields = sats[i].split(",");

            if (fields[2] in tles) {
                var sptle = tles[fields[2]].split(",");
                
                // switch satellite type in satlist
                switch (fields[0]) {
                    case "1": // GPS
                        gpstles[Number(fields[1]) - 1] = { "name": sptle[0], "line1": sptle[1], "line2": sptle[2] };
                        break;
                    case "2": // SBAS
                        sbstles[Number(fields[1]) - 120] = { "name": sptle[0], "line1": sptle[1], "line2": sptle[2] };
                        break;
                    case "3": // GLONASS
                        glotles[Number(fields[1]) - 1] = { "name": sptle[0], "line1": sptle[1], "line2": sptle[2] };
                        break;
                    case "4": // Galileo
                        galtles[Number(fields[1]) - 1] = { "name": sptle[0], "line1": sptle[1], "line2": sptle[2] };
                        break;
                    case "5": // QZSS
                        qzstles[Number(fields[1]) - 193] = { "name": sptle[0], "line1": sptle[1], "line2": sptle[2] };
                        break;
                    case "6": // BeiDou
                        bdstles[Number(fields[1]) - 1] = { "name": sptle[0], "line1": sptle[1], "line2": sptle[2] };
                        break;
                }
            }
        }

        // add initialize event
        google.maps.event.addDomListener(window, 'load', initialize);

        
        // initialization function
        function initialize() {
            // get number of time intervals to display
            ntimes = getKey("ntimes", 24);
            if (ntimes>maxntimes) ntimes = maxntimes;
            
            // get number of time intervals to display
            tint = getKey("tint", 30);
            tint = tint*60*1000; // ms
            
            // get elevation mask (default: 10 degree)
            elemask = getKey("elemask", 10);

            // get location (default: Tokyo)
            orglat = getKey("lat", 35.7);
            orglon = getKey("lon", 139.8);

            orgllh = {
                "latitude": orglat/180*Math.PI,
                "longitude": orglon/180*Math.PI,
                "height": 0
            }

            // offset hour
            offhr = getKey("offhr", 0);

            // Google Earth options
            var myOptions = {
                zoom: 3,
                center: new google.maps.LatLng(orglat, orglon),
                navigationControl: false,
                mapTypeControl: false,
                streetViewControl: false,
                mapTypeId: google.maps.MapTypeId.SATELLITE
            };
            map = new google.maps.Map(document.getElementById('map'), myOptions);

            // Load satellite icons
            iconqzs = new google.maps.MarkerImage('./gnssradar/icon/QZSS.png',
                new google.maps.Size(100, 68),
                new google.maps.Point(0,0),
                new google.maps.Point(40, 25));
            icongps = new google.maps.MarkerImage('./gnssradar/icon/GPS.png',
                new google.maps.Size(100, 73),
                new google.maps.Point(0,0),
                new google.maps.Point(50, 41));
            iconglo = new google.maps.MarkerImage('./gnssradar/icon/GLONASS.png',
                new google.maps.Size(90, 49),
                new google.maps.Point(0, 0),
                new google.maps.Point(39, 26));
            icongal = new google.maps.MarkerImage('./gnssradar/icon/GALILEO.png',
                new google.maps.Size(110, 55),
                new google.maps.Point(0, 0),
                new google.maps.Point(71, 34));
            iconbds = new google.maps.MarkerImage('./gnssradar/icon/BeiDou.png',
                new google.maps.Size(100, 85),
                new google.maps.Point(0, 0),
                new google.maps.Point(45, 48));
            iconsbs = new google.maps.MarkerImage('./gnssradar/icon/SBAS.png',
                new google.maps.Size(100, 70),
                new google.maps.Point(0, 0),
                new google.maps.Point(47, 37));
            icon = new google.maps.MarkerImage('./gnssradar/icon/transparence.png',
                new google.maps.Size(3, 3),
                new google.maps.Point(0, 0),
                new google.maps.Point(1, 1));

            // Display satellite icons at default position
            var latlon = new google.maps.LatLng(0.0,0.0);
            for (var i = 0; i < 32; i++) {
                gpsmarkers[i] = new google.maps.Marker({ position: latlon, map: map, icon: icon });
                gpsmarkers[i].setMap(map);
            }
            for (var i = 0; i < 7; i++) {
                qzsmarkers[i] = new google.maps.Marker({ position: latlon, map: map, icon: icon });
                qzsmarkers[i].setMap(map);
            }
            for (var i = 0; i < 24; i++) {
                glomarkers[i] = new google.maps.Marker({ position: latlon, map: map, icon: icon });
                glomarkers[i].setMap(map);
            }
            for (var i = 0; i < 27; i++) {
                galmarkers[i] = new google.maps.Marker({ position: latlon, map: map, icon: icon });
                galmarkers[i].setMap(map);
            }
            for (var i = 0; i < 35; i++) {
                bdsmarkers[i] = new google.maps.Marker({ position: latlon, map: map, icon: icon });
                bdsmarkers[i].setMap(map);
            }
            for (var i = 0; i < 23; i++) {
                sbsmarkers[i] = new google.maps.Marker({ position: latlon, map: map, icon: icon });
                sbsmarkers[i].setMap(map);
            }

            // original position
            var latlon = new google.maps.LatLng(orglat, orglon);
            orgmarker = new google.maps.Marker({
                position: latlon, map: map,
                icon: "./gnssradar/icon/rangerstation.png",
                shadow: "./gnssradar/icon/rangerstation.shadow.png",
                draggable: true
            });
            var title = "Observer (Drag and drop me!)\nLat:" + orglat + "\nLon:" + orglon;
            orgmarker.setTitle(title);

            // get current time
            var time = new Date();
            utcoffset = time.getTimezoneOffset();
            time.setHours(time.getHours() + offhr); // add offset hour
            // generate time 
            for (var i = 0; i < ntimes; i++) {
                times[i] = new Date(time.getTime());
                time.setMinutes(time.getMinutes() + tint / 1000 / 60);
            }

            for (var i = 0; i < ntimes; i++) {
                run_compute_satllh(i); // compute satellites locations
                run_compute_satazel(i); // compute satellites azels
            }
            run_set_sat_gmap(tind); // set satellites on gmap
            run_visible_sat_gmap(); // visible satellites on gmap
            run_gen_skyplotdata(tind); // generate plot data
            run_gen_nsatplotdata();
            dop = compute_dop(tind); // compute dop

            // draw chart
            skychartdraw(skyplts);
            nsatchartdraw(nsatplts, times, orgllh, elemask);

            // add marker drag and drop event
            google.maps.event.addListener(orgmarker, 'dragend', function (ev) {
                // get new location
                orglat = ev.latLng.lat();
                orglon = ev.latLng.lng();
                orgllh = {
                    "latitude": orglat * D2R,
                    "longitude": orglon * D2R,
                    "height": 0
                }
                var title = "Observer (Drag and drop me!)\nLat:" + orglat + "\nLon:" + orglon;
                orgmarker.setTitle(title);

                map.panTo(ev.latLng); // pan to new location
                
                for (var i = 0; i < ntimes; i++) {
                    run_compute_satazel(i); // re-compute azels
                }
                run_gen_skyplotdata(tind); // re-generate plot data
                run_gen_nsatplotdata();
                dop = compute_dop(tind); // re-compute dop

                // draw chart
                skychartdraw(skyplts);
                nsatchartdraw(nsatplts, times, orgllh, elemask);
            });
        }

        // run compute_satllh(ind)
        function run_compute_satllh(ind) {
            gpsllhs[ind] = compute_satllh(gpstles, times[ind]);
            glollhs[ind] = compute_satllh(glotles, times[ind]);
            galllhs[ind] = compute_satllh(galtles, times[ind]);
            bdsllhs[ind] = compute_satllh(bdstles, times[ind]);
            qzsllhs[ind] = compute_satllh(qzstles, times[ind]);
            sbsllhs[ind] = compute_satllh(sbstles, times[ind]);
        }

        // run set_sat_gmap()
        function run_set_sat_gmap(ind) {
            gpsmarkers = set_sat_gmap(gpsllhs[ind], gpstles, gpsmarkers, 1);
            glomarkers = set_sat_gmap(glollhs[ind], glotles, glomarkers, 1);
            galmarkers = set_sat_gmap(galllhs[ind], galtles, galmarkers, 1);
            bdsmarkers = set_sat_gmap(bdsllhs[ind], bdstles, bdsmarkers, 1);
            qzsmarkers = set_sat_gmap(qzsllhs[ind], qzstles, qzsmarkers, 193);
            sbsmarkers = set_sat_gmap(sbsllhs[ind], sbstles, sbsmarkers, 120);
        }

        // run visible_sat_gmap()
        function run_visible_sat_gmap() {
            visible_sat_gmap(1, "gps");
            visible_sat_gmap(1, "glo");
            visible_sat_gmap(1, "gal");
            visible_sat_gmap(1, "bds");
            visible_sat_gmap(1, "qzs");
            visible_sat_gmap(1, "sbs");
        }

        // run compute_satazel()
        function run_compute_satazel(ind) {
            gpsazels[ind] = compute_satazel(orgllh, gpsllhs[ind]);
            gloazels[ind] = compute_satazel(orgllh, glollhs[ind]);
            galazels[ind] = compute_satazel(orgllh, galllhs[ind]);
            bdsazels[ind] = compute_satazel(orgllh, bdsllhs[ind]);
            qzsazels[ind] = compute_satazel(orgllh, qzsllhs[ind]);
            sbsazels[ind] = compute_satazel(orgllh, sbsllhs[ind]);
        }

        // run gen_skyplotdata()
        function run_gen_skyplotdata(ind) {
            var gpsplt = gen_skyplotdata(gpsazels[ind], 1);
            var gloplt = gen_skyplotdata(gloazels[ind], 1);
            var galplt = gen_skyplotdata(galazels[ind], 1);
            var bdsplt = gen_skyplotdata(bdsazels[ind], 1);
            var qzsplt = gen_skyplotdata(qzsazels[ind], 193);
            var sbsplt = gen_skyplotdata(sbsazels[ind], 120);
            skyplts = { gps: gpsplt, glo: gloplt, gal: galplt, bds: bdsplt, qzs: qzsplt, sbs: sbsplt };
        }

        // run gen_nsatplotdata()
        function run_gen_nsatplotdata() {
            var gpsplt = gen_nsatplotdata(gpsazels);
            var gloplt = gen_nsatplotdata(gloazels);
            var galplt = gen_nsatplotdata(galazels);
            var bdsplt = gen_nsatplotdata(bdsazels);
            var qzsplt = gen_nsatplotdata(qzsazels);
            var sbsplt = gen_nsatplotdata(sbsazels);
            nsatplts = { gps: gpsplt, glo: gloplt, gal: galplt, bds: bdsplt, qzs: qzsplt, sbs: sbsplt };
        }

        // get additonal inputs
        function getKey(key, def) {
            var str = location.search.substring(1);
            if (str) {
                var x = str.split("&");
                for (var i = 0; i < x.length; i++) {
                    var y = x[i].split("=");
                    if (y[0] == key) return Number(y[1]);
                }
                return def;
            } else {
                return def;
            }
        }

        // parse TLE data
        function parse_tle(tleall) {
            var tles = {};
            var tlesplit = tleall.split("\n");
            var ntle = Math.floor(tlesplit.length / 3);
            for (var i = 0; i < ntle; i++) {
                var satnames = tlesplit[3 * i + 1].split(" ");
                tles[satnames[1]] = tlesplit[3 * i] + "," + tlesplit[3 * i + 1] + "," + tlesplit[3 * i + 2];
            }
            return tles;
        }

        // compute gmst
        function date2gmst(date) {
            var y = date.getUTCFullYear();
            var m = date.getUTCMonth() + 1;
            var d = date.getUTCDate();
            var w = date.getUTCDay();
            var hr = date.getUTCHours();
            var min = date.getUTCMinutes();
            var sec = date.getUTCSeconds();
            return satellite.gstime_from_date(y, m, d, hr, min, sec);
        }
        // compute gmst
        function date2utc(date) {
            var y = date.getUTCFullYear();
            var m = date.getUTCMonth() + 1;
            var d = date.getUTCDate();
            var w = date.getUTCDay();
            var hr = date.getUTCHours();
            var min = date.getUTCMinutes();
            var sec = date.getUTCSeconds();
            return Date.UTC(y, m, d, hr, min, sec);
        }
        // compute satellite geodetic location
        function compute_satllh(tles, time) {
            var llhs = new Array(tles.length);
            for (var i = 0; i < tles.length; i++) {
                if (tles[i]) {
                    // compute satellite position from TLE
                    llhs[i] = _compute_satllh(tles[i]["line1"], tles[i]["line2"], time);
                }
            }
            return llhs;
        }
        function _compute_satllh(tle1, tle2, date) {
            var satrec = satellite.twoline2satrec(tle1, tle2);
            var y = date.getUTCFullYear();
            var m = date.getUTCMonth() + 1;
            var d = date.getUTCDate();
            var w = date.getUTCDay();
            var hr = date.getUTCHours();
            var min = date.getUTCMinutes();
            var sec = date.getUTCSeconds();
            var gmst = satellite.gstime_from_date(y, m, d, hr, min, sec);
            var sateci = satellite.propagate(satrec, y, m, d, hr, min, sec);
            return satellite.eci_to_geodetic(sateci["position"], gmst);
        }

        // compute satellite azimuth and elevation
        function compute_satazel(orgllh, llhs) {
            var azels = new Array(llhs.length);
            for (var i = 0; i < llhs.length; i++) {
                if (llhs[i]) {
                    // azimuth and elevation angle
                    azels[i] = _compute_satazel(orgllh, llhs[i]);
                }
            }
            return azels;
        }
        function _compute_satazel(orgllh, satllh) {
            var satecf = satellite.geodetic_to_ecf(satllh);
            var azel = satellite.ecf_to_look_angles(orgllh, satecf);
            azel["azimuth"] = azel["azimuth"] / Math.PI * 180; // degree
            azel["elevation"] = azel["elevation"] / Math.PI * 180; // degree
            return azel;
        }

        // set satellite marker on gmap
        function set_sat_gmap(llhs, tles, markers, prnoffset) {
            for (var i = 0; i < tles.length; i++) {
                markers[i].exist = false;
                if (tles[i]) {
                    _set_sat_gmap(llhs[i]["latitude"] * R2D, llhs[i]["longitude"] * R2D, tles[i]["name"], i + prnoffset, markers[i]);
                    markers[i].exist = true;
                }
            }
            return markers;
        }
        function _set_sat_gmap(lat, lon, svname, prn, marker) {
            var latlon = new google.maps.LatLng(lat, lon);
            marker.setPosition(latlon);

            // tooltip
            var title = svname + "\nPRN:" + prn + "\nLat:" + lat + "\nLon:" + lon;
            marker.setTitle(title);
        }

        // generate skychart plot data 
        function gen_skyplotdata(azels,offi) {
            var plt = [];
            for (var i = 0; i < azels.length; i++) {
                if (azels[i]) {
                    if (azels[i]["elevation"] > elemask) {
                        var az = Math.round(azels[i]["azimuth"] * 10) / 10;
                        var el = Math.round(azels[i]["elevation"] * 10) / 10;
                        plt[plt.length] = { name: (i + offi).toString(), x: az, y: el };
                    }
                }
            }
            return plt;
        }
        // generate nsatchart plot data 
        function gen_nsatplotdata(azels) {
            var plt = [];
            for (var j = 0; j < ntimes; j++) {
                var nsat = 0;
                for (var i = 0; i < azels[j].length; i++) {
                    if (azels[j][i]) {
                        if (azels[j][i]["elevation"] > elemask) {
                            nsat++;
                        }
                    }
                }
                plt[plt.length] = { x: j, y: nsat };
            }
            return plt;
        }
        function check_azel(azels, azs, els) {
            for (var i = 0; i < azels.length; i++) {
                if (azels[i]) {
                    if (azels[i]["elevation"] > elemask) {
                        azs[azs.length] = azels[i]["azimuth"] * D2R;
                        els[els.length] = azels[i]["elevation"] * D2R;
                    }
                }
            }
            return [azs, els];
        }
        // compute DOP
        function compute_dop(ind) {
            var azs = [];
            var els = [];
            var tmp;

            // check number of available satellites
            if (visstate["GPS"]) {
                tmp = check_azel(gpsazels[ind], azs, els); azs = tmp[0]; els = tmp[1];
            }
            if (visstate["GLONASS"]) {
                tmp = check_azel(gloazels[ind], azs, els); azs = tmp[0]; els = tmp[1];
            }
            if (visstate["Galileo"]) {
                tmp = check_azel(galazels[ind], azs, els); azs = tmp[0]; els = tmp[1];
            }
            if (visstate["BeiDou"]) {
                tmp = check_azel(bdsazels[ind], azs, els); azs = tmp[0]; els = tmp[1];
            }
            if (visstate["QZSS"]) {
                tmp = check_azel(qzsazels[ind], azs, els); azs = tmp[0]; els = tmp[1];
            }
            if (visstate["SBAS"]) {
                tmp = check_azel(sbsazels[ind], azs, els); azs = tmp[0]; els = tmp[1];
            }

            var nsat = azs.length;
            if (nsat < 4) return { "hdop": 999, "vdop": 999, "pdop": 999 };

            var G = Matrix.Zero(nsat, 4);

            for (var i = 0; i < nsat; i++)
            {
                G.elements[i][0] = Math.cos(els[i]) * Math.sin(azs[i]); // East
                G.elements[i][1] = Math.cos(els[i]) * Math.cos(azs[i]); // North
                G.elements[i][2] = Math.sin(els[i]); // Up
                G.elements[i][3] = 1;
            }


            var D = (G.transpose().multiply(G)).inverse();
            var out = {};
            out["hdop"] = Math.sqrt(D.elements[0][0] + D.elements[1][1]);
            out["vdop"] = Math.sqrt(D.elements[2][2]);
            out["pdop"] = Math.sqrt(D.elements[0][0] + D.elements[1][1] + D.elements[2][2]);

            return out;
        }
        
        // display or non-display satellite icons on gmap
        function visible_sat_gmap(view, sysstr) {
            var markers;
            var iconsat;
            switch (sysstr) {
                case "GPS":
                case "gps":
                    markers = gpsmarkers;
                    iconsat = icongps;
                    break;
                case "GLONASS":
                case "glo":
                    markers = glomarkers;
                    iconsat = iconglo;
                    break;
                case "Galileo":
                case "gal":
                    markers = galmarkers;
                    iconsat = icongal;
                    break;
                case "BeiDou":
                case "bds":
                    markers = bdsmarkers;
                    iconsat = iconbds;
                    break;
                case "QZSS":
                case "qzs":
                    markers = qzsmarkers;
                    iconsat = iconqzs;
                    break;
                case "SBAS":
                case "sbs":
                    markers = sbsmarkers;
                    iconsat = iconsbs;
                    break;
            }
            for (var i = 0; i < markers.length; i++) {
                if (markers[i].exist) {
                    if (view)
                        markers[i].setIcon(iconsat);
                    else
                        markers[i].setIcon(icon); // transparence
                }
            }
        }

        // nsat chart setting
        function nsatchartdraw(nsatplts, times, llh, elemask) {
            var col = ['rgba(0,128,0,0.5)', 'rgba(255,170,0,0.5)', 'rgba(255,0,255,0.5)', 'rgba(255,0,0,0.5)', 'rgba(0,0,255,0.5)', 'rgba(0,128,128,0.5)'];
            var coldark = ['rgba(0,78,0,0.8)', 'rgba(225,140,0,0.8)', 'rgba(205,0,205,0.8)', 'rgba(205,0,0,0.8)', 'rgba(0,0,205,0.8)', 'rgba(0,100,100,0.8)'];
            var cat = [];

            for (var i = 0; i < ntimes; i++)
                cat[i] = Highcharts.dateFormat('%H:%M', times[i] - utcoffset * 60 * 1000);

            var plotdata = {
                chart: {
                    type: 'column',
                    renderTo: 'nsatchart'
                },
                credits: {
                    enabled: false
                },
                title: {
                    text: 'GNSS Radar <a href="gnssradar_e.html" target="_blank"><span style="font-size: large">help</span></a>',
                    style: {
                        fontSize: 'xx-large'
                    },
                    useHTML: true
                },
                subtitle: {
                    style: {
                        fontSize: 'medium'
                    }
                },
                xAxis: {
                    type: 'datetime',
                    gridLineWidth: 0,
                    tickmarkPlacement: 'on',
                    categories: cat
                },
                yAxis: {
                    gridLineWidth: 0,
                    min: 0,
                    title: {
                        text: 'Number of satellite'
                    },
                    stackLabels: {
                        enabled: true,
                        style: {
                            fontWeight: 'bold',
                            color: 'gray'
                        }
                    }
                },
                legend: {
                    enabled: false
                },
                tooltip: {
                    formatter: function () {
                        return this.x + '<br/>' +
                            '<b>' + this.series.name + ': ' + this.y + '</b>';
                    },
                    positioner: function () {
                        return { x: 50, y: 50 };
                    }
                },
                exporting: {
                    scale: 1,
                    sourceHeight: 400,
                    sourceWidth: 800,
                    filename: 'nsat'
                },
                plotOptions: {
                    column: {
                        stacking: 'normal',
                        pointPadding: 0,
                        groupPadding: 0,
                        borderWidth: 0,
                        point: {
                            events: {
                                click: function () {
                                    for (var i = 0; i < 6; i++) {
                                        nsatchart.series[i].data[tind].update({ color: col[i] }, true, false);
                                        nsatchart.series[i].data[this.x].update({ color: coldark[i] }, true, false);
                                    }
                                    tind = this.x;
                                    run_set_sat_gmap(tind); // set satellites on gmap
                                    run_gen_skyplotdata(tind); // generate plot data
                                    dop = compute_dop(tind); // compute dop
                                    skychartdraw(skyplts);
                                }
                            }
                        }
                    }
                },
                series: [{
                    name: 'GPS',
                    color: col[0]
                }, {
                    name: 'GLONASS',
                    color: col[1]
                }, {
                    name: 'Galileo',
                    color: col[2]
                }, {
                    name: 'BeiDou',
                    color: col[3]
                }, {
                    name: 'QZSS',
                    color: col[4]
                }, {
                    name: 'SBAS',
                    color: col[5]
                }]
            } // plotdata

            // set subtitle
            var time = new Date();
            var y = time.getFullYear();
            var m = time.getMonth() + 1;
            var d = time.getDate();
            var H = time.getHours();
            var M = time.getMinutes();

            var lt = "Time: " + y + "/" + m + "/" + d + " " + H + ":" + M;
            var ele = "Mask:" + elemask.toString() + " deg";
            var dopstr = "HDOP:" + dop["hdop"].toFixed(1) + ", PDOP:" + dop["pdop"].toFixed(1);
            plotdata["subtitle"]["text"] = lt + "<br> " + ele + ", " + dopstr;

            // set plot data
            plotdata["series"][0]["data"] = nsatplts["gps"];
            plotdata["series"][1]["data"] = nsatplts["glo"];
            plotdata["series"][2]["data"] = nsatplts["gal"];
            plotdata["series"][3]["data"] = nsatplts["bds"];
            plotdata["series"][4]["data"] = nsatplts["qzs"];
            plotdata["series"][5]["data"] = nsatplts["sbs"];

            // chart generation
            nsatchart = new Highcharts.Chart(plotdata);


            for (var i = 0; i < 6; i++)
                nsatchart.series[i].data[tind].update({ color: coldark[i] }, true, false);
        }

        // sky chart setting
        function skychartdraw(skyplts) {
            var labels = {
                "0": "N", "45": "NE", "90": "E", "135": "SE",
                "180": "S", "225": "SW", "270": "W", "315": "NW"
            }
            
            var plotdata = {
                chart: {
                    renderTo: "skychart",
                    polar: true
                },
                credits: {
                    enabled: false
                },
                legend: {
                    title: {
                        style: {
                            fontSize: '14px'
                        }
                    },
                    itemStyle: {
                        fontSize: '12px'
                    }
                },
                title: {
                    text: ''
                },
                subtitle: {
                    text: ''
                },
                tooltip: {
                    pointFormat: 'PRN: <b>{point.name}</b><br>EL: <b>{point.y}</b>deg<br>AZ: <b>{point.x}</b>deg',
                    hideDelay: 0
                },
                pane: {
                    startAngle: 0,
                    endAngle: 360
                },
                xAxis: {
                    tickInterval: 45,
                    min: 0,
                    max: 360,
                    labels: {
                        formatter: function () { return labels[this.value] },
                        style: {
                            fontSize: 'medium'
                        }
                    }
                },
                yAxis: {
                    min: 0,
                    max: 90,
                    minorTickInterval: 10,
                    tickInterval: 30,
                    reversed: true,
                    labels: { enabled: false }
                },
                exporting: {
                    scale: 1,
                    sourceHeight: 800,
                    sourceWidth: 600,
                    filename: 'skyplot'
                },
                plotOptions: {
                    bubble: {
                        minSize: 30,
                        maxSize: 30,
                        dataLabels: {
                            enabled: true,
                            formatter: function () { return this.point.name },
                            y: 4,
                            style: {
                                fontWeight: 'bold',
                                fontSize: 'medium'
                            }
                        },
                        tooltip: {
                            followPointer: false,
                            followTouchMove: false,
                            hideDelay: 0
                        },
                        events: {
                            show: function () {
                                var name = this.name.split("(");
                                visible_sat_gmap(1, name[0]);
                                visstate[name[0]] = true;
                                dop = compute_dop(tind); // re-compute dop
                                var sp = nsatchart.options.subtitle["text"].split("<br>");
                                var newtext = sp[0] + "<br>" + "Mask:" + elemask.toString() + " deg" +
                                     ", HDOP:" + dop["hdop"].toFixed(1) + ", PDOP:" + dop["pdop"].toFixed(1);
                                nsatchart.setTitle(this.title, { text: newtext });
                                nsatchart.series[this.index].show();
                            },
                            hide: function () {
                                var name = this.name.split("(");
                                visible_sat_gmap(0, name[0]);
                                visstate[name[0]] = false;
                                dop = compute_dop(tind); // re-compute dop
                                var sp = nsatchart.options.subtitle["text"].split("<br>");
                                var newtext = sp[0] + "<br>" + "Mask:" + elemask.toString() + " deg" +
                                     ", HDOP:" + dop["hdop"].toFixed(1) + ", PDOP:" + dop["pdop"].toFixed(1);
                                nsatchart.setTitle(this.title, { text: newtext });
                                nsatchart.series[this.index].hide();
                            }
                        }
                    }
                },
                series: [
                    {
                        type: 'bubble',
                        color: '#008000',
                        pointPlacement: 'between'
                    },
                    {
                        type: 'bubble',
                        color: '#FFAA00',
                        pointPlacement: 'between'
                    },
                    {
                        type: 'bubble',
                        color: '#FF00FF',
                        pointPlacement: 'between'
                    },
                    {
                        type: 'bubble',
                        color: '#FF0000',
                        pointPlacement: 'between'
                    },
                    {
                        type: 'bubble',
                        color: '#0000FF',
                        pointPlacement: 'between'
                    },
                    {
                        type: 'bubble',
                        color: '#008080',
                        pointPlacement: 'between'
                    }
                ]
            } // plotdata

            var gps = skyplts["gps"];
            var glo = skyplts["glo"];
            var gal = skyplts["gal"];
            var bds = skyplts["bds"];
            var qzs = skyplts["qzs"];
            var sbs = skyplts["sbs"];

            // set plot data
            plotdata["series"][0]["data"] = gps;
            plotdata["series"][1]["data"] = glo;
            plotdata["series"][2]["data"] = gal;
            plotdata["series"][3]["data"] = bds;
            plotdata["series"][4]["data"] = qzs;
            plotdata["series"][5]["data"] = sbs;

            // set legend name
            plotdata["series"][0]["name"] = "GPS(" + gps.length.toString() + ")";
            plotdata["series"][1]["name"] = "GLONASS(" + glo.length.toString() + ")";
            plotdata["series"][2]["name"] = "Galileo(" + gal.length.toString() + ")";
            plotdata["series"][3]["name"] = "BeiDou(" + bds.length.toString() + ")";
            plotdata["series"][4]["name"] = "QZSS(" + qzs.length.toString() + ")";
            plotdata["series"][5]["name"] = "SBAS(" + sbs.length.toString() + ")";

            // set initial visible state
            plotdata["series"][0]["visible"] = visstate["GPS"];
            plotdata["series"][1]["visible"] = visstate["GLONASS"];
            plotdata["series"][2]["visible"] = visstate["Galileo"];
            plotdata["series"][3]["visible"] = visstate["BeiDou"];
            plotdata["series"][4]["visible"] = visstate["QZSS"];
            plotdata["series"][5]["visible"] = visstate["SBAS"];

            // set legend title
            var nsat = gps.length+glo.length+gal.length+bds.length+qzs.length+sbs.length;
            plotdata["legend"]["title"]["text"] =
                'GNSS (' + nsat.toString() + ') ' +
                '<span style="color: #666; font-weight: normal; font-size: 14px">click to hide</span>';

            // chart generation
            skychart = new Highcharts.Chart(plotdata);
        }
    </script>
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
objNavbar = document.getElementById("navbar_gps");
objNavbar.className += " w3-light-blue";


</script>

</body>
</html>

