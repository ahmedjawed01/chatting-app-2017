<?php 

include("connection.php");

if(isset($_POST['form_login'])) 
{
    
    try {

        if(empty($_POST['name'])) {
            throw new Exception('Name can not be empty');
        }
        
        if(empty($_POST['password'])) {
            throw new Exception('Password can not be empty');
        }
    
        
        $password = $_POST['password']; 
        $password = md5($password);
            
        $num = 0;

        $statement = $db->prepare("SELECT * FROM users WHERE email=? AND password=?");
        $statement->execute(array($_POST['name'], $password));       
        
        $num = $statement->rowCount();
        
        if($num > 0) {

            session_start();

            $the_user = $statement->fetch();

            $_SESSION['name'] = "angryboys-chatbox";
            $_SESSION['userid'] = $the_user['id'];
            $_SESSION['username'] = $the_user['username'];

            header("location: chatbox/index.php");
        }
        
        throw new Exception('Invalid Name or password');
    }

    catch(Exception $e) {
        $error_message = $e->getMessage();
    }
} ?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Chatbox | Log in</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="plugins/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="plugins/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

  </head>
  <body class="hold-transition login-page" style="background-image:url(dist/img/medicine.png)">
    
    <div class="login-box">
      
      <!-- <div class="login-logo">
        <a href="#"><b>ANGRYBOYS </b> CHATBOX</a>
      </div>
 -->

    <div class="row text-center">
      <div class="col-md-12">
      <img src="img/logo.png" alt="CHAT APP 2017">
      </div>
    </div>

      <div style="margin-top:50px" class="row">
      <div class="col-md-4 col-sm-6 col-md-offset-4 col-sm-offset-3">
      <div class="panel panel-default">
      <div class="panel-body">
        <div class="login-box-body">

          <?php
          if(isset($error_message))
          { ?>
            <div class="alert alert-danger">
                <p class=""><?php echo $error_message ; ?></p>
            </div>
            <br />
        <?php } ?>

          <p class="login-box-msg">Login In To Dashboard</p>
          <form action="" method="post">
            <div class="form-group has-feedback">
              <input type="text" class="form-control" placeholder="Name" name="name">
              <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
              <input type="password" class="form-control" placeholder="Password" name="password">
              <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row">
              <div class="col-xs-8">
                
              </div><!-- /.col -->
              <div class="col-xs-4">
                <button type="submit" class="btn btn-primary btn-block btn-flat" name="form_login">Sign In</button>
              </div><!-- /.col -->
            </div>
          </form>
        </div><!-- /.login-box-body -->
      </div>
      </div>
      </div>
      </div>


    </div><!-- /.login-box -->

    <!-- jQuery 2.1.4 -->
    <script src="js/jquery.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="plugins/bootstrap/js/bootstrap.min.js"></script>

</body>
</html>
