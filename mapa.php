<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
		<title>Google Maps AJAX + mySQL/PHP Example</title>
		<style type="text/css"> 
			#map {
				width: 800px;
				height: 600px;
				float: left;
			}
			#infoPanel {
				float: left;
				margin-left: 10px;
			}
			#infoPanel div {
				margin-bottom: 5px;
			}
			#block {
				margin-bottom:10px;
				background: #cccccc;
				padding:4px;
			}
		</style> 
		<script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
		<script type="text/javascript">
		//<![CDATA[
		
		//Array com novos marcadores
		var markersData = new Object();

		//Objeto de �cones
		var customIcons = {
		  restaurant: {
			icon: 'icons/restaurant.png',
		  },
		  bar: {
			icon: 'icons/bar.png',
		  },
		  aeroporto: {
			icon: 'icons/airport.png',
		  },
		  onibus: {
			icon: 'icons/bus.png',
		  },
		  cafeteria: {
			icon: 'icons/coffee.png',
		  },
		  gasolina: {
			icon: 'icons/gazstation.png',
		  },
		  hotel: {
			icon: 'icons/hotel.png',
		  },
		  restaurante: {
			icon: 'icons/restaurant.png',
		  },
		  wifi: {
			icon: 'icons/wifi.png',
		  }
		};
		
		//Vari�veis globais
		var map;
		var infoWindow;
		
		//Inicializa o mapa
		function load() {
		
			//Configura��es do mapa
			map = new google.maps.Map(document.getElementById("map"), {
				center: new google.maps.LatLng(47.6145, -122.3418),
				zoom: 13,
				mapTypeId: 'roadmap'
			});
		
			//Janela de informa��es
			infoWindow = new google.maps.InfoWindow;

			//Carrega o XML com as coordenadas salvas no banco de dados
			downloadUrl("gerarXML.php", function(data) {
				var xml = data.responseXML;
				var markers = xml.documentElement.getElementsByTagName("marker");
				
				//Cria os marcadores
				for (var i = 0; i < markers.length; i++) {
					var name = markers[i].getAttribute("name");
					var address = markers[i].getAttribute("address");
					var type = markers[i].getAttribute("type");
					var point = new google.maps.LatLng(
						parseFloat(markers[i].getAttribute("lat")),
						parseFloat(markers[i].getAttribute("lng"))
					);
					var html = "<b>" + name + "</b> <br/>" + address;
					var icon = customIcons[type] || {};
					var marker = new google.maps.Marker({
						map: map,
						position: point,
						icon: icon.icon,
						title: name
					});
					bindInfoWindow(marker, map, infoWindow, html);
				}
			});
		}

		//Inclui a janela de informa��es ao marcador
		function bindInfoWindow(marker, map, infoWindow, html) {
			google.maps.event.addListener(marker, 'click', function() {
				infoWindow.setContent(html);
				infoWindow.open(map, marker);
				
				//Painel de Informa��es
				updateMarkerStatus('Fixo.');
				geocodePosition(false, marker.getPosition());
				updateMarkerPosition(marker.getPosition())
			});
		}

		//Parseia o XML
		function downloadUrl(url, callback) {
			var request = window.ActiveXObject ?
				new ActiveXObject('Microsoft.XMLHTTP') :
				new XMLHttpRequest;

			request.onreadystatechange = function() {
				if (request.readyState == 4) {
					request.onreadystatechange = doNothing;
					callback(request, request.status);
				}
			};

			request.open('GET', url, true);
			request.send(null);
		}

		function doNothing() {}

		/////////////////////////////////////////////
		// Fun��es dos Marcadores
		/////////////////////////////////////////////
		
		//Adiciona novo marcador ao mapa
		function newMarker(title, type) {
			
			//Verifica se title ou type n�o s�o vazios
			if (!title || !type)
				return false;
				
			//Pega os limites do mapa
			var bounds = map.getBounds();
			
			//Obt�m as coordenadas da posi��o central do mapa
			var center = bounds.getCenter();
			
			//�cone de acordo com o tipo de marcador
			var icon = customIcons[type] || {};
			
			//Cria um novo marcador
			var marker = new google.maps.Marker({
				position: center,
				title: title,
				map: map,
				icon: icon.icon,
				draggable: true
			});
			
			//Atualiza as informa��es de posi��o atual
			updateMarkerPosition(center);
			
			//Grava as informa��es no array
			markersData[title] = new Object();
			markersData[title]['name'] = title;			
			markersData[title]['type'] = type;
			
			//Obt�m o endere�o da posi��o global
			geocodePosition(marker.getTitle(), marker.getPosition());
					  
			//Adiciona os eventos de arrastar ao marcador
			//Estes ir�o atualizar as informa��es do painel de informa��es
			google.maps.event.addListener(marker, 'dragstart', function() {
				updateMarkerAddress('Arrastando...');
			});
			google.maps.event.addListener(marker, 'drag', function() {
				updateMarkerStatus('Arrastando...');
				updateMarkerPosition(marker.getPosition());
			});
			google.maps.event.addListener(marker, 'dragend', function() {
				updateMarkerStatus('Arraste terminou');
				geocodePosition(marker.getTitle(), marker.getPosition());
			});
			
			//Exibe as informa��es do marcador selecionado no painel
			google.maps.event.addListener(marker, 'click', function() {
				updateMarkerStatus('Parado.');
				geocodePosition(marker.getTitle(), marker.getPosition());
			});
		  
			//Limpa os inputs
			clearInputs();
			
			return false;
		}
		
		//Essa fun��o recebe coordenadas de latitude e longitude
		// e retorna o endere�o desta coordenada
		//Se title == false, ent�o n�o atualiza dados de novos marcadores
		var geocoder = new google.maps.Geocoder();
		function geocodePosition(title, pos) {
			
			if (title) {
				//Grava a latitude e longitude
				markersData[title]['lat'] = pos.lat();
				markersData[title]['lng'] = pos.lng();
			}
			
			//Obt�m o endere�o das coordenadas
			geocoder.geocode({
				latLng: pos
			}, function(responses) {
				if (responses && responses.length > 0) {
					updateMarkerAddress(responses[0].formatted_address);
					if (title)
						markersData[title]['address'] = responses[0].formatted_address;
				} else {
					updateMarkerAddress('Imposs�vel determinar o endere�o desta localiza��o.');
					if (title)
						markersData[title]['address'] = "erro";
				}
			});
		}
		 
		//Atualiza o status do marcador
		function updateMarkerStatus(str) {
			document.getElementById('markerStatus').innerHTML = str;
		}
		 
		//Atualiza a posi��o do marcador
		function updateMarkerPosition(latLng) {
			document.getElementById('info').innerHTML = [
				latLng.lat(),
				latLng.lng()
			].join(', ');
		}
		 
		//Atualiza o endere�o do marcador
		function updateMarkerAddress(str) {
			document.getElementById('address').innerHTML = str;
		}
		
		//Limpa campo(s) de marcador
		function clearInputs() {
			document.getElementById('type').selectedIndex = 0;
			document.getElementById('title').value = "";
		}
		
		
		/////////////////////////////////////////////
		// Fun��es de Busca de Endere�os
		/////////////////////////////////////////////
		function geocode() {
			var address = document.getElementById("address2").value;
			geocoder.geocode({
				'address': address,
				'partialmatch': true}, geocodeResult);
		}
 
		function geocodeResult(results, status) {
			if (status == 'OK' && results.length > 0) {
				map.fitBounds(results[0].geometry.viewport);
			} else {
				alert("Geocode was not successful for the following reason: " + status);
			}
		}
		
		
		/////////////////////////////////////////////
		// Fun��es Ajax
		/////////////////////////////////////////////
		
		//Cria um objeto para requisi��es XML
		function getXMLObject() {
			var xmlHttp = false;
			try {
				xmlHttp = new ActiveXObject("Msxml2.XMLHTTP")  // For Old Microsoft Browsers
			}
			catch (e) {
				try {
					xmlHttp = new ActiveXObject("Microsoft.XMLHTTP")  // For Microsoft IE 6.0+
				}
				catch (e2) {
					xmlHttp = false;   // No Browser accepts the XMLHTTP Object then false
				}
			}
			if (!xmlHttp && typeof XMLHttpRequest != 'undefined') {
				xmlHttp = new XMLHttpRequest();        //For Mozilla, Opera Browsers
			}
			return xmlHttp;  // Mandatory Statement returning the ajax object created
		}
			 
		var xmlhttp = new getXMLObject();
		
		//Executa a requisi��o ajax
		function ajaxFunction(data) {
			var getdate = new Date();  //Used to prevent caching during ajax call
			if (xmlhttp) {
				xmlhttp.open("POST","gravarMarcadores.php",true); //calling testing.php using POST method
				xmlhttp.onreadystatechange  = handleServerResponse;
				xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				xmlhttp.send(data); //Posting txtname to PHP File
			}
		}
		
		
		function handleServerResponse() {
			//Recarrega o mapa
			load();
			
			//Mensagem
			if (xmlhttp.responseText == '1')
				var message = xmlhttp.responseText + " marcador adicionado.";
			else
				var message = xmlhttp.responseText + " marcadores adicionados.";
			
			//Exibe mensagem de sucesso
			document.getElementById("message").innerHTML = message;
		}
		
		//Grava no banco os marcadores
		function recordMarkers() {
			
			var data = '';
			
			//Serializa o array dos marcadores novos
			for (var i in markersData) {
				for (var j in markersData[i]) {
					data = data + "data["+i+"]["+j+"]="+encodeURIComponent(markersData[i][j])+"&";
				}
			}
			
			//Grava no banco os novos marcadores
			ajaxFunction(data);
			
			//Limpa o array
			markersData = new Object();
			
			return false;
		}

		//]]>
	</script>
	</head>

	<!-- Executa a fun��o load() ao carregar o corpo do documento -->
	<body onload="load()">
	
		<!-- Div onde ser� carregado o mapa -->
		<div id="map"></div>
	
		<!-- In�cio Painel de Informa��es -->
		<div id="infoPanel"> 
		
			<div id="block">
				Buscar Endere�o: <input type="text" id="address2"/><input type="button" value="Go" onclick="geocode()" />
			</div>
		
			<div id="block">
				<b>Status do marcador:</b> 
				<div id="markerStatus"><i>Adicione um novo marcador.</i></div> 
				<b>Posi��o atual:</b> 
				<div id="info"></div> 
				<b>Endere�o:</b> 
				<div id="address"></div> 
			</div>
			
			<div id="block">
				<b>Novo Marcador:</b> 
				<p>Nome: <input id="title" type="text" /></p>
				<p>
					Tipo:
					<select id="type">
						<option value="aeroporto">Aeroporto</option>
						<option value="bar">Bar</option>
						<option value="onibus">�nibus</option>
						<option value="cafeteria">Cafeteria</option>
						<option value="gasolina">Posto de Gasolina</option>
						<option value="hotel">Hotel</option>
						<option value="restaurante">Restaurante</option>
						<option value="wifi">Wi-Fi</option>
					</select>
				</p>
				<p><input type="button" onclick="newMarker(document.getElementById('title').value, document.getElementById('type').value);" value="Adicionar Marcador" /></p>
				
				<p><input type="button" onclick="recordMarkers();" value="Salvar Marcadores" /></p>
				
				<p id="message"></p> 
			</div>
		</div> 
		<!-- Fim Painel de Informa��es -->
		
		
	</body>
</html>
