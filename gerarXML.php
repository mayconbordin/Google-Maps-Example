<?php

//Fun��o p/ banco MySQL
require("runSQL.php");

//Obt�m todos os registros da tabela markers
$sql = "SELECT name, address, lat, lng, type FROM markers";
$result = runSQL($sql);

//Cria novo documento xml
$doc = new DOMDocument();
$doc->formatOutput = true;

//Cria o n� pai
$markers = $doc->createElement("markers");
$doc->appendChild($markers);

//Seta o tipo de cabe�alho para texto/XML
header("Content-type: text/xml");

//Obt�m as linhas do banco
while ($row = @mysql_fetch_assoc($result))
	{
		//Cria um n� filho
		$mark = $doc->createElement("marker");
		
		//Insere o n� filho ao n� pai
		$newMark = $markers->appendChild( $mark );
		
		//Insere no n� os seus atributos
		foreach ($row as $index => $attr)
			{				
				$newMark->setAttribute($index, $attr);
			}
		
		
	}

//Imprime o documento XML
 echo $doc->saveXML();