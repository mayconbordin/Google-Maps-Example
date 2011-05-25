<?php

//Funчуo p/ banco MySQL
require("runSQL.php");

//Recebe os dados dos marcadores
if (isset($_POST['data']))
	$markers = $_POST['data'];
	
//Contador
$count = 0;

//Insere cada um dos marcadores criados
foreach ($markers as $mark) {
	$sql = "INSERT INTO markers (name, address, lat, lng, type) VALUES ('".addslashes($mark['name'])."', '"
	.addslashes($mark['address'])."', ".addslashes($mark['lat']).", ".addslashes($mark['lng']).", '".addslashes($mark['type'])."')";
	
	//Executa o sql
	runSQL($sql);
	
	$count++;
}

echo $count;