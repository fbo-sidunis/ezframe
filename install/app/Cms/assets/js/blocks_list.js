let Blocks = (() => {
  let self = {
    tr_prefix: "tr_block_",
    modal: {
      labelEl: () => document.querySelector('#add_block_label'),
      getEl: () => document.querySelector("#add_block"),
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
      formEl: "#form_block",
      btnSave: "#btn_save_block",
      route: "cms_blocks_ajax_save",
      callBackAfterSaveSuccess: () => {
        self.sidTable.dataTable.ajax.reload(null, false);
        self.modal.hide();
      },
      callBackAfterFill(datas){
        foreach(this.formEl.querySelectorAll("input.quill-editor"),input=>input.fillQuill());
        self.content.update();
      }
    }),
    btnsDelete : () => document.querySelectorAll(".btn-delete-block:not([data-event-added])"),
    _btnsDelete : {},
    btnsEdit : () => document.querySelectorAll(".btn-edit-block:not([data-event-added])"),
    sidTable : new SidunisDataTable({
      table: "#table_blocks",
      route: "cms_blocks_ajax_list",
      pagination: "#blocks_pagination",
      tmplPagination: ".template-page",
      columns: [
        {data: "id"},   
        {data: "libelle"},
        {data: "id_layout"},
        {data: "content"},
        {data: "id", orderable: false, render: function (data, type) {
          return processTemplate({selector: "#blocks_template_actions", data: {
            CONTENT: [
              processTemplate({selector: "#blocks_template_action_editer", data: {TOOLTIP: "Editer", ID: data}}),
              processTemplate({selector: "#blocks_template_action_suppr", data: {TOOLTIP: "Supprimer", ID: data}}),
            ].join(""),
          }});
        }},
      ],
      callbackDataSrc: function (json) {
        foreach(json.data, (/** @type {Object} */ block) => {
          block.DT_RowId = self.trPrefix + block.id;
        });
      },
      callbackDrawCallback: function (d) {
        self._btnsDelete = {};
        foreach(self.btnsDelete(),(/** @type {HTMLButtonElement} */ btnDelete) => {
          self._btnsDelete[btnDelete.dataset.id] = new BtnDelete({
            btnDelete: btnDelete,
            route : "cms_blocks_ajax_delete",
            vars : {id: btnDelete.dataset.id},
            callBackAfterDeleteSuccess: () => {
              self.sidTable.dataTable.ajax.reload(null,false);
            }
          });
          self._btnsDelete[btnDelete.dataset.id].init();
        });
        foreach(self.btnsEdit(), (/** @type {HTMLButtonElement} */ btnEdit) => {
          let id = btnEdit.dataset.id ?? 0;
          btnEdit.addEventListener("click", () => {
            let datas = self.sidTable.dataTable.row("#" + self.trPrefix + id).data() ?? {};
            self.form.fill(datas);
            self.modal.setTitle(!id ? "Ajouter un nouveau bloc" : "Editer le bloc : #" + id);
            self.modal.show();
          })
          btnEdit.dataset.eventAdded = 1;
        });
      }
    }),
    content : {
      sidQuill : null,
      inputLayout: () => document.querySelector("#select_block_layout"),
      selectedLayoutOption(){
        return this.inputLayout().querySelector("option:checked");
      },
      input: () => document.querySelector("#input_block_content"),
      group(){return getParent(this.input(),".form-group");},
      container(){
        let id = "block_content_container";
        return this.group().querySelector("#"+id) ?? createElement({
          tagName: "div",
          attrs: {
            id:id,
            className:"quill-container container"
          },
          parent: this.input().parentNode,
        });
      },
      initEditor(){
        let quills = {};
        foreach(self.form.formEl.querySelectorAll(".quill-content"), (/** @type {HTMLDivElement} */ div) => {
          quills[div.dataset.number] = new Quill(div, {
            theme: 'bubble',
            modules: {
              toolbar: [
                ['bold', 'italic', 'underline'],
                ['link', 'blockquote', 'code-block'],
                [{ 'align': [] }],
              ]
            }
          });
        });
        this.sidQuill = new SidunisQuill({input : this.input(), quills : quills});
        this.sidQuill.fillQuill();
      },
      update(){
        if (this.sidQuill){
          this.sidQuill.destroy();
        }
        if (this.inputLayout().value) {
          this.container().innerHTML = this.selectedLayoutOption().dataset.html;
          this.initEditor();
          this.group().style.display = "";
        }else{
          this.group().style.display = "none";
        }
      },
      init(){
        this.inputLayout().addEventListener("change", () => {
          this.update();
        }),
        this.update();
      }
    },
    init(){
      self.form.init();
      self.sidTable.init();
      self.content.init()
    }
  };
  self.init();
  return self;
})();