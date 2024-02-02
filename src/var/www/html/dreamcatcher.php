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

/*
curl -c cookie.txt -X POST http://192.168.1.60/login -d '{username: “othernet”, password: “othernet”}’
*/

  const host = "http://<?php echo $_SERVER['SERVER_ADDR']; ?>";
  var refreshRate = 1000;
  var arrAPRS = [];
  var arrOutnetAPRS = [];
  var json_request = false;


  setInterval(function() {
    if (!json_request) {
      json_request = true;
        //console.log("Calling for updates");
      getJSON("outnet");
    }
  }, refreshRate);

  function getJSON(strDataset) { /*  */
    var xhttp = new XMLHttpRequest();
    var strUrl  = host;

    switch (strDataset){
      case "outnet":
        strUrl += "/bin/getData.php?feed=outnet&records=50"
        break;
    }

    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        json_request = false;
        //console.log("Processing results");
        var strJSON = this.responseText;
        switch (strDataset) {
          case "outnet":
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
            var arrCurrentAPRS = myObj.records;

            switch (strDataset) {
              case "outnet":
                arrOutnetAPRS = arrCurrentAPRS;
                break;
            }

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
    //console.log("APRS Array: " + Array.isArray(arrAPRS));
    arrAPRS.forEach(consoleLogFeedValue);
    switch (strDataset) {
      case "aprs":
        updateAprsFeed("aprs");
        break;
      case "outnet":
        updateAprsFeed("outnet");
        break;
    }
  }

  function consoleLogFeedValue(value) {
    console.log(value.time + " " + value.from + " (->" + value.to + ") " + value.latitude + ", " + value.longitude + " symbol: " + value.symbol)
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
      case "outnet":
        strTable = "outnetAprsTable";
        break;
    }
    console.log("updateAprsFeed(): feedName=" + feedName + ", strTable=" + strTable);
    var t = document.getElementById(strTable);
    var i = t.rows.length;
    //console.log("APRS Table length = " + i);

    var tempArr = []; // Assigned below...
    switch (feedName) {
      case "outnet":
        var tempArr = tempArr.concat(arrOutnetAPRS);
        break;
    }
    var strRows = "";
    while (tempArr.length > 0) {
      var record = tempArr.shift();
      var strRow
      switch (feedName) {
        case "outnet":
          var strRow = generateAprsTableRow(record);
          break;
      }
      strRows += strRow;
    }
    document.getElementById(strTable).tBodies.item(0).innerHTML = strRows;
    //console.log(strRows);
  }

  function generateAprsTableRow(value) {
    var chrSymbol = "";
    if (value.symbol == "error") {
      chrSymbol = ".".charCodeAt(0);
    } else {
      chrSymbol = value.symbol.charCodeAt(0);
    }

    //var strRow = "<tr onclick='showJsonData(" + JSON.stringify(value) + ");'>";
    var strRow = "<tr>";
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
    <button id="DreamCatcher_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event,'DreamCatcher_tab', this)"><b><i class="fa fa-dashboard"></i> DreamCatcher </b></button>
    <button id="WX_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event,'WX_tab', this);document.getElementById('WX_iframe').refresh();"><i class="fa fa-plane"></i> WX </button>
    <button id="APRS_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event,'APRS_tab', this)"><i class="fa fa-train"></i> APRS </button>
    <button id="Radio_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event,'Radio_tab', this)"><i class="fa fa-train"></i> Radio </button>
    <button id="News_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event,'News_tab', this)"><i class="fa fa-train"></i> News </button>
    <button id="Wikipedia_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event,'Wikipedia_tab', this)"><i class="fa fa-train"></i> Wikipedia </button>
  </div>

<div id="summary_tab" class="w3-cell-row dasherTab" style="display:block;">
  <!--<a href="http://192.168.1.61/packages/skylark/Weather/data/#current/wind/surface/level/orthographic=10.33,41.21,2048" target="_blank">*****Wind&Weather*****</a>-->

</div>

<div id="DreamCatcher_tab" class="w3-cell-row dasherTab" style="display:none;">
  <iframe id="DreamCatcher_iframe" style="width:100%;"></iframe>
</div>

<div id="WX_tab" class="w3-cell-row dasherTab">
  <iframe id="WX_iframe" style="width:100%;"></iframe>
</div>

<div id="APRS_tab" class="w3-cell-row dasherTab" style="display:none;">
  <h1> Outnet APRS Messages </h1>
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

<div id="Radio_tab" class="w3-cell-row dasherTab" style="display:none;">
  <iframe id="Radio_iframe" style="width:100%;"></iframe>
</div>

<div id="News_tab" class="w3-cell-row dasherTab" style="display:none;">
  <iframe id="downloads_iframe" height="300" width="99%"></iframe>
</div>

<div id="Wikipedia_tab" class="w3-cell-row dasherTab" style="display:none;">
  <iframe id="wikipedia_iframe" height="300" width="99%"></iframe>
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
  objNavbar = document.getElementById("navbar_dreamcatcher");
  objNavbar.className += " w3-light-blue";

  setIFrameHeight("DreamCatcher_iframe");
  setIFrameHeight("WX_iframe");
  setIFrameHeight("Radio_iframe");
  setIFrameHeight("downloads_iframe");
  setIFrameHeight("wikipedia_iframe");

  document.getElementById("downloads_iframe").src = "http://" + _config.dreamCatcherAddress + "/FS/get/downloads:/News/?dd";
  document.getElementById("wikipedia_iframe").src = "http://" + _config.dreamCatcherAddress + "/FS/get/downloads:/Wikipedia/?dd";
  //document.getElementById("aprsat_iframe").src = "http://" + _config.dreamCatcherAddress + "/FS/get/downloads:/Amateur Radio/APRS/APRSAT/?dd";
  document.getElementById("DreamCatcher_iframe").src = "http://" + _config.dreamCatcherAddress + "/";
  document.getElementById("WX_iframe").src = "http://" + _config.dreamCatcherAddress + "/packages/skylark/Weather/data/";
  document.getElementById("Radio_iframe").src = "http://" + _config.dreamCatcherAddress + ":8090/";


  setTimeout(function() {
      var objWxTab;
      objWxTab = document.getElementById("WX_tab");
      objWxTab.style.display = "none";
  }, 500);


</script>

</body>
</html>

