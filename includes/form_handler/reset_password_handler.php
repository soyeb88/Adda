<?php

//create random String
function generateRandomString($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
$random = generateRandomString(6);

//Forget Password
if (isset($_POST['forget_password'])) {
  $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); //sanitize email
  $_SESSION["email"] = $email;

  $email_Query_stmt = "SELECT COUNT(*) as total FROM users WHERE email = :email";
  $email_Query = $con->prepare($email_Query_stmt);
  $email_Query->bindParam(":email", $email);
  $email_Query->execute();

  $email_Query_match = $email_Query->fetch(PDO::FETCH_ASSOC);

  if($email_Query_match["total"] == 1){

     // send email
    $url = "http://localhost/~soyebahmed/FreeLand/reset_password_form.php?token=$random&email=$email";
         
    $to      = $email;
    $subject = "Reset Password";
    $message = "To reset your password, please visit this link: $url";
    $headers = 'From: admin@FreeLand.com' . "\r\n" . 'Reply-To: no-reply@FreeLand.com' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
    
    mail($to, $subject, $message, $headers);

    //later task do it encrypt

    $update_Token_stmt = "UPDATE users SET token = :token WHERE email = :email";
    $update_Token = $con->prepare($update_Token_stmt);
    $update_Token->bindParam(":email", $email);
    $update_Token->bindParam(":token", $random);
    $update_Token->execute();
    array_push($error_array, "Please check your email");
  }
  else {
    array_push($error_array, "Email doesn't exists in our database. Please register for a new account.");
  }

}


//reset Password
if (isset($_POST['password_reset_button'])) {

  $password = strip_tags($_POST['reset_password']);;
  $confirm_password = strip_tags($_POST['reset_password2']);;

  if($password != $confirm_password){
    array_push($error_array, "Your password does not match <br>");
  }
  else{
    $email = $_SESSION["email"];
    $password = md5($password);
    $change_password_stmt = "UPDATE users SET password = :password, token=''  WHERE email=:email";
    $change_password = $con->prepare($change_password_stmt);
    $change_password->bindParam(":email", $email);
    $change_password->bindParam(":password", $password);
    $change_password->execute();

    header("Location: register.php");
    exit();
  }
  
} 

?>