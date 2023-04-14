if (!Object.keys) {
	Object.keys = function(obj) {
		var keys = [];

		for (var i in obj) {
			if (obj.hasOwnProperty(i)) {
				keys.push(i);
			}
		}

		return keys;
	};
}

/**
 * La seule et unique
 * @param {array|object} iterated 
 * @param {Function} callback 
 * @returns 
 */
function foreach(iterated,callback){
	if (!(iterated ?? false)) return false;
	if (iterated instanceof Array){
		iterated.forEach(function(value,index){
			callback(value,index);
		});
	}else if (iterated instanceof Object){
		Object.keys(iterated).forEach(function(key){
			callback(iterated[key],key);
		});
	}
}

function objectToFormData(object){
	object = object ?? {};
	var formData = new FormData;
	var rec = function(root,child){
		foreach(child,function(element,key){
			let name = root ? root + "["+key+"]" : key;
			if (element && element instanceof File){
				formData.append(name,element,element.name);
			}else if (element && typeof(element) == "object" && !Array.isArray(element)){
				rec(name,element);
			}else{
				formData.append(name,element);
			}
		});
	}
	rec(null,object);
	return formData;
}

if (!Object.entries) {
	Object.entries = function( obj ){
		var ownProps = Object.keys( obj ),
				i = ownProps.length,
				resArray = new Array(i);
		while (i--)
			resArray[i] = [ownProps[i], obj[ownProps[i]]];

		return resArray;
	};
}

// Create Element.remove() function if not exist
if (!('remove' in Element.prototype)) {
	Element.prototype.remove = function() {
			if (this.parentNode) {
					this.parentNode.removeChild(this);
			}
	};
}
if (!Array.prototype.filter){
	Array.prototype.filter = function(func, thisArg) {
		'use strict';
		if ( ! ((typeof func === 'Function' || typeof func === 'function') && this) )
				throw new TypeError();

		var len = this.length >>> 0,
				res = new Array(len), // preallocate array
				t = this, c = 0, i = -1;
		if (thisArg === undefined){
			while (++i !== len){
				// checks to see if the key was set
				if (i in this){
					if (func(t[i], i, t)){
						res[c++] = t[i];
					}
				}
			}
		}
		else{
			while (++i !== len){
				// checks to see if the key was set
				if (i in this){
					if (func.call(thisArg, t[i], i, t)){
						res[c++] = t[i];
					}
				}
			}
		}

		res.length = c; // shrink down array to proper size
		return res;
	};
}

function ReplaceWith(Ele) {
	'use-strict'; // For safari, and IE > 10
	var parent = this.parentNode,
			i = arguments.length,
			firstIsNode = +(parent && typeof Ele === 'object');
	if (!parent) return;

	while (i-- > firstIsNode){
		if (parent && typeof arguments[i] !== 'object'){
			arguments[i] = document.createTextNode(arguments[i]);
		} if (!parent && arguments[i].parentNode){
			arguments[i].parentNode.removeChild(arguments[i]);
			continue;
		}
		parent.insertBefore(this.previousSibling, arguments[i]);
	}
	if (firstIsNode) parent.replaceChild(Ele, this);
}
if (!Element.prototype.replaceWith)
		Element.prototype.replaceWith = ReplaceWith;
if (!CharacterData.prototype.replaceWith)
		CharacterData.prototype.replaceWith = ReplaceWith;
if (!DocumentType.prototype.replaceWith)
		DocumentType.prototype.replaceWith = ReplaceWith;

if(!Array.prototype.includes){
	//or use Object.defineProperty
	Array.prototype.includes = function(search){
		return !!~this.indexOf(search);
	}
}
if (typeof(jQuery) != "undefined"){
	(function($,undefined){
		"$:nomunge"; // Used by YUI compressor.

		/**
		 * Serialize a form into a "submitted" format
		 */
		$.fn.serializeObject = function(){
			var obj = {};

			$.each( this.serializeArray(), function(i,o){
				var n = o.name,
					v = o.value;

					obj[n] = obj[n] === undefined ? v
						: $.isArray( obj[n] ) ? obj[n].concat( v )
						: [ obj[n], v ];
			});

			return obj;
		};

	})(jQuery);
}

function isEmpty(variable){
	var bResponse = false;
	switch (typeof(variable)){
		case "undefined" :
			bResponse = true;
			break;
		case "object" :
			if (variable === null){
				bResponse = true;
			}else if (!isElement(variable)){
				bResponse = Object.entries(variable).length === 0 ? true : bResponse;
			}
			break;
		case "boolean" :
			bResponse = variable ? bResponse : true;
			break;
		case "number", "bigint" :
			bResponse = variable === 0 ? true : bResponse;
			break;
		case "string" :
			bResponse = variable === "0" || variable === "" || variable === "null" ? true : bResponse;
			break;
		default :
			break;

	}
	return bResponse;
}

