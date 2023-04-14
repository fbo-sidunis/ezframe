let Pages = (() => {
  let self = {
    modal : {
      labelEl: () => document.querySelector('#add_page_label'),
      getEl : () => document.querySelector("#add_page"),
      show(){
        this._modal = bootstrap.Modal.getOrCreateInstance(this.getEl());
        this._modal.show();
      },
      hide(){
        this._modal.hide();
      },
      setTitle(title){
        this.labelEl().innerText = title;
      }
    },
    form : new Form({
      formEl: "#form_page",
      btnSave: "#btn_save_page",
      route: "cms_pages_ajax_save",
      callBackAfterSaveSuccess: () => {
        self.sidTable.dataTable.ajax.reload(null,false);
        self.modal.hide();
      }
    }),
    btnsDelete : () => document.querySelectorAll(".btn-delete-pages:not([data-event-added])"),
    _btnsDelete : {},
    btnsEdit : () => document.querySelectorAll(".btn-addedit-pages:not([data-event-added])"),
    trPrefix : "tr_page_",
    sidTable : new SidunisDataTable({
      table: "#table_pages",
      route: "cms_pages_ajax_list",
      pagination: "#pages_pagination",
      tmplPagination: ".template-page",
      columns: [
        {data: "id"},   
        {data: "libelle"},
        {data: "created_by"},
        {data: "created_date"},
        {data: "id", orderable: false, render: function (data, type) {
          //TEMPLATE
          return processTemplate({
            selector: "#pages_template_actions",
            data: {
              CONTENT: [
                processTemplate({
                  selector: "#pages_template_action_editer",
                  data: {
                    TOOLTIP: "Editer",
                    ID: data
                  }
                }),
                processTemplate({
                  selector: "#pages_template_action_suppr",
                  data: {
                    TOOLTIP: "Supprimer",
                    ID: data
                  }
                })
              ].join(""),
          }});
        }},
      ],
      callbackDataSrc: function (json) {
        foreach(json.data,(/** @type {Object} */ page) => {
          page.DT_RowId = self.trPrefix + page.id;
        });
      },
      callbackDrawCallback: function (d) {
        self._btnsDelete = {};
        foreach(self.btnsDelete(),(/** @type {HTMLButtonElement} */ btnDelete) => {
          self._btnsDelete[btnDelete.dataset.id] = new BtnDelete({
            btnDelete: btnDelete,
            route : "cms_pages_ajax_delete",
            vars : {id: btnDelete.dataset.id},
            callBackAfterDeleteSuccess: () => {
              self.sidTable.dataTable.ajax.reload(null,false);
            }
          });
          self._btnsDelete[btnDelete.dataset.id].init();
        });
        foreach(self.btnsEdit(),(/** @type {HTMLButtonElement} */ btnEdit) => {
          let id = btnEdit.dataset.id ?? 0;
          btnEdit.addEventListener("click", () => {
            let datas = self.sidTable.dataTable.row("#"+self.trPrefix+id).data() ?? {};
            self.form.fill(datas);
            self.modal.setTitle(!id ? "Ajouter une nouvelle page" : "Editer la page : #"+id);
            self.modal.show();
          })
          btnEdit.dataset.eventAdded = 1;
        });
      }
    }),
    init(){
      self.sidTable.init();
      self.form.init();
    }
  };
  self.init();
  return self;
})();