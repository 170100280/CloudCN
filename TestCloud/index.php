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
//print_r($a);

$oDomImagens = new DOMDocument();

if ($oDomImagens)
{
    //@ - "silencer"
    @$oDomImagens->loadHTML($a);
    $srIMG="";
    $xpath = new DomXPath($oDomImagens);
    $nodeList = $xpath->query("//div[@class='article-thumb-list col-xs-12 news-three']");
    $myNode = $xpath->query("//p[@class='news-title']");
    print_r($myNode);
    echo($myNode->item(0)->nodeValue);
    if($nodeList->count() == 0){
        echo "<br> Nada foi escolhido";
    }else
    {    $urlNoticia = $nodeList->item(0)->childNodes->item(1)->childNodes->item(1)->getAttribute("href");
        //$srcImagem= $nodeList->item(0)->childNodes->item(1)->childNodes->item(1)->childNodes->item(1)->childNodes->item(1)->childNodes->item(1)->getAttribute("src");
        $urlTitulo = $nodeList->item(0)->childNodes->item(1)->childNodes->item(1)->childNodes->item(1)->nextElementSibling->childNodes->item(1)->nextElementSibling->textContent;
        //var_dump($urlTitulo);
        //echo $lengthNode=$nodeList->item(0)->childNodes->length;
       
        /*for ($i=0; $i+1 <= $lengthNode ; $i++)
        {
            if($node->item($i)->childNodes->item(1)!=NULL)
            {
                $srcIMG=$node->item($i);
                
            }//if
        }//for*/
       
    }
}
//print_r($c);//Só por no HTML
//echo $c[" /pt-pt/agora/noticias/2021/06/01/direto-covid-19-novo-coronavirus-portugal-numeros-do-dia-dgs"];
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
                    <p class="lead fw-normal text-white-50 mb-0">Notícias do melhor do mundo</p>
                </div>
            </div>
        </header>
        <!-- Section-->
        <section class="py-5">
            <div class="container px-4 px-lg-5 mt-5">
                <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                <?php 
                /*array_shift($c);
                foreach ($c as $value => $val)*/{
                                 //echo $value."<br>";?>
                    <div class="col mb-5">
                        <div class="card h-100">
                            <!-- Product image-->
                            <img class="card-img-top" src="https://dummyimage.com/450x300/dee2e6/6c757d.jpg" alt="..." />
                            <!-- Product details-->
                            
                            <div class="card-body p-4">
                                <div class="text-center">
                                    <!-- Product name-->
                                    <h5 class="fw-bolder"><?php  //echo $value; ?></h5>
                                    <!-- Product price-->
                                    Contéudo
                                </div>
                            </div>
                           
                            <!-- Product actions-->
                            <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                <div class="text-center"><a class="btn btn-outline-dark mt-auto" href="#">Ver mais</a></div>
                            </div>
                        </div>
                    </div>
                   <?php } ?>
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
