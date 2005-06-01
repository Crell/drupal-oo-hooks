/**
 * Only enable Javascript functionality if all required features are supported.
 */
function isJsEnabled() {
  if (typeof document.jsEnabled == 'undefined') {
    // Note: ! casts to boolean implicitly.
    document.jsEnabled = !(
     !document.getElementsByTagName ||
     !document.createElement        ||
     !document.createTextNode       ||
     !document.getElementById);
  }
  return document.jsEnabled;
}

// Global Killswitch
if (isJsEnabled()) {

}

/**
 * Make IE's XMLHTTP object accessible through XMLHttpRequest()
 */
if (typeof XMLHttpRequest == 'undefined') {
 XMLHttpRequest = function () {
   var msxmls = ['MSXML3', 'MSXML2', 'Microsoft']
   for (var i=0; i < msxmls.length; i++) {
     try {
       return new ActiveXObject(msxmls[i]+'.XMLHTTP')
     }
     catch (e) { }
   }
   throw new Error("No XML component installed!")
 }
}

/**
 * Creates an HTTP request and sends the response to the callback function
 */
function HTTPGet(uri, callbackFunction, callbackParameter) {
  var xmlHttp = new XMLHttpRequest();
  var bAsync = true;
  if (!callbackFunction)
    bAsync = false;
  xmlHttp.open('GET', uri, bAsync);
  xmlHttp.send(null);

  if (bAsync) {
    if (callbackFunction) {
      xmlHttp.onreadystatechange = function() {
        if (xmlHttp.readyState == 4)
          callbackFunction(xmlHttp.responseText, xmlHttp, callbackParameter)
      }
    }
    return true;
  }
  else {
    return xmlHttp.responseText;
  }
}

/**
 * Adds a function to the window onload event
 */
function addLoadEvent(func) {
  var oldOnload = window.onload;
  if (typeof window.onload != 'function') {
    window.onload = func;
  }
  else {
    window.onload = function() {
      oldOnload();
      func();
    }
  }
}

/**
 * Retrieves the absolute position of an element on the screen
 */
function absolutePosition(el) {
  var sLeft = 0, sTop = 0;
  var isDiv = /^div$/i.test(el.tagName);
  if (isDiv && el.scrollLeft) {
    sLeft = el.scrollLeft;
  }
  if (isDiv && el.scrollTop) {
    sTop = el.scrollTop;
  }
  var r = { x: el.offsetLeft - sLeft, y: el.offsetTop - sTop };
  if (el.offsetParent) {
    var tmp = absolutePosition(el.offsetParent);
    r.x += tmp.x;
    r.y += tmp.y;
  }
  return r;
};

/**
 * Returns true if an element has a specified class name
 */
function hasClass(node, className) {
  if (node.className == className) {
    return true;
  }
  var reg = new RegExp('(^| )'+ className +'($| )')
  if (reg.test(node.className)) {
    return true;
  }
  return false;
}

/**
 * Adds a class name to an element
 */
function addClass(node, className) {
  if (hasClass(node, className)) {
    return false;
  }
  node.className += ' '+ className;
  return true;
}

/**
 * Removes a class name from an element
 */
function removeClass(node, className) {
  if (!hasClass(node, className)) {
    return false;
  }
  node.className = eregReplace('(^| )'+ className +'($| )', '', node.className);
  return true;
}

/**
 * Toggles a class name on or off for an element
 */
function toggleClass(node, className) {
  if (!removeClass(node, className) && !addClass(node, className)) {
    return false;
  }
  return true;
}

/**
 * Emulate PHP's ereg_replace function in javascript
 */
function eregReplace(search, replace, subject) {
  return subject.replace(new RegExp(search,'g'), replace);
}

/**
 * Removes an element from the page
 */
function removeNode(node) {
  if (typeof node == 'string') {
    node = document.getElementById(node);
  }
  if (node && node.parentNode) {
    return node.parentNode.removeChild(node);
  }
  else {
    return false;
  }
}
