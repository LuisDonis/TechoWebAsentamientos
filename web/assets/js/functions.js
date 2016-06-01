var ubicacion=jQuery.parseJSON($('#country').val());
var agencia=jQuery.parseJSON($('#agencias').val());  

$(function() {
 	var citymap = $('#mostrarMapa').gmap3(
		{
			action: 'init',
			mapTypeId: google.maps.MapTypeId.HYBRID,
			center: {
				lat: 15.783471,
				lng: -90.2307589999999
			},
			zoom: 7,
			mapTypeControl: true,
			mapTypeControlOptions: {
				style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
			},
			navigationControl: true,
			scrollwheel: true,
			streetViewControl: true
		},  
		{
			action: 'addFixPanel',
			options: {
				content: '<div></div>',
				middle: true,
				right: 0
			},
			events: {
				bounds_changed: function(map) {
					//var ll = $('#ll').val(map.getCenter().lat() + ',' + map.getCenter().lng());
				},
				mousemove: function(map, event) {
					latlng = '' + event.latLng + '';
					$('#posicion').html(latlng.substring(1, latlng.length - 1));
				}
			}
		}
	);

	show_ag();
	show_4g();
		

});



$('#departamento').change(function() {
	var id= $(this).val();
	if(id==="0"){
		$("#municipio").html('<option value="">Municipio</option>');
		$('select#municipio').attr('disabled','disabled');
		$("#comunidad").html('<option value="0">Comunidad</option>');
		$('select#comunidad').attr('disabled','disabled');
		$("#poblado").html('<option value="0">Poblado</option>');
		$('select#poblado').attr('disabled','disabled');
		$('#mostrarMapa').gmap3(
		  { action: 'getLatLng', 
		    address: 15.783471 + ',' + -90.2307589999999,
		    callback: function(result){
		      if (result){
		        $(this).gmap3({action: 'setCenter', args:[ result[0].geometry.location ]});
		        $(this).gmap3({action: 'setZoom', args:[ 7 ]});
		      }
		    }
		  }
		);
	}else{
		jQuery.ajax({
			url: './index.php/home/findMuni',
			type: 'POST',
			async: true,
			dataType: 'json',
			data: {
			    id: id
			},
			success: function(j){

		         jQuery.ajax({
							url: './index.php/home/findUbicacion',
							type: 'POST',
							async: true,
							dataType: 'json',
							data: {
							    id1: id
							},
							success: function(json){

								$('#mostrarMapa').gmap3(
									  { action: 'getLatLng', 
									    address: json.latitud + ',' + json.longitud,
									    callback: function(result){
									      if (result){
									        $(this).gmap3({action: 'setCenter', args:[ result[0].geometry.location ]});
									        $(this).gmap3({action: 'setZoom', args:[ 10 ]});
									      }
									    }
									  }
									);
								$('#posicion').html(json.latitud+","+json.longitud);
							}
						}); 

				var options = '';
				for (var i = 0; i < j.list.length; i++) {
					if (!i) {
						options += '<option value="0">Municipio</option>';
						$('#municipio').removeAttr("disabled");
					}
					
					options += '<option value="' + j.list[i].canton_id + '">' + j.list[i].canton_name + '</option>';
				}
				$("select#municipio").html(options);


				$("#comunidad").html('<option value="0">Comunidad</option>');
				$('select#comunidad').attr('disabled','disabled');	
				$("#poblado").html('<option value="0">Poblado</option>');
				$('select#poblado').attr('disabled','disabled');

			}
		});
	}
});


