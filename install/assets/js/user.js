var searchTimer = null;

$('#inp_search_user').keyup(function () {
  var search = $(this).val();
  clearTimeout(searchTimer);
  searchTimer = setTimeout(function () {
    searchUsers(search);
  }, 300);
});

//----------------------------------
//SEARCH USER
//----------------------------------

function searchUsers(search) {

  let variables = {
    search: search
  };

  sendFormData(Route.get("user_ajax_getUsers"), variables, function (response) {
    //  console.log(response);
    if (typeof (response) != 'undefined' && response.result) {
      var HTML = '';

      $("#tbody_tbl_user").html('');
      foreach(response.data,function (el) {
        //console.log(i,el);
        var edit = '<button class="btn btn_edit" data-id="' + el.id + '">' +
          '<i class="fas fa-edit"></i>' +
          '</button>';

        var suppr = '<button class="btn btn_suppr" style="color:#8B0000"  data-id="' + el.id + '">' +
          '<i class="fas fa-trash-alt"></i>' +
          '</button>';

        var etat = el.actif == 'Y' ? 'fa-user-check' : 'fa-user-times';

        var color = el.actif == 'Y' ? '#008000' : '#8B0000';

        var btnactivate = '<button class="btn btn_activate"  data-id="' + el.id + '" data-etat="' + el.actif + '">' +
          '<i id="btn_activate_user_' + el.id + '" class="fas ' + etat + '" style="color:' + color + '"></i>' +
          '</button>';
        var btnreset = '<button class="btn btn_reset" data-id="' + el.id + '" data-mail="' + el.mail + '">' +
          '<i class="fas fa-sync-alt" style="color: #00008B"></i>' +
          '</button>';
        var lastCo = '';

        if (el.date == null) {
          lastCo = "Aucune connexion";
        } else {
          lastCo = el.date
        }

        HTML += '<tr data-id="' + el.id + '" id="tr_user_' + el.id + '">' +
          '<td id="id_user" value="' + el.id + '">' + el.id + '</td>' +
          '<td>' + el.nom + '</td>' +
          '<td>' + el.prenom + '</td>' +
          '<td>' + el.mail + '</td>' +
          '<td>' + lastCo + '</td>' +
          '<td style="text-align:center">' + btnactivate + ' ' + edit + ' ' + btnreset + ' ' + suppr + '</td>' +
          '</tr>';
      });
    }


    $("#tbody_tbl_user").html(HTML);

    //----------------------------------
    //Button suppr user
    //----------------------------------
    $(".btn_suppr").click(function () {
      var id_user = $(this).data('id');
      $("#userid_suppr").val(id_user);
      $("#mdl_confirmed_suppr").modal('show');
    });

    //----------------------------------
    //Button edit user
    //----------------------------------
    $(".btn_edit").click(function () {
      var id_user = $(this).data('id');
      $("#userid_edit").val(id_user);

      let variables = {
        user_id: id_user
      };

      //vider la modale
      $('#inp_name_user').val('');
      $('#inp_firstname_user').val('');
      $('#inp_email_user').val('');
      $('.cbx_edit_role').prop('checked', false);

      //remplir la modal avec les infos de l'utilisateur
      sendFormData(Route.get("user_ajax_getUserById"), variables, function (response) {
        //console.log('GetUserById',response);
        var USER = typeof (response.USER) != 'undefined' ? response.USER : null;
        if (USER) {
          $('#inp_name_user').val(USER.nom);
          $('#inp_firstname_user').val(USER.prenom);
          $('#inp_email_user').val(USER.mail);
        } else {
          alert('Utilisateur introuvable...');
        }

        var ROLE = typeof (response.ROLE) != 'undefined' ? response.ROLE : null;
        if (ROLE) {
          $.each(ROLE, function (i, code_role) {
            //console.log(i,code_role);
            $('#chk_edit_' + code_role).prop('checked', true);
          });
        }
        $("#mdl_edit_user").modal('show');
      });
    });

    //----------------------------------
    //Button activate or deactivate user
    //----------------------------------
    $('.btn_activate').click(function () {
      var id_user = $(this).data('id');
      var etat = $(this).attr('data-etat');
      //console.log('DESACTIVER');
      activateUser(id_user, etat);
    });

    //----------------------------------
    //Button to generate password in modal mdl_reset_pwd
    //----------------------------------
    $('#btn_generate_reset_pwd').click(function () {
      var randomString = Math.random().toString(36).slice(-8);
      $('#inp_reset_pwd').val(randomString);
    });

    //----------------------------------
    //Button to open modal mdl_reset_pwd
    //----------------------------------
    $(".btn_reset").click(function () {
      var id_user = $(this).data('id');
      var mail = $(this).data('mail');
      $('#inp_reset_pwd').val('');

      $("#userid_reset_pwd").val(id_user);
      $("#mail_reset_pwd").val(mail);

      $("#mdl_reset_pwd").modal('show');
    });
  });
}

searchUsers("");

//----------------------------------
//Button to generate password for new users
//----------------------------------
$('#btn_generate_pwd').click(function () {
  var randomString = Math.random().toString(36).slice(-8);
  $('#inp_password_new_user').val(randomString);
});

