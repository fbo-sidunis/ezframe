class SidunisModal {
  selector;
  titleSelector;

  constructor(parameters) {
    if (typeof(bootstrap) == "undefined") {
      throw "Bootstrap n'est pas chargé";
    }
    this.selector = parameters.selector ?? null;
    this.titleSelector = parameters.titleSelector ?? null;
  }

  modal(){
    return bootstrap.Modal.getOrCreateInstance(this.selector);
  }

  title(){
    return this.modal.querySelector(this.titleSelector);
  }

  show() {
    this.modal().show();
  }

  hide() {
    this.modal().hide();
  }

  setTitle(title) {
    if (this.title()) {
      this.title().innerHTML = title;
    }
    return this;
  }
}