$('#municipio').change(function() {
	var id= $(this).val();
	if(id==="0"){
		$("#comunidad").html('<option value="0">Comunidad</option>');
		$('select#comunidad').attr('disabled','disabled');
		$("#poblado").html('<option value="0">Poblado</option>');
		$('select#poblado').attr('disabled','disabled');
	}else{
		jQuery.ajax({
			url: './index.php/home/findComunidad',
			type: 'POST',
			async: true,
			dataType: 'json',
			data: {
			    id: id
			},
			success: function(j){
					jQuery.ajax({
					url: './index.php/home/findUbicacionMuni',
					type: 'POST',
					async: true,
					dataType: 'json',
					data: {
					    id1: id
					},
					success: function(json){
					$('#mostrarMapa').gmap3(
						  { action: 'getLatLng', 
						    address: json.latitud + ',' + json.longitud,
						    callback: function(result){
						      if (result){
						        $(this).gmap3({action: 'setCenter', args:[ result[0].geometry.location ]});
						        $(this).gmap3({action: 'setZoom', args:[ 11 ]});
						      }
						    }
						  }
						);
					$('#posicion').html(json.latitud+","+json.longitud);
					}
				}); 

				var options = '';
				if(j.list.length>0){
					for (var i = 0; i < j.list.length; i++) {
					if (!i) {
						options += '<option value="0">Comunidad</option>';
						$('#comunidad').removeAttr("disabled");
					}
					options += '<option value="' + j.list[i].distrito_id + '">' + j.list[i].distrito_name + '</option>';
					}
				}else{
					options += '<option value="0">Comunidad</option>';
					$('select#comunidad').attr('disabled','disabled');
				}

				$("#poblado").html('<option value="0">Poblado</option>');
				$('select#poblado').attr('disabled','disabled');
				
				$("select#comunidad").html(options);

			}
		});
	}
});


$('#comunidad').change(function() {
	var id= $(this).val();
	if(id==="0"){
		$("#poblado").html('<option value="0">Poblado</option>');
		$('select#poblado').attr('disabled','disabled');
	}else{
		jQuery.ajax({
			url: './index.php/home/findPoblado',
			type: 'POST',
			async: true,
			dataType: 'json',
			data: {
			    id: id
			},
			success: function(j){
					jQuery.ajax({
					url: './index.php/home/findUbiComunidad',
					type: 'POST',
					async: true,
					dataType: 'json',
					data: {
					    id1: id
					},
					success: function(json){
					$('#mostrarMapa').gmap3(
						  { action: 'getLatLng', 
						    address: json.latitud + ',' + json.longitud,
						    callback: function(result){
						      if (result){
						        $(this).gmap3({action: 'setCenter', args:[ result[0].geometry.location ]});
						        $(this).gmap3({action: 'setZoom', args:[ 13 ]});
						      }
						    }
						  }
						);
					$('#posicion').html(json.latitud+","+json.longitud);
					}
				}); 

				var options = '';
				if(j.list.length>0){
					for (var i = 0; i < j.list.length; i++) {
					if (!i) {
						options += '<option value="0">Poblado</option>';
						$('#poblado').removeAttr("disabled");
					}
					options += '<option value="' + j.list[i].poblado_id + '">' + j.list[i].poblado_name + '</option>';
					}
				}else{
					options += '<option value="0">Poblado</option>';
				}
				
				$("select#poblado").html(options);

			}
		});
	}
});


$('#poblado').change(function() {
	var id= $(this).val();
	if(id!="0"){
		 jQuery.ajax({
				url: './index.php/home/findUbicacionPoblado',
				type: 'POST',
				async: true,
				dataType: 'json',
				data: {
				    id1: id
				},
				success: function(json){
				$('#mostrarMapa').gmap3(
					  { action: 'getLatLng', 
					    address: json.latitud + ',' + json.longitud,
					    callback: function(result){
					      if (result){
					        $(this).gmap3({action: 'setCenter', args:[ result[0].geometry.location ]});
					        $(this).gmap3({action: 'setZoom', args:[ 15 ]});
					      }
					    }
					  }
					);
				$('#posicion').html(json.latitud+","+json.longitud);
				}
			}); 
	}
	
});