//----------------------------------
//UPDATE USER
//----------------------------------
function updateUser(user_id) {

  var name = $('#inp_name_user').val();
  var firstname = $('#inp_firstname_user').val();
  var mail = $('#inp_email_user').val();
  var arrRoles = [];

  $('.cbx_edit_role:checked').each(function () {
    arrRoles.push($(this).val());
  });
  let variables = {
    user_id: user_id,
    name: name,
    firstname: firstname,
    mail: mail,
    roles: JSON.stringify(arrRoles)
  };

  sendFormData(Route.get("user_ajax_updateUser"), variables, function (response) {
    //console.log(response);
    window.location.reload();
  });
}

//----------------------------------
//ADD USER
//----------------------------------
function addUser() {
  var name = $('#inp_name_new_user').val();
  var firstname = $('#inp_firstname_new_user').val();
  var mail = $('#inp_email_new_user').val();
  var pwd = $('#inp_password_new_user').val();
  var arrRoles = [];
  $('.cbx_role:checked').each(function () {
    arrRoles.push($(this).val());
  });

  let variables = {
    name: name,
    firstname: firstname,
    mail: mail,
    pwd: pwd,
    roles: JSON.stringify(arrRoles)
  };
  sendFormData(Route.get("user_ajax_insertUser"), variables, function (response) {
    window.location.reload();
  });
}

//----------------------------------
//VERIFIER() to verify fields are not empty
//----------------------------------
function verifier() {
  var inp_name_new_user = $('#inp_name_new_user').value;
  var inp_firstname_new_user = $('#inp_firstname_new_user').value;
  var inp_email_new_user = $('#inp_email_new_user').value;
  var inp_password_new_user = $('#inp_password_new_user').value;

  if (inp_name_new_user == "" || inp_firstname_new_user == "" || inp_email_new_user == "" || inp_password_new_user == "") {
    alert('Veuillez complétez tous les champs du formulaire');
    $('#inp_name_new_user').focus;
    $('#inp_firstname_new_user').focus;
    $('#inp_email_new_user').focus;
    $('#inp_password_new_user').focus;
    return false;
  } else {
    return true;
  }
}

//----------------------------------
//ACTIVATE or DEACTIVATE USER
//----------------------------------
function activateUser(user_id, etat) {

  let variables = {
    user_id: user_id,
    etat: etat == 'Y' ? 'N' : 'Y'
  };

  sendFormData(Route.get("user_ajax_activate"), variables, function (response) {
    //console.log('activateUser',response,variables);
    //console.log('etat',etat);
    var btn_etat = $('#btn_activate_user_' + user_id);
    //console.table('btn_etat',btn_etat);
    var color = etat == 'N' ? '#008000' : '#8B0000';
    if (etat == 'N') {
      //console.log('Ici je désactive...');
      btn_etat.removeClass('fa-user-times');
      btn_etat.addClass('fa-user-check');
      btn_etat.parent().attr("data-etat", "Y");

    } else {
      //console.log('Ici j active...');
      btn_etat.addClass('fa-user-times');
      btn_etat.removeClass('fa-user-check');
      btn_etat.parent().attr("data-etat", "N");
    }
    btn_etat.css('color', color);
  });
}

//----------------------------------
//DELETE USER
//----------------------------------
function deleteUser(user_id) {
  let variables = {
    user_id: user_id
  };
  sendFormData(Route.get("user_ajax_delete"), variables, function (response) {
    console.log(response);
    $('#tr_user_' + user_id).remove();
  });
}

//----------------------------------
//UPDATE PWD USER
//----------------------------------
function updatePass(user_id) {

  var pass = $('#inp_reset_pwd').val();
  let variables = {
    user_id: user_id,
    pass: pass
  };
  sendFormData(Route.get("user_ajax_updatePass"), variables, function (response) {
    //console.log(response);
  });
}

//----------------------------------
//SENDNEWPASS(mail)
//----------------------------------
function sendNewPass(mail) {

  var pass = $('#inp_reset_pwd').val();
  let variables = {
    mail: mail,
    pass: pass
  };
  sendFormData(Route.get("user_sendNewPass"), variables, function (response) {
    //console.log(response);
  });

}

//----------------------------------
//Button to confirm suppr user
//----------------------------------
$('#btn_confirmed_suppr').click(function () {
  var user_id = $("#userid_suppr").val();
  //console.log('SUPPRIMER', user_id);
  deleteUser(user_id);
  $('#mdl_confirmed_suppr').modal('hide');
});

//----------------------------------
//Button to create user
//----------------------------------
$('#btn_create_user').click(function () {
  if (verifier() == true) {
    addUser();
    $('#mdl_add_user').modal('hide');
  }
});

//----------------------------------
//Button to edit user
//----------------------------------
$('#btn_edit_user').click(function () {
  var user_id = $("#userid_edit").val();
  updateUser(user_id);
  $('#mdl_edit_user').modal('hide');
});

//----------------------------------
//Button to reset pwd user
//----------------------------------
$('#btn_reset_pwd').click(function () {
  var user_id = $("#userid_reset_pwd").val();
  var mail = $("#mail_reset_pwd").val();

  updatePass(user_id);
  sendNewPass(mail);

  $('#mdl_reset_pwd').modal('hide');
});

