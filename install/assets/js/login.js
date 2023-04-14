let LoginPage = new NestedObject({
  inputs : {
    login : document.querySelector("#inp-login"),
    password : document.querySelector("#pass"),
    onKeyPress(e){
      if (e.key == "Enter"){
        this.getRoot().buttons.login.onClick(e);
      }
    }
  },
  buttons : {
    login : {
      el : document.querySelector("#btn_login"),
      onClick(e){
        e.preventDefault();
        let inputs = this.getRoot().inputs;
        let loginValue = inputs.login.value;
        let passwordValue = inputs.password.value;
        if (!loginValue || !passwordValue) {
          return Swal.fire({
            title: "Erreur",
            text: "Merci de saisir vos identifiants",
            icon: 'warning'
          });
        }
        this.el.disabled = true;
        sendPOST({
          route : "authenticate",
          data : {
            login: loginValue,
            pass: passwordValue,
          },
          callback : response =>{
            if (response.result != 1){
              return Swal.fire({
                title: "Erreur",
                text: response.message ?? "Erreur interne",
                icon: 'error'
              });
            }
            this.el.disabled = false;
            if (response.data.is_logged) {
              location.reload();
            }
          }
        })
      }
    },
    forgot : {
      el : document.querySelector(".forgot-pwd"),
      onClick(){
        Swal.fire({
          title: 'Entrez votre adresse e-mail',
          input: 'text',
          inputAttributes: {
            autocapitalize: 'off'
          },
          showCancelButton: true,
          confirmButtonText: 'Réinitialiser',
          cancelButtonText: 'Annuler',
        }).then((result) => {
          if (!result.isConfirmed){
            return null;
          }
          sendPOST({
            route : "passwordForgotten",
            data : {
              mail : result.value,
            },
            callback : response => {
              if (response.result != 1){
                return Swal.fire({
                  title: 'Erreur',
                  text: response.message ?? "Erreur interne",
                  icon: 'error',
                });
              }
              Swal.fire({
                title: "Mot de passe",
                text: "Un nouveau mot de passe vient de vous être envoyé par mail.",
                icon: 'success'
              });
            }
          })
        })
      }
    }
  },
  init(){
    this._initError = false;
    if (!this.inputs.login){
      this._initError = true;
      console.error("Champ \"login\" introuvable");
    }
    if (!this.inputs.password){
      this._initError = true;
      console.error("Champ \"mot de passe\" introuvable");
    }
    if (!this.buttons.login.el){
      this._initError = true;
      console.error("Bouton d'authentification introuvable");
    }
    if (!this.buttons.forgot.el){
      this._initError = true;
      console.error("Bouton de récupèration de mot de passe introuvable");
    }
    if (this._initError){
      return console.error("Initialisation annulée");
    }
    this.buttons.login.el.addEventListener("click", e => {this.buttons.login.onClick(e)});
    this.buttons.forgot.el.addEventListener("click", e => {this.buttons.forgot.onClick(e)});
    this.inputs.login.addEventListener("keypress", e => {this.inputs.onKeyPress(e)});
    this.inputs.password.addEventListener("keypress", e => {this.inputs.onKeyPress(e)});
  }
});
LoginPage.init();