$('#agenciadep').change(function() {
	var id= $(this).val();
	if(id==="0"){
		$("#agenciamuni").html('<option value="">Municipio</option>');
		$('select#agenciamuni').attr('disabled','disabled');
		$("#agenciacomu").html('<option value="0">Comunidad</option>');
		$('select#agenciacomu').attr('disabled','disabled');
		$("#agenciapoblado").html('<option value="0">Poblado</option>');
		$('select#agenciapoblado').attr('disabled','disabled');
		$('#mostrarMapa').gmap3(
		  { action: 'getLatLng', 
		    address: 15.783471 + ',' + -90.2307589999999,
		    callback: function(result){
		      if (result){
		        $(this).gmap3({action: 'setCenter', args:[ result[0].geometry.location ]});
		        $(this).gmap3({action: 'setZoom', args:[ 7 ]});
		      }
		    }
		  }
		);
	}else{
		jQuery.ajax({
			url: './index.php/home/findMuni',
			type: 'POST',
			async: true,
			dataType: 'json',
			data: {
			    id: id
			},
			success: function(j){

		         jQuery.ajax({
							url: './index.php/home/findUbicacion',
							type: 'POST',
							async: true,
							dataType: 'json',
							data: {
							    id1: id
							},
							success: function(json){

								$('#mostrarMapa').gmap3(
									  { action: 'getLatLng', 
									    address: json.latitud + ',' + json.longitud,
									    callback: function(result){
									      if (result){
									        $(this).gmap3({action: 'setCenter', args:[ result[0].geometry.location ]});
									        $(this).gmap3({action: 'setZoom', args:[ 10 ]});
									      }
									    }
									  }
									);
								$('#posicion').html(json.latitud+","+json.longitud);
							}
						}); 

				var options = '';
				for (var i = 0; i < j.list.length; i++) {
					if (!i) {
						options += '<option value="0">Municipio</option>';
						$('#agenciamuni').removeAttr("disabled");
					}
					
					options += '<option value="' + j.list[i].canton_id + '">' + j.list[i].canton_name + '</option>';
				}
				$("select#agenciamuni").html(options);


				$("#agenciacomu").html('<option value="0">Comunidad</option>');
				$('select#agenciacomu').attr('disabled','disabled');	
				$("#agenciapoblado").html('<option value="0">Poblado</option>');
				$('select#agenciapoblado').attr('disabled','disabled');

			}
		});
	}
});

$('#agenciamuni').change(function() {
	var id= $(this).val();
	if(id==="0"){
		$("#agenciacomu").html('<option value="0">Comunidad</option>');
		$('select#agenciacomu').attr('disabled','disabled');
		$("#agenciapoblado").html('<option value="0">Poblado</option>');
		$('select#agenciapoblado').attr('disabled','disabled');
	}else{
		jQuery.ajax({
			url: './index.php/home/findComunidad',
			type: 'POST',
			async: true,
			dataType: 'json',
			data: {
			    id: id
			},
			success: function(j){
					jQuery.ajax({
					url: './index.php/home/findUbicacionMuni',
					type: 'POST',
					async: true,
					dataType: 'json',
					data: {
					    id1: id
					},
					success: function(json){
					$('#mostrarMapa').gmap3(
						  { action: 'getLatLng', 
						    address: json.latitud + ',' + json.longitud,
						    callback: function(result){
						      if (result){
						        $(this).gmap3({action: 'setCenter', args:[ result[0].geometry.location ]});
						        $(this).gmap3({action: 'setZoom', args:[ 11 ]});
						      }
						    }
						  }
						);
					$('#posicion').html(json.latitud+","+json.longitud);
					}
				}); 

				var options = '';
				if(j.list.length>0){
					for (var i = 0; i < j.list.length; i++) {
					if (!i) {
						options += '<option value="0">Comunidad</option>';
						$('#agenciacomu').removeAttr("disabled");
					}
					options += '<option value="' + j.list[i].distrito_id + '">' + j.list[i].distrito_name + '</option>';
					}
				}else{
					options += '<option value="0">Comunidad</option>';
					$('select#agenciacomu').attr('disabled','disabled');
				}

				$("#agenciapoblado").html('<option value="0">Poblado</option>');
				$('select#agenciapoblado').attr('disabled','disabled');
				
				$("select#agenciacomu").html(options);

			}
		});
	}
});


