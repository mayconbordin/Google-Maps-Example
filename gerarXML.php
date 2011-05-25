<?php

//Função p/ banco MySQL
require("runSQL.php");

//Obtém todos os registros da tabela markers
$sql = "SELECT name, address, lat, lng, type FROM markers";
$result = runSQL($sql);

//Cria novo documento xml
$doc = new DOMDocument();
$doc->formatOutput = true;

//Cria o nó pai
$markers = $doc->createElement("markers");
$doc->appendChild($markers);

//Seta o tipo de cabeçalho para texto/XML
header("Content-type: text/xml");

//Obtém as linhas do banco
while ($row = @mysql_fetch_assoc($result))
	{
		//Cria um nó filho
		$mark = $doc->createElement("marker");
		
		//Insere o nó filho ao nó pai
		$newMark = $markers->appendChild( $mark );
		
		//Insere no nó os seus atributos
		foreach ($row as $index => $attr)
			{				
				$newMark->setAttribute($index, $attr);
			}
		
		
	}

//Imprime o documento XML
 echo $doc->saveXML();