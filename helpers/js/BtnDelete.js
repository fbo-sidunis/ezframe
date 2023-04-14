class BtnDelete {
  /** @type {HTMLButtonElement} */ _btnDelete;
  get btnDelete() {
    return this._btnDelete;
  }
  set btnDelete(value) {
    let _value = null;
    if (value instanceof HTMLElement){
      _value = value;
    }else if (typeof(value) == "string"){
      _value = document.querySelector(value);
    }
    if (!(_value instanceof HTMLElement)){
      throw "Le bouton de suppression donné est invalide"
    }
    this._btnDelete = _value;
  }
  /** @type {String} */ url;
  /** @type {String} */ route;
  /** @type {Object} */ vars;
  /** @type {Function} */ callBackBeforeDelete = function (vars, btnDelete) { }; //avant sauvegarde
  /** @type {Function} */ callBackAfterDeleteSuccess = function (response, btnDelete) { }; //si response.result == 1
  /** @type {Function} */ callBackAfterAlertSuccess = function (result, response, btnDelete) { }; //si response.result == 1
  /** @type {Function} */ callBackAfterDeleteFailure = function (response, btnDelete) { }; //si response.result != 1
  /** @type {Function} */ callBackAfterAlertFailure = function (result, response, btnDelete) { }; //si response.result != 1
  /** @type {Function} */ callBackAfterDeleteError = function (response, btnDelete) { }; //si erreur Ajax
  /** @type {String|Function} */ confirmMessage = "Êtes vous sûr de vouloir supprimer cette entrée ?";
  /** @type {String|Function} */ successMessage = "L'entrée a bien été supprimée";
  /** @type {String|Function} */ successBtnText = "OK";

  /**
   * Constructeur
   * @param {Object} parameters
   * @param {HTMLButtonElement} parameters.btnDelete
   * @param {String} parameters.url
   * @param {String} parameters.route
   * @param {Object} parameters.vars
   * @param {Function} parameters.callBackBeforeDelete
   * @param {Function} parameters.callBackAfterDeleteSuccess
   * @param {Function} parameters.callBackAfterAlertSuccess
   * @param {Function} parameters.callBackAfterDeleteFailure
   * @param {Function} parameters.callBackAfterAlertFailure
   * @param {Function} parameters.callBackAfterDeleteError
   * @param {String|Function} parameters.confirmMessage
   * @param {String|Function} parameters.successMessage
   * @param {String|Function} parameters.successBtnText
   */
  constructor(parameters) {
    parameters = parameters ?? {};
    foreach(parameters, (value, key) => {
      if (this.hasOwnProperty(key) || this.hasOwnProperty("_"+key)) this[key] = value;
    })
  }

  /**
   * Initialise le formulaire
   * @returns
   */
  init() {
    if (!(this.btnDelete ?? null) || (!(this.btnDelete instanceof HTMLElement))) {
      return console.error("btnDelete is invalid")
    }
    this.btnDelete.addEventListener("click", e => {
      return Swal.fire({
        text: typeof (this.confirmMessage) == "function" ? this.confirmMessage(this.btnDelete) : this.confirmMessage,
        showCancelButton: true,
        confirmButtonText: "Oui",
        icon: "warning",
        cancelButtonText: "Annuler",
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          let sendData = {
            callback: response => {
              this.btnDelete.disabled = false;
              if (response.result != 1) {
                this.callBackAfterDeleteFailure(response, this.btnDelete)
                return Swal.fire({ //Sinon popup de succès
                  text: response.message ?? "Erreur interne",
                  icon: "error",
                }).then(result => {
                  this.callBackAfterAlertFailure(result, response, this.btnDelete);
                });
              }
              this.callBackAfterDeleteSuccess(response, this.btnDelete);
              return Swal.fire({ //Sinon popup de succès
                text: typeof (this.successMessage) == "function" ? this.successMessage(response, this.btnDelete) : this.successMessage,
                icon: "info",
                confirmButtonText: typeof (this.successBtnText) == "function" ? this.successBtnText(response, this.btnDelete) : this.successBtnText,
              }).then((result) => {
                this.callBackAfterAlertSuccess(result, response, this.btnDelete);
              })
            },
            callbackError: response => {
              this.btnDelete.disabled = false;
              this.callBackAfterDeleteError(response, this.btnDelete);
              return Swal.fire({ //Sinon popup de succès
                text: "Une erreur s'est produite lors de l'envoi de la requête, êtes vous bien connecté à internet ?",
                icon: "error",
              });
            }
          };
          if (this.url ?? null) {
            sendData.url = this.url;
          } else if (this.route ?? null) {
            sendData.route = this.route;
            if (this.vars ?? null) {
              sendData.vars = typeof(this.vars) == "function" ? this.vars() : this.vars;
            }
          }
          this.callBackBeforeDelete(this.btnDelete);
          this.btnDelete.disabled = true;
          sendDELETE(sendData);
        }
      });
    })
    return this;
  }

  static create(parameters) {
    let btn = new BtnDelete(parameters);
    btn.init();
    btn.btnDelete.dataset.eventApplied = "1";
    return btn;
  }
}