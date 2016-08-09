/*
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

/* 
 * Function forceResult is the callback function 
 * for the ajax forcePasswd call. 
 */
function forceResult(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  else if (response === 'success') {
    var changeNote = 'You password has been successfully changed.';
    doLogNote(changeNote);
    $('#passwd form').slideUp(300);
    setTimeout(function(){window.location = "board18Main.php";}, 5000);
  }
  else if (response === 'player') {
    $("#pname2_error").text('Invalid player ID.').show();
    $("#pname2").focus();
  }
  else if (response === 'fail') {
    var errmsg = 'Data Base update failed.\n';
    errmsg += 'Please contact the BOARD18 webmaster.';
    alert(errmsg);
  }
  else { // Something is definitly wrong in the code.
    var nerrmsg ='Invalid return code from forcePasswd.php.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
}

/* 
 * Function forceChange is called by the on-click
 * method of the passwd submit button. It checks
 * the input for missing or invalid fields and
 * does an ajax call to updateUser.php. 
 */
function forceChange(currpw) {
  $('.error').hide();  
  var pname2 = $("input#pname2").val();
  if (pname2 === "") {
    $("#pname2_error").show();
    $("#pname2").focus();
    return false;
  }

  var passwrd3 = $("input#passwrd3").val();
  if (passwrd3 === "") {
    $("#passwrd3_error").show();
    $("#passwrd3").focus();
    return false;
  }

  var passwrd4 = $("input#passwrd4").val();
  if (passwrd4 !== passwrd3) {
    $("#passwrd4_error").show();
    $("#passwrd4").focus();
    return false;
  }

  var hash = hex_sha256(passwrd3);
  var regString = 'player=' + pname2 + '&passwd=' + hash;
  $.post("php/forcePasswd.php", regString, forceResult);
  return false;
}

/* 
 * Function adminResult is the callback function 
 * for the ajax newUser call. 
 */
function adminResult(response) {
  if (response.indexOf("<!doctype html>") !== -1) { // User has timed out.
    window.location = "access-denied.html";
  }
  else if (response === 'bademail') {
    $("#email_error").text('Invalid email format, please correct.').show();
    $("#email").focus();
  }
  else if (response.substring(0, 5) === 'email') {
    var logmessage = 'User ' + response.substring(5);
    logmessage += ' is already using this email address.';
    $("#email_error").text(logmessage).show();
    $("#email").focus();
  }
  else if (response === 'success') {
    $('#login #password').val('');
    $('#login :text').val('');
    $('#register form').slideUp(300);
    $('#login form').slideDown(300);
    var loginNote = 'Your registration has been successfully updated. ';
    doLogNote(loginNote);
  }
  else if (response === 'fail') {
    var errmsg = 'Data Base update failed.\n';
    errmsg += 'Please contact the BOARD18 webmaster.';
    alert(errmsg);
  }
  else { // Something is definitly wrong in the code.
    var nerrmsg ='Invalid return code from updateUser.php.\n';
    nerrmsg += response + '\nPlease contact the BOARD18 webmaster.';
    alert(nerrmsg);
  }
}

/* 
 * Function administrate is called by the on-click
 * method of the administrate submit button. 
 * It checks the input for missing fields and
 * does an ajax call to updateUser.php. 
 */
function administrate(currpw) {
  $('.error').hide();
  var oldpw1 = $("input#oldpw1").val();
  if (oldpw1 === "") {
    $("#oldpw1_error").show();
    $("#oldpw1").focus();
    return false;
  }
  var hash1 = hex_sha256(oldpw1);
  if (hash1 !== currpw) {
    var pwmessage = "Invalid Current Password.";
    $("#oldpw1_error").text(pwmessage).show();
    $("#oldpw1").focus();
    return false;
  }
  var name = $("input#pname").val();
  if (name === "") {
    $("#pname_error").show();
    $("#pname").focus();
    return false;
  }
  var passwrd1 = $("input#passwrd1").val();
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
  var aString = $('.reg').serialize();
  if (passwrd1 !== "") {
    var hash = hex_sha256(passwrd1);
    aString += '&passwd=' + hash;
  }
  $.post("php/updateUser.php", aString, adminResult);
  return false;
}
