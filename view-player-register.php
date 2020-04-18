<?php
function viewPlayerRegister($rootClass, $rootStyle = "", $title, $showCancelButton = true) {
  echo '<div id="register" class="'.$rootClass.'" style="'.$rootStyle.'">';
  echo <<< EOT
        <form name="register" class="hideform" action="">
          <fieldset>
EOT;
  echo '<p style="font-size: 130%">'.$title.'</p>';
  echo <<< EOT
            <p>
              <label for="newuser">Enter Player ID:</label>
              <input type="text" name="newuser" id="newuser" class="reg">
              <label class="error" for="newuser" id="newuser_error">
                This field is required.</label>
            </p>
            <p>
              <label for="passwrd1">Enter Password: </label>
              <input type="password" name="passwrd1" id="passwrd1"
                     autocomplete="off">
              <label class="error" for="passwrd1" id="passwrd1_error">
                This field is required.</label>
            </p>
            <p>
              <label for="passwrd2">Reenter Password: </label>
              <input type="password" name="passwrd2" id="passwrd2"
                     autocomplete="off">
              <label class="error" for="passwrd2" id="passwrd2_error">
                Password field mismatch.</label>
            </p>
            <p>
              <label for="email">Enter Email Address: </label>
              <input type="text" name="email" id="email" class="reg">
              <label class="error" for="email" id="email_error">
                This field is required.</label>
            </p>
            <p style="font-size: 130%">Optional Fields: </p>
            <p>
              <label for="fname">Enter First Name: </label>
              <input type="text" name="fname" id="fname" class="reg">
            </p>
            <p>
              <label for="lname">Enter Last Name: </label>
              <input type="text" name="lname" id="lname" class="reg">
            </p>
            <p>
              <input type="submit" name="regbutton" class="pwbutton"  
                     id="button2" value="Register" >
EOT;
    if ($showCancelButton) {
      echo '<input type="button" name="canbutton" class="pwbutton" id="button3" value="Cancel" >';
    }
    echo <<< EOT
            </p>
          </fieldset>
        </form>
      </div> 
EOT;
};
?>
