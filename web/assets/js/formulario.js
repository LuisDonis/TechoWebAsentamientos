

function create_user(){
  	location.href='./createUsers';
}

function myAnterior(){
  	location.href='./editor';
}

function createUser(){
	if(!$('#nombre').val() || !$('#pass').val() || !$('#email').val()){
   		$.fancybox('Error en el registro! No fue posible realizar los cambios.');
	}else{
		expr = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		if ( !expr.test(jQuery('#email').val()) ){
			$.fancybox('Formato de correo incorrecto.');
		}else{
				jQuery.ajax({
					url: './validateFormUser',
					type: 'POST',
					async: true,
					data: {
					    name: $('#nombre').val(), 
					    pass: $('#pass').val(),
					    pais: $('#pais').val(),
					    rol: $('#rol').val(),
					    email: $('#email').val()
					},
					dataType: 'json',
				
					beforeSend: function(xhr){

						$.fancybox.showLoading();
					},
					success: function(json){

							if(json.status==1){
							jQuery.fancybox(json.message,{
							afterClose: function() {top.location.href = './admin';}
							});

						}else{
							jQuery.fancybox(json.message,{
							afterClose: function() {}
							});
						}		

					},
					complete: function(xhr, textStatus){
						$.fancybox.hideLoading();
					}
				});
		}
	}
}

function eliminar_user(id)
 {
var msg='¿Realmente desea eliminar este usuario?'

$('#message').html("");

$('#message').append("<div id='dialog-messagee' title='Eliminar Usuario' style='display:none'><p>"+msg+"</p></div>");

  $(function() {
     $( "#dialog-messagee" ).dialog({
       modal: true,
       buttons: {
        Ok: function() {
           $( this ).dialog( "close" );  
           jQuery.ajax({
		  url: "./deleteUser",
		  type: 'post',
		  data: { 
		   id:id
		  },
		  dataType: 'json',
      beforeSend: function(xhr){
          $.fancybox.showLoading();
          },
		success: function(json) {
					  jQuery.fancybox(json.message,{
			afterClose: function() {top.location.href = location.href;}
			});

		   },
		complete: function(xhr, textStatus){
            //elimina box de loader al finalizar Ajax
           $.fancybox.hideLoading();
          }
		});     
        },
       Cancel: function() {
          $( this ).dialog( "close" );
        } 
      }
    });
  });
 }//fin de la funcion eliminar_user


function eliminar_region(id,pais,region)
{
var msg='¿Realmente desea eliminar esta Region?'
$('#message').html("");
$('#message').append("<div id='dialog-messagee' title='Eliminar Region' style='display:none'><p>"+msg+"</p></div>");


 $(function() {
     $( "#dialog-messagee" ).dialog({
       modal: true,
       buttons: {
        Ok: function() {
           $( this ).dialog( "close" );  
           jQuery.ajax({
		  url: "./deleteRegion",
		  type: 'post',
		  data: { 
		   id:id,
		   pais:pais,
		   region:region
		  },
		  dataType: 'json',
      beforeSend: function(xhr){
          $.fancybox.showLoading();
          },
		success: function(json) {
					  jQuery.fancybox(json.message,{
			afterClose: function() {top.location.href = './regiones';}
			});

		   },
		complete: function(xhr, textStatus){
            //elimina box de loader al finalizar Ajax
           $.fancybox.hideLoading();
          }
		});     
        },
       Cancel: function() {
          $( this ).dialog( "close" );
        } 
      }
    });
  });
 
 }//fin de la funcion eliminar_user