$('#agenciacomu').change(function() {
	var id= $(this).val();
	if(id==="0"){
		$("#agenciapoblado").html('<option value="0">Poblado</option>');
		$('select#agenciapoblado').attr('disabled','disabled');
	}else{
		jQuery.ajax({
			url: './index.php/home/findPoblado',
			type: 'POST',
			async: true,
			dataType: 'json',
			data: {
			    id: id
			},
			success: function(j){
					jQuery.ajax({
					url: './index.php/home/findUbiComunidad',
					type: 'POST',
					async: true,
					dataType: 'json',
					data: {
					    id1: id
					},
					success: function(json){
					$('#mostrarMapa').gmap3(
						  { action: 'getLatLng', 
						    address: json.latitud + ',' + json.longitud,
						    callback: function(result){
						      if (result){
						        $(this).gmap3({action: 'setCenter', args:[ result[0].geometry.location ]});
						        $(this).gmap3({action: 'setZoom', args:[ 13 ]});
						      }
						    }
						  }
						);
					$('#posicion').html(json.latitud+","+json.longitud);
					}
				}); 

				var options = '';
				if(j.list.length>0){
					for (var i = 0; i < j.list.length; i++) {
					if (!i) {
						options += '<option value="0">Poblado</option>';
						$('#agenciapoblado').removeAttr("disabled");
					}
					options += '<option value="' + j.list[i].poblado_id + '">' + j.list[i].poblado_name + '</option>';
					}
				}else{
					options += '<option value="0">Poblado</option>';
				}
				
				$("select#agenciapoblado").html(options);

			}
		});
	}
});

$('#agenciapoblado').change(function() {
	var id= $(this).val();
	if(id!="0"){
		 jQuery.ajax({
				url: './index.php/home/findUbicacionPoblado',
				type: 'POST',
				async: true,
				dataType: 'json',
				data: {
				    id1: id
				},
				success: function(json){
				$('#mostrarMapa').gmap3(
					  { action: 'getLatLng', 
					    address: json.latitud + ',' + json.longitud,
					    callback: function(result){
					      if (result){
					        $(this).gmap3({action: 'setCenter', args:[ result[0].geometry.location ]});
					        $(this).gmap3({action: 'setZoom', args:[ 15 ]});
					      }
					    }
					  }
					);
				$('#posicion').html(json.latitud+","+json.longitud);
				}
			}); 
	}
	
});


$('#buscar').click(function() {
	expr = /^(\-?\d+(\.\d+)?),\s*(\-?\d+(\.\d+)?)$/;
    var posicion= $('#coordenada').val();
    if ( (posicion!="") && (expr.test(posicion))  ){
    	var partsArray = posicion.split(',');
	     $('#mostrarMapa').gmap3(
		  { action: 'getLatLng', 
		    address: partsArray[0] + ',' + partsArray[1],
		    callback: function(result){
		      if (result){
		        $(this).gmap3({action: 'setCenter', args:[ result[0].geometry.location ]});
		        $(this).gmap3({action: 'setZoom', args:[ 10 ]});
		      }
		    }
		  }
		);
	     $('#posicion').html(partsArray[0]+","+partsArray[1]);
    }	     	
});

/***************************************************************/
$('#2g').click(function() {
	 $('#4g').prop('checked', false);
	 $('#3g').prop('checked', false);
	 quitInternet();
	 findMap("2");
         $("#fecha").html(ubicacion.fm2g);
});

