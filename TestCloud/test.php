<?php
$frase = "Frase. teste pasot. 21 maio 2020";
   $val = str_replace(substr(strrchr($frase, "."),1),"",$frase);
   echo $val;