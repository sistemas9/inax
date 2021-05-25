//** funcion para cargar los datos de
//** los minimos de venta en la tabla
var table;
function cargarDatosTabla(){
	table = $('#montosMinimosTable').DataTable({
                    destroy        : true,
                    search         : true,
                    paging         : true, 
                    info           : false,
                    ajax           : {
			        	beforeSend : function(){
			        		$('body').attr('data-msj', 'Procesando Petición ...');
                            $('body').addClass('load-ajax');
			        	},
			        	processing : true,
        				serverSide : true,
			        	url        : 'minimoventasdomicilio/getdataminimos'
			        },
			        initComplete   : function(settings, json){
				        	$('body').removeClass('load-ajax');
				        },
                    columns        : [
                        { title    : 'Sucursal'                                                                              , className : "dt-body-left"},
                        { title    : 'Monto con Pago en Efectivo'                                                            , className : "dt-body-center", 
                          render   : function(data, type){ 
                                        var display = $.fn.dataTable.render.number( ',', '.', 2, '$' ).display;
                                        var formattedNumber = display( data );
                                        return formattedNumber;
                                    }
                        },
                        { title    : 'Monto mínimo con Pago en Web Pay & Cuenta Referenciada y Tarjeta de Débito y/o Credito', className : "dt-body-center",
                          render   : function(data, type){ 
                                        var display = $.fn.dataTable.render.number( ',', '.', 2, '$' ).display;
                                        var formattedNumber = display( data );
                                        return formattedNumber;
                                    }
                        },
                        { title    : 'Acciones'                                                                                , className : "dt-body-center",
                          render   : function(data, type, row, index){
                                        var checked = '';
                                        var info = 'Habiltar';
                                        if (data == 1){
                                            checked = 'checked';
                                            info = 'Deshabiltar';
                                        }
                                        html  = '';
                                        html += '<div class="row">';
                                        html += '   <div class="col-xs-6">';
                                        html += '       <div class="material-switch">';
                                        html += '           <input type="checkbox" data-idmonto="'+row[4]+'" class="custom-control-input" id="customSwitch'+index.row+'" '+checked+'>';
                                        html += '           <label class="label-success" for="customSwitch'+index.row+'"></label>';
                                        html += '       </div>';
                                        html += '   </div>';
                                        html += '   <div class="col-xs-6" title="Editar" onclick="updateminimos('+row[1]+','+row[2]+','+row[4]+')" style="cursor:pointer">';
                                        html += '       <i class="fa fa-pencil-square-o" aria-hidden="true" style="cursor:pointer; font-size: 2rem;" title="Editar" ></i>';
                                        html += '   </div>';
                                        html += '</div>'

                                        return html;
                                    },
                        }
                    ],
                    language       : {
                        url        : "../application/assets/js/spanish.json"
                    }
                });
}

//** fucion para actualizar los datos de los minimos de venta
//**
function updateminimos(minEfe,minTarj,idMonto){
    var sucursal = 'CHIHUAHUA';
    var montoefe = minEfe;
    var montotarje = minTarj;
    var idmonto = idMonto;
    html  = '';
    html += '<div class="row">';
    html += '   <h1>EDITAR SUCURSAL '+sucursal+'</h1>';
    html += '</div>';
    html += '<div class="row">';
    html += '   <div class="col-xs-6">';
    html += '       <label for="montoEfectivo">Monto Efectivo</label>';
    html += '       <input type="number" min="0.1" id="montoEfectivo" value="'+montoefe+'"/>';
    html += '   </div>';
    html += '   <div class="col-xs-6">';
    html += '       <label for="montoTarjeta">Monto Tarjeta</label>';
    html += '       <input type="number" min="0.1" id="montoTarjeta" value="'+montotarje+'"/>';
    html += '   </div>';
    html += '   <input type="hidden" id="idMonto" value="'+idmonto+'">';
    html += '</div>';
    swal({
        title             : 'Atencion',
        html              : html,
        type              : 'warning',
        allowOutsideClick : false,
        showCancelButton  : true,
        confirmButtonText : 'Actualizar',
        cancelButtonText  : 'Cancelar',
        preConfirm        : function(result){
                                return new Promise(function(resolve,reject){
                                    var montoEfe  = $('#montoEfectivo').val();
                                    var montoTarj = $('#montoTarjeta').val();
                                    if ( eval(montoEfe) == 0 || eval(montoTarj) == 0){
                                        reject('El monto debe ser mayor a $0.00');
                                    }
                                    resolve(true);
                                })
                            }
    }).then(function(res){
        if (res){
            var montoEfe  = $('#montoEfectivo').val();
            var montoTarj = $('#montoTarjeta').val();
            var idMonto   = $('#idMonto').val();
            $.ajax({
                url      : 'minimoventasdomicilio/updateminimos',
                type     : "post", 
                dataType : "json", 
                data     : {montoEfe,montoTarj,idMonto},
                success  : function(res){
                    if (res > 0){
                        swal('Exito!','Datos actualizados correctamente','success').then(function(res){
                            if(res){
                                table.ajax.reload();
                            }
                        });
                    }else{
                        swal('Error', 'Ocurrio un error al actualizar los datos','error');
                    }
                }
            });
        }
    },
    function (dismiss){
        if (dismiss='cancel'){
            console.log('Cancelo actualizar');
        }
    });
}

//*Evento para habiltar o deshabiltar los minimos de venta
//*
$(document).on('click', '.custom-control-input', function(){
    var idMonto = $(this).data('idmonto');
    var status = 0;
    if ( $(this).is(':checked') ){
        status = '1';
    }
    $.ajax({
        url      : 'minimoventasdomicilio/updatestatus',
        type     : "post", 
        dataType : "json", 
        data     : {idMonto, status},
        success  : function(res){
            if (res > 0){
                swal('Exito!','Estado actualizado correctamente','success').then(function(res){
                    if(res){
                        table.ajax.reload();
                    }
                });
            }else{
                swal('Error', 'Ocurrio un error al actualizar los datos','error');
            }
        }
    });
})