//Returns true if it is a DOM node
function isNode(o){
	return (
		typeof Node === "object" ? o instanceof Node :
		o && typeof o === "object" && typeof o.nodeType === "number" && typeof o.nodeName==="string"
	);
}

//Returns true if it is a DOM element
function isElement(o){
	return (
		typeof HTMLElement === "object" ? o instanceof HTMLElement : //DOM2
		o && typeof o === "object" && o !== null && o.nodeType === 1 && typeof o.nodeName==="string"
	);
}

function getVariable(variable,defaultValue){
	defaultValue = typeof(defaultValue) != "undefined" ? defaultValue : null;
	return !isEmpty(variable) ? variable : defaultValue;
}

function pad(number,length) {
	length = getVar(length,2);
	var string = number.toString();
	var padding = "";
	length = length - string.length;
	for (let i = 0;i < length;i++){
		padding += "0";
	}
	return padding+string;
}

function getUrlVars()
{
		var vars = [], hash;
		var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
		for(var i = 0; i < hashes.length; i++)
		{
				hash = hashes[i].split('=');

				if($.inArray(hash[0], vars)>-1)
				{
						vars[hash[0]]+=","+hash[1];
				}
				else
				{
						vars.push(hash[0]);
						vars[hash[0]] = hash[1];
				}
		}

		return vars;
}

function getUrlParam(parameter, defaultvalue){
	var urlparameter = getVariable(defaultvalue);
	if(window.location.href.indexOf(parameter) > -1){
			urlparameter = getUrlVars()[parameter];
			}
	return urlparameter;
}

function isIE(userAgent) {
	userAgent = userAgent || navigator.userAgent;
	return /MSIE|Trident|Edge\//.test(userAgent);
}

function solveDateInputs(){
	if (isIE()){
		var dateInputs = document.querySelectorAll("input[type='date']");
		if (!isEmpty(dateInputs)){
			foreach(dateInputs,function(element){
				element.value = element.value.split("/").reverse().join("-");
			});
		}
	}
	return null;
}

function IsJsonString(str) {
	try {
			JSON.parse(str);
	} catch (e) {
			return false;
	}
	return true;
}

var timersearch = null;
function searchChange(input){
	if (!isEmpty(dataTable)){
		dataTable.search(input.value)
		clearTimeout(timersearch);
		timersearch = setTimeout(function(){
			dataTable.draw();
		}, 500);
	}
}

function isFileImage(file) {
	return file && file['type'].split('/')[0] === 'image';
}

function arrayIntersect(array1,array2){
	return array1.filter(function(n) {
		return array2.indexOf(n) !== -1;
	})
}

function domReady(fn) {
	// If late; I mean on time.
  if (document.readyState === "interactive" || document.readyState === "complete" ) {
		return fn();
  }
	// If we're early to the party
  document.addEventListener("DOMContentLoaded", fn);
}

function getVar(variable, defaultValue) {
  let defaultVal = typeof (defaultValue) != 'undefined' ? defaultValue : null;
  return typeof (variable) != 'undefined' && variable != null ? variable : defaultVal;
}

/**
 * Log dans la console la variable si ENV = "dev"
 * Retourne la variable dans tous les cas
 * @param {*} variable 
 * @param {*} titre 
 * @returns 
 */
function debug(variable, titre) {
  if (DEBUG == true) {
    let title = getVar(titre);
    if (title) {
      console.log(title, variable);
    } else {
      console.log(variable);
    }
  }
  return variable;
}


/**
 * Conversion Date universelle ou timestamp
 * Vers Date FR d/m/Y
 * @param {*} date
 */
function toDateFR(date){
	date = date ?? new Date
	date = new Date(date);
	var formatter = new Intl.DateTimeFormat('fr-FR');
	return formatter.format(date);
}

function toHoursMinutes(date,h){
	if (isEmpty(date)) date = new Date
	date = new Date(date);
	h= getVar(h,":");
	return pad(date.getHours())+h+pad(date.getMinutes());
}
function toUniversalDate(date){
	date = date ?? new Date
	date = new Date(date);
	return date.getFullYear()+"-"+pad(date.getMonth()+1)+"-"+pad(date.getDate());
}

function toLocaleDateTime(date){
	if (isEmpty(date)) date = new Date
	date = new Date(date);
	return date.getFullYear()+"-"+pad(date.getMonth()+1)+"-"+pad(date.getDate())+" "+pad(date.getHours())+":"+pad(date.getMinutes())+":"+pad(date.getSeconds())+"."+pad(date.getMilliseconds(),3);
}


/**
 * Retourne la string avec le premier caractère de celle-ci en uppercase
 */
function str_capitalize(string){
	return string.replace(/(^|\s)([a-z])/g,
		function(m, p1, p2) {
				return p1 + p2.toUpperCase();
		});
};

/**
 * Récupère le contenu HTML de l'élément html spécifié par le selector
 * @param {string} selector 
 * @returns 
 */
