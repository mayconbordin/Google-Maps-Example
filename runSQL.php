<?php

//Informa��es do banco de dados
require("config.inc.php");

function runSQL($sql) {

	//Cria a conex�o com o MySQL
	$connection = mysql_connect (HOST, USERNAME, PASSWORD);

	//Se houver erros, finaliza script e imprime o erro
	if (!$connection) {
	  die('Erro na conex�o : ' . mysql_error());
	}

	//Seleciona o banco de dados
	$db_selected = mysql_select_db(DATABASE, $connection);

	//Se houver erros, finaliza script e imprime o erro
	if (!$db_selected) {
	  die ('Erro ao selecionar banco de dados : ' . mysql_error());
	}

	//Executa o sql
	$result = mysql_query($sql);
	
	//Se houver erros, finaliza script e imprime o erro
	if (!$result) {
	  die('Requisi��o inv�lida: ' . mysql_error());
	}
	
	return $result;
}