$('#3g').click(function() {
	 $('#4g').prop('checked', false);
	 $('#2g').prop('checked', false);
	 quitInternet();
	 findMap("3");
         $("#fecha").html(ubicacion.fm3g);
});

$('#4g').click(function() {
	 $('#2g').prop('checked', false);
	 $('#3g').prop('checked', false);
	 quitInternet();
 	 findMap("4");
	 $("#fecha").html(ubicacion.fm4g);
});

$('#i2g').click(function() {
	 $('#i4g').prop('checked', false);
	 $('#i3g').prop('checked', false);
	 quitMovil();
	 findMap("5");
         $("#fecha").html(ubicacion.fm2g);
});

$('#i3g').click(function() {
	 $('#i4g').prop('checked', false);
	 $('#i2g').prop('checked', false);
	 quitMovil();
	 findMap("6");
         $("#fecha").html(ubicacion.fm3g);
});

$('#i4g').click(function() {
	 $('#i2g').prop('checked', false);
	 $('#i3g').prop('checked', false);
	 quitMovil();
	 findMap("7");
         $("#fecha").html(ubicacion.fm4g);
});

function quitInternet(){
	$('#i4g').prop('checked', false);
	$('#i3g').prop('checked', false);
	$('#i2g').prop('checked', false);
}

function quitMovil(){
	$('#4g').prop('checked', false);
	$('#3g').prop('checked', false);
	$('#2g').prop('checked', false);
}


function findMap(id){
	
	var m2g=false;
	var m3g=false;
	var m4g=false;
	var i2g=false;
	var i3g=false;
	var i4g=false;
	
	switch (id) {

		case '2':
		     m2g=true;	
    		break;
		
		case '3':
		     m3g=true;	
    		break;

		case '4':
		     m4g=true;	
    		break;

		case '5':
		     i2g=true;	
    		break;
		
		case '6':	
		     i3g=true;	
    		break;

		case '7':	
		     i4g=true;	
    		break;
	}
	
	$('#mostrarMapa').gmap3({
		action: 'clear'
	});

	
	if(m2g){
		show_2g();
	}else if(m3g){
	        show_3g();
	}else if(m4g){
	        show_4g();
	}else if(i2g){
	        show_i2g();
	}else if(i3g){
	        show_i3g();
	}else if(i4g){
	        show_i4g();
	}
	
	show_ag();

		 
}
/*****************************************************************************************/

