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
  var _moduleRow; // Drag and Drop placeholder
  var _debug = false;

  const MODULE_COLUMN_ORDER = 0;
  const MODULE_COLUMN_NAME = 1;
  const MODULE_COLUMN_VERSION = 2;
  const MODULE_COLUMN_ACTIVE = 3;
  const MODULE_COLUMN_VISIBLE = 4;
  const MODULE_COLUMN_MANAGE = 5;

  setInterval(function() {
    if (!json_request["dasher"]) {
      json_request["dasher"] = true;
      if (_debug) console.log("Calling for updates");
      getJSON("dasher");
    }
  }, refreshRate);

  function initialize() { /*  */
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
        if (_debug) console.log("Processing results");
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
                  if (_debug) console.log("getJSON(\"" + strDataset + "\"): arrAPRS = arrCurrentAPRS;");
                  break;
              }

              // Update UI...
              updateUI(strDataset);
              if (_debug) console.log("getJSON(\"" + strDataset + "\"): updateUI(strDataset);");
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

  function updateUI(strDataset) { /*  */
    //var d = new Date();
    switch (strDataset) {
      case "dasher":
        updateDasher();
        //document.getElementById("lastUpdatedAprs").innerHTML = d.toString();
        //document.getElementById("lastUpdatedSummary").innerHTML = d.toString();
        break;
    }
  }

  function updateDasher() { /*  */
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
    if (_debug) console.log("Calling openTab('" + tabName + "', '[" + element.innerHTML + "]');");
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

  function loadFile(filename) { /* loads json files */
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
              generateModules();
              break;
          }
          if (_debug) console.log("loadFile(): filename = '" + filename + "' - Successful");
        } catch (err) {
          console.log("loadFile(): filename = '" + filename + "' - Failed");
          console.log(err);
        }
      }
    };

    xhttp.open("GET", strUrl, true);
    xhttp.send();   
  }

  function saveFile(filename) {
    var xhttp = new XMLHttpRequest();
    var strUrl = "saveFile.php";
    var strPost = "";

    strPost += "filename=" + filename;
    switch(filename) {
      case "modules_master.json":
        strPost += "&content=" + JSON.stringify(json_modules_master);
        break;
      case "modules_local.json":
        strPost += "&content=" + JSON.stringify(json_modules_local);
        break;
    }

    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        console.log(this.responseText);
        console.log("saveFile(): filename = '" + filename + "' - Successful");

        _navbar_loadFile();
      }
    };

    console.log("strPost:  " + strPost);

    xhttp.open("POST", strUrl);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(strPost);   
  }

  function generateModules() {  /* Create Modules table Modules Tab */
    var tempArr = []; // Assigned below...
    var tempArr = tempArr.concat(json_modules_local);

    var oOldTable = document.getElementById("tblModules");
    var oDivTable = document.getElementById("divModules");
    var oTable = document.createElement("TABLE");
    oTable.id = "tblModules";
    oTable.className = "w3-table-all";


    // Create an empty <tr> element and add it to the 1st position of the table:
    var oRow = oTable.insertRow(-1);


    // Create header row:
    var oHeader_order = oRow.insertCell(MODULE_COLUMN_ORDER);
    var oHeader_name = oRow.insertCell(MODULE_COLUMN_NAME);
    var oHeader_version = oRow.insertCell(MODULE_COLUMN_VERSION);
    var oHeader_active = oRow.insertCell(MODULE_COLUMN_ACTIVE);
    var oHeader_visible = oRow.insertCell(MODULE_COLUMN_VISIBLE);
    var oHeader_manage = oRow.insertCell(MODULE_COLUMN_MANAGE);

    // Populate header names...
    oHeader_order.innerHTML = "Order";
    oHeader_name.innerHTML = "Name";
    oHeader_version.innerHTML = "Version";
    oHeader_active.innerHTML = "Active";
    oHeader_visible.innerHTML = "Visible";
    oHeader_manage.innerHTML = "Manage";

    // Set option display based on screen size...
    oHeader_version.className = "w3-hide-small";

    // Hide desired columns...
    oHeader_order.style = "display: none;";

    // Assign new table...
    oDivTable.replaceChild(oTable, oOldTable);

    // Populate new table...
    while (tempArr.length > 0) {
      var objModule = tempArr.shift();
      generateModuleRow(objModule);
    }
  }
  
  function moduleTableOnDragStart(){  /* Handles when a module row begins to drag */
    if (_debug) console.log("moduleTableOnDragStart()");
    _moduleRow = event.target; 
  }

  function moduleTableOnDragOver(){ /* Handles when a module row drags over other rows */
    if (_debug) console.log("moduleTableOnDragOver()");
    var e = event;
    e.preventDefault(); 
    
    let children= Array.from(e.target.parentNode.parentNode.children);
    
    if(children.indexOf(e.target.parentNode)>children.indexOf(_moduleRow)) {
      e.target.parentNode.after(_moduleRow);
    }
    else {
      e.target.parentNode.before(_moduleRow);
    }
  }

  function moduleTableOnDragEnd(){  /* Handles when a module row begins to drag */
    if (_debug) console.log("moduleTableOnDragEnd()");
    var e = event;
    e.preventDefault(); 
    
    let rows = Array.from(e.target.parentNode.children);

    for (let i = 1; i < rows.length; i++) {
      rows[i].cells[MODULE_COLUMN_ORDER].innerHTML = i;
    }
  }

  function generateModuleRow(objModule) { /* Adds module row in Modules Tab */
    // Find a <table> element with id="myTable":
    var table = document.getElementById("tblModules");

    // Create an empty <tr> element and add it to the 1st position of the table:
    var row = table.insertRow(-1);

    row.draggable = "true";
    row.ondragstart = function(){moduleTableOnDragStart()};
    row.ondragover = function(){moduleTableOnDragOver()};
    row.ondragend = function(){moduleTableOnDragEnd()};

    // Insert new cells (<td> elements) at the 1st and 2nd position of the "new" <tr> element:
    var cell_order = row.insertCell(MODULE_COLUMN_ORDER);
    var cell_name = row.insertCell(MODULE_COLUMN_NAME);
    var cell_version = row.insertCell(MODULE_COLUMN_VERSION);
    var cell_active = row.insertCell(MODULE_COLUMN_ACTIVE);
    var cell_visible = row.insertCell(MODULE_COLUMN_VISIBLE);
    var cell_manage = row.insertCell(MODULE_COLUMN_MANAGE);

    cell_name.className = "w3-large";
    cell_version.className = "w3-hide-small";

    // Add some text to the new cells:
    if (objModule.order != undefined) {
      cell_order.innerHTML = objModule.order;
    } else {
      cell_order.innerHTML = "-1";
    }
    if (objModule.name != undefined) {
      cell_name.innerHTML = objModule.name;
    } else {
      cell_name.innerHTML = "n/a";
    }
    if (objModule.version_installed != undefined) {
      if (objModule.version_available != undefined) {
        var html = "";
        html += objModule.version_installed;
        if (objModule.version_available > objModule.version_installed) {
          html += "<br /> (" + objModule.version_available + " available)";
        }
        cell_version.innerHTML = html;
      } else {
        cell_version.innerHTML = objModule.version_installed;
      }
    } else {
      cell_active.innerHTML = "n/a";
    }
    if (objModule.active != undefined) {
      var html = "<span class=\"d_navSpan w3-xlarge\">";
      if (objModule.active == "true") {
        html += "<i class=\"fa fa-toggle-on\" onclick=\"startManageModule('" + objModule.name + "', 'inactivate')\"></i>";
      } else {
        html += "<i class=\"fa fa-toggle-off\" onclick=\"startManageModule('" + objModule.name + "', 'activate')\"></i>";
      }
      html += "</span>";
      cell_active.innerHTML = html;
    } else {
      cell_active.innerHTML = "n/a";
    }
    if (objModule.visible != undefined) {
      var html = "<span class=\"d_navSpan w3-xlarge\">";
      if (objModule.visible == "true") {
        html += "<i class=\"fa fa-toggle-on\" onclick=\"startManageModule('" + objModule.name + "', 'hide')\"></i>";
      } else {
        html += "<i class=\"fa fa-toggle-off\" onclick=\"startManageModule('" + objModule.name + "', 'show')\"></i>";
      }
      html += "</span>";
      cell_visible.innerHTML = html;
    } else {
      cell_visible.innerHTML = "n/a";
    }
    if (objModule.installed != undefined) {
      var html = ""; //"<button class=\"w3-button w3-round-xlarge w3-teal\">";
      if (objModule.installed == "true") {
        if (objModule.version_available != undefined && objModule.version_installed != undefined) {
          if (objModule.version_available > objModule.version_installed) {
            html += "<button class=\"w3-button w3-round-xlarge w3-teal\" ";
            html += "onclick=\"startManageModule('" + objModule.name + "', 'upgrade')\">";
            html += "Upgrade";
            html += "</button>";
          }
        }
        html += "<button class=\"w3-button w3-round-xlarge w3-teal\" ";
        html += "onclick=\"startManageModule('" + objModule.name + "', 'uninstall')\">";
        html += "Uninstall";
        html += "</button>";
      } else {
        html += "<button class=\"w3-button w3-round-xlarge w3-teal\" ";
        html += "onclick=\"startManageModule('" + objModule.name + "', 'install')\">";
        html += "Install";
        html += "</button>";
      }
      cell_manage.innerHTML = html;
    } else {
      cell_manage.innerHTML = "n/a";
    }

    // Hide desired columns...
    cell_order.style = "display: none;";
  }

  function startManageModule(moduleName, action) { /* Triggered when a module action is clicked */
    var e = event;
    var eventButton = event.target; 

    populateManageModule(moduleName, action, eventButton);
    document.getElementById("divManageModule").style.display = "block";
  }

  function populateManageModule(moduleName, action, eventButton) { /* Populates Manage Module Modal */
    var objModule = getModuleJson(moduleName);

    //--------------------------------------------------------------
    // Set header...
    //--------------------------------------------------------------
    var strHeader = properCapitalization(action) + " " + moduleName + " module";
    document.getElementById("headerManageModule").innerHTML = strHeader;

    //--------------------------------------------------------------
    // Set body...
    //--------------------------------------------------------------
    //var strBody = objModule.description;
    var divBody = document.getElementById("bodyManageModule");

    // remove old content
    while (divBody.hasChildNodes()) {
      divBody.removeChild(divBody.childNodes[0]);
    }

    var hDescription = document.createElement("H3");
    var strHtml = "Module Description";
    hDescription.innerHTML = strHtml;
    divBody.appendChild(hDescription);

    var pDescription = document.createElement("P");
    var strHtml = "";
    if (objModule.description != undefined) {
      strHtml += objModule.description;
    } else {
      strHtml += "n/a";
    }
    pDescription.innerHTML = strHtml;
    divBody.appendChild(pDescription);

    var hAction = document.createElement("H3");
    hAction.innerHTML = strHtml;
    var strHtml = properCapitalization(action);
    divBody.appendChild(hAction);

    switch(action) { /* Populate Action Details */
      case "activate":
        var pAction = document.createElement("P");
        var strHtml = "";
        strHtml += "By clicking \"Activate\" below, you will be turning on ";
        strHtml += "any related background services.  You can adjust the ";
        strHtml += "specific settings for this module in its Settings tab. ";
        pAction.innerHTML = strHtml;
        divBody.appendChild(pAction);
        break;
      case "inactivate":
        var pAction = document.createElement("P");
        var strHtml = "";
        strHtml += "By clicking \"Inactivate\" below, you will be turning off ";
        strHtml += "any related background services.  It will not remove the ";
        strHtml += "installed components, but it will free up any SDRs that ";
        strHtml += "are associated.";
        pAction.innerHTML = strHtml;
        divBody.appendChild(pAction);
        break;
      case "hide":
        var pAction = document.createElement("P");
        var strHtml = "";
        strHtml += "By clicking \"Hide\" below, you will be setting this ";
        strHtml += "module to be hidden in the navigation bar on the left ";
        strHtml += "side of the screen.  This does not uninstall the module ";
        strHtml += "or stop any related services which might be running.";
        pAction.innerHTML = strHtml;
        divBody.appendChild(pAction);
        break;
      case "show":
        var pAction = document.createElement("P");
        var strHtml = "";
        strHtml += "By clicking \"Show\" below, you will be setting this ";
        strHtml += "module to be displayed in the navigation bar on the left ";
        strHtml += "side of the screen.";
        pAction.innerHTML = strHtml;
        divBody.appendChild(pAction);
        break;
      case "upgrade":
        var pAction = document.createElement("P");
        var strHtml = "";
        strHtml += "By clicking \"Upgrade\" below, you will be updating the module's ";
        strHtml += "installed components and related services.";
        strHtml += "Please visit the module's Settings tab to make changes ";
        strHtml += "after installation is complete. ";
        pAction.innerHTML = strHtml;
        divBody.appendChild(pAction);
        break;
      case "uninstall":
        var pAction = document.createElement("P");
        var strHtml = "";
        strHtml += "By clicking \"Uninstall\" below, you will be turning off ";
        strHtml += "any related background services.  This process will remove ";
        strHtml += "installed components and free up any associated SDRs. ";
        pAction.innerHTML = strHtml;
        divBody.appendChild(pAction);
        break;
      case "install":
        var pAction = document.createElement("P");
        var strHtml = "";
        strHtml += "By clicking \"Install\" below, you will be installing the ";
        strHtml += "necessary components and any related background services. ";
        strHtml += "Please visit the module's Settings tab to make changes ";
        strHtml += "after installation is complete. ";
        pAction.innerHTML = strHtml;
        divBody.appendChild(pAction);
        break;
      default:
        // code block
    }

    switch(action) { /* Add parameters */
      case "install":
      case "upgrade":
        if (objModule.installer != undefined) {
          if (objModule.installer.parameters != undefined) {

            var formParam = document.createElement("FORM");
            formParam.id = "module_form_paramters";
            formParam.className = "w3-container w3-card-4 w3-light-grey";

            var hParameters = document.createElement("H3");
            var strHtml = "Parameters";
            hParameters.innerHTML = strHtml;
            divBody.appendChild(hParameters);


            var objParams = objModule.installer.parameters;
            for (var i = 0; i < objParams.length; i++) { 
              var pParam = document.createElement("P");

              var labelParam = document.createElement("LABEL");
              var strHtml = properCapitalization(objParams[i].name);
              strHtml += ":";
              if (objParams[i].required == "true") {
                strHtml += " <span style='font-style: italic;color: red;'>&nbsp;&nbsp; * required</span>";
              }
              labelParam.innerHTML = strHtml;
              labelParam.style.fontWeight = "bolder";
              pParam.appendChild(labelParam);

              var inputParam = document.createElement("INPUT");
              // <input class="w3-input" type="text">
              inputParam.className = "w3-input";
              inputParam.type = "text";
              inputParam.id = "param_" + i;
              pParam.appendChild(inputParam);

              var descriptionParam = document.createElement("SPAN");
              descriptionParam.innerHTML = objParams[i].description;
              descriptionParam.style.fontStyle = "italic";
              pParam.appendChild(descriptionParam);


              formParam.appendChild(pParam);
            }
            divBody.appendChild(formParam);
          }
        }
        break;
    }


    //--------------------------------------------------------------
    // Set footer...
    //--------------------------------------------------------------
    var divFooter = document.getElementById("footerManageModule");

    // remove old buttons
    while (divFooter.hasChildNodes()) {
      divFooter.removeChild(divFooter.childNodes[0]);
    }

    // create new buttons
    var btnAction = document.createElement("BUTTON");
    btnAction.className = "w3-button w3-round-xlarge w3-teal";
    btnAction.onclick = function(){
      try {
        switch(action) {
          case "hide":
          case "show":
            setVisibleModule(moduleName, action, eventButton);
            break;
          case "upgrade":
          case "install":
          case "uninstall":
          case "activate":
          case "inactivate":
            var strCommand = "";

            switch (action) {
              case "upgrade":
              case "install":
                strCommand += objModule.installer.scripts.install;
                break;
              case "uninstall":
                strCommand += objModule.installer.scripts.uninstall;
                break;
              case "activate":
                strCommand += objModule.installer.scripts.active;
                break;
              case "inactivate":
                strCommand += objModule.installer.scripts.inactive;
                break;
            }

            switch(action) {
              case "upgrade":
              case "install":
                for (var i = 0; i < objParams.length; i++) { 
                  if (document.getElementById("param_" + i).value.length > 0) {
                    strCommand += " " + objParams[i].flag;
                    strCommand += " " + document.getElementById("param_" + i).value;
                  } else {
                    if (objParams[i].required == "true") {
                      const err = {name:"Required Parameter Missing", message:"Please enter a value for the '" + objParams[i].name + "' parameter."};
                      throw err;
                    }
                  }
                }
                break;
            }

            // Send command to CLI...
            document.getElementById("divModuleCLI").style.display = "block";
            document.getElementById("headerModuleCLI").innerHTML = properCapitalization(action) + " Progress...";
            document.getElementById("bodyModuleCLI").innerText = "";

            var bodyHeight = screen.availHeight / 2;
            document.getElementById("bodyModuleCLI").style.height = bodyHeight + "px";


            console.log("POSTing command=" + strCommand);

            var xhttp = new XMLHttpRequest();
            var strUrl  = host + "/executeBash.php";

            xhttp.onreadystatechange = function() {
              console.log("xhttp.onreadystatechange: readyState = " + this.readyState + ", status = " + this.status);
              if (this.readyState == 4 && this.status == 200) {
                if (_debug) console.log("getting data back from executeBash.php");
                var strBash = this.responseText;
                document.getElementById('bodyModuleCLI').innerText = strBash;

                if (strBash.search("Dasher Status: Success") > -1) {
                  console.log("SUCCESS!!!");

                  for (var i = 0; i < json_modules_local.length; i++) {
                    if (json_modules_local[i].name == moduleName) {
                      if (action == "install" || action == "upgrade") {
                        json_modules_local[i].installed = "true";
                        json_modules_local[i].active = "true";
                        json_modules_local[i].visible = "true";
                        json_modules_local[i].version_installed = json_modules_local[i].version_available;
                      } else if (action == "uninstall") {
                        json_modules_local[i].installed = "false";
                        json_modules_local[i].active = "false";
                        json_modules_local[i].visible = "false";
                        json_modules_local[i].version_installed = -1;
                      }

                      saveFile("modules_local.json");
                      generateModules();
                      break;
                    }
                  }

                } else if (strBash.search("Dasher Status: Failure") > -1) {
                  console.log("FAILURE!!!");
                }
              }
            };
            xhttp.open("POST", strUrl, true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("command=" + strCommand);   

            var btnModuleCLI = document.getElementById("btnModuleCLI");
            btnModuleCLI.onclick = function(){

              // After successfully executing script...
              setManageModuleButton(moduleName,action,eventButton);
              document.getElementById('divModuleCLI').style.display = 'none';
              document.getElementById("divManageModule").style.display = "none";
            }



            break;
          default:
            // code block
        }
 
      }
      catch (err) {
        showErrorMessage(err);
      }
    };
    btnAction.innerHTML = properCapitalization(action);
    btnAction.style.marginRight = "3ch";

    var btnCancel = document.createElement("BUTTON");
    btnCancel.className = "w3-button w3-round-xlarge w3-teal";
    btnCancel.onclick = function(){
      document.getElementById("divManageModule").style.display = "none";
    };
    btnCancel.innerHTML = "Cancel";

    // add new buttons
    divFooter.appendChild(btnAction);
    divFooter.appendChild(btnCancel);

  }

  function getModuleJson(moduleName) { /* returns module from json */
    var tempArr = []; // Assigned below...
    var tempArr = tempArr.concat(json_modules_local);

    while (tempArr.length > 0) {
      var objModule = tempArr.shift();
      if (objModule.name != undefined) {
        if (objModule.name == moduleName) {
            return objModule;
        }
      }
    }
    const err = {name:"Module Name Not Found", message:"getModuleJson() was unable to find '" + moduleName + "'"};
    showErrorMessage(err);
  }

  function setManageModuleButton(moduleName,action,eventButton) { /* Sets button state after action completes */
    switch(action) {
      case "activate":
        eventButton.className = "fa fa-toggle-on";
        eventButton.onclick = function(){startManageModule(moduleName,"inactivate")};
        break;
      case "inactivate":
        eventButton.className = "fa fa-toggle-off";
        eventButton.onclick = function(){startManageModule(moduleName,"activate")};
        break;
      case "hide":
        eventButton.className = "fa fa-toggle-off";
        eventButton.onclick = function(){startManageModule(moduleName,"show")};
        break;
      case "show":
        eventButton.className = "fa fa-toggle-on";
        eventButton.onclick = function(){startManageModule(moduleName,"hide")};
        break;
      case "upgrade":
        // code block
        break;
      case "uninstall":
        // code block
        break;
      case "install":
        eventButton.className = "fa fa-toggle-on";
        eventButton.onclick = function(){startManageModule(moduleName,"hide")};
        break;
      default:
        // code block
    }
  }

  function setVisibleModule(moduleName,action,eventButton) {
    for (var i = 0; i < json_modules_local.length; i++) {
      if (json_modules_local[i].name == moduleName) {
        if (action == "show") {
          json_modules_local[i].visible = "true";
        } else {
          json_modules_local[i].visible = "false";
        }
        saveFile("modules_local.json");
        document.getElementById("divManageModule").style.display = "none";
        setManageModuleButton(moduleName, action, eventButton);
        break;
      }
    }
  }

  function properCapitalization(strText) { /* Returns strText with capitalized first character */
    var strReturn = strText.charAt(0);
    strReturn = strReturn.toUpperCase();
    strReturn += strText.slice(1);

    return strReturn;
  }

  function updateModulesLocalFromMaster() {
    //var json_modules_master = [];
    //var json_modules_local = [];
    var tempArr = []; // Assigned below...
    var tempArr = tempArr.concat(json_modules_master);

    // Cycle through all master modules to add/update local modules...
    while (tempArr.length > 0) {
      var objModule_master = tempArr.shift();
      if (objModule_master.name != undefined) {

        // Search for an existing match...
        var foundMatch = false;
        for (var i = 0; i < json_modules_local.length; i++) {

          // Update existing local record from master...
          if (objModule_master.name == json_modules_local[i].name) {
            foundMatch = true;
            var foundNewData = false;

            if (json_modules_local[i].type != objModule_master.type) {
              foundNewData = true;
              json_modules_local[i].type = objModule_master.type
            }
            json_modules_local[i].installer = objModule_master.installer;
            if (json_modules_local[i].version_available != objModule_master.version_available) {
              foundNewData = true;
              json_modules_local[i].version_available = objModule_master.version_available;
            }
            if (json_modules_local[i].description != objModule_master.description) {
              foundNewData = true;
              json_modules_local[i].description = objModule_master.description;
            }
            json_modules_local[i].navbar = objModule_master.navbar;

            if (foundNewData) {
              json_modules_local[i].master_status = "new";
            }

            break;
          }
        }

        var maxOrder = 0;
        for (var i = 0; i < json_modules_local.length; i++) {
          if (maxOrder < json_modules_local[i].order) {
            maxOrder = json_modules_local[i].order;
          }
        }

        // Add new module from master to local...
        if (!foundMatch) {
          // Add local module properties before pushing onto local array...
          objModule_master.version_installed = "-1";
          objModule_master.installed = "false";
          objModule_master.active = "false";
          objModule_master.visible = "false";
          objModule_master.master_status = "new";
          objModule_master.order = maxOrder + 1;

          // Push module onto local array...
          json_modules_local.push(objModule_master);
        }



      }
    }

    generateModules();

    saveFile("modules_local.json");
  }

  function showErrorMessage(err){ /*  */
    document.getElementById("divErrorMessage").style.display = "block";

    var objHeader = document.getElementById("headerErrorMessage");
    var objBody = document.getElementById("bodyErrorMessage");

    var strHeader = "ERROR: ";
    strHeader += err.name;
    objHeader.innerHTML = strHeader;

    var strBody = "<p>";
    strBody += err.message;
    strBody += "</p>";
    objBody.innerHTML = strBody;
  }

  function deleteModulesLocal() { /* Empties local modules json array and saves empty file */
    while (json_modules_local.length > 0) {
      json_modules_local.shift();
    }
    saveFile("modules_local.json");
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
    <button id="Settings_nav" class="w3-bar-item w3-button tab-button w3-border-bottom w3-border-black" style="padding-bottom: 0px;" onclick="openDasherTab(event, 'Modules_tab', this);updateModulesLocalFromMaster();"><i class="fa fa-wrench"></i> Modules </button>
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




  <div class="w3-cell-row">
    <h4> Modules JSON </h4>
    <button onclick="deleteModulesLocal();">Delete Local Module Data</button>
  </div>


</div>







<div id="Modules_tab" class="w3-cell-row dasherTab" style="display: none;"> <!-- Settings Tab -->
  <div id="divModules" class="w3-border w3-border-gray w3-margin w3-padding-16">
    <h2> Modules </h2>

    <table id="tblModules" class="w3-table-all">
        <tr>
          <td> Loading modules... </td>
        </tr>
    </table>

  </div>




  <div id="divManageModule" class="w3-modal w3-card-4" style="display: none;">
    <div class="w3-modal-content">
      <header class="w3-container w3-blue">
        <h1 id="headerManageModule">manage module</h1>
      </header>
      <div id="bodyManageModule" class="w3-container">
        <p>do some stuff...</p>
      </div>
      <footer id="footerManageModule" class="w3-container w3-blue w3-right-align" style="padding-top: 1ch; padding-bottom: 1ch;">
        <h5>put some close buttons here</h5>
      </footer>
    </div>
  </div>



  <div id="divErrorMessage" class="w3-modal w3-card-4" style="display: none;">
    <div class="w3-modal-content">
      <header class="w3-container w3-red">
        <h1 id="headerErrorMessage">ERROR!</h1>
      </header>
      <div id="bodyErrorMessage" class="w3-container">
        <p>Something went wrong...</p>
      </div>
      <footer id="footerErrorMessage" class="w3-container w3-red w3-right-align" style="padding-top: 1ch; padding-bottom: 1ch;">
        <h5>
          <button class="w3-button w3-round-xlarge w3-teal" onclick="document.getElementById('divErrorMessage').style.display = 'none';">
            Close
          </button>
        </h5>
      </footer>
    </div>
  </div>



  <div id="divModuleCLI" class="w3-modal w3-card-4" style="display: none;">
    <div class="w3-modal-content">
      <header class="w3-container w3-orange">
        <h4 id="headerModuleCLI">Progress...</h4>
      </header>
      <div id="bodyModuleCLI" class="w3-container" style="max-height: 50%; overflow: scroll;">
        <p>Something going on...</p>
      </div>
      <footer id="footerModuleCLI" class="w3-container w3-orange w3-right-align" style="padding-top: 1ch; padding-bottom: 1ch;">
        <button id="btnModuleCLI" class="w3-button w3-round-xlarge w3-teal" onclick="document.getElementById('divModuleCLI').style.display = 'none';">
          Close
        </button>
      </footer>
    </div>
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

