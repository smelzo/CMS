jQuery.isString=function(a){return(typeof(a)=="string")};jQuery.isNumber=function(a){return(!isNaN(a)&&!isNaN(parseFloat(a)))};jQuery.fn.showif=function(a){return this.each(function(){if(a){jQuery(this).show()}else{jQuery(this).hide()}})};jQuery.cookie=function(b,j,m){if(typeof j!="undefined"){m=m||{};if(j===null){j="";m.expires=-1}var e="";if(m.expires&&(typeof m.expires=="number"||m.expires.toUTCString)){var f;if(typeof m.expires=="number"){f=new Date();f.setTime(f.getTime()+(m.expires*24*60*60*1000))}else{f=m.expires}e="; expires="+f.toUTCString()}var l=m.path?"; path="+(m.path):"";var g=m.domain?"; domain="+(m.domain):"";var a=m.secure?"; secure":"";document.cookie=[b,"=",encodeURIComponent(j),e,l,g,a].join("");return true}else{var d=null;if(document.cookie&&document.cookie!=""){var k=document.cookie.split(";");for(var h=0;h<k.length;h++){var c=jQuery.trim(k[h]);if(c.substring(0,b.length+1)==(b+"=")){d=decodeURIComponent(c.substring(b.length+1));break}}}return d}};jQuery.JSON={encode:function(c){if(typeof(JSON)=="object"&&JSON.stringify){return JSON.stringify(c)}var m=typeof(c);if(c===null){return"null"}if(m=="undefined"){return undefined}if(m=="number"||m=="boolean"){return c+""}if(m=="string"){return this.quoteString(c)}if(m=="object"){if(typeof c.toJSON=="function"){return this.encode(c.toJSON())}if(c.constructor===Date){var l=c.getUTCMonth()+1;if(l<10){l="0"+l}var p=c.getUTCDate();if(p<10){p="0"+p}var n=c.getUTCFullYear();var q=c.getUTCHours();if(q<10){q="0"+q}var f=c.getUTCMinutes();if(f<10){f="0"+f}var r=c.getUTCSeconds();if(r<10){r="0"+r}var h=c.getUTCMilliseconds();if(h<100){h="0"+h}if(h<10){h="0"+h}return'"'+n+"-"+l+"-"+p+"T"+q+":"+f+":"+r+"."+h+'Z"'}if(c.constructor===Array){var j=[];for(var g=0;g<c.length;g++){j.push(this.encode(c[g])||"null")}return"["+j.join(",")+"]"}var b=[];for(var e in c){var a;var m=typeof e;if(m=="number"){a='"'+e+'"'}else{if(m=="string"){a=this.quoteString(e)}else{continue}}if(typeof c[e]=="function"){continue}var d=this.encode(c[e]);b.push(a+":"+d)}return"{"+b.join(", ")+"}"}return"null"},decode:function(src){if(typeof(src)=="object"){return src}if(typeof(JSON)=="object"&&JSON.parse){return JSON.parse(src)}return eval("("+src+")")},decodeSecure:function(src){if(typeof(src)=="object"){return src}if(typeof(JSON)=="object"&&JSON.parse){return JSON.parse(src)}var filtered=src;filtered=filtered.replace(/\\["\\\/bfnrtu]/g,"@");filtered=filtered.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,"]");filtered=filtered.replace(/(?:^|:|,)(?:\s*\[)+/g,"");if(/^[\],:{}\s]*$/.test(filtered)){return eval("("+src+")")}else{throw new SyntaxError("Error parsing JSON, source is not valid.")}},quoteString:function(a){if(a.match(this._escapeable)){return'"'+a.replace(this._escapeable,function(b){var d=this._meta[b];if(typeof d==="string"){return d}d=b.charCodeAt();return"\\u00"+Math.floor(d/16).toString(16)+(d%16).toString(16)})+'"'}return'"'+a+'"'},_escapeable:/["\\\x00-\x1f\x7f-\x9f]/g,_meta:{"\b":"\\b","\t":"\\t","\n":"\\n","\f":"\\f","\r":"\\r",'"':'\\"',"\\":"\\\\"}};jQuery.encode=function(a){return jQuery.JSON.encode(a)};jQuery.decode=function(a){return jQuery.JSON.decode(a)};jQuery.makeUrl=function(f,e){if(!e){e=window.location.pathname}var b=[];var d=window.location.search;var c={};if(d){d=d.substr(1);jQuery.each(d.split("&"),function(g,a){var h=a.split("=");if(h.length>=2){c[h[0]]=h[1]}})}f=$.extend(c,f);jQuery.each(f,function(g,a){if(a!==null){b.push([g,encodeURIComponent(a)].join("="))}});return e+"?"+b.join("&")};