function changeMap(id){
    var id = id;
    
    var map = $('#mostrarMapa').gmap3('get');
    
    var kmz_5g = $('#mostrarMapa').gmap3({
	   	action: 'get', name: 'kmllayer', tag: 'kmz_5g'
	});

    var kmz_2g = $('#mostrarMapa').gmap3({
	   	action: 'get', name: 'kmllayer', tag: 'kmz_2g'
	});
	  
	var kmz_3g = $('#mostrarMapa').gmap3({
    	action: 'get', name: 'kmllayer', tag: 'kmz_3g'
	});

	var kmz_4g = $('#mostrarMapa').gmap3({
    	action: 'get', name: 'kmllayer', tag: 'kmz_4g'
	});

	var kmz_i2g = $('#mostrarMapa').gmap3({
    	action: 'get', name: 'kmllayer', tag: 'kmz_i2g'
	});

	var kmz_i3g = $('#mostrarMapa').gmap3({
    	action: 'get', name: 'kmllayer', tag: 'kmz_i3g'
	});

	var kmz_i4g = $('#mostrarMapa').gmap3({
    	action: 'get', name: 'kmllayer', tag: 'kmz_i4g'
	});

    

	switch (id) {

		case '2':
		    if (kmz_i2g) {
    			kmz_i2g.setMap(null);
    		}
    		if (kmz_i3g) {
    			kmz_i3g.setMap(null);
    		}
    		if (kmz_i4g) {
    			kmz_i4g.setMap(null);
    		}
    		if (kmz_5g) {
    			kmz_5g.setMap(null);
    		}
    		if (kmz_3g) {
    			kmz_3g.setMap(null);
    		}
    		if (kmz_4g) {
    			kmz_4g.setMap(null);
    		}
    		if (kmz_2g) {
		    	kmz_2g.setMap(map);
		    	return;
		    }
		    //  alert(ubicacion.m2g);
				var map_2g = $('#mostrarMapa').gmap3({
					action: 'addKmlLayer',
					url: ubicacion.m2g,
					options: {
						suppressInfoWindows: true,
						preserveViewport: true
					},
					tag: 'kmz_2g'
				});
    		break;

    	case '3':
    		if (kmz_i2g) {
    			kmz_i2g.setMap(null);
    		}
    		if (kmz_i3g) {
    			kmz_i3g.setMap(null);
    		}
    		if (kmz_i4g) {
    			kmz_i4g.setMap(null);
    		}	
    		if (kmz_5g) {
    			kmz_5g.setMap(null);
    		}
    		if (kmz_2g) {
    			kmz_2g.setMap(null);
    		}
    		if (kmz_4g) {
    			kmz_4g.setMap(null);
    		}
    		if (kmz_3g) {
		    	kmz_3g.setMap(map);
		    	return;
		    }
		    //  alert(ubicacion.m3g);
				var map_3g = $('#mostrarMapa').gmap3({
					action: 'addKmlLayer',
					url: ubicacion.m3g,
					options: {
						suppressInfoWindows: true,
						preserveViewport: true
					},
					tag: 'kmz_3g'
				});
    		break;

    	case '4':
    		if (kmz_i2g) {
    			kmz_i2g.setMap(null);
    		}
    		if (kmz_i3g) {
    			kmz_i3g.setMap(null);
    		}
    		if (kmz_i4g) {
    			kmz_i4g.setMap(null);
    		}
    		if (kmz_5g) {
    			kmz_5g.setMap(null);
    		}
    		if (kmz_2g) {
    			kmz_2g.setMap(null);
    		}
    		if (kmz_3g) {
    			kmz_3g.setMap(null);
    		}
    		if (kmz_4g) {
		    	kmz_4g.setMap(map);
		    	return;
		    }
		    //  alert(ubicacion.m4g);
				var map_4g = $('#mostrarMapa').gmap3({
					action: 'addKmlLayer',
					url: ubicacion.m4g,
					options: {
						suppressInfoWindows: true,
						preserveViewport: true
					},
					tag: 'kmz_4g'
				});
    		break;

    	case '5':
    		if (kmz_4g) {
    			kmz_4g.setMap(null);
    		}
    		if (kmz_i3g) {
    			kmz_i3g.setMap(null);
    		}
    		if (kmz_i4g) {
    			kmz_i4g.setMap(null);
    		}
    		if (kmz_5g) {
    			kmz_5g.setMap(null);
    		}
    		if (kmz_2g) {
    			kmz_2g.setMap(null);
    		}
    		if (kmz_3g) {
    			kmz_3g.setMap(null);
    		}
		    if (kmz_i2g) {
    			kmz_i2g.setMap(map);
		    	return;
    		}
		    //  alert(ubicacion.m2g);
				var map_i2g = $('#mostrarMapa').gmap3({
					action: 'addKmlLayer',
					url: ubicacion.m2g,
					options: {
						suppressInfoWindows: true,
						preserveViewport: true
					},
					tag: 'kmz_i2g'
				});
    		break;

    	case '6':
    		if (kmz_4g) {
    			kmz_4g.setMap(null);
    		}
    		if (kmz_i2g) {
    			kmz_i2g.setMap(null);
    		}
    		if (kmz_i4g) {
    			kmz_i4g.setMap(null);
    		}
    		if (kmz_5g) {
    			kmz_5g.setMap(null);
    		}
    		if (kmz_2g) {
    			kmz_2g.setMap(null);
    		}
    		if (kmz_3g) {
    			kmz_3g.setMap(null);
    		}
		    if (kmz_i3g) {
    			kmz_i3g.setMap(map);
		    	return;
    		}
		    //  alert(ubicacion.m3g);
				var map_i3g = $('#mostrarMapa').gmap3({
					action: 'addKmlLayer',
					url: ubicacion.m3g,
					options: {
						suppressInfoWindows: true,
						preserveViewport: true
					},
					tag: 'kmz_i3g'
				});
    		break;

    	case '7':
    		if (kmz_4g) {
    			kmz_4g.setMap(null);
    		}
    		if (kmz_i2g) {
    			kmz_i2g.setMap(null);
    		}
    		if (kmz_i3g) {
    			kmz_i3g.setMap(null);
    		}
    		if (kmz_5g) {
    			kmz_5g.setMap(null);
    		}
    		if (kmz_2g) {
    			kmz_2g.setMap(null);
    		}
    		if (kmz_3g) {
    			kmz_3g.setMap(null);
    		}
		    if (kmz_i4g) {
    			kmz_i4g.setMap(map);
		    	return;
    		}
		    //  alert(ubicacion.m4g);
				var map_i4g = $('#mostrarMapa').gmap3({
					action: 'addKmlLayer',
					url: ubicacion.m4g ,
					options: {
						suppressInfoWindows: true,
						preserveViewport: true
					},
					tag: 'kmz_i4g'
				});
    		break;    				    		    				    	    				    			
    }

	return false;

}

