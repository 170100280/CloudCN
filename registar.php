<?php
session_start();
//header("location:registar.php");


?>

<!doctype html>
<html lang="en">
<head>
    <title>Registo</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="css1/style.css">

</head>
<body>
<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-10">
                <div class="wrap d-md-flex">
                    <div class="text-wrap p-4 p-lg-5 text-center d-flex align-items-center order-md-last">
                        <div class="text w-100">
                            <h2>Welcome to Register</h2>
                            <a href="login.php" class="btn btn-white btn-outline-white">Sign In</a>
                        </div>
                    </div>
                    <div class="login-wrap p-4 p-lg-5">
                        <div class="d-flex">
                            <div class="w-100">
                                <h3 class="mb-4">Sign Up</h3>

                            </div>
                            <div class="w-100">

                            </div>
                        </div>
                        <form action="registarPDO.php" method="post" class="signin-form">
                            <div class="form-group mb-3">
                                <label class="label" for="autor">Autor</label>
                                <input type="text"  name="autor" class="form-control" placeholder="Username" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="label" for="email">Email</label>
                                <input type="text" name="email" class="form-control" placeholder="Email" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="label" for="password">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Password" required>
                            </div>
                            <div class="form-group">

                                <input type="submit" class="form-control btn btn-primary submit px-3" value="Sign Up"></input>
                            </div>
                            <span>
                                <?PHP
                                if(isset($_SESSION["sucesso"]))
                                {
                                    if($_SESSION["sucesso"] == false)
                                    {
                                        echo $_SESSION["mensagem"];
                                        $_SESSION["mensagem"] = "";
                                    }
                                }
                                ?>
                            </span>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="js1/jquery.min.js"></script>
<script src="js1/popper.js"></script>
<script src="js1/bootstrap.min.js"></script>
<script src="js1/main.js"></script>

</body>
</html>

