<?php
session_start();
header("location:login.php");

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

$autor = $_POST["autor"];
$email = $_POST["email"];
$pass = $_POST["password"];



echo $db->dbInstall(
    false //unnecesssary, but makes it clear that one can switch off the install procedure
);


$a = $db->insertNewUser($email, $autor, $pass);
if (!$a){
    header("location:login.php");
}
else
{
    $_SESSION["sucesso"] = false;
    $_SESSION["mensagem"] = "Registo Inválido!";
    header("location:registar.php");
    die();
}

?>