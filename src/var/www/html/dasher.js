const iFrameHeightPadding = 100;

function openDasherTab(evt, tabName) {
  var i, x, tablinks;
  x = document.getElementsByClassName("dasherTab");
  for (i = 0; i < x.length; i++) {
    x[i].style.display = "none"; 
  }
  document.getElementById(tabName).style.display = "block"; 


  tablinks = document.getElementsByClassName("tab-button");
  for (i = 0; i < x.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" w3-light-blue", "");
  }
  evt.currentTarget.className += " w3-light-blue";

}

function setIFrameHeight(iFrameName) {
  var h = window.innerHeight
       || document.documentElement.clientHeight
       || document.body.clientHeight;
  var objIFrame = document.getElementById(iFrameName);
  objIFrame.style.height = (h - iFrameHeightPadding) + "px";
  //console.log("[dasher.js]setIFrameHeigh() window.innerHeight = " + window.innerHeight);
  //console.log('[dasher.js]setIFrameHeigh() document.getElementById("' + iFrameName + '").style.height = ' + objIFrame.style.height
}

// Toggle between showing and hiding the sidebar, and add overlay effect
function w3_open() {
  // Get the Sidebar
  var mySidebar = document.getElementById("mySidebar");
  // Get the DIV with overlay effect
  var overlayBg = document.getElementById("myOverlay");

  if (mySidebar.style.display === 'block') {
    mySidebar.style.display = 'none';
    overlayBg.style.display = "none";
  } else {
    mySidebar.style.display = 'block';
    overlayBg.style.display = "block";
  }
  console.log("opening navbar");
}

// Close the sidebar with the close button
function w3_close() {
  // Get the Sidebar
  var mySidebar = document.getElementById("mySidebar");
  // Get the DIV with overlay effect
  var overlayBg = document.getElementById("myOverlay");

  mySidebar.style.display = "none";
  overlayBg.style.display = "none";
  console.log("closing navbar");
}

function convertToF(celsius) {
  var fahrenheit = 0;
  fahrenheit = (celsius * 9 / 5) + 32;
  return fahrenheit;
}

function convertToMph(kph) {
  var mph = 0;
  mph = (kph * 0.62).toFixed(1);
  return mph;
}

function convertMetersToFeet(meters) {
  //Converting Meters to Feet...
  return (meters * 3.28084).toFixed(0);
}

function showJsonData(strData) {
  console.log("showJsonData: " + JSON.stringify(strData));
  document.getElementById("json-modal").style.display = "block";
  document.getElementById("json-details").innerHTML = JSON.stringify(strData);
}

function bypassCORS(strURL) {
  var strReturn;

  strReturn = "/bin/bypassCORS.php?target=" + encodeURIComponent(strURL);

  return strReturn;
}

function global_dasherBodyResize() {
  resizeFooter();
}

function resizeFooter() {
  var footerStatus = document.getElementById("footer_status");
  if (document.getElementById("dasher_body").clientWidth >= 1400) {
    footerStatus.style.paddingRight = "310px";
  } else {
    footerStatus.style.paddingRight = "10px";
  } 
}

