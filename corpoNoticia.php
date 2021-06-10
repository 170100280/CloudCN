<?php
session_start();


use util\HttpHelper;
use util\dbCloud;

require "./vendor/autoload.php";

if (isset($_POST["urlNoticia"])){
    $_SESSION["sessionUrlNoti"] = $_POST["urlNoticia"];
    $_SESSION["sessionIMG"] = $_POST["img"];
}

$URL = $_SESSION["sessionUrlNoti"];
$img = $_SESSION["sessionIMG"];


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
                    <div class="col-8 text-center"></div>
                    <div class="col-2">
                        <a class="btn btn-secondary" href="index.php" role="button">Voltar a Página Principal</a>
                        <br>
                        <?php
                        if(isset($_SESSION["usernameAutor"]))
                        {
                            ?>
                        <form class="d-flex" method="post" action="logout.php">
                            <button type="submit" class="btn btn-danger">Logout</button>
                        </form>
                        <?php
                        }else{
                            ?>
                            <a class="btn btn-secondary" href="login.php" role="button">Login</a>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-2"></div>
                    <div class="col-8 text-center">
                        <img src="img/<?php echo $img; ?>" class="img-fluid" alt="...">
                        <h1 class="text-center"><?php echo $titleNoticia;?></h1>
                        <p class="lead">
                            <?php echo $noticiaText; ?>
                        </p>
                        <?php
                        $db->dbInstall(false);

                        $a = $db->dbSelectAllComments($URL);
                        if($a===false)
                        {
                            ?>
                            <div class="list-group">
                                <a href="" class="list-group-item list-group-item-action">

                                    <p class="mb-1">Não existem comentários associados.</p>
                                </a>
                            </div>
                                <?php
                        }
                        else
                        {
                            foreach($a as $x)
                            {
                        ?>
                        <div class="list-group">
                            <a href="" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?php echo $x['nome'];?></h5>
                                    <small class="text-muted"><?= $x['dataComent'];?></small>
                                </div>
                                <p class="mb-1"><?php echo $x['comentario'];?></p>
                            </a>
                        </div>
                        <?php
                            }
                        }

                        if(isset($_SESSION["usernameAutor"]))
                        {
                        ?>
                        <br>
                        <form method="post" action="comment.php">
                            <div class="mb-3">
                                <label for="exampleInputEmail1" class="form-label">Adicione o seu comentário</label>
                                <input hidden name="urlNoticia"  value="<?php echo $URL; ?>" type="text"></input>
                                <input type="text" class="form-control" name="comentario">
                                <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                        <?php
                        }
                        ?>
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