function getTmpl(selector){
	return ((document.querySelector(selector) ?? {}).innerHTML ?? "").trim();
}

/**
 * Le template est une string contenants des placeholder
 * formattés comme tel : [%PARAM_1%]
 * Les placeholders seront remplacées par le contenu de data
 * data : {PARAM_1 : "2"} : [%PARAM_1%] sera remplacé par "2"
 * @param {object} parameters
 * @param {string} parameters.template 
 * @param {string} parameters.selector 
 * @param {object} parameters.data 
 */
 function processTemplate(parameters) {
	parameters = parameters ?? {};
	let selector = parameters.selector ?? null;
	let template = parameters.template ?? (selector ? getTmpl(selector) : "");
	let data = parameters.data ?? {};
	foreach(data, function (value, key) {
		template = template.replaceAll("${" + key + "}", value);
		template = template.replaceAll("[%" + key + "%]", value);
	})
	return template;
}

/**
 * Retourne une valeur aléatoire entre min et max
 * @param {*} min 
 * @param {*} max 
 * @returns 
 */
function getRandomArbitrary(min, max) {
  return Math.random() * (max - min) + min;
}

/**
 * Initialise les tooltip
 */
function initToolTip() {
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  })

}

/**
 * Retourne une fonction s'appelant toutes les 0.25 secondes, jusqu'à ce qu'elle prenne une nouvelle valeur
 * @param {number|null|void} timer
 * @returns Function
 */
function createPlaceholderFunction(timer){
	let func = function(){
		setTimeout(() => {
			func();
		},timer ?? 250);
	}
	return func;
}

/**
 * Retourne la prochaine occurence de dayofWeek après la date renseignée
 * 0 = dimanche
 * 6 = samedi
 * @param {*} date 
 * @param {*} dayOfWeek 
 * @returns 
 */
function getNextDayOfWeek(date, dayOfWeek) {
	// Code to check that date and dayOfWeek are valid left as an exercise ;)

	var resultDate = new Date(date.getTime());

	resultDate.setDate(date.getDate() + (7 + dayOfWeek - date.getDay()) % 7);

	return resultDate;
}

function getGET(name) {
	if (name = (new RegExp('[?&]' + encodeURIComponent(name) + '=([^&]*)')).exec(location.search))
		return decodeURIComponent(name[1]);
}

/**
 * Récupère le parent le plus proche correspondant au sélécteur donné
 * @param {HTMLElement} element L'élément dont on doit checher le plus proche parent avec le sélecteur donné
 * @param {string} selectorString Le sélécteur donné
 * @returns {HTMLElement|null} Retourne null si rien trouvé
 */
function getParent(element,selectorString){
	return element === document ? null : element.matches(selectorString) ? element : getParent(element.parentNode,selectorString);
}

/**
 * 
 * @param {object} parameters 
 * @param {string} parameters.tagName
 * @param {object} parameters.attrs
 * @param {object} parameters.dataset
 * @param {object} parameters.style
 * @param {HTMLElement} parameters.parent
 * @param {(HTMLElement|object)[]} parameters.children
 * @returns {HTMLElement}
 */
function createElement(parameters){
	let tagName = parameters.tagName;
	let attrs = parameters.attrs ?? {};
	let parent = parameters.parent ?? null;
	let dataset = parameters.dataset ?? {};
	let style = parameters.style ?? {};
	let element = document.createElement(tagName);
	foreach(attrs, (value,attrName) => {
		element[attrName] = value;
	});
	foreach(dataset, (value,attrName) => {
		element.dataset[attrName] = value;
	});
	if (parent) parent.appendChild(element);
	if (style){
		Object.assign(element.style,style);
	}
	let children = parameters.children ?? [];
	foreach(children, child => {
		if (child instanceof HTMLElement){
			element.appendChild(child);
		} else {
			child.parent = element;
			createElement(child)
		}
	});
	return element;
}

/**
 * 
 * @param {object} object 
 * @param {Function} mapFn 
 * @returns 
 */
function objectMap(object, mapFn) {
	return Object.keys(object).reduce(function(result, key) {
	  result[key] = mapFn(object[key], key)
	  return result
	}, {});
}

/**
 * Charge une classe javascript depuis un fichier js ou lance la callback si la classe est déjà chargée
 * @param {string} className 
 * @param {string} url 
 * @param {function} callback 
 */
function loadClass(className,url,callback){
	if (typeof window.Autocomplete !== "undefined") {
		return callback();
	}
	if ((window._classLoading ?? []).includes(className)) {
		setTimeout(() => {
			loadClass(className,url,callback);
		}, 100);
	}
	window._classLoading = window._classLoading ?? [];
	window._classLoading.push(className);
	import(url).then(module => {
		window[className] = module[className];
		//we remove the class from the loading list
		window._classLoading = window._classLoading.filter(c => c !== className);
		callback();
	});
}