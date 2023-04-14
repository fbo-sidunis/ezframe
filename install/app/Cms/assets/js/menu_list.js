let Menus = (() => {
  let self = {
    modal: {
      labelEl: () => document.querySelector('#add_menu_label'),
      getEl: () => document.querySelector("#add_menu"),
      show() {
        this._modal = bootstrap.Modal.getOrCreateInstance(this.getEl());
        this._modal.show();
      },
      hide() {
        this._modal.hide();
      },
      setTitle(title) {
        this.labelEl().innerText = title;
      }
    },
    form: new Form({
      formEl: "#form_menu",
      btnSave: "#btn_save_menu",
      route: "cms_menu_ajax_save",
      callBackAfterSaveSuccess: () => {
        self.sidTable.dataTable.ajax.reload(null, false);
        self.initListMenusParents();
        self.modal.hide();
      },
      callBackAfterFill(datas){
        //Ici, on gère le remplissage des éléments du formulaire qui néccessitent un traitement complexe
        //Les roles
        foreach(this.formEl.querySelectorAll("input.crud_role"), (/** @type {HTMLInputElement} */ roleInput)=>{
          roleInput.checked = datas.roles.filter(a => a.code_role == roleInput.dataset.role).map(a => a[roleInput.value]).includes("Y");
        })
        //Type de lien
        foreach(this.formEl.querySelectorAll("input[data-attr-name=\"type_link\"]"), (/** @type {HTMLInputElement} */ typeLinkInput)=>{
          switch(typeLinkInput.value){
            case "ID" :
              typeLinkInput.checked = datas.id_page ? true : false;
            break;
            case "LINK" :
              typeLinkInput.checked = datas.link ? true : false;
            break;
            case "ALIAS" :
              typeLinkInput.checked = datas.alias ? true : false;
            break;
            case "" :
              typeLinkInput.checked = !datas.id_page && !datas.link && !datas.alias ? true : false;
            break;
          }
        })
      }
    }),
    btnsDelete: () => document.querySelectorAll(".btn-delete-menu:not([data-event-added])"),
    _btnsDelete: {},
    btnsEdit: () => document.querySelectorAll(".btn-addedit-menu:not([data-event-added])"),
    trPrefix: "tr_menu_",
    sidTable: new SidunisDataTable({
      table: "#table_menu",
      customPagination : false,
      customOptions: {
        paging:false,
        scrollY:"300px",
        rowReorder: {
          dataSrc: 'ordre',
        },
      },
      route: "cms_menu_ajax_list",
      pagination: "#menu_pagination",
      tmplPagination: ".template-page",
      columns: [
        { data: "ordre", orderable: true },
        { data: "id" },
        { data: "public" },
        { data: "libelle" },
        { data: "title" },
        { data: "active"},
        {
          data: "id", orderable: false, render: function (data, type) {
            //TEMPLATE
            return processTemplate({
              selector: "#menu_template_actions", data: {
                CONTENT: [
                  {selector: "#menu_template_action_editer", data: { TOOLTIP: "Editer", ID: data }},
                  {selector: "#menu_template_action_suppr", data: { TOOLTIP: "Supprimer", ID: data }},
                ].map(processTemplate).join(''),
              }
            });
          }
        },
      ],
      callbackData: function (d) {
        //DANS LE CAS OU UN réordonnancement DOIT ETRE EFFECTUE
        d.nextReorder = self.nextReorder;
        self.nextReorder = null;
      },
      callbackDataSrc: function (json) {
        foreach(json.data, (/** @type {Object} */ menu) => {
          menu.DT_RowId = self.trPrefix + menu.id;
        });
      },
      callbackDrawCallback: function (d) {
        self._btnsDelete = {};
        foreach(self.btnsDelete(), (/** @type {HTMLButtonElement} */ btnDelete) => {
          self._btnsDelete[btnDelete.dataset.id] = new BtnDelete({
            btnDelete: btnDelete,
            route: "cms_menu_ajax_delete",
            vars: { id: btnDelete.dataset.id },
            callBackAfterDeleteSuccess: () => {
              self.sidTable.dataTable.ajax.reload(null, false);
            }
          });
          self._btnsDelete[btnDelete.dataset.id].init();
        });
        foreach(self.btnsEdit(), (/** @type {HTMLButtonElement} */ btnEdit) => {
          let id = btnEdit.dataset.id ?? 0;
          btnEdit.addEventListener("click", () => {
            let datas = self.sidTable.dataTable.row("#" + self.trPrefix + id).data() ?? {};
            self.form.fill(datas);
            self.modal.setTitle(!id ? "Ajouter un nouveau menu" : "Editer le menu : #" + id);
            self.modal.show();
          })
          btnEdit.dataset.eventAdded = 1;
        });
      }
    }),
    nextReorder:null,
    selectMenusParents : () => self.form.formEl.querySelector("#select_menu_parent"),
    initListMenusParents(){
      sendPOST({
        route: "cms_menu_ajax_list",
        vars: {noTable:1},
        callback: response => {
          foreach(self.selectMenusParents().querySelectorAll("option"), option=>{
            if (option.value !== ""){
              option.remove();
            }
          });
          let menus = response.data.menus;
          foreach(menus, menu => {
            createElement({
              tagName: "option",
              attrs:{
                value: menu.id,
                innerText: menu.libelle,
              },
              parent:self.selectMenusParents(),
            });
          })
          jQuery(self.selectMenusParents()).selectpicker("refresh");
        }
      })
    },
    init() {
      self.sidTable.init();
      self.sidTable.dataTable.on( 'row-reorder', function ( e, diff, edit ) {
        let nextReorder = {};
        foreach(edit.nodes,(/** @type {HTMLTableRowElement} */ tr) => {
          let datas = self.sidTable.dataTable.row(tr).data();
          nextReorder[datas.id] = edit.values[tr.id];
        });
        self.nextReorder = nextReorder;
        //On met en attente un réordonnancement pour la prochaine requête Ajax
      });
      self.form.init();
      self.initListMenusParents();
    }
  };
  self.init();
  return self;
})();