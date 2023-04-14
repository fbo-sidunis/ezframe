/*
 * Fonctions ajax JQUERY
 * (c) Jordane REYNET - Janvier 2015
 *  dernière mise à jour - Juin 2017
 */
//console.log('Script AJAX chargé');

// je définis quelques variables/constantes qui servirons à plusieurs endroits dans le code
const urlFichierAjx  = ROOT_URL + "Cores/Common/Ajax/ajax.php";
var nbMessageInfos = 0;

//surveille l'appel des ajax
// Ne sert qu'à titre d'infos
/*
 $(document).ajaxSend(function ( event, request, settings ) {
    console.log(" Ajax request", JSON.stringify(settings) );
 });
 */

/**
 * Envoi une requête AJAX
 * en POST
 */
function sendAjax(data, callBackSuccess, callBackError, options) {
  var opt = typeof (options) != 'undefined' && options != null ? options : null;
  var async = opt != null && typeof (opt.async) != 'undefined' && opt.async != null ? opt.async : true;
  var timeout = opt != null && typeof (opt.timeout) != 'undefined' && opt.timeout != null ? opt.timeout : 9000000;
    var ajxRes = $.ajax({ 
    type: "POST",
    url: urlFichierAjx,
    data: data,
    timeout: timeout,
    async: async,
    dataType: "json"
  }).done(function (reponse) {
    if (typeof (callBackSuccess) != "undefined") {
      return callBackSuccess(reponse, opt);
    }
  }).fail(function (jqXHR, textStatus) {
    if (typeof (callBackError) != "undefined") {
      return callBackError(jqXHR, opt);
    } else {
      alert('Ajax error :\n' + formatErrorMessage(jqXHR, textStatus));
      return false;
    }
  });
  return ajxRes;
}

function sendAjaxRoute(aliasRoute, data, callBackSuccess, callBackError, options) {
  var opt = typeof (options) != 'undefined' && options != null ? options : null;
  var async = opt != null && typeof (opt.async) != 'undefined' && opt.async != null ? opt.async : true;
  var timeout = opt != null && typeof (opt.timeout) != 'undefined' && opt.timeout != null ? opt.timeout : 9000000;
    var ajxRes = $.ajax({ 
    type: "POST",
    url: Route.get(aliasRoute),
    data: data,
    timeout: timeout,
    async: async,
    dataType: "json"
  }).done(function (reponse) {
    if (typeof (callBackSuccess) != "undefined") {
      return callBackSuccess(reponse, opt);
    }
  }).fail(function (jqXHR, textStatus) {
    if (typeof (callBackError) != "undefined") {
      return callBackError(jqXHR, opt);
    } else {
      alert('Ajax error :\n' + formatErrorMessage(jqXHR, textStatus));
      return false;
    }
  });
  return ajxRes;
}

/**
 * Envoie un requête ajax en Javascript pur
 * @param {String} url [Lien du script]
 * @param {FormData|object} formData [Le FormData...]
 * @param {Function} callback [Fonction appelée si réponse]
 * @param {Function} callback_error [Fonction appelée si erreur]
 * @author Mathieu NADEAU <mnadeau@groupefbo.com>
 */
 function sendFormData(url,formData,callback,callback_error,method,debug){
	debug = debug ?? 0;
	method = method ?? "POST";
	var xhr = new XMLHttpRequest();
	if (debug) console.log("Sending to "+url+" :",formData);
	xhr.addEventListener('load', function(e) {
		var response = IsJsonString(e.target.responseText) ? JSON.parse(e.target.responseText) : e.target.responseText;
		if (debug) console.log("Received from "+url+" :",response);
		callback(response);
	});
	xhr.addEventListener('error', function(e) {
		if (debug) console.log("Error sending to "+url+" :",e);
		if (callback_error ?? false){
			callback_error(e.target.statusText);
		}else{
			alert(e.target.statusText);
		}
	});
	xhr.open(method, url);
	if (!(formData instanceof FormData) && formData instanceof Object) formData = objectToFormData(formData);
	xhr.send(formData);
}

