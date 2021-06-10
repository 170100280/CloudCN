<?php
session_start();

use util\dbCloud;
require "./vendor/autoload.php";

define("CLOUD", false);

if (CLOUD) {
    $db = new dbCloud(
        null,
        "root",
        //"PASSwzerord",
        "1234!",
        //"schema210323",
        dbCloud::DEFAULT_SCHEMA_NAME, //will be created if it does not exist
        3306,
        //"/cloudsql/<project id>:<region>:<sql instance name>
        "/cloudsql/simplestcsql-210323:europe-west1:am-210406"
    );
} else {
    $db = new dbCloud(
        "localhost",
        "root",
        "1234",
        //"schema210323",
        dbCloud::DEFAULT_SCHEMA_NAME, //will be created if it does not exist
        3306,
        null
    );
}


//criação de variáveis e obtenção do valor associado no POST

$usernameAutor = $_POST["username"];
$pass = $_POST["password"];


echo $db->dbInstall(
    false
);


$a = $db->dbloginUser($usernameAutor,$pass);
var_dump("a",$a);
if ($a){//!=false
    $_SESSION["usernameAutor"] = $usernameAutor;
    header("location:index.php");
}
else
{
    $_SESSION["sucesso"] = false;
    $_SESSION["mensagem"] = "Login Inválido!";
    header("location:login.php");
    die();
}


?>