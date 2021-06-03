<?php

$frase = "Futebol Desmentido É falso o que o jornal Record escreve hoje sobre Darwin Nuñez.";
$val = str_replace(substr(strrchr($frase, "."),1),"",$frase);
$val1 = strrchr($frase, ".");
$val2 = substr(strrchr($frase, "."),1);

$val3 = str_replace(substr(strrchr($frase, "["),1),"",$frase);
echo $val3;