/**
 * Envoie une requête GET à une certaine URL
 * @param {object} parameters 
 * @param {string} parameters.url
 * @param {string} parameters.route
 * @param {object} parameters.vars
 * @param {function|null} parameters.callback
 * @param {function|null} parameters.callbackError
 */
 function sendGET(parameters){
	parameters = parameters ?? {};
	parameters.method = "GET";
	return translateSend(parameters);
}
/**
 * Envoie une requête POST à une certaine URL
 * @param {object} parameters 
 * @param {string} parameters.url
 * @param {string} parameters.route
 * @param {object} parameters.vars
 * @param {object|FormData} parameters.data
 * @param {function|null} parameters.callback
 * @param {function|null} parameters.callbackError
 */
function sendPOST(parameters){
	parameters = parameters ?? {};
	parameters.method = "POST";
	return translateSend(parameters);
}
/**
 * Envoie une requête PUT à une certaine URL
 * @param {object} parameters 
 * @param {string} parameters.url
 * @param {string} parameters.route
 * @param {object} parameters.vars
 * @param {object|FormData} parameters.data
 * @param {function|null} parameters.callback
 * @param {function|null} parameters.callbackError
 */
function sendPUT(parameters){
	parameters = parameters ?? {};
	parameters.method = "PUT";
	return translateSend(parameters);
}
/**
 * Envoie une requête POST à une certaine URL
 * @param {object} parameters 
 * @param {string} parameters.url
 * @param {string} parameters.route
 * @param {object} parameters.vars
 * @param {object|FormData} parameters.data
 * @param {function|null} parameters.callback
 * @param {function|null} parameters.callbackError
 */
function sendDELETE(parameters){
	parameters = parameters ?? {};
	parameters.method = "DELETE";
	return translateSend(parameters);
}

/**
 * Traduis les 4 fonctions GET,POST,PUT,DELETE pour sendFormData
 * @param {object} parameters 
 * @param {string} parameters.url
 * @param {string} parameters.route
 * @param {object} parameters.vars
 * @param {object|FormData} parameters.data
 * @param {function|null} parameters.callback
 * @param {function|null} parameters.callbackError
 * @param {string|null} parameters.method
 */
 function translateSend(parameters){
	parameters = parameters ?? {};
	let url = parameters.url ?? null;
	let route = parameters.route ?? null;
	let vars = parameters.vars ?? null;
  if (!url && route){
    url = Route.get(route,vars);
  }else if (!url && !route){
    url = "/";
  }
	let data = parameters.data ?? null;
	let callback = parameters.callback ?? null;
	let callbackError = parameters.callbackError ?? null;
	let method = parameters.method ?? "POST";
	let debug = parameters.debug ?? 0;
	return sendFormData(url,data,callback,callbackError,method,debug);
}


//--------------------------------------------------------------------------------------------------------------//
// FONCTIONS AJAX
// Jordane45 - 02-2015
//--------------------------------------------------------------------------------------------------------------//
 /**
   Format les messages erreurs AJAX pour pouvoir les afficher
  */
 function formatErrorMessage(jqXHR, exception) {
   var errorTxt = "";
   var err = jqXHR.responseText ;
      if (jqXHR.status === 0) {
            errorTxt = ('Not connected.\nPlease verify your network connection.');
      } else if (jqXHR.status == 404) {
            errorTxt = ('The requested page not found. [404]');
      } else if (jqXHR.status == 500) {
            errorTxt = ('Internal Server Error [500].');
      } else if (exception === 'parsererror') {
            errorTxt = ('Requested JSON parse failed.');
      } else if (exception === 'timeout') {
            errorTxt = ('Time out error.');
      } else if (exception === 'abort') {
            errorTxt = ('Ajax request aborted.');
      } else {
            errorTxt = ('Uncaught Error.\n' + jqXHR.responseText);
      }
    
      return errorTxt + " : " + err;
}

function isIterable(obj) {
  // checks for null and undefined
  if (obj == null) {
    return false;
  }
  return typeof obj[Symbol.iterator] === 'function';
}


