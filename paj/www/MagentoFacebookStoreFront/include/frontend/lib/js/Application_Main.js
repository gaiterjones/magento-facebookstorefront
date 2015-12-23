// global vars

jQuery(document).ready(function(){

	//$(".popup").fancybox();
	
	$("#message_container").on("click", ".close", function() {	
			// clicking the close span causes the closest message to fadeout
			$(this).closest('.message').fadeOut(500);
    });		

	$('.message').hover(
    function() {
        // while hovering over the message, it fades the close element in after a delay
        $(this).find('.close').delay(500).fadeIn(500);
    },
    function() {
        // after leaving/mouseout of the the message, has a delay and then fades the close out
        $(this).find('.close').delay(1000).fadeOut(500);
    });	
	
});	

// global functions



// AJAX Requests
//	
function ajaxRequest(ajaxVars,el,callbackFunction,phpuri)
{
	if (typeof phpuri === "undefined" || phpuri===null) phpuri = '/index.php';
	
	var xmlhttp;
	
	if ('withCredentials' in new XMLHttpRequest()) {
		/* supports cross-domain requests */
		//console.log("CORS supported (XHR)");
		xmlhttp=new XMLHttpRequest();
		typeCor='XHR2';
	} else {
	
		if(typeof XDomainRequest !== "undefined"){
			//Use IE-specific "CORS" code with XDR
			//console.log("CORS supported (XDR)");
			xmlhttp=new XDomainRequest();
			typeCor='ie-XDR';
			
		} else {
	  
			//console.log("No CORS Support!");
			typeCor='ie-X';
			if($.browser.msie) {
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			} else {
				// browser not supported
				callbackFunction(false,'Error - browser not supported.',el);
			}
		}
	}	

	function alert_timeout()
	{
		callbackFunction(false,'Timeout',el);
	}

	function alert_error()
	{
		callbackFunction(false,'Error',el);
	}		
	
	function alert_loaded()
	{
		var php=jQuery.parseJSON(xmlhttp.responseText);
		
			if(php.status==='success')
			{			
				callbackFunction(true,php,el);
			
			} else {
			
				callbackFunction(false,php,el);
			}	
		
	}	
	
	var sendString='';
	for (var i = 0; i < ajaxVars.phpClassVariableNames.length; i++) {
		sendString=sendString+ajaxVars.phpClassVariableNames[i] + '=' + ajaxVars.phpClassVariableValues[i];
			if ((i+1) < ajaxVars.phpClassVariableNames.length) { sendString=sendString + '&'; }
	}	
	
	xmlhttp.open("POST",phpuri + "?ajax=true&class=" + ajaxVars.phpClassName[0],true);
	xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xmlhttp.timeout = 30000; // 30 seconds
	xmlhttp.ontimeout = alert_timeout;
	xmlhttp.onerror = alert_error;
	xmlhttp.onload = alert_loaded;		
	xmlhttp.send("variables=true&" + sendString);
}

function replaceAll(find, replace, str) {
  while( str.indexOf(find) > -1)
  {
	str = str.replace(find, replace);
  }
  return str;
}
	
function $id(id) {
	return document.getElementById(id);
}

function success(el,message,fade) {
	if (typeof fade === "undefined" || fade===null) fade = 1;
	$('#' + el).removeClass('error').addClass('success');
	if(message) $('#' + el).html(message + '<span class="close"><img src="/include/frontend/lib/images/close_icon.png"</span>');
	hideshow(el,1,fade);	
}

function info(el,message,fade) {
	if (typeof fade === "undefined" || fade===null) fade = 1;
	$('#' + el).removeClass('error').addClass('info');
	if(message) $('#' + el).html(message + '<span class="close"><img src="/include/frontend/lib/images/close_icon.png"</span>');
	hideshow(el,1,fade);	
}		

function error(el,message,fade)
{
	if (typeof fade === "undefined" || fade===null) fade = 1;
	$('#' + el).removeClass('success').addClass('error');
	if(message) $('#' + el).html(message + '<span class="close"><img src="/include/frontend/lib/images/close_icon.png"</span>');
	hideshow(el,1,fade);
}

function hideshow(el,act,fade)
{
	if (typeof fade === "undefined" || fade===null) fade = 1;
	
	if(act) {
		$('#'+el).css('visibility','visible');
		
		if(fade) {
		$('#'+el).stop(true).hide().fadeTo(500,1);
		
		clearTimeout(window.elTimeout);
		
			window.elTimeout=setTimeout(function() {
				$('#'+el).fadeOut("slow", function() {
					$('#'+el).show().css({visibility: "hidden"});
				});
			}, 5000);
		}			
		
	} else {
		$('#'+el).css('visibility','hidden');
	}
}

function isChecked(el)
{
	if ($(el).prop('checked')) { return true; }
	
	return false;
}

function goBack() {
    window.history.back();
	return false;
}

function loop(span) {
    span.fadeIn(1000).delay(1000).fadeOut(1000, function() { 
        loop( span.next().length ? span.next() : spans.first() );
    });
}

function getDateTime()
{
	$('#date_time').html(dateFormat(Date(), "dddd, mmmm dS, yyyy, h:MM TT"));
}

