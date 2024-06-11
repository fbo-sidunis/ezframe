class SidunisDataTable {
  maxPageDiff = 2;
  table = null;
  route = null;
  dataTable = null;
  pagination = null;
  colReorder = false;
  defaultOrder = [];
  responsive = false;
  columnDefs = [];
  _customPagination = true;
  _customOptions = {};

  callbackDataSrc = json => {
  };
  callbackData = d => {
  };
  callbackInitComplete = () => {
  };
  callbackDrawCallback = settings => {
  };

  /**
   * Constructeur
   * @param {object} parameters 
   * @param {array} parameters.defaultOrder ['column','dir']
   * @param {HTMLTableElement|string} parameters.table
   * @param {string} parameters.route
   * @param {string} parameters.colReorder true/false
   * @param {string} parameters.responsive true/false
   * @param {HTMLElement|string} parameters.pagination
   * @param {HTMLElement|string} parameters.tmplPagination
   * @param {array} parameters.columns
   * @param {array} parameters.columnDefs
   * @param {function} parameters.callbackData
   * @param {function} parameters.callbackDataSrc
   * @param {function} parameters.callbackInitComplete
   * @param {function} parameters.callbackDrawCallback
   * @param {function} parameters.customOptions
   */
  constructor(parameters) {
    this.setTable(parameters.table ?? null);
    this.setRoute(parameters.route ?? null);
    this.setColReorder(parameters.colReorder ?? false);
  
    this.setResponsive(parameters.responsive ?? false);
    this.customPagination = parameters.customPagination ?? this.customPagination;
    this.customOptions = parameters.customOptions ?? {}
    if (this.customPagination) {
      this.setPagination(parameters.pagination ?? null);
      this.setTmplPagination(parameters.tmplPagination ?? null);
    }
    this.setColumns(parameters.columns ?? []);
    this.setColumnDefs(parameters.columnDefs ?? []);
    this.setCallbackData(parameters.callbackData ?? null);
    this.setCallbackDataSrc(parameters.callbackDataSrc ?? null);
    this.setCallbackInitComplete(parameters.callbackInitComplete ?? null);
    this.setCallbackDrawCallback(parameters.callbackDrawCallback ?? null);
    this.setDefaultOrder(parameters.defaultOrder ?? null);
  }

  init() {
    if (this.dataTable) this.dataTable.destroy();
    this.table.style.width = "100%";
    this.dataTable = new DataTable(this.table, this.getOptions());
  }

  setTable(table) {
    let _table = null;
    if (table instanceof HTMLElement) {
      _table = table;
    } else if (typeof (table) == "string") {
      _table = document.querySelector(table);
    }
    if (!(_table instanceof HTMLTableElement)) {
      throw "La table donnée est invalide"
    }
    this.table = _table;
    return this;
  }

  setPagination(pagination) {
    let _pagination = null;
    if (pagination instanceof HTMLElement) {
      _pagination = pagination;
    } else if (typeof (pagination) == "string") {
      _pagination = document.querySelector(pagination);
    }
    if (!(_pagination instanceof HTMLElement)) {
      throw "La pagination donnée est invalide"
    }
    this.pagination = _pagination;
    return this;
  }

  setTmplPagination(tmplPagination) {
    let _tmplPagination = null;
    if (tmplPagination instanceof HTMLElement) {
      _tmplPagination = tmplPagination;
    } else if (typeof (tmplPagination) == "string") {
      _tmplPagination = this.pagination.querySelector(tmplPagination) || document.querySelector(tmplPagination);
    }
    if (!(_tmplPagination instanceof HTMLElement)) {
      throw "Le template pagination donné est invalide"
    }
    this.tmplPagination = (_tmplPagination.innerHTML ?? "").trim();
    return this;
  }

  setRoute(route) {
    if (typeof (route) != "string") {
      throw "La route est invalide"
    }
    this.route = route;
    return this;
  }

  getCols() {
    return this.dataTable.columns;
  }
  getColumnDefs() {
    return this.dataTable.columnDefs;
  }
  getColByName(colName) {
    console.log('colName', colName);
    return this.dataTable.column(colName + ':name').index();
  }
  getColByIndex(index) {
    return this.dataTable.column(index);
  }

  setColumns(columns) {
    if (typeof (columns) != "object") {
      throw "Les colonnes sont invalides"
    }
    this.columns = columns;
    return this;
  }

  setColumnDefs(columnDefs) {
    // console.log('>> columnDefs', columnDefs);
    this.columnDefs = columnDefs;
    return this;
  }
  setResponsive(responsive) {
    this.responsive = responsive;
    return this;
  }
  setColReorder(colreorder) {
    this.colReorder = colreorder;
    return this;
  }
  setDefaultOrder(defaultOrder) {
    this.defaultOrder = defaultOrder;
    return this;
  }

  setCallbackInitComplete(callbackInitComplete) {
    if (typeof (callbackInitComplete) != "function") {
      return this;
    }
    this.callbackInitComplete = callbackInitComplete;
    return this;
  }

  setCallbackDrawCallback(callbackDrawCallback) {
    if (typeof (callbackDrawCallback) != "function") {
      return this;
    }
    this.callbackDrawCallback = callbackDrawCallback;
    return this;
  }

  setCallbackData(callbackData) {
    if (typeof (callbackData) != "function") {
      return this;
    }
    this.callbackData = callbackData;
    return this;
  }

  setCallbackDataSrc(callbackDataSrc) {
    if (typeof (callbackDataSrc) != "function") {
      return this;
    }
    this.callbackDataSrc = callbackDataSrc;
    return this;
  }

  getOptions() {
    let defaultOptions = {
      paging: true,
      dom: 't',
      scrollX: true,
      processing: true,
      serverSide: true,
      deferRender: true,
      columns: this.columns,
      columnDefs: this.columnDefs,
      colReorder: this.colReorder,
      responsive: this.responsive,
      order: this.defaultOrder ?? [0, 'asc'],
      ajax: {
        url: Route.get(this.route),
        dataSrc: json => {
          this.callbackDataSrc(json);
          return json.data;
        },
        data: d => {
          this.callbackData(d);
        },
      },
      language: {
        url: DATATABLE_FR_PATH
      },
      initComplete: () => {
        setTimeout(() => {
          // this.dataTable.columns.adjust();
        }, 100);
        if (this.customPagination) {
          this.initPagination(this.dataTable);
        }
        this.callbackInitComplete();
      },
      drawCallback: settings => {
        if (this.customPagination) {
          this.cbPagination(this.dataTable);
        }
        this.callbackDrawCallback(settings);
      }
    };
    foreach(this.customOptions, (value, key) => {
      defaultOptions[key] = value;
    })
    return defaultOptions;
  }

  initPagination() {
    let navPagination = this.pagination.querySelector("nav.pagination-nav");
    if (!navPagination) return false;
    let pageNext = navPagination.querySelector("a.page-link.page-next");
    let pagePrevious = navPagination.querySelector("a.page-link.page-previous");
    let disable_all = () => {
      foreach(navPagination.querySelectorAll(".page-item"), (item) => {
        item.classList.add("disabled");
      })
    }
    if (pageNext) pageNext.addEventListener("click", () => {
      disable_all();
      this.dataTable.page("next").draw("page");
    });
    if (pagePrevious) pagePrevious.addEventListener("click", () => {
      disable_all();
      this.dataTable.page("previous").draw("page");
    });
    let numbersContainer = navPagination.querySelector("ul.numbers");
    if (numbersContainer) numbersContainer.addEventListener("click", (event) => {
      if (event.target.classList.contains("page-link") && event.target.dataset.page !== "") {
        disable_all();
        this.dataTable.page(parseInt(event.target.dataset.page)).draw("page");
      }
    })
  }

  cbPagination() {
    let navPagination = this.pagination.querySelector("nav.pagination-nav");
    let pageInfo = this.dataTable.page.info();
    if (!navPagination) return console.error("Aucune navPagination");
    let pageNext = navPagination.querySelector("a.page-link.page-next") ?? null;
    if (!pageNext) return console.error("Aucune pageNext");
    let pagePrevious = navPagination.querySelector("a.page-link.page-previous") ?? null;
    if (!pagePrevious) return console.error("Aucune pagePrevious");
    if (pageInfo.page > 0) {
      pagePrevious.parentNode.classList.remove("disabled");
    } else {
      pagePrevious.parentNode.classList.add("disabled");
    }
    if ((pageInfo.page + 1) < pageInfo.pages) {
      pageNext.parentNode.classList.remove("disabled");
    } else {
      pageNext.parentNode.classList.add("disabled");
    }
    let numbersContainer = navPagination.querySelector("ul.numbers");
    if (!numbersContainer) return console.error("Aucune numbersContainer");
    if (this.tmplPagination.length <= 0) return console.error("Le template de puce est vide");
    let add_link = (active, num, nums) => {
      numbersContainer.innerHTML += processTemplate({
        template: this.tmplPagination, data: {
          PAGE_ACTIVE: active,
          PAGE_NUMBER: num,
          PAGE_NUMBER_SHOWN: nums
        }
      });
    }
    numbersContainer.innerHTML = "";
    add_link(pageInfo.page == 0 ? " active " : "", 0, 1);
    if (0 < (pageInfo.page - this.maxPageDiff - 1)) add_link(" disabled ", "", "...");
    for (let i = pageInfo.page - this.maxPageDiff; i <= (pageInfo.page + this.maxPageDiff); i++) {
      if (pageInfo.pages > (i + 1) && i > 0) add_link(i == pageInfo.page ? " active " : "", i, i + 1);
    }
    if (pageInfo.pages > (pageInfo.page + this.maxPageDiff + 2)) add_link(" disabled ", "", "...");
    if (pageInfo.pages > 1) add_link(pageInfo.page == (pageInfo.pages - 1) ? " active " : "", pageInfo.pages - 1, pageInfo.pages);
  }

  get customOptions() {
    return this._customOptions;
  }
  set customOptions(value) {
    this._customOptions = value;
  }
  get customPagination() {
    return this._customPagination;
  }
  set customPagination(value) {
    this._customPagination = value;
  }
}
