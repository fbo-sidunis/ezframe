class CountDown{
  countDownDate = null;
  container = null;
  callback = null;

  /**
   * 
   * @param {object} parameters 
   * @param {string} parameters.date - La date limite du minuteur
   * @param {HTMLElement|string} parameters.container  - Le conteneur qui va accueillir le minuteur
   * @param {Function} parameters.callback - La callback appelée à la fin du minuteur
   */
  constructor(parameters){
    parameters = parameters ?? {};
    this.setDate(parameters.date ?? "");
    this.setContainer(parameters.container ?? null);
    this.setCallback(parameters.callback ?? null);
    this.init();
  }

  init(){
    this.x = setInterval(() => {

      // Get today's date and time
      let now = new Date().getTime();
    
      // Find the distance between now and the count down date
      let distance = this.countDownDate - now;
      
      // Display the result in the element with id="demo"

      let arrDate = [];
      let beginned = false;
      foreach({
        j : Math.floor(distance / (1000 * 60 * 60 * 24)),
        h : Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
        m : Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)),
        s : Math.floor((distance % (1000 * 60)) / 1000),
      },(value,unit) => {
        if (value || beginned){
          arrDate.push(value+unit);
          beginned = true;
        }
      });
      // console.log(arrDate);
      this.container.innerText = arrDate.join(" ");
      // If the count down is finished, write some text
      if (distance < 0) {
        clearInterval(this.x);
        this.container.innerText = "Expiré";
        if (typeof(this.callback) == "function") this.callback(this);
      }
    }, 1000);
  }

  destroy(){
    clearInterval(this.x);
    this.container.innerText = ""; 
  }

  setDate(date){
    let newDate = new Date(date ?? "").getTime();
    if (isNaN(this.countDownDate)) throw "La date donnée est invalide";
    this.countDownDate = newDate;
    return this;
  }

  setContainer(container){
    let newContainer = null;
    if (container instanceof HTMLElement){
      newContainer = container;
    }else if (typeof(container) == "string"){
      newContainer = document.querySelector(container);
    }
    if (!(newContainer instanceof HTMLElement)){
      throw "Le container donné est invalide"
    }
    this.container = newContainer;
    return this;
  }

  setCallback(callback){
    if (!(callback instanceof Function)){
      this.callback = null;
      return this; 
    }
    this.callback = callback;
    return this;
  }

  
}