/****************************/
function show_ag() {
		
	var kmz_ag = $('#mostrarMapa').gmap3({
  	action: 'get', name: 'kmllayer', tag: 'kmz_a'
  	});
    
	if ($('#showag').is(':checked')) {
      
	    if (kmz_ag) {
	    	kmz_ag.setMap($('#mostrarMapa').gmap3('get'));
	    	return;
	    }

		for (var i = 0; i < agencia.length; i++) {
   		       var kmz_ag = $('#mostrarMapa').gmap3({
			action: 'addKmlLayer',
			url: agencia[i],
			options: {
				suppressInfoWindows: true,
				preserveViewport: true,
				suppressMarkers: false
			},
			tag: 'kmz_a'
		});
		
		}
		
	} else {
		try {	
			verificar();
  	} catch(e) { }
	}
}


function show_4g() {

	var kmz_4g = $('#mostrarMapa').gmap3({
    	action: 'get', name: 'kmllayer', tag: 'kmz_4g'
	});
    
      
    if (kmz_4g) {
    	kmz_4g.setMap($('#mostrarMapa').gmap3('get'));
    	return;
    }	
		for (var i = 0; i < ubicacion.length; i++) {
   			var kmz_4g = $('#mostrarMapa').gmap3({
			action: 'addKmlLayer',
			url: ubicacion[i],
			options: {
				suppressInfoWindows: true,
				preserveViewport: true,
				suppressMarkers: false
			},
			tag: 'kmz_4g'
		});
		
		}
		
}

function show_2g() {
 jQuery.ajax({
	url: './index.php/home/findMapa2g',
	type: 'POST',
	async: true,
	dataType: 'json',
	success: function(json){
			for (var i = 0; i < json.length; i++) {
	   			var kmz_2g = $('#mostrarMapa').gmap3({
				action: 'addKmlLayer',
				url: json[i],
				options: {
					suppressInfoWindows: true,
					preserveViewport: true,
					suppressMarkers: false
				},
				tag: 'kmz_2g'
			});

			}
	}
  }); 
		
}