function editar_user(id)
 {
 	$('#editar'+id).submit();

 }//fin de la funcion editar_user


 function editar_region(id)
 {
 	$('#editar'+id).submit();

 }//fin de la funcion editar_user

 function editUser(id){

	if(!$('#nombre').val() || !$('#pass').val() || !$('#email').val()){
   		$.fancybox('Error en el registro! No fue posible realizar los cambios.');
	}else{
		expr = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		if ( !expr.test(jQuery('#email').val()) ){
			$.fancybox('Formato de correo incorrecto.');
		}else{
				jQuery.ajax({
					url: './validEditUser',
					type: 'POST',
					async: true,
					data: {
					    name: $('#nombre').val(), 
					    pass: $('#pass').val(),
					    pais: $('#pais').val(),
					    email: $('#email').val(),
					    id: id
					},
					dataType: 'json',
				
					beforeSend: function(xhr){

						$.fancybox.showLoading();
					},
					success: function(json){

							if(json.status==1){
										jQuery.fancybox(json.message,{
									afterClose: function() {top.location.href = './admin';}
									});
								}else{
									jQuery.fancybox(json.message,{
									afterClose: function() { }
									});
								}

					
						
					},
					complete: function(xhr, textStatus){
						$.fancybox.hideLoading();
					}
				});
		}
	}
}


function editRegion(id){

	if(!$('#nombre').val() || !$('#latitud').val() || !$('#longitud').val()){
   		$.fancybox('Error en el registro! Por favor ingresar todos los campos.');
	}else{
		expr = /^(\-?\d+(\.\d+)?)$/;
		expr1= /^\s*(\-?\d+(\.\d+)?)$/;
		if ( !expr.test(jQuery('#latitud').val()) || !expr1.test(jQuery('#longitud').val()) ){
			$.fancybox('Formato de coordenadas incorrecto.');
		}else{
				jQuery.ajax({
					url: './validEditRegion',
					type: 'POST',
					async: true,
					data: {
					    name: $('#nombre').val(), 
					    latitud: $('#latitud').val(),
					    longitud: $('#longitud').val(),
					    pais: $('#pais').val(), 
					    region: $('#region').val(),
					    id: id
					},
					dataType: 'json',
				
					beforeSend: function(xhr){

						$.fancybox.showLoading();
					},
					success: function(json){

							if(json.status==1){
										jQuery.fancybox(json.message,{
									afterClose: function() {top.location.href = './regiones';}
									});
								}else{
									jQuery.fancybox(json.message,{
									afterClose: function() { }
									});
								}
					},
					complete: function(xhr, textStatus){
						$.fancybox.hideLoading();
					}
				});
		}
	}
}


function bloquear_user(id){
				jQuery.ajax({
					url: './blockUser',
					type: 'POST',
					async: true,
					data: {
					    id: id
					},
					dataType: 'json',
				
					beforeSend: function(xhr){

						$.fancybox.showLoading();
					},
					success: function(json){
								
									jQuery.fancybox(json.message,{
						afterClose: function() {top.location.href = location.href;}
						});
					},
					complete: function(xhr, textStatus){
						$.fancybox.hideLoading();
					}
				});
		
}

function active_user(id){
				jQuery.ajax({
					url: './activeUser',
					type: 'POST',
					async: true,
					data: {
					    id: id
					},
					dataType: 'json',
				
					beforeSend: function(xhr){

						$.fancybox.showLoading();
					},
					success: function(json){
								
									jQuery.fancybox(json.message,{
						afterClose: function() {top.location.href = location.href;}
						});
					},
					complete: function(xhr, textStatus){
						$.fancybox.hideLoading();
					}
				});
		
}


function bloquear_region(id,pais,region){
				jQuery.ajax({
					url: './blockRegion',
					type: 'POST',
					async: true,
					data: {
					    id: id,
					    pais: pais,
					    region:region
					},
					dataType: 'json',
				
					beforeSend: function(xhr){

						$.fancybox.showLoading();
					},
					success: function(json){
								
									jQuery.fancybox(json.message,{
						afterClose: function() {top.location.href = './regiones';}
						});
					},
					complete: function(xhr, textStatus){
						$.fancybox.hideLoading();
					}
				});
		
}

