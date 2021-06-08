<?php

use util\HttpHelper;

require "./vendor/autoload.php";
$URL = $_POST['urlNoticia'];

const MAIN_URL = "https://www.slbenfica.pt/";

$o = new HttpHelper();
$a = $o->expandGooglUrl(MAIN_URL.$URL);

$oDomImagens = new DOMDocument();

if ($oDomImagens)
{
    @$oDomImagens->loadHTML($a);
    $xpath = new DomXPath($oDomImagens);

    $titleNoticia = $xpath->query("//h1[@class='title']")->item(0)->nodeValue;
 

    $noticiaText = $xpath->query("//div[@class='text-block col-xs-12']")->item(0)->nodeValue;
   // print_r($noticiaText);
  
}


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
            <div class="container">
                <div class="row">
                    <div class="col-2"></div>
                    <div class="col-8 text-center">
                        <img src="https://dummyimage.com/450x300/dee2e6/6c757d.jpg" class="img-fluid" alt="...">
                        <h1 class="text-center"><?php echo $titleNoticia;?></h1>
                        <p class="lead">
                            <?php echo $noticiaText; ?>
                        </p>
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action active" aria-current="true">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">List group item heading</h5>
                                    <small>3 days ago</small>
                                </div>
                                <p class="mb-1">Some placeholder content in a paragraph.</p>
                                <small>And some small print.</small>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">List group item heading</h5>
                                    <small class="text-muted">3 days ago</small>
                                </div>
                                <p class="mb-1">Some placeholder content in a paragraph.</p>
                                <small class="text-muted">And some muted small print.</small>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">List group item heading</h5>
                                    <small class="text-muted">3 days ago</small>
                                </div>
                                <p class="mb-1">Some placeholder content in a paragraph.</p>
                                <small class="text-muted">And some muted small print.</small>
                            </a>
                        </div>
                        <br>
                        <form>
                            <div class="mb-3">
                                <label for="exampleInputEmail1" class="form-label">Adicione o seu comentário</label>
                                <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
                                <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                    <div class="col-2"></div>
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
