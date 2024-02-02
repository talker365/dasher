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
  //const host = "http://n4ldr.ddns.net:12480";
  var refreshRate = 1000;
  var arrTpms = [];
  var arrKnownModels = [];
  var arrTabs= [];
  var json_request = false;
  var modelFilter = "";
  var arrSummary = [];
  var arrColumns = [];


  setInterval(function() {
    if (!json_request) {
      json_request = true;
        //console.log("Calling for updates");
      getJSON();
    }
  }, refreshRate);

  function getJSON() { /*  */
    var xhttp = new XMLHttpRequest();
    var strUrl  = host;

    strUrl += "/bin/getData.php?feed=tpms&format=json&records=1000"
    //strUrl += "/getData.php?feed=tpms&format=json&records=1000"

    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        json_request = false;
        //console.log("Processing results");
        var strJSON = this.responseText;
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
        var arrCurrentTpms = myObj.records;

        arrTpms = arrCurrentTpms;
        //console.log("getJSON(\"" + strDataset + "\"): arrTpms = arrCurrentTpms;");

        // Update UI...
        updateUI();
        //console.log("getJSON(\"" + strDataset + "\"): updateUI(strDataset);");

      }
    };
    xhttp.open("GET", strUrl, true);
    xhttp.send();   
  }

  function updateUI() {
    //console.log("TPMS Array: " + Array.isArray(arrTpms));
    updateFeed();
    manageTabs();
    manageSummary();
  }

  function manageSummary() {
    var arrTables = [];
    var summaryTab = document.getElementById("summary_tab");

    arrTables = arrTables.concat(arrTabs);

    for (var i = 0; i < arrTables.length; i++) {

      var strTable = arrTables[i];
      var strSummaryTableId = "summary_" + strTable + "_table";
      var strHeadingId = "summary_heading_" + strTable;
      var strSummaryW3Cell = "summary_w3cell_" + strTable;

      try {
        document.getElementById(strSummaryTableId).remove();
      } catch (err) { }

      try {
        document.getElementById(strHeadingId).remove();
      } catch (err) { }

      try {
        document.getElementById(strSummaryW3Cell).remove();
      } catch (err) { }

      var objSummaryW3Cell = document.createElement("div");
      objSummaryW3Cell.id = strSummaryW3Cell;
      objSummaryW3Cell.className = "w3-cell w3-half";
      var objSummaryTable = document.createElement("table");
      var objTable = document.getElementById(strTable + "_table");
      objSummaryTable.id = strSummaryTableId;
      var objHeading = document.createElement("h3");
      objHeading.id = strHeadingId;
      objHeading.innerText = strTable;
      objSummaryW3Cell.appendChild(objHeading);
      objSummaryW3Cell.appendChild(objSummaryTable);
      summaryTab.appendChild(objSummaryW3Cell);

      // If there is data in the main tabs for a device, take up to the top
      // 10 rows (most recent data) and create an array of top row values.
      // Iterating the the main table from row 0 downward, populate the top
      // row values array, populating the value in each element of the array
      // from the table row if the array value is not yet populated, or if
      // it is a zero value and table cell value is a non-blank value other
      // than zero.
      if (objTable.tBodies.item(0).rows.length > 0) {
        // Array of top row values...
        var arrTopRow = [];

        // Get array of column headings...
        var columns = getArrColumns(strTable);
        // objSummaryTable.appendChild(objTable.tHead.cloneNode(true));  // clone table headings

        const MAX_SUMMARY_TABLES_ROWS = 10;
        var availableRows = objTable.tBodies.item(0).rows.length;
        var j = 0;
        while (j < availableRows && j < MAX_SUMMARY_TABLES_ROWS) {
          // objSummaryTable.appendChild(objTable.tBodies.item(0).rows[j].cloneNode(true));
          for (var k = 0; k < objTable.tBodies.item(0).rows[j].cells.length; k++) {
            var cellValue = objTable.tBodies.item(0).rows[j].cells[k].innerHTML;

            // If first row of table, use value, otherwise compare to top row value...
            if (j == 0) {
              arrTopRow.push(cellValue);
            } else {
              if ( (arrTopRow[k] == " " && cellValue != " ") || (arrTopRow[k] == "0" && cellValue != " " && cellValue != "0") ) {
                arrTopRow[k] = cellValue;
              }
            }
          }
          j++; // Increment row...
        }

        // Create table...
        for (var n = 0; n < columns.length; n++) {
          // Create row...
          var objRow = document.createElement("tr");
          var objHeading = document.createElement("td");
          var objValue = document.createElement("td");

          objHeading.innerHTML = " <b> " + columns[n] + " </b> ";
          objHeading.style.fontWeight = "bold";
          objValue.innerHTML = arrTopRow[n];

          objRow.appendChild(objHeading);
          objRow.appendChild(objValue);


          // Append to summary table...
          objSummaryTable.appendChild(objRow);
        }
      }

    }
  }

  function updateFeed() {
    var oldModelFilter = modelFilter;
    var arrTempTabs = [];
    arrTempTabs = arrTempTabs.concat(arrTabs);
    arrTempTabs.unshift("default");

    // Create Columns Arrays for all data...
    for (var i = 0; i < arrTpms.length; i++) {
      // Add columns if not found...
      manageColumns(arrTpms[i]);
    }

    // Create tables... 
    for (var i = 0; i < arrTempTabs.length; i++){
      var temparrTpms = []; // Assigned below...
      var temparrTpms = temparrTpms.concat(arrTpms);
      var strRows = "";
      var strTable = arrTempTabs[i] + "_table";

      modelFilter = arrTempTabs[i];
      if (modelFilter == "default") {
        modelFilter = "";
      }

      while (temparrTpms.length > 0) {
        var record = temparrTpms.shift();
        var strRow = generateTableRow(record);
        strRows += strRow;
      }

      if (arrTempTabs[i] != "default") {
        document.getElementById(strTable).tBodies.item(0).innerHTML = strRows;
      }

    }

    modelFilter = oldModelFilter;
  }

  function findModel(record) {
    var boolFound = false;

    for (var i = 0; i < arrKnownModels.length; i++) {
      if (arrKnownModels[i].model == record.model) {
        boolFound = true;
        break;
      }
    }
    return boolFound;
  }

  function manageTabs() {
    // Iterate arrTabs, if match is not in arrKnownModels, remove tab...
    for (var i = 0; i < arrTabs.length; i++) {
      var boolFound = false;

      for (var j = 0; j < arrKnownModels.length; j++) {
        if (arrTabs[i] == arrKnownModels[j].model) {
          boolFound = true;
          break;
        }
      }

      if (!boolFound) {
        removeTab(arrTabs[i]);
      }
    }
      // Iterate arrKnownModels, if no match in arrTabs, create tab...
    for (var i = 0; i < arrKnownModels.length; i++) {
      var boolFound = false;

      for (var j = 0; j < arrTabs.length; j++) {
        if (arrKnownModels[i].model == arrTabs[j]) {
          boolFound = true;
          break;
        }
      }

      if (!boolFound) {
        addTab(arrKnownModels[i].model);
      }
    }
  }

  function removeTab(tabName) {
    try{
      var tab = document.getElementById(tabName + "_tab");
      tab.remove();

      var arrTempTabs = [];
      while (arrTabs.length > 0) {
        var strTabName = arrTabs.shift();

        if (strTabName != tabName) {
          arrTempTabs.push(strTabName);
        }
      }

      arrTabs.concat(arrTempTabs);
    } catch (err) {
      console.log("error trying to remove " + tabName + "_tab: " + err);
    }
  }

  function addTab(tabName) {
    var mainContainer = document.getElementById("main_container");
    var tabBar = document.getElementById("tab_bar");

    // Create new dasher tab...
    var newTab = document.createElement("div");

    newTab.id = tabName + "_tab";
    newTab.className = "w3-cell-row dasherTab";
    newTab.style.display = "none";

    mainContainer.appendChild(newTab);

    // Create new table in the new dasher tab...
    var newTable = document.createElement("table");
    var newTableHead = document.createElement("thead");

    newTable.id = tabName + "_table";
    newTable.className = "w3-table w3-striped w3-white";


    for (var i = 0; i < arrKnownModels.length; i++) {
      if (tabName == arrKnownModels[i].model) {
        var columns = getArrColumns(arrKnownModels[i].model);
        for (var j = 0; j < columns.length; j++) {
          var newTH = document.createElement("th");
          var newTH_node = document.createTextNode(columns[j]);
          newTH.appendChild(newTH_node);
          newTH.style.fontWeight = "bold";
          newTableHead.appendChild(newTH);
        }
      }
    }
    newTable.appendChild(newTableHead);

    var newTableBody = document.createElement("tbody");
    newTable.appendChild(newTableBody);

    newTab.appendChild(newTable);



    // Create new Tab Bar Button...
    var newButton = document.createElement("button");
    var newButton_b = document.createElement("b");
    var newButton_i = document.createElement("i");
    var newButton_node = document.createTextNode(tabName);

    newButton.id = tabName + "_nav";
    newButton.className = "w3-bar-item w3-button tab-button w3-border-bottom w3-border-black";
    newButton.style.paddingBottom = "0px";
    newButton.onclick = function() {openDasherTab(event, tabName + "_tab", this);};

    tabBar.appendChild(newButton);
    newButton.appendChild(newButton_b);
    newButton_b.appendChild(newButton_i);
    newButton_b.appendChild(newButton_node);

    // Add tab name to array...
    arrTabs.push(tabName);
  }

  function generateTableRow(record) {
    var strRow = "";

    // Add Model if not found...
    if (!findModel(record)) {
      arrKnownModels.unshift(record);   
    }

    // Get columns to iterate...
    var columns = getArrColumns(record.model);

    if ((modelFilter != "" && record.model.toUpperCase() == modelFilter.toUpperCase()) || modelFilter == "") {
      // Make entire row clickable to show raw JSON data...
      strRow += "<tr onclick='showJsonData(" + JSON.stringify(record) + ");'>";

      //      // Loop through record to build row...
      //      for (let [key, value] of Object.entries(record)) {
      //        strRow += "<td>" + value + "</td>";
      //      }

      for (var i = 0; i < columns.length; i++) {
        var boolFoundColumn = false;
        // Loop through record to build row...
        for (let [key, value] of Object.entries(record)) {
          if (columns[i] == key) {
            strRow += "<td>" + value + "</td>";
            boolFoundColumn = true;
            break;
          }
        }
        if (!boolFoundColumn) {
          strRow += "<td> </td>";
        }
      }

      strRow += "</tr>";
    }
    return strRow;
  }

  function manageColumns(record) {
    // Get columns...
    var columns = getArrColumns(record.model);

    for (let [key, value] of Object.entries(record)) {
      var foundColumn = false;
      for (var i = 0; i < columns.length; i++) {
        if (columns[i] == key) {
          foundColumn = true;
          break;
        }
      }

      if (!foundColumn) {
        columns.push(key);
      }
    }
  }

  function getArrColumns(model){
    var columns = [];
    var boolFound = false;

    // Find columns
    for (var i = 0; i < arrColumns.length; i++) {
      if (arrColumns[i].model == model) {
        columns = arrColumns[i].columns;
        break;
      }
    }

    // Add new record to arrColumns if it is missing...
    if (!boolFound) {
      var objColumns = {model:"", columns:[]};
      objColumns.model = model;
      objColumns.columns = columns;
      arrColumns.push(objColumns);
      columns = arrColumns[arrColumns.length - 1].columns;
    }

    return columns;
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
<div id="main_container" class="w3-main" style="margin-left:300px;margin-top:43px;">

  <!-- Header -->

  <div id="tab_bar" class="w3-bar w3-border-top"> <!-- Tab Navigation -->
    <button id="summary_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black w3-light-blue" style="padding-bottom: 0px;" onclick="openDasherTab(event,'summary_tab', this)"><b><i class="fa fa-dashboard"></i> Summary </b></button>
  </div>

<div id="summary_tab" class="w3-cell-row dasherTab" style="display:block;">
</div>

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
objNavbar = document.getElementById("navbar_tpms");
objNavbar.className += " w3-light-blue";


</script>

</body>
</html>