function activar_region(id,pais,region){
				jQuery.ajax({
					url: './activeRegion',
					type: 'POST',
					async: true,
					data: {
					    id: id,
					    pais: pais,
					    region:region
					},
					dataType: 'json',
				
					beforeSend: function(xhr){

						$.fancybox.showLoading();
					},
					success: function(json){
								
									jQuery.fancybox(json.message,{
						afterClose: function() {top.location.href = './regiones';}
						});
					},
					complete: function(xhr, textStatus){
						$.fancybox.hideLoading();
					}
				});
		
}

function subirArchivo(){
       if(!$('#ag').val() && !$('#5g').val() && !$('#4g').val()  && !$('#3g').val()  && !$('#2g').val()  && !$('#vi').val()){
   		$.fancybox('Error en el registro! Por favor seleccione un archivo.');
	     }else{
	     	$('#upArchivo').submit();
	}	
	
}

function editTex(){

	if(!$('#texto1').val() || !$('#texto2').val() || !$('#texto3').val() || !$('#texto4').val()){
   		$.fancybox('Por favor ingresa todos los textos! No fue posible realizar los cambios.');
	}else{
				jQuery.ajax({
					url: './validTextos',
					type: 'POST',
					async: true,
					data: {
					    texto1: $('#texto1').val(), 
					    idtexto1: $('#idtexto1').val(),
					    texto2: $('#texto2').val(),
					    idtexto2: $('#idtexto2').val(),
					    texto3: $('#texto3').val(), 
					    idtexto3: $('#idtexto3').val(),
					    texto4: $('#texto4').val(),
					    idtexto4: $('#idtexto4').val(),
					    pais: $('#pais').val()
					},
					dataType: 'json',
				
					beforeSend: function(xhr){

						$.fancybox.showLoading();
					},
					success: function(json){

							if(json.status==1){
										jQuery.fancybox(json.message,{
									afterClose: function() {top.location.href = './textos';}
									});
								}else{
									jQuery.fancybox(json.message,{
									afterClose: function() { }
									});
								}

					
						
					},
					complete: function(xhr, textStatus){
						$.fancybox.hideLoading();
					}
				});
		
	}
}

//********************************************/
function createDepar(){
	if(!$('#nombre').val() || !$('#latitud').val() || !$('#longitud').val()){
   		$.fancybox('Error en el registro! Favor ingrese todos los datos.');
	}else{
		 expr = /^(\-?\d+(\.\d+)?)$/;
		 expr1= /^\s*(\-?\d+(\.\d+)?)$/;
		if ( !expr.test(jQuery('#latitud').val()) || !expr1.test(jQuery('#longitud').val()) ){
			$.fancybox('Formato de coordenadas incorrecto.');
		}else{
				jQuery.ajax({
					url: './validateFormDepart',
					type: 'POST',
					async: true,
					data: {
					    name: $('#nombre').val(), 
					    latitud: $('#latitud').val(),
					    longitud: $('#longitud').val(),
					    pais: $('#pais').val(),
					},
					dataType: 'json',
				
					beforeSend: function(xhr){

						$.fancybox.showLoading();
					},
					success: function(json){

							if(json.status==1){
							jQuery.fancybox(json.message,{
							afterClose: function() {top.location.href = './regiones';}
							});

						}else{
							jQuery.fancybox(json.message,{
							afterClose: function() {}
							});
						}		

					},
					complete: function(xhr, textStatus){
						$.fancybox.hideLoading();
					}
				});
		}
	}
}

