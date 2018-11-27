<?php
/**
 * Created by PhpStorm.
 * User: xheghun
 * Date: 21/11/2018
 * Time: 08:56 PM
 */

function clean($string){
    htmlentities($string);
}

function validation_errors($alert) {
$alert = <<<MHT
    <div class="alert alert-danger alert-dismissable" role="alert">
                        <strong>Error!</strong> $alert 
                         <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true"> &times;
                            </span>
                        </button>       
     </div>
MHT;
    return $alert;
}

function email_exits($email) {
    $sql = "SELECT id FROM users WHERE email = '$email'";
    $result = query($sql);
    if(row_count($result) == 1) {
        return true;
    }else{
        return false;
    }
}

function username_exits($username) {
    $sql = "SELECT id FROM users WHERE username = '$username'";
    $result = query($sql);
    if(row_count($result) == 1) {
        return true;
    }else{
        return false;
    }
}

function redirect($url) {
    return header("Location: {$url}");
}

function set_message($message) {
    if (!empty($message)) {
        $_SESSION["message"] = $message;
    }else {
        $message = "";
    }
}

function display_message() {
    if (isset($_SESSION["message"])) {
        echo $_SESSION["message"];
        unset($_SESSION["message"]);
    }
}

function token_generator() {
    $token = $_SESSION["token"] = md5(uniqid("void_".mt_rand(), true));
    return $token;
}

//validate

function validate_user_registration() {
    $errors = [];
    $min = 3;
    $max = 20;
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $firstname = filter_input(INPUT_POST, "firstname");
        $lastname = filter_input(INPUT_POST,"lastname");
        $username = filter_input(INPUT_POST,"username");
        $email = filter_input(INPUT_POST,"email");
        $password = filter_input(INPUT_POST, "password");
        $confirm_pass = filter_input(INPUT_POST,"confirm_password");

       if (strlen($firstname) < $min) {
            $errors[] = "first name is too short, min of {$min} characters required {$firstname}";
        }

        if (strlen($firstname) > $max) {
            $errors[] = "first name is too long, max of {$max} characters allowed";
        }
        if (strlen($lastname) < $min) {
            $errors[] = "last name is too short, min of {$min} characters required";
        }
        if (strlen($lastname) > $max) {
            $errors[] = "last name is too long, max of {$max} characters allowed";
        }

        if (strlen($username) < $min) {
            $errors[] = "username is too short, min of {$min} characters required";
        }
        if (strlen($username) > $max) {
            $errors[] = "username is too long, max of  {$max} characters allowed";
        }


        if (username_exits($username)) {
            $errors[] = "username already taken";
        }

        if (email_exits($email)) {
            $errors[] = "email exist";
        }

        if ($password !== $confirm_pass) {
            $errors[] = "passwords doesn't match";
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
               echo validation_errors($error);
            }
        }else {
           if (register($firstname,$lastname,$username,$email,$password)) {
                set_message("
                    <p class='bg-success text-center'>
                    A Verification Link has been sent to your email<br>
                    </p>
                ");
                redirect("index.php");
           }else {
               set_message("
                    <p class='bg-danger text-center'>
                        User Registration Failed
                    </p>
               ");
               redirect("index.php");
           }
        }
    }
}

function send_mail($email,$subject,$msg,$headers) {
    return mail($email,$subject,$msg,$headers);
}

//registration function
function register($firstname,$lastname,$username,$email,$password) {
    //escape data
    $firstname = escape($firstname);
    $lastname = escape($lastname);
    $email = escape($email);
    $username = escape($username);
    $password = escape($password);



    if (email_exits($email)) {
        return false;
    }
    elseif (username_exits($username)) {
        return false;
    }else {
        $password = md5($password);
        $time = time();
        $validation_code = md5($username.microtime()) ;
        $f_time = strftime("%B-%d-%Y %H:%M:%S",$time);
        $sql = "INSERT INTO users(firstname,lastname,username,email,password,validation_code,active,time_added)
                VALUES ('$firstname','$lastname','$username','$email','$password','$validation_code','0','$f_time')";
        $result = query($sql);
        confirm($result);

        $subject = "ACCOUNT ACTIVATION(void.io)";
        $msg = "Please click to activate account
        http://localhost/Login_System(Standard)/activate.php?email=$email&code=$validation_code";
        $header = "From: admin@void.io";
        //send mail
        send_mail($email,$subject,$msg,$header);
        return true;
    }
}

//Activate user
function activate() {
  if ($_SERVER["REQUEST_METHOD"] == "GET") {
      if (isset($_GET["email"])) {
           $email = filter_input(INPUT_GET, "email");
          $code = filter_input(INPUT_GET,"code");


          $sql = "SELECT id FROM users WHERE email = '".escape($email)."' AND validation_code = '".escape($code)."'";
          $result = query($sql);
          confirm($result);
            if (row_count($result) == 1) {
                $sql = "UPDATE users SET active = 1, validation_code = 0 WHERE email = '$email' AND validation_code = '$code'";
                $result = query($sql);
                confirm($result);
                set_message("
                    <p class='bg-success' style='padding: 12px;'>
                    Your Account Has Been Activated
                    </p>");
                redirect("login.php");
            }
      }else {
          set_message("
          <p class='bg-danger' style='padding: 12px;'>
            Sorry, There was a problem activating your account
          </p>");
          redirect("register.php");
      }
  }
}

//validate login
function validate_user_login() {
    $errors = [];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = filter_input(INPUT_POST,"email");
        $password = filter_input(INPUT_POST,"password");


        if (empty($email)) {
            $errors[] = "Email field required";
        }
        if (empty($password)) {
            $errors[] = "Password field required";
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo validation_errors($error);
            }
        }else {
            if (login_user($email,$password)) {
                redirect("admin.php");
            }else {
                echo validation_errors("Account not registered");
            }
        }
    }
}

//login user
function login_user($email,$password) {
    $sql = "SELECT password FROM users WHERE email = '$email' AND active = 1";
    $result = query($sql);
    if (row_count($result) == 1) {
        $row = fetch_array($result);
        $user_password = $row['password'];

        if (md5($password) == $user_password) {
            return true;
        }else {
            return false;
        }
    }else {
        return false;
    }
}
//echo token_generator();