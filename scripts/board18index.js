/* 
 * Copyright (c) 2014 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

/* 
 * Function loginOK is the callback function for the ajax
 * Validate User call. 
 */
function loginOK(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  var resp = jQuery.parseJSON(response);
  if (resp.stat === 'success') {
    $('#login #password').val('');
    $('#login :text').val('');
    var loginNote = 'You are logged in as ';
    loginNote += resp.firstname + ' ';
    loginNote += resp.lastname + ' at ';
    loginNote += resp.level + ' level';
    doLogNote(loginNote);
    $('#login form').slideUp(300);
    if (resp.changeit === '0') {
      window.location = "board18Main.php";
    } else {
      window.location = "board18Admin.php";
    }
  } 
  else if (resp.stat === 'no') {
    $("#signon_error").show();
    $("#username").focus();
  } 
  else if (resp.stat === 'fail') {
    var errmsg1 = 'Program error in validateUser.php.\n';
    errmsg1 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg1);
  } 
  else {  // Something is definitly wrong in the code.
    var nerrmsg = 'Invalid return code from validateUser.php.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
}

/* 
 * Function login is called by the on-click
 * method of the login submit button. It 
 * checks the input for missing fields and
 * does an ajax call to validateUser.php. 
 */
function login() {
  $('.error').hide();
  var name = $("input#username").val();
  if (name === "") {
    $("#name_error").show();
    $("#username").focus();
    return false;
  }
  var passwd = $("input#password").val();
  if (passwd === "") {
    $("#password_error").show();
    $("#password").focus();
    return false;
  }
  var hash = hex_sha256(passwd);
  var dataString = 'login=' + name + '&password=' + hash;
  $.post("php/validateUser.php", dataString, loginOK);
  return false;
}

/* 
 * Function regResult is the callback function 
 * for the ajax newUser call. 
 */
function regResult(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  if (response === 'success') {
    $('#login #password').val('');
    $('#login :text').val('');
    $('#register form').slideUp(300);
    $('#login form').slideDown(300);
    var loginNote = 'You have successfully registered. ';
    doLogNote(loginNote);
  }
  else if (response === 'duplicate') {
    $("#newuser_error").text('Username is already in use.').show();
    $("#newuser").focus();
  }
  else if (response === 'bademail') {
    $("#email_error").text('Invalid email format, please correct.').show();
    $("#email").focus();
  }
  else if (response.substring(0, 5) === 'email') {
    var logmessage = 'Player ' + response.substring(5);
    logmessage += ' is already using this email address.';
    $("#email_error").text(logmessage).show();
    $("#email").focus();
  }
  else if (response === 'fail') {
    var errmsg1 = 'Program error in newUser.php.\n';
    errmsg1 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg1);
  }
  else {  // Something is definitly wrong in the code.
    var errmsg2 = 'Invalid return code from newUser.php.\n';
    errmsg2 += response + '\nPlease contact the BOARD18 webmaster.';
    alert(errmsg2);
  }
}

/* 
 * Function register is called by the on-click
 * method of the register submit button. It 
 * checks the input for missing fields and
 * does an ajax call to newuser.php. 
 */
function register() {
  $('.error').hide();
  var name = $("input#newuser").val();
  if (name === "") {
    $("#newuser_error").show();
    $("#newuser").focus();
    return false;
  }
  var passwrd1 = $("input#passwrd1").val();
  if (passwrd1 === "") {
    $("#passwrd1_error").show();
    $("#passwrd1").focus();
    return false;
  }
  var passwrd2 = $("input#passwrd2").val();
  if (passwrd2 !== passwrd1) {
    $("#passwrd2_error").show();
    $("#passwrd2").focus();
    return false;
  }
  var email = $("input#email").val();
  if (email === "") {
    $("#email_error").show();
    $("#email").focus();
    return false;
  }
  var regString = $('.reg').serialize();
  var hash = hex_sha256(passwrd1);
  regString += '&passwrd=' + hash;
  $.post("php/newUser.php", regString, regResult);
  return false;
}

/* 
 * Function emailPlayerResult is the callback function 
 * for the ajax emailPlayerID call. 
 */
function emailPlayerResult(response) {
  if (response === 'success') {
    $('#lostid :text').val('');
    $('#lostid form').slideUp(300);
    $('#login form').slideDown(300);
    var lostidNote = 'Email with player ID has been sent.';
    doLogNote(lostidNote);
  }
  else if (response === 'bademail') {
    $("#email1_error").text('Format of email address is invalid.')
            .show();
    $("#email1").focus();
  }
  else if (response === 'fail') {
    var errmsg1 = 'Program error in emailPlayerID.php.\n';
    errmsg1 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg1);
  }
  else {  // Something is definitly wrong in the code.
    var errmsg2 = 'Invalid return code from emailPlayerID.php.\n';
    errmsg2 +=  response + '\nPlease contact the BOARD18 webmaster.';
    alert(errmsg2);
  }
}

/* 
 * Function lostid is called by the on-click
 * method of the lostid submit button. It 
 * checks the input for missing fields and
 * does an ajax call to emailPlayerID.php. 
 */
function lostid() {
  $('.error').hide();
  var email = $("input#email1").val();
  if (email === "") {
    $("#email1_error").show();
    $("#email1").focus();
    return false;
  }
  var dataString = 'email=' + email;
  $.post("php/emailPlayerID.php", dataString, emailPlayerResult);
  return false;
}

/* 
 * Function emailPasswdResult is the callback function 
 * for the ajax emailPassword call. 
 */
function emailPasswdResult(response) {
  if (response === 'success') {
    $('#lostpw :text').val('');
    $('#lostpw form').slideUp(300);
    $('#login form').slideDown(300);
    var lostpwNote = 'Email with temporary password has been sent.';
    doLogNote(lostpwNote);
  }
  else if (response === 'bademail') {
    $("#email2_error").text('Format of email address is invalid.')
            .show();
    $("#email2").focus();
  }
  else if (response === 'noplayer') {
    $("#name1_error").text('Player ID is not correct.')
            .show();
    $("#username1").focus();
  }
  else if (response === 'noemail') {
    $("#email2_error").text('Email address is not correct.')
            .show();
    $("#email2").focus();
  }
  else if (response === 'fail') {
    var errmsg1 = 'Program error in emailPassword.php.\n';
    errmsg1 += 'Please contact the BOARD18 webmaster.';
    alert(errmsg1);
  }
  else {  // Something is definitly wrong in the code.
    var errmsg2 = 'Invalid return code from emailPassword.php.\n';
    errmsg2 += response + '\nPlease contact the BOARD18 webmaster.';
    alert(errmsg2);
  }
}

/* 
 * Function lostpw is called by the on-click
 * method of the lostpw submit button. It 
 * checks the input for missing fields and
 * does an ajax call to emailPassword.php. 
 */
function lostpw() {
  $('.error').hide();
  var name = $("input#username1").val();
  if (email === "") {
    $("#name1_error").show();
    $("#username1").focus();
    return false;
  }
  var email = $("input#email2").val();
  if (email === "") {
    $("#email1_error").show();
    $("#email1").focus();
    return false;
  }
  var dataString = 'name=' + name + '&email=' + email;
  $.post("php/emailPassword.php", dataString, emailPasswdResult);
  return false;
}