<?php
session_start();
header("location:corpoNoticia.php");

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
        "password",
        //"schema210323",
        dbCloud::DEFAULT_SCHEMA_NAME, //will be created if it does not exist
        3306,
        null
    );
}


//criação de variáveis e obtenção do valor associado no POST

$comment = $_POST["comentario"];
$username = $_SESSION["usernameAutor"]; //depois de fazer o login criar variaveis de sessao com [autor][id]
$urlNoti = $_POST["urlNoticia"];




$db->dbInstall(
    false //unnecesssary, but makes it clear that one can switch off the install procedure
);
$a = $db->dbSelectidByUsername($username);

foreach($a as $x) {
    $idAutor = $x['idAutor'];
}
$a = $db->dbInsertComment($comment,$idAutor,$urlNoti);


?>