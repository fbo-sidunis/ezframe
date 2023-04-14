class Form {
  /** @type {HTMLFormElement} */ _formEl;
  get formEl() {
    return this._formEl;
  }
  set formEl(value) {
    let _value = null;
    if (value instanceof HTMLElement){
      _value = value;
    }else if (typeof(value) == "string"){
      _value = document.querySelector(value);
    }
    if (!(_value instanceof HTMLFormElement)){
      console.error("Le formulaire donné est invalide",_value);
      throw "Le formulaire donné est invalide"
    }
    this._formEl = _value;
  }
  /** @type {HTMLButtonElement} */ _btnSave;
  get btnSave() {
    return this._btnSave;
  }
  set btnSave(value) {
    let _value = null;
    if (value instanceof HTMLElement){
      _value = value;
    }else if (typeof(value) == "string"){
      _value = document.querySelector(value);
    }
    if (!(_value instanceof HTMLButtonElement)){
      throw "Le bouton de sauvegarde donné est invalide"
    }
    this._btnSave = _value;
  }
  /** @type {String} */ url;
  /** @type {String} */ route;
  /** @type {Object} */ vars;
  /** @type {Function} */ callBackBeforeSave = function (formData, formEl) { }; //avant sauvegarde
  /** @type {Boolean} */ callBackBeforeSaveUseCallback = false; //Si on doit faire une requpete asynchrone avant de sauvegarder
  /** @type {Function} */ callBackAfterSaveSuccess = function (response, formEl) { }; //si response.result == 1
  /** @type {Function} */ callBackAfterAlertSuccess = function (result, response, formEl) { }; //si response.result == 1
  /** @type {Function} */ callBackAfterSaveFailure = function (response, formEl) { }; //si response.result != 1
  /** @type {Function} */ callBackAfterAlertFailure = function (result, response, formEl) { }; //si response.result != 1
  /** @type {Function} */ callBackAfterSaveError = function (response, formEl) { }; //si erreur Ajax
  /** @type {Function} */ callBackAfterFill = function (datas) { };
  /** @type {String|Function} */ successMessage = "Les informations ont été sauvegardées";
  /** @type {HTMLElement[]} */ inputsRequired;

  /**
   * Constructeur
   * @param {Object} parameters
   * @param {HTMLFormElement} parameters.formEl
   * @param {HTMLButtonElement} parameters.btnSave
   * @param {String} parameters.url
   * @param {String} parameters.route
   * @param {Object} parameters.vars
   * @param {Function} parameters.callBackBeforeSave
   * @param {Boolean} parameters.callBackBeforeSaveUseCallback
   * @param {Function} parameters.callBackAfterSaveSuccess
   * @param {Function} parameters.callBackAfterAlertSuccess
   * @param {Function} parameters.callBackAfterSaveFailure
   * @param {Function} parameters.callBackAfterAlertFailure
   * @param {Function} parameters.callBackAfterSaveError
   * @param {Function} parameters.callBackAfterFill
   * @param {String|Function} parameters.successMessage
   */
  constructor(parameters) {
    parameters = parameters ?? {};
    foreach(parameters, (value, key) => {
      if ((this.hasOwnProperty(key) || this.hasOwnProperty("_"+key)) && key != "inputsRequired") this[key] = value;
    })
  }

  /**
   * Initialise le formulaire
   * @returns
   */
  init() {
    if (!(this.formEl ?? null) || !(this.formEl instanceof HTMLFormElement)) {
      return console.error("formEl is invalid")
    }
    if (!(this.btnSave ?? null) || !(this.btnSave instanceof HTMLButtonElement)) {
      return console.error("btnSave is invalid")
    }
    this.inputsRequired = this.formEl.querySelectorAll("input[required],select[required],textarea[required]");
    foreach(this.inputsRequired, /** @type {HTMLInputElement} */ input => { //Pour chaque champ requis ...
      foreach(["input", "change"], /** @type {String} */ eventName => {
        input.addEventListener(eventName, e => { //Si il y a un changement quel qu'il soit
          input.style.border = ""; //On retire les bordures rouges
        });
      });
    });
    this.btnSave.addEventListener("click", e => { //Au clic du bouton de sauvegarde
      let formData = new FormData(this.formEl);
      let required = [];
      foreach(this.inputsRequired, /** @var {HTMLInputElement} */ input => { //On check les champs requis
        if (!input.value) { // Si vide
          input.style.border = "1px solid red"; //On les marque en rouge
          required.push(((this.formEl.querySelector("label[for=\"" + input.id + "\"]") ?? {}).innerText ?? input.id).replace(" *", "")); //Et on garde en mémoire leur libellé
        }
      });
      if (required.length) { //Si il y a des champs requis vides
        let s = required.length > 1 ? "s" : "";
        let est = required.length > 1 ? "sont" : "est";
        let last = required.length > 1 ? required.pop() : null;
        let errorMessage = "Le" + s + " champ" + s + " " + required.join(", ") + (last ? " et " + last : "") + " " + est + " requis";
        return Swal.fire({ //On retourne une popup d'erreur
          title: "Erreur",
          text: errorMessage,
          icon: "error",
        });
      }
      let followUp = () => {
        this.btnSave.disabled = true; //On désactive le bouton avant la requête
        let sendData = {
          data: formData,
          callback: response => {
            this.btnSave.disabled = false;  //On le réactive
            if (response.result != 1) {
              this.callBackAfterSaveFailure(response, this.formEl)
              return Swal.fire({ //Popup d'erreur si erreur
                title: "Erreur",
                text: response.message ?? "Erreur interne",
                icon: "error",
              }).then(result => {
                this.callBackAfterAlertFailure(result, response, this.formEl);
              });
            }
            this.callBackAfterSaveSuccess(response, this.formEl)
            return Swal.fire({ //Sinon popup de succès
              text: typeof (this.successMessage) == "function" ? this.successMessage(response, this.formEl) : this.successMessage,
              icon: "info",
            }).then(result => {
              this.callBackAfterAlertSuccess(result, response, this.formEl);
            });
          },
          callbackError: response => {
            this.btnSave.disabled = false;  //On le réactive
            this.callBackAfterSaveError(response, this.formEl)
          }
        }
        if (this.url ?? null) {
          sendData.url = this.url;
        } else if (this.route ?? null) {
          sendData.route = this.route;
          if (this.vars ?? null) {
            sendData.vars = this.vars;
          }
        }
        sendPOST(sendData);
      }
      if (this.callBackBeforeSaveUseCallback ?? false) {
        this.callBackBeforeSave(formData, this.formEl, followUp);
      } else {
        this.callBackBeforeSave(formData, this.formEl);
        followUp();
      }
    });
    return this;
  }

  fill(datas){
    datas = datas ?? {};
    foreach(this.formEl.querySelectorAll("[data-col]"),(/** @type {HTMLInputElement|HTMLSelectElement} */el) => {
      let col = el.dataset.col;
      let value = datas[col] ?? el.dataset.defaultValue ?? "";
      switch(el.tagName){
        case "TEXTAREA" :
          el.innerHTML = value;
          el.value = value;
        break;
        case "INPUT" :
          switch(el.type){
            case "radio" :
            case "checkbox" :
              value = Array.isArray(value) ? value : [value];
              el.checked = value.includes(el.value);
            break;
            default :
              el.value = value;
            break;
          }
        break;
        case "SELECT" :
          value = Array.isArray(value) ? value : [value];
          //On décoche les options cochées
          foreach(el.querySelectorAll("option:checked"),(/** @type {HTMLOptionElement} */option) => {
            option.selected = false;
          });
          //On coche les options correspondantes en ne sélectionnant que les options ayant les valeurs concernées
          foreach(value,(/** @type {String} */val) => {
            foreach(el.querySelectorAll("option[value=\"" + val + "\"]"),(/** @type {HTMLOptionElement} */option) => {
              option.selected = true;
            });
          });

          if(el.classList.contains("selectpicker")){
            $(el).selectpicker("refresh");
          }
        break;
      }
    });
    this.callBackAfterFill(datas);
  }

  
}