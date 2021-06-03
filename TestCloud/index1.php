<?php
require "./vendor/autoload.php";

use util\HttpHelper;
use util\dbCloud;

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

/*echo $db->dbInstall(
    false //unnecesssary, but makes it clear that one can switch off the install procedure
);

dbCloud::fb("Will now test an insert" . PHP_EOL);

$strNow = date("Y-m-d H:i:s");

//$strTestUrl = "https://something.net ($strNow)";

$result = $strInert = $db->dbInsert(
"teste comentario",
"Calin",
"novo URL"); */



/*----------------------------------------CURL------------------ */
$o = new HttpHelper();

$a = $o->expandGooglUrl("https://www.slbenfica.pt/agora/newsFeed?m=Futebol");
$b= $o->extractAsFromHtml($a);
$c = $o->getTitleAndUrl($b);

print_r($c);//Só por no HTML

