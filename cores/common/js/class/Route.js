class Route {

  /**
   * Permet de générer la Route en fonction des l'alias (et des variables)
   * La route récupérée sera toujours relative au domaine
   * Elle prends également en compte si la racine du site se situe à la racine du domaine ou pas.
   * @param {string} alias
   * @param {object|null} variables
   * @returns {string} [Url de la route]
   * @author Mathieu NADEAU <mnadeau@groupefbo.com>
   */
  static get(alias, variables) {
    variables = typeof (variables) != "undefined" ? variables : null;
    if (typeof (ROUTES) != "undefined") {
      var url = typeof (ROUTES[alias]) != "undefined" ? ROUTES[alias] : null;
      if (!url)
        return null;
      var arrUrl = url.split("/");
      if (url.includes("{") || url.includes("[")) {
        var arrUrl_ = [];
        var regex = /\{([^\/]*?)\}/;
        var regex_ = /\[([^\/]*?)\]/;
        arrUrl.forEach(function (urlPart) {
          var found = urlPart.match(regex);
          var foundFirst = !isEmpty(found);
          found = found ? found : urlPart.match(regex_);
          if (found && variables) {
            let varname = found[1];
            let variable = !isEmpty(variables[varname]) ? variables[varname] : "";
            delete variables[varname];
            variable = variable.toString().split("/")
            variable.forEach(function (element, index) {
              variable[index] = encodeURIComponent(element);
            });
            variable = variable.join(foundFirst ? "" : "/");
            arrUrl_.push(variable);
          } else {
            arrUrl_.push(urlPart);
          }
        });
        arrUrl = arrUrl_;
      }
      url = isEmpty(arrUrl.shift()) || isEmpty(arrUrl.pop()) ? arrUrl.slice(0, -1).join("/") : arrUrl.join("/");
      let vars = "";
      if (typeof(DEBUG) != "undefined" && DEBUG && (typeof(AUTODEBUG) != "undefined" && AUTODEBUG)){
        if (variables !== null){
          variables.xdebug = 1;
        }else{
          variables = {xdebug:1};
        }
      }
      console.log
      if (!isEmpty(variables)){
        let arrVars = []
        Object.keys(variables).forEach(function(key){
          let value = variables[key];
          arrVars.push(encodeURIComponent(key)+"="+encodeURIComponent(value));
        })
        if (arrVars.length) vars = "?" + arrVars.join("&");
      }
      return ROOT_URL + url + vars;
    } else {
      console.log('ROUTE IS UNDEFINED');
      return null;
    }
  }

  static redirect(alias, variables) {
    var url = this.get(alias, variables);
    if (url) {
      window.location.href = url;
    }
  }
  static open(alias, variables) {
    var url = this.get(alias, variables);
    if (url) {
      window.open(url, '_blank');
    }
  }
}