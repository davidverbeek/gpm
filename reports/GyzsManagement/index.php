<?php 
   session_start();
   require_once("../../app/Mage.php");
   umask(0);
   Mage::app();
   
   $message="";

   //echo md5("admin@123");
   //exit; testing the changes
   
   if(count($_POST)>0) {
     $resource = Mage::getSingleton('core/resource');
     $readConnection = $resource->getConnection('core_read');
   
     $user_name = $_POST["user_name"];
     $password = md5($_POST["password"]);


   
     $sql = "SELECT * FROM price_management_users WHERE id = 1";
     $price_admin_user = $readConnection->fetchAll($sql);
       
       if($price_admin_user[0]['user_name'] == $user_name and $price_admin_user[0]['password'] == $password) {
         $_SESSION["price_id"] = $price_admin_user[0]['id'];
         $_SESSION["price_name"] = $price_admin_user[0]['name'];
       } else {
         $message = "Invalid Username or Password!";
       }
   }
   
   if(isset($_SESSION["price_id"])) {
     header("Location:setprice.php");
   }
   
   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link href="css/bootstrap.css" rel="stylesheet" type="text/css">
      <link href="css/login.css" rel="stylesheet">
      <title>admin login</title>
      <script type="text/javascript" src="js/jquery-3.3.1.js"></script>
      <script type="text/javascript" src="js/jquery-ui.js"></script>
   </head>
   <body>
      <section class="container">
         <div class="content-wrapper d-flex">
            <div class="beauty">
               <img src="css/gif/GYZS_black_black.gif">
            </div>
            <div class="form">
               <div class="form-header">
                  <h2 class="p-0 m-0">Administrator Login</h2>
               </div>
               <form class="user" name="frmUser" method="post" action="">
                  <div class="form-group">
                     <label for="name">Username</label>
                     <input type="text" class="form-control form-control-user" placeholder="username"
                        name="user_name" id="name">
                  </div>
                  <div class="form-group">
                     <label for="password">Password</label>
                     <input type="password" class="form-control form-control-user" placeholder="password"
                        name="password" id="password">
                  </div>
                  <div class="form-group">
                     <input type="submit" value="Login" class="btn">
                  </div>
               </form>
            </div>
         </div>
      </section>
      <script>
         $(document).ready(function() {
           var msg = '<?php echo $message; ?>';
           if(msg != "") {
             $(".form-control" ).effect( "shake" );
             $(".form-control").css('border-color', 'red');
           }
         
         });
         
      </script>
   </body>
</html>