function createMunicipio(){
	if(!$('#nombre').val() || !$('#latitud').val() || !$('#longitud').val()){
   		$.fancybox('Error en el registro! Favor ingrese todos los datos.');
	}else{
		 expr = /^(\-?\d+(\.\d+)?)$/;
		 expr1= /^\s*(\-?\d+(\.\d+)?)$/;
		if ( !expr.test(jQuery('#latitud').val()) || !expr1.test(jQuery('#longitud').val()) ){
			$.fancybox('Formato de coordenadas incorrecto.');
		}else{
				jQuery.ajax({
					url: './validateFormMunicipio',
					type: 'POST',
					async: true,
					data: {
					    name: $('#nombre').val(), 
					    latitud: $('#latitud').val(),
					    longitud: $('#longitud').val(),
					    departamento: $('#depar').val(),
					    pais: $('#pais').val(),
					},
					dataType: 'json',
				
					beforeSend: function(xhr){

						$.fancybox.showLoading();
					},
					success: function(json){

							if(json.status==1){
							jQuery.fancybox(json.message,{
							afterClose: function() {top.location.href = './regiones';}
							});

						}else{
							jQuery.fancybox(json.message,{
							afterClose: function() {}
							});
						}		

					},
					complete: function(xhr, textStatus){
						$.fancybox.hideLoading();
					}
				});
		}
	}
}


function createComunidad(){
	if(!$('#nombre').val() || !$('#latitud').val() || !$('#longitud').val() ||  ($('#municipio').val()==0) ){
   		$.fancybox('Error en el registro! Favor ingrese todos los datos.');
	}else{
		 expr = /^(\-?\d+(\.\d+)?)$/;
		 expr1= /^\s*(\-?\d+(\.\d+)?)$/;
		if ( !expr.test(jQuery('#latitud').val()) || !expr1.test(jQuery('#longitud').val()) ){
			$.fancybox('Formato de coordenadas incorrecto.');
		}else{
				jQuery.ajax({
					url: './validateFormComunidad',
					type: 'POST',
					async: true,
					data: {
					    name: $('#nombre').val(), 
					    latitud: $('#latitud').val(),
					    longitud: $('#longitud').val(),
					    municipio: $('#municipio').val(),
					    pais: $('#paisActual').val(),
					},
					dataType: 'json',
				
					beforeSend: function(xhr){

						$.fancybox.showLoading();
					},
					success: function(json){

							if(json.status==1){
							jQuery.fancybox(json.message,{
							afterClose: function() {top.location.href = './regiones';}
							});

						}else{
							jQuery.fancybox(json.message,{
							afterClose: function() {}
							});
						}		

					},
					complete: function(xhr, textStatus){
						$.fancybox.hideLoading();
					}
				});
		}
	}
}


function createPoblado(){
	if(!$('#nombre').val() || !$('#latitud').val() || !$('#longitud').val() ||  ($('#comunidad').val()==0) ){
   		$.fancybox('Error en el registro! Favor ingrese todos los datos.');
	}else{
		 expr = /^(\-?\d+(\.\d+)?)$/;
		 expr1= /^\s*(\-?\d+(\.\d+)?)$/;
		if ( !expr.test(jQuery('#latitud').val()) || !expr1.test(jQuery('#longitud').val()) ){
			$.fancybox('Formato de coordenadas incorrecto.');
		}else{
				jQuery.ajax({
					url: './validateFormPoblado',
					type: 'POST',
					async: true,
					data: {
					    name: $('#nombre').val(), 
					    latitud: $('#latitud').val(),
					    longitud: $('#longitud').val(),
					    comunidad: $('#comunidad').val(),
					    pais: $('#paisActual').val(),
					},
					dataType: 'json',
				
					beforeSend: function(xhr){

						$.fancybox.showLoading();
					},
					success: function(json){

							if(json.status==1){
							jQuery.fancybox(json.message,{
							afterClose: function() {top.location.href = './regiones';}
							});

						}else{
							jQuery.fancybox(json.message,{
							afterClose: function() {}
							});
						}		

					},
					complete: function(xhr, textStatus){
						$.fancybox.hideLoading();
					}
				});
		}
	}
}

/**********************************************/


function editar_mapa(id)
 {
 	$('#editarMapa'+id).submit();

 }//fin de la funcion editar_user

