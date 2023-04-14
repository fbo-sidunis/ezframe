/* 
 *
 *
 *
 */


const MODAL_ADDEDIT_ROUTE = "mdl_addedit_route";
const TITLE_MODAL_ADDEDIT_ROUTE = "mdl_title_addedit_route";


$(document).ready(function () {
  initBtnTbl();
  $("#table_routes").DataTable({
    "pageLength": 50
    , fixedHeader: true
  });
  $("#btn_add_route").click(function () {
    $("#" + TITLE_MODAL_ADDEDIT_ROUTE).text("Ajouter une route");
    clearMdlRoute();
    $("#" + MODAL_ADDEDIT_ROUTE).modal('show');
  });
  $("#btn_save_route").click(function () {
    saveRoute();
  });

});



function initBtnTbl() {
  $(".edit_route").click(function () {
    let alias = $(this).data('alias');
    MdlEditRoute(alias);
  });

  $(".delete_route").click(function () {
    let url = $(this).data('url');
    if (confirm("Suppression de la route " + url + " ? ")) {
      deleteRoute(url);
    }
  });

  $("#modules").change(function () {
    console.info("modules change");
    let app = $(this).val();
    let datas = {app: app};
    sendAjaxRoute("admin_ajax_get_controller", datas, function (response) {
      console.log('admin_ajax_get_controller', response);
      let list = typeof (response.data) != 'undefined' && response.data != null ? response.data : [];
      $("#controllers").html("<option value=''> -- </option>");
      $("#fonctions").val("");
      $.each(list, function (i, el) {
        $("#controllers").append("<option value='" + el + "'>" + el.replace("Controller", "") + "</option>");
      });
    }, null, {opt: {async: false}});
  });

  $("#controllers").change(function () {
    console.info("controllers change");
    let ctrl = $(this).val();
    let app = $("#modules").val();
    let datas = {'app': app, 'ctrl': ctrl};
    sendAjaxRoute("admin_ajax_get_functions", datas, function (response) {
      console.log('admin_ajax_get_functions', response);
      let list = typeof (response.data) != 'undefined' && response.data != null ? response.data : [];
      $("#fonctions").html("<option value=''> -- </option>");

      $.each(list, function (i, el) {
        $("#fonctions").append("<option value='" + el + "'>" + el + "</option>");
      });
    }, null, {opt: {async: false}});
  });
}

function clearMdlRoute() {
  $("#inp_alias").val("");
  $("#inp_url").val("");
  $("#controllers").val("");
  $("#fonctions").val("");
  $("#inp_fallback").val("");
  $("#inp_old_url").val("");
}

function MdlEditRoute(alias) {
  $("#" + TITLE_MODAL_ADDEDIT_ROUTE).text("Modifier la route");
  clearMdlRoute();
  let datas = {alias: alias};
  sendAjaxRoute("admin_ajax_get_route", datas, function (response) {
    console.log('admin_ajax_get_route', response);
    let infos = typeof (response.data) != 'undefined' && response.data != null ? response.data : null;
    let template = typeof (infos.t) != "undefined" ? infos.t : "";
    let fallback = typeof (infos.fallback) != "undefined" ? infos.fallback : "";
    $("#inp_alias").val(infos.alias);
    $("#inp_url").val(infos.url);
    $("#inp_old_url").val(infos.url);
    $("#inp_template").val(template);
    $("#inp_fallback").val(fallback);
    $("#modules").val(infos.m).trigger('change');
    setTimeout(function () {
      $("#controllers").val(infos.c).trigger('change');
      setTimeout(function () {
        $("#fonctions").val(infos.f);
      }, 200);
    }, 200);

  }, null, {opt: {async: false}});
  $("#" + MODAL_ADDEDIT_ROUTE).modal('show');
}

/**
 * Sauvegarde de la route dans le Json
 * @returns {undefined}
 */
function saveRoute() {
  let datas = {
    url: $("#inp_url").val()
    , oldurl: $("#inp_old_url").val()
    , alias: $("#inp_alias").val()
    , m: $("#modules").val()
    , c: $("#controllers").val()
    , f: $("#fonctions").val()
    , template: $("#inp_template").val()
    , fallback: $("#inp_fallback").val()
  };
  sendAjaxRoute("admin_ajax_save_route", datas, function (response) {
    console.log('admin_ajax_save_route', response);
    document.location.reload();

  }, null, {opt: {async: false}});


  $("#" + MODAL_ADDEDIT_ROUTE).modal('hide');
}

function deleteRoute(url) {
  let datas = {url: url};
  sendAjaxRoute("admin_ajax_delete_route", datas, function (response) {
    console.log('admin_ajax_delete_route', response);
    document.location.reload();
  });
}