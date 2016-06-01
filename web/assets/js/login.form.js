/*! Pluguin  login v1
*	Login de usuarios
*/

(function($){

	var methods = {

		init : function(options){
			//valores default
			var settings = {
				error_message : 'Usuario o contraseña incorrecto.'//Mensaje de alerta
			}
			jQuery.extend(settings, options);

			var id 	= jQuery(this).attr('id');
			jQuery('#inputUser').focus();

			jQuery(this).submit(function() {

				
				jQuery('#'+id+' input').removeClass('empty_param');//credenciales incorrectas
				jQuery('#'+id+' .control-group .input-controls .help-inline').remove();

				//var data_form 	= jQuery(this).serializeArray();//Obtiene valores de los campos del formulario

                if($('#inputUser').val()=="" || $('#inputPassword').val()=="" ){
                		var msn_error = '<div class="alert alert-error" style="left: 20%; position: absolute; width: 60%;">'+
											'<button data-dismiss="alert" class="close" type="button">×</button>'+
											'<center><h4>Error en el registro!</h4></strong>Ingrese todos los campos, por favor intenta de nuevo.</div>';
							jQuery('body').prepend(msn_error);
							return false;

                }

				var user=encode($('#inputUser').val());
				var pass=encode($('#inputPassword').val());
				//Ajax de validacion de campos
				jQuery.ajax({
					url: basepath+'/index.php/admin/logear',
					type: 'POST',
					async: true,
					data: {
						task: 'login',
						dataForm1: user, 
					    dataForm2: pass
					},
					dataType: 'json',
					beforeSend: function(xhr){
						//agragar loader mientras se ejecuta el Ajax
						jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%;"><img src="'+basepath+'/templates/template_home/images/img/loading.gif" /></div>');
					},
					success: function(json){
						if(json.login == 'error'){


							jQuery('#'+id+' input').addClass('empty_param');//credenciales incorrectas
							//jQuery('#'+id+' .control-group .input-controls').append( '<span class="help-inline"><i class="icon-asterisk"></i></span>');

							//Despliegue de alerta de error
							var msn_error = '<div class="alert alert-error" style="left: 20%; position: absolute; width: 60%;">'+
											'<button data-dismiss="alert" class="close" type="button">×</button>'+
											'<center><h4>Error en el registro!</h4></strong>Es posible que el usuario y/o la contraseña sean incorrectos, por favor intenta de nuevo.</div>';

							jQuery('body').prepend(msn_error);
						} else{

								if(json.login == 'success'){
							       window.location.href = basepath+'/index.php/admin/admin';
								}else{
								   window.location.href = basepath+'/index.php/admin/editor';
								}

						}

						
					},
					complete: function(xhr, textStatus){
						//elimina box de loader al finalizar Ajax
						jQuery('.box-loader-img').remove();
					}
				});
				return false; 
			});

		}
	};

	$.fn.loginform = function( method ) {  
		if ( methods[method] ) {
			//return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));  
		} else if ( typeof method === 'object' || ! method ){
			return methods.init.apply( this, arguments );  
		} else {  
			jQuery.error( 'Este método ' +  method + ' no existe en jQuery.estiloPropio' );  
		}
	};

})( jQuery );

function encode (input) {
                var _keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
                var output = "";
                var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
                var i = 0;
             
                input = utf8_encode(input);
             
                while (i < input.length) {
             
                  chr1 = input.charCodeAt(i++);
                  chr2 = input.charCodeAt(i++);
                  chr3 = input.charCodeAt(i++);
             
                  enc1 = chr1 >> 2;
                  enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                  enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                  enc4 = chr3 & 63;
             
                  if (isNaN(chr2)) {
                    enc3 = enc4 = 64;
                  } else if (isNaN(chr3)) {
                    enc4 = 64;
                  }
             
                  output = output +
                  _keyStr.charAt(enc1) + _keyStr.charAt(enc2) +
                  _keyStr.charAt(enc3) + _keyStr.charAt(enc4);
             
                }
             
                return output;
            }

function utf8_encode (string) {
    string = string.replace(/\r\n/g,"\n");
    var utftext = "";
 
    for (var n = 0; n < string.length; n++) {
 
      var c = string.charCodeAt(n);
 
      if (c < 128) {
        utftext += String.fromCharCode(c);
      }
      else if((c > 127) && (c < 2048)) {
        utftext += String.fromCharCode((c >> 6) | 192);
        utftext += String.fromCharCode((c & 63) | 128);
      }
      else {
        utftext += String.fromCharCode((c >> 12) | 224);
        utftext += String.fromCharCode(((c >> 6) & 63) | 128);
        utftext += String.fromCharCode((c & 63) | 128);
      }
 
    }
 
    return utftext;
}
