export class Autocomplete{
  /** @type {HTMLInputElement} */
  _input;
  /** @type {HTMLDataListElement} */
  _datalist = null;
  /** @type {number} */
  _timer = null;
  /** @type {number} */
  _timeout = 500;
  /** @type {string} */
  _route;
  /** @type {Function|object} */
  _extraFilters = {};
  /** @type {Function|string} */
  _callbackValue = choice => choice.value ?? "";
  /** @type {Function|string} */
  _callbackLabel = choice => choice.label ?? "";
  /** @type {Function|string} */
  _callbackOnSelect = (value,choice,input) => {
    this.input.value = value;
  };
  /** @type {Object} */
  _choices = {};
  /** @type {boolean} */
  _disabled = false;

  /**
   * Constructeur
   * @param {object} parameters
   * @param {HTMLInputElement|string} parameters.input
   * @param {HTMLDataListElement|string} parameters.datalist
   * @param {number} parameters.timeout
   * @param {string} parameters.route
   * @param {Function|object} parameters.extraFilters 
   */
  constructor(parameters) {
    parameters = parameters ?? {};
    foreach(parameters, (value, key) => {
      if ((this.hasOwnProperty(key) || this.hasOwnProperty("_"+key)) && key != "inputsRequired") this[key] = value;
    })
  }

  init(){
    this.input.addEventListener("input",this.getOnInput());
    this.datalist = this.datalist;
  }

  getOnKeyPress(){
    return e => {
      this.datalist.innerHTML = "";
      clearTimeout(this.timer);
      this.timer = setTimeout(()=>{
        this.autocomplete();
      },this.timeout);
    }
  }

  getOnInput(){
    return e => {
      console.log(e.inputType,this.input.value);
      if ((e.inputType ?? null) && e.inputType != "insertReplacementText"){
        this.datalist.innerHTML = "";
        clearTimeout(this.timer);
        this.timer = setTimeout(()=>{
          this.autocomplete();
        },this.timeout);
      }else{
        this._callbackOnSelect(this.input.value,this.getChoiceByValue(this.input.value),this.input);
        this.datalist.innerHTML = "";
      }
    }
  }
  
  autocomplete(){
    if (!this.input.value.trim()) return null;
    let vars = {};
    if (this.extraFilters instanceof Function){
      vars = this.extraFilters();
    }else if (this.extraFilters instanceof Object){
      vars = this.extraFilters;
    }
    if (this.disabled) return null;
    vars.q = this.input.value.trim();
    sendGET({
      route : this.route,
      vars : vars,
      callback : response => {
        if (this.disabled) return null;
        if (response.result != 1) return console.error(response.message);
        this.choices = response.data.choices;
      }
    })
  }

  
  get input() {
    return this._input;
  }
  set input(value) {
    let _value = null;
    if (value instanceof HTMLElement){
      _value = value;
    }else if (typeof(value) == "string"){
      _value = document.querySelector(value);
    }
    if (!(_value instanceof HTMLInputElement)){
      throw("Le champ à autocompléter donné est invalide", {value : value,_value:_value});
    }
    _value.setAttribute("autocomplete","off");
    this._input = _value;
  }

  get datalist() {
    if (!this._datalist && this.input){
      let id = this.input.id + "_datalist";
      this._datalist = createElement({
        tagName : "datalist",
        attrs : {
          id : id,
        },
        parent : this.input.parentNode,
      });
      this.input.setAttribute("list",id);
    }
    return this._datalist;
  }
  set datalist(value) {
    let _value = null;
    if (value instanceof HTMLElement){
      _value = value;
    }else if (typeof(value) == "string"){
      _value = document.querySelector(value);
    }
    if (!(_value instanceof HTMLDataListElement)){
      throw "Le datalist donné est invalide"
    }
    this._datalist = _value;
  }

  get timer() {
    return this._timer;
  }
  set timer(value) {
    this._timer = value;
  }

  get timeout() {
    return this._timeout;
  }
  set timeout(value) {
    this._timeout = value;
  }

  get route() {
    if (!this._route) throw "La route n'est pas définie"
    return this._route;
  }
  set route(value) {
    this._route = value;
  }
  get extraFilters() {
    return this._extraFilters;
  }

  set extraFilters(value) {
    if (!value instanceof Function || !value instanceof Object) throw "extraFilters invalide"
    this._extraFilters = value;
  }

  get callbackValue() {
    return this._callbackValue;
  }
  set callbackValue(value) {
    if (!value instanceof Function) throw "callbackValue invalide"
    this._callbackValue = value;
  }

  get callbackOnSelect() {
    return this._callbackOnSelect;
  }
  set callbackOnSelect(value) {
    if (!value instanceof Function) throw "callbackOnSelect invalide"
    this._callbackOnSelect = value;
  }

  get choices() {
    return this._choices;
  }

  set choices(value) {
    if (!value instanceof Array) throw "choices invalide"
    this._choices = {};
    foreach (value,choice=>{
      let value = !(choice instanceof Object) ? choice.trim() : this._callbackValue(choice);
      this._choices[value] = {
        value : value,
        label : !(choice instanceof Object) ? choice.trim() : this._callbackLabel(choice),
        dataset : (choice instanceof Object) ? choice : {},
      };
    });
    this.datalist.innerHTML = "";
    foreach(this._choices,choice=>{
      createElement({
        tagName : "option",
        attrs : {
          value : choice.value,
          innerText : choice.label,
        },
        parent : this.datalist,
        dataset : choice.dataset,
      });
    });
  }

  get callbackLabel() {
    return this._callbackLabel;
  }
  set callbackLabel(value) {
    this._callbackLabel = value;
  }

  getChoiceByValue(value){
    return this.choices[value] ?? null;
  }

  disableTemporarily(){
    this.disabled = true;
    setTimeout(()=>{
      this.disabled = false;
    },this.timeout+100);
  }
}