// FUNc
function calculateAge()
{
	// born
	var birthDate = new Date(1970,(9-1),15,0,0);
	// today
	var Today = new Date();
	// elapsed milliseconds
	var elapsedTime = Today.getTime() - birthDate.getTime();
	// elapsed days
	var age=convertMS(elapsedTime);
	var daysold=age.d;
	// elapsed years
	var yearsold=daysold/365;
	// fin.
	return (yearsold.toFixed(9));
}

function convertMS(ms) {
  var d, h, m, s;
  s = Math.floor(ms / 1000);
  m = Math.floor(s / 60);
  s = s % 60;
  h = Math.floor(m / 60);
  m = m % 60;
  d = Math.floor(h / 24);
  h = h % 24;
  return { d: d, h: h, m: m, s: s };
};

function pad (str, max) {
  str = str.toString();
  return str.length < max ? pad("0" + str, max) : str;
}

function humanise (diff) {
	var	str = '',
		values = {
    ' year': 365, 
    ' month': 30, 
    ' day': 1
  };

  for (var x in values) {
    var amount = Math.floor(diff / values[x]);
    
    if (amount >= 1) {
       str += amount + x + (amount > 1 ? 's' : '') + ' ';
       diff -= amount * values[x];
    }
  }
  
  return str;
}

/*
 * Date Format 1.2.3
 * (c) 2007-2009 Steven Levithan <stevenlevithan.com>
 * MIT license
 *
 * Includes enhancements by Scott Trenda <scott.trenda.net>
 * and Kris Kowal <cixar.com/~kris.kowal/>
 *
 * Accepts a date, a mask, or a date and a mask.
 * Returns a formatted version of the given date.
 * The date defaults to the current date/time.
 * The mask defaults to dateFormat.masks.default.
 */

var dateFormat = function () {
    var token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
        timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
        timezoneClip = /[^-+\dA-Z]/g,
        pad = function (val, len) {
            val = String(val);
            len = len || 2;
            while (val.length < len) val = "0" + val;
            return val;
        };

    // Regexes and supporting functions are cached through closure
    return function (date, mask, utc) {
        var dF = dateFormat;

        // You can't provide utc if you skip other args (use the "UTC:" mask prefix)
        if (arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
            mask = date;
            date = undefined;
        }

        // Passing date through Date applies Date.parse, if necessary
        date = date ? new Date(date) : new Date;
        if (isNaN(date)) throw SyntaxError("invalid date");

        mask = String(dF.masks[mask] || mask || dF.masks["default"]);

        // Allow setting the utc argument via the mask
        if (mask.slice(0, 4) == "UTC:") {
            mask = mask.slice(4);
            utc = true;
        }

        var _ = utc ? "getUTC" : "get",
            d = date[_ + "Date"](),
            D = date[_ + "Day"](),
            m = date[_ + "Month"](),
            y = date[_ + "FullYear"](),
            H = date[_ + "Hours"](),
            M = date[_ + "Minutes"](),
            s = date[_ + "Seconds"](),
            L = date[_ + "Milliseconds"](),
            o = utc ? 0 : date.getTimezoneOffset(),
            flags = {
                d:    d,
                dd:   pad(d),
                ddd:  dF.i18n.dayNames[D],
                dddd: dF.i18n.dayNames[D + 7],
                m:    m + 1,
                mm:   pad(m + 1),
                mmm:  dF.i18n.monthNames[m],
                mmmm: dF.i18n.monthNames[m + 12],
                yy:   String(y).slice(2),
                yyyy: y,
                h:    H % 12 || 12,
                hh:   pad(H % 12 || 12),
                H:    H,
                HH:   pad(H),
                M:    M,
                MM:   pad(M),
                s:    s,
                ss:   pad(s),
                l:    pad(L, 3),
                L:    pad(L > 99 ? Math.round(L / 10) : L),
                t:    H < 12 ? "a"  : "p",
                tt:   H < 12 ? "am" : "pm",
                T:    H < 12 ? "A"  : "P",
                TT:   H < 12 ? "AM" : "PM",
                Z:    utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
                o:    (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
                S:    ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
            };

        return mask.replace(token, function ($0) {
            return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
        });
    };
}();

// Some common format strings
dateFormat.masks = {
    "default":      "ddd mmm dd yyyy HH:MM:ss",
    shortDate:      "m/d/yy",
    mediumDate:     "mmm d, yyyy",
    longDate:       "mmmm d, yyyy",
    fullDate:       "dddd, mmmm d, yyyy",
    shortTime:      "h:MM TT",
    mediumTime:     "h:MM:ss TT",
    longTime:       "h:MM:ss TT Z",
    isoDate:        "yyyy-mm-dd",
    isoTime:        "HH:MM:ss",
    isoDateTime:    "yyyy-mm-dd'T'HH:MM:ss",
    isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
};

// Internationalization strings
dateFormat.i18n = {
    dayNames: [
        "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
        "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
    ],
    monthNames: [
        "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
        "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
    ]
};

// For convenience...
Date.prototype.format = function (mask, utc) {
    return dateFormat(this, mask, utc);
};

var Fu={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Fu._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},zzy:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Fu._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}