/* 
 * Copyright (c) 2014 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

$(function () {
  $("#register #regbutton").click(function () {
    $("#register .error").hide();
    $("#register :password").val("");
    $("#register :text").val("");
    $("#register form").slideDown(300);
    $("#newuser").focus();
    return false;
  }); // end regbutton click

  $("#register").submit(function() {  
    register();
    return false;
  }); // end register submit
  $("#register #button3").click(function(){  //cancel register
    $('#register form').slideUp(300);
    return false;
  }); // end button3 click
});

/*
 * Function register is called by the on-click
 * method of the register submit button. It
 * checks the input for missing fields and
 * does an ajax call to newuser.php.
 */
function register() {
  $(".error").hide();
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
  var regString = $(".reg").serialize();
  var hash = hex_sha256(passwrd1);
  regString += "&passwrd=" + hash;
  $.post("php/newUser.php", regString, regResult);
  return false;
}

/*
 * Function regResult is the callback function
 * for the ajax newUser call.
 */
function regResult(response) {
  if (response.indexOf("<!doctype html>") !== -1) {
    // User has timed out.
    window.location = "access-denied.html";
  }
  if (response === "success") {
    $("#login #password").val("");
    $("#login :text").val("");
    $("#register form").slideUp(300);
    $("#login form").slideDown(300);
    var loginNote = "You have successfully registered the player ID. ";
    doLogNote(loginNote);
  } else if (response === "duplicate") {
    $("#newuser_error").text("Username is already in use.").show();
    $("#newuser").focus();
  } else if (response === "bademail") {
    $("#email_error").text("Invalid email format, please correct.").show();
    $("#email").focus();
  } else if (response.substring(0, 5) === "email") {
    var logmessage = "Player " + response.substring(5);
    logmessage += " is already using this email address.";
    $("#email_error").text(logmessage).show();
    $("#email").focus();
  } else if (response === "fail") {
    var errmsg1 = "Program error in newUser.php.\n";
    errmsg1 += "Please contact the BOARD18 webmaster.";
    alert(errmsg1);
  } else {
    // Something is definitly wrong in the code.
    var errmsg2 = "Invalid return code from newUser.php.\n";
    errmsg2 += response + "\nPlease contact the BOARD18 webmaster.";
    alert(errmsg2);
  }
}
