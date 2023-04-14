class SidunisQuill {
  constructor(parameters) {
    this.quills = parameters.quills ?? null;
    this.quill = parameters.quill ?? null;
    this.input = parameters.input ?? null;
    this.input.getQuill = () => this.quill ?? this.quills;
    this.input.fillQuill = () => this.fillQuill();
    if (this.quill) {
      this.quill.on('text-change', this.onTextChange.bind(this));
    }
    if (this.quills) {
      foreach(this.quills, (/** @type {Quill} */ quill) => {
        quill.on('text-change', this.onTextChange.bind(this));
      });
    }
  }

  onTextChange(delta, oldDelta, source) {
    if (source === 'user') {
      if (this.quill) {
        this.input.value = JSON.stringify(this.quill.getContents());
      }
      if (this.quills){
        let data = {};
        foreach(this.quills, (/** @type {SidunisQuill} */ quill,index) => {
          data[index] = quill.getContents();
        });
        this.input.value = JSON.stringify(data);
      }
    }
  }

  fillQuill() {
    let data = this.input.value;
    if (!data) return;
    if (!(data instanceof Object)) {
      data = JSON.parse(data);
    }
    if (this.quill) {
      this.quill.setContents(data);
    }else if (this.quills) {
      console.log(this.quills);
      foreach(this.quills, (/** @type {Quill} */ quill,index) => {
        quill.setContents(data[index] ?? {ops:[]});
      });
    }
  }

  destroy(){
    if (this.quill) {
      this.quill.container.remove();
    }
    foreach(this.quills ?? [], (/** @type {Quill} */ quill) => quill.container.remove())
    delete this.input.getQuill;
    delete this.input.fillQuill;
    delete this;
  }
}