let Layouts = (() => {
  let self = {
    modal : {
      labelEl: () => document.querySelector('#add_layout_label'),
      getEl : () => document.querySelector("#add_layout"),
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
      formEl: "#form_layout",
      btnSave: "#btn_save_layout",
      route: "cms_layout_ajax_save",
      callBackAfterSaveSuccess: () => {
        self.sidTable.dataTable.ajax.reload(null,false);
        self.modal.hide();
      }
    }),
    btnsDelete : () => document.querySelectorAll(".btn-delete-layouts:not([data-event-added])"),
    _btnsDelete : {},
    btnsEdit : () => document.querySelectorAll(".btn-addedit-layouts:not([data-event-added])"),
    trPrefix : "tr_layout_",
    sidTable : new SidunisDataTable({
      table: "#table_layouts",
      route: "cms_layout_ajax_list",
      pagination: "#layouts_pagination",
      tmplPagination: ".template-page",
      columns: [
        {data: "id"},   
        {data: "libelle"},
        {data: "template_path"},
        {data: "template_back_path"},
        {data: "nb_content"},
        {data: "type", render: (data, type, row, meta) => {
          if (type !== "display") {
            return data;
          }
          return str_capitalize(data);
        }},
        {data: "id", orderable: false, render: function (data, type) {
          //TEMPLATE
          return processTemplate({
            selector: "#layouts_template_actions",
            data: {
              CONTENT: [
                processTemplate({
                  selector: "#layouts_template_action_editer",
                  data: {
                    TOOLTIP: "Editer",
                    ID: data
                  }
                }),
                processTemplate({
                  selector: "#layouts_template_action_suppr",
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
        foreach(json.data,(/** @type {Object} */ layout) => {
          layout.DT_RowId = self.trPrefix + layout.id;
        });
      },
      callbackDrawCallback: function (d) {
        self._btnsDelete = {};
        foreach(self.btnsDelete(),(/** @type {HTMLButtonElement} */ btnDelete) => {
          self._btnsDelete[btnDelete.dataset.id] = new BtnDelete({
            btnDelete: btnDelete,
            route : "cms_layout_ajax_delete",
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
            self.modal.setTitle(!id ? "Ajouter un nouveau layout" : "Editer la layout : #"+id);
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