function subirVariosArchivo(){
       if(!$('#ag').val() && !$('#4g').val()  && !$('#3g').val()  && !$('#2g').val()  && !$('#v2g').val() && !$('#v3g').val() && !$('#v4g').val()){
   		$.fancybox('Error en el registro! Por favor seleccione un archivo.');
	     }else{
	     	$('#upVariosArchivos').submit();
	}	
	
}

function subirUnArchivo(){
       if(!$('#ag').val()){
   		$.fancybox('Error en el registro! Por favor seleccione un archivo.');
	     }else{
	     	$('#upUnArchivo').submit();
	}	
}

function updateArchivo(){
       if(!$('#ag').val()){
   		$.fancybox('Error en el registro! Por favor seleccione un archivo.');
	     }else{
	     	$('#updateArchivo').submit();
	}	
}



function eliminar_mapa(id,pais)
{
		var msg='¿Realmente desea eliminar este Archivo?'
		$('#message').html("");
		$('#message').append("<div id='dialog-messagee' title='Eliminar Region' style='display:none'><p>"+msg+"</p></div>");


		 $(function() {
		     $( "#dialog-messagee" ).dialog({
		       modal: true,
		       buttons: {
		        Ok: function() {
		           $( this ).dialog( "close" );  
		           jQuery.ajax({
				  url: "./deleteMapa",
				  type: 'post',
				  data: { 
				   id:id,
				   pais:pais
				  },
				  dataType: 'json',
		      beforeSend: function(xhr){
		          $.fancybox.showLoading();
		          },
				success: function(json) {
							  jQuery.fancybox(json.message,{
					afterClose: function() {top.location.href = './editor';}
					});

				   },
				complete: function(xhr, textStatus){
		            //elimina box de loader al finalizar Ajax
		           $.fancybox.hideLoading();
		          }
				});     
		        },
		       Cancel: function() {
		          $( this ).dialog( "close" );
		        } 
		      }
		    });
		  });
 
 }//fin de la funcion eliminar_user	