function show_3g() {
 jQuery.ajax({
	url: './index.php/home/findMapa3g',
	type: 'POST',
	async: true,
	dataType: 'json',
	success: function(json){
			for (var i = 0; i < json.length; i++) {
	   			var kmz_3g = $('#mostrarMapa').gmap3({
				action: 'addKmlLayer',
				url: json[i],
				options: {
					suppressInfoWindows: true,
					preserveViewport: true,
					suppressMarkers: false
				},
				tag: 'kmz_3g'
			});

			}
	}
  }); 		
}

function show_i2g() {
 jQuery.ajax({
	url: './index.php/home/findMapai2g',
	type: 'POST',
	async: true,
	dataType: 'json',
	success: function(json){
			for (var i = 0; i < json.length; i++) {
	   			var kmz_i2g = $('#mostrarMapa').gmap3({
				action: 'addKmlLayer',
				url: json[i],
				options: {
					suppressInfoWindows: true,
					preserveViewport: true,
					suppressMarkers: false
				},
				tag: 'kmz_i2g'
			});

			}
	}
  }); 		
}

function show_i3g() {
 jQuery.ajax({
	url: './index.php/home/findMapai3g',
	type: 'POST',
	async: true,
	dataType: 'json',
	success: function(json){
			for (var i = 0; i < json.length; i++) {
	   			var kmz_i3g = $('#mostrarMapa').gmap3({
				action: 'addKmlLayer',
				url: json[i],
				options: {
					suppressInfoWindows: true,
					preserveViewport: true,
					suppressMarkers: false
				},
				tag: 'kmz_i3g'
			});

			}
	}
  }); 		
}

function show_i4g() {
 jQuery.ajax({
	url: './index.php/home/findMapai4g',
	type: 'POST',
	async: true,
	dataType: 'json',
	success: function(json){
			for (var i = 0; i < json.length; i++) {
	   			var kmz_i4g = $('#mostrarMapa').gmap3({
				action: 'addKmlLayer',
				url: json[i],
				options: {
					suppressInfoWindows: true,
					preserveViewport: true,
					suppressMarkers: false
				},
				tag: 'kmz_i4g'
			});

			}
	}
  }); 		
}

function verificar(){	
	 var mapaActual="";
	       var kmz_2g = $('#mostrarMapa').gmap3({
		   	action: 'get', name: 'kmllayer', tag: 'kmz_2g'
		});
		  
		var kmz_3g = $('#mostrarMapa').gmap3({
	    	action: 'get', name: 'kmllayer', tag: 'kmz_3g'
		});

		var kmz_4g = $('#mostrarMapa').gmap3({
	    	action: 'get', name: 'kmllayer', tag: 'kmz_4g'
		});

		var kmz_i2g = $('#mostrarMapa').gmap3({
	    	action: 'get', name: 'kmllayer', tag: 'kmz_i2g'
		});

		var kmz_i3g = $('#mostrarMapa').gmap3({
	    	action: 'get', name: 'kmllayer', tag: 'kmz_i3g'
		});

		var kmz_i4g = $('#mostrarMapa').gmap3({
	    	action: 'get', name: 'kmllayer', tag: 'kmz_i4g'
		});
	
	
	if (kmz_2g) {
    	     mapaActual=1;
    		}
	if (kmz_3g) {
	     mapaActual=2;
	}
	if (kmz_4g) {
	     mapaActual=3;	
	}
	if (kmz_i2g) {
	     mapaActual=4;	
	}
	if (kmz_i3g) {
	     mapaActual=5;	
	}
	if (kmz_i4g) {
	     mapaActual=6;	
	}

	
	$('#mostrarMapa').gmap3({
		action: 'clear'
	});
	
	switch (mapaActual) {

	case 1:
	    show_2g();
	break;
	case 2:
	    show_3g();
	break;
	case 3:
	    show_4g();
	break;
	case 4:
	    show_i2g();
	break;
	case 5:
	    show_i3g();
	break;
	case 6:
	    show_i4g();
	break;		
	}	

}

/*********************************************************/

$('#showag').change(show_ag);
