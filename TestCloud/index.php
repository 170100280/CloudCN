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

//print_r($a);

$oDomImagens = new DOMDocument();
$img = array("img1.jpg", "img2.jpg", "img3.jpg","img4.jpg","img5.jpg","img6.jpg","img7.jpg","img8.jpg","img9.jpg","img10.jpg"); 


if ($oDomImagens)
{
    @$oDomImagens->loadHTML($a);
    $xpath = new DomXPath($oDomImagens);
    //$imgNode = $xpath->query("//div[@class='news-img-wrapper']")->item(1)->childNodes->item(1)->childNodes->item(1)->getAttribute("src"); bloqueado?
    
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>NewsBot S.L.Benfica</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <!-- Bootstrap icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="css/styles.css" rel="stylesheet" />
    </head>
    <body>
        <!-- Header-->
        <header class="bg-dark py-5">
            <div class="container px-4 px-lg-5 my-5">
                <div class="text-center text-danger">
                    <h1 class="display-4 fw-bolder">News S.L.Benfica</h1>
                    <p class="lead fw-normal text-white-50 mb-0">Not??cias do melhor do mundo</p>
                </div>
            </div>
        </header>
        <!-- Section-->
        <section class="py-5">
            <div class="container px-4 px-lg-5 mt-5">
                <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                <?php 
                      for ($x = 0; $x <= 17; $x++) {//17 numero de noticias j?? carregadas no website.
       
                        $titleNode = $xpath->query("//p[@class='news-title']")->item($x)->nodeValue;
                        $urlNoticia = $xpath->query("//div[@class='news col-xs-12']")->item($x)->childNodes->item(1)->getAttribute("href");
                        
                ?>
                    <div class="col mb-5">
                        <div class="card h-100">
                            <!-- Product image-->
                            <img class="card-img-top" src="img/<?php 
                            $finImg = mt_rand(0,count($img)-1);
                            echo $img[$finImg];
                            ?>" alt="..." />
                            <!-- Product details-->
                            
                            <div class="card-body p-4">
                                <div class="text-center">
                                    <!-- Product name-->
                                    <h3 class="fw-bolder"><?php  
                                    echo($titleNode); 
                                    ?></h3>
                                    <!-- Product price-->
                                    
                                </div>
                            </div>
                           
                            <!-- Product actions-->
                            <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                <div class="text-center">

                                    <form class="d-flex" method="post" action="corpoNoticia.php">
                                        <input hidden name="urlNoticia"  value="<?php echo $urlNoticia; ?>" type="text"></input>
                                        <input  type="submit"  class="btn btn-outline-dark mt-auto" value="Ver mais"></input>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                   <?php  }} ?>
                </div>
            </div>
        </section>
        <!-- Footer-->
        <footer class="py-5 bg-dark">
            <div class="container"><p class="m-0 text-center text-white">Copyright &copy; NewsBot S.L.Benfica 2021</p></div>
        </footer>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="js/scripts.js"></script>
    </body>
</html>