jQuery(document).ready(function(){ 

jQuery("body").delegate("#depar","change",function() {
				jQuery.ajax({
					url: './getMunicipios',
					type: 'POST',
					async: true,
					data: {
					    depar: $('#depar').val(),
					    pais: $('#paisActual').val()
					},
					dataType: 'json',
				
					beforeSend: function(xhr){

						$.fancybox.showLoading();
					},
					success: function(json){

							if(json.status==1){
										//var txt = "";
             							$("#tabla").html(json.message);
								}else{
									jQuery.fancybox(json.message,{
									afterClose: function() { }
									});
								}
					},
					complete: function(xhr, textStatus){
						$.fancybox.hideLoading();
					}
				});
	});


jQuery("body").delegate("#deparmuni","change",function() {
	var id= $(this).val();
	if(id!="0"){
		 jQuery.ajax({
				url: './getSelecMuni',
				type: 'POST',
				async: true,
				dataType: 'json',
				data: {
				    depar: id,
				    pais: $('#paisActual').val()
				},
				success: function(j){

				var options = '';
				for (var i = 0; i < j.list.length; i++) {
					if (!i) {
						options += '<option value="0">Municipio</option>';
						$('#municipio').removeAttr("disabled");
					}
					options += '<option value="' + j.list[i].canton_id + '">' + j.list[i].canton_name + '</option>';
				}
				$("select#municipio").html(options);
				
				}
			}); 
	}
	});

 jQuery("body").delegate("#municipio","change",function() {
	 var id= $(this).val();
	 		jQuery.ajax({
					url: './getComunidades',
					type: 'POST',
					async: true,
					data: {
					    muni: id,
					    pais: $('#paisActual').val()
					},
					dataType: 'json',
				
					beforeSend: function(xhr){

						$.fancybox.showLoading();
					},
					success: function(json){

							if(json.status==1){
										//var txt = "";
             							$("#tabla").html(json.message);
								}else{
									jQuery.fancybox(json.message,{
									afterClose: function() { }
									});
								}
					},
					complete: function(xhr, textStatus){
						$.fancybox.hideLoading();
					}
				});
	});


 jQuery("body").delegate("#deparmunicomuni","change",function() {
	var id= $(this).val();
	if(id!="0"){
		 jQuery.ajax({
				url: './getSelecMuni',
				type: 'POST',
				async: true,
				dataType: 'json',
				data: {
				    depar: id,
				    pais: $('#paisActual').val()
				},
				success: function(j){

				var options = '';
				for (var i = 0; i < j.list.length; i++) {
					if (!i) {
						options += '<option value="0">Municipio</option>';
						$('#deparmunicipio').removeAttr("disabled");
					}
					options += '<option value="' + j.list[i].canton_id + '">' + j.list[i].canton_name + '</option>';
				}
				$("select#deparmunicipio").html(options);
				
				}
			}); 
	}
	});

 jQuery("body").delegate("#deparmunicipio","change",function() {
	 var id= $(this).val();
	 if(id!="0"){
		 jQuery.ajax({
				url: './getSelecComunidad',
				type: 'POST',
				async: true,
				dataType: 'json',
				data: {
				    muni: id,
				    pais: $('#paisActual').val()
				},
				success: function(j){

				var options = '';
				for (var i = 0; i < j.list.length; i++) {
					if (!i) {
						options += '<option value="0">Comunidad</option>';
						$('#comunidad').removeAttr("disabled");
					}
					options += '<option value="' + j.list[i].distrito_id + '">' + j.list[i].distrito_name + '</option>';
				}
				$("select#comunidad").html(options);
				
				}
			}); 
	}
	 		
	});


 jQuery("body").delegate("#comunidad","change",function() {
	 var id= $(this).val();
	 		jQuery.ajax({
					url: './getPoblados',
					type: 'POST',
					async: true,
					data: {
					    comunidad: id,
					    pais: $('#paisActual').val()
					},
					dataType: 'json',
				
					beforeSend: function(xhr){

						$.fancybox.showLoading();
					},
					success: function(json){

							if(json.status==1){
										//var txt = "";
             							$("#tabla").html(json.message);
								}else{
									jQuery.fancybox(json.message,{
									afterClose: function() { }
									});
								}
					},
					complete: function(xhr, textStatus){
						$.fancybox.hideLoading();
					}
				});
	});


 $('#tipomapa').change(function() {
	var id= $(this).val();
			jQuery.ajax({
					url: './getCobertura',
					type: 'POST',
					async: true,
					data: {
					    servicio: id,
					},
					dataType: 'json',
				
					beforeSend: function(xhr){

						//$.fancybox.showLoading();
					},
					success: function(j){
							var options = '';
							for (var i = 0; i < j.list.length; i++) {
								options += '<option value="' + j.list[i].id + '">' + j.list[i].nombre + '</option>';
							}
							$("select#tipocobertura").html(options);
					},
					complete: function(xhr, textStatus){
						//$.fancybox.hideLoading();
					}
				});
});

 	$('#mapadep').change(function() {
 			var id= $(this).val();
			var tcobertura=$('#tipoCobertura').val();
			var actual=$('#paisActual').val();
			var servicio=$('#tipoMapa').val();

			jQuery.ajax({
					url: './getMapas',
					type: 'POST',
					async: true,
					data: {
					    departamento: id,
					    cobertura: tcobertura,
					    pais: actual,
					    service:servicio,	
					},
					dataType: 'json',
				
					beforeSend: function(xhr){

						$.fancybox.showLoading();
					},
					success: function(json){
							
							if(json.status==1){
										//var txt = "";
             							$("#tabla").html(json.message);
								}else{
									jQuery.fancybox(json.message,{
									afterClose: function() { }
									});
								}
					},
					complete: function(xhr, textStatus){
						$.fancybox.hideLoading();
					}
				});
	});
 
});
