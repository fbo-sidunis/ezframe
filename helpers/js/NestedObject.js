class NestedObject{
  _root;
  _parent;

  constructor(object,root,parent){
    this._root = root ?? this;
    this._parent = parent ?? null;
    Object.keys(object).forEach(key => {
      let value = object[key];
      if (value instanceof Object && !(value instanceof Function) && !(value instanceof HTMLElement)){
        this[key] = new NestedObject(value,this.getRoot(),this);
      }else{
        if (value instanceof Function){
          value.bind(this);
        }
        this[key] = value;
      }
    });
  }

  getRoot(){
    return this._root;
  }

  getParent(){
    return this._parent;
  }
}