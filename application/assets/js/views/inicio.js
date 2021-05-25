var seccionOV = "";
var sitioCliente = "";
$(document).ready(function() {
    gaTrigger('Inicio','Ready',userBranchOffice);
});
var parcelLabelSubmited = false;
editionPromoCodeSelected = "None";
promoCodeBeenRemoved = false;

function loaderSwal(){
    // Swal.fire({
    //   title: 'Cargando',
    //   text: 'Por favor espere',
    //   imageUrl: '../application/assets/img/loading.gif',
    //   imageWidth: 200,
    //   imageHeight: 200,
    //   imageAlt: 'Custom image',
    //   animation: false,
    //   allowOutsideClick: false
    // })
    // $('.swal2-actions').hide();
}

    $('#proposito').change(function(e){
        proposito = $(this).val();
        var ov = $("#OrdenVentaRem").val();
        $.ajax({
                url: "inicio/parcharpro",
                type: "POST",
                dataType: "json",
                data: {ov:ov,proposito:proposito,dlvmode:$('#modoentregaResumen').html(),paymentmode:$('#cargodesc').html()},   
                success: function (data){
                    Materialize.toast('Proposito cambiado en la OV', 3000);
                }
            });
                
    });

// sandboxValidate()

$(document).on('click','#CancelarExistencias,#CancelarActualizarEncabezado',function(){
        $('#F2pressed').val('0');
        var seleccionado = $('#LineaArticulo').val();
        var flag=$("#edicionExis").val();
        if(flag!='edit'){
            $('#descripcion'+seleccionado).val('');
            $('#cantidad'+seleccionado).val('');
            $('#unidad'+seleccionado).val('');
            $('#item'+seleccionado).val('');
            $('#sitio'+seleccionado).val('');
            $('#almacen'+seleccionado).val('');
        }
        $('#item'+seleccionado).focus();
        $('#modal1').closeModal();
    });
    $(document).on('click','.checkFam, .checkFamDetVta',function(e){
        var itemFam = {};
        var row = '';
        var cantFam = $('.checkFam:checked').length;
        var cantFamDet = $('.checkFamDetVta:checked').length;
        if (cantFam > 0){
                $('#agregarItemsFamilia').html('Agregar');
        }else{
            $('#agregarItemsFamilia').html('Cerrar');
        }
        if (cantFamDet > 0){
            $('#btnAgregarDetVta').html('Agregar');
        }else{
            $('#btnAgregarDetVta').html('Cerrar');
        }
        if ( $(this).is(':checked') && $(this).hasClass('checkFam') ){
            row = $(this).parents('tr');
            itemFam = {
                item : $(row).find('td').eq(0).html(),
		nombre : $(row).find('td').eq(1).html(),
		sitio : $("#sitioLineas").val(),
		almacen : $(row).find('td').eq(2).html(),
		cliente : $("#claveclte").val(),
		qty : '1',
                localidad : 'GRAL',
                unidad : $(this).attr('data-unidad'),
                lineaArticulo : $(row).index()
            };
            familiaArr.push(itemFam);
        }
        else if ( $(this).is(':checked') && $(this).hasClass('checkFamDetVta') ){
            row = $(this).closest('tr');
            itemFam = {
                item : $(row).find('td').eq(2).html(),
                nombre : $(row).find('td').eq(4).html(),
                sitio : $("#sitioLineas").val(),
                almacen : $(row).find('td').eq(3).html(),
                cliente : $("#claveclte").val(),
                qty : '1',
                localidad : 'GRAL',
                unidad : $(this).attr('data-unidad'),
                lineaArticulo : $(row).index()
            };
            familiaArr.push(itemFam);
        }
        else if ( !$(this).is(':checked') && $(this).hasClass('checkFamDetVta') ){
            row = $(this).closest('tr');
            itemFam = {					
                item : $(row).find('td').eq(2).html(),
                nombre : $(row).find('td').eq(4).html(),
                sitio : $("#sitioLineas").val(),
                almacen : $(row).find('td').eq(3).html(),
                cliente : $("#claveclte").val(),
                qty : '1',
                localidad : 'GRAL',
                unidad : $(this).attr('data-unidad'),
                lineaArticulo : $(row).index(),
            };
            var elementPos = familiaArr.map(function(x) {return x.item; }).indexOf(itemFam.item);
            familiaArr.splice(elementPos,1);
        }
        else if ( !$(this).is(':checked') && $(this).hasClass('checkFam') ){
            row = $(this).parents('tr');
            itemFam = {					
                item : $(row).find('td').eq(0).html(),
                nombre : $(row).find('td').eq(1).html(),
                sitio : $("#sitioLineas").val(),
                almacen : $(row).find('td').eq(2).html(),
                cliente : $("#claveclte").val(),
                qty : '1',
                localidad : 'GRAL',
                unidad : $(this).attr('data-unidad'),
                lineaArticulo : $(row).index()
            };
            var elementPos = familiaArr.map(function(x) {return x.item; }).indexOf(itemFam.item);
            familiaArr.splice(elementPos,1);
        }
    });
    $("#familia").on('keydown',function(ev){
        if (ev.which === 13){
            $("#BuscarFamilia").click();
        }
    });

    $(document).on('click',"#ConvertirCot-Ov",function(e){
        gaTrigger('SetSalesQuotationToSalesOrder','entity',userBranchOffice);
        gaTrigger('SalesOrderHeadersV2','entity',userBranchOffice);
        if (!parcelLabelSubmited && $('#modoentregaResumen').html() == 'PAQUETERIA') {
            Materialize.toast('Favor de crear etiqueta', 3000, 'red');
            return;
        }
        verifica();
        confirmaDocumento('');
        // var htmlCYR = '';
        showCyRDialog().then(function(res){
            if (res){
                if(localStorage.getItem('bandera')==='si' && !$( "#ConvertirCot-Ov" ).hasClass( "disabled" ) ){
                    var cot = $('#DocumentId').val();//$("#DocumentoConfirmado").val();
                    $('#ATP_Cot').val(cot);
                    var cta = $("#ctaBanco").val();
                    $('#preloaderConv').show();
                    promoCodeEnable = false;
                    if ($('#promoCode').val() != 'None') {
                        promoCodeEnable = true;
                        promoCodeName = promoCodes[$('#promoCode').val()].PromoCode;
                    }
                    var datosConv = {'cliente':$('#claveclte').val(),
                                     'cotizacion': cot,
                                     'sitio' : $('#sitioLineas option:selected').val(),
                                     'dlvMode':$('#entregalineas').val(),
                                     'condiEntrega' : $('#condiEntrega').val(),
                                     'moneda' : $('#monedalineas').val(),
                                     'metodoPagoCode':$('#pagolineas option:selected').attr('data-paymmode'),
                                     'metodoPago' : $("#pagolineas option:selected").text(),
                                     'proposito':$("#proposito").val(),
                                     'paymentTermName' : $('#paytermArt option:selected').val(),
                                     'cuenta': cta,
                                     'origenVenta': $("#origenV").val(),
                                     'encabezadoov': $("#cabeceraOriginal").val(),
                                     'clientname': $("#desccliente").text(),
                                     'saleresponsible': $("#vendedor").text(),
                                     'encabezadoov': $("#cabeceraOriginal").val(),
                                     'comentarioCabecera':$("#comentariosCabecera").val(),
                                     'promoCodeName': $('#promoCode').val() != 'None' ? promoCodes[$('#promoCode').val()].PromoCode : '',
                                     'promoCodeEnable': promoCodeEnable
                                    }
                    var condiLinea = $('#paytermArt option:selected').val(); //
                    if ( $('#pagolineas option:selected').data('paymmode') == '99' && ( condiLinea != 'CONTADO' && condiLinea != 'CONTADO PD' )  ) {
                        var tot = $('#totalResumen').html().replace('$','');
                        revisarLimite('',$('#claveclte').val(),tot,'PPD').then(function(res){
                            var consumido = Number(res.total) - Number(res.monto);
                            consumido += Number(res.montocoti);
                            var porcentaje = Number(consumido) / Number(res.total) * 100;
                            if( porcentaje <= 110 ){
                                $.ajax({
                                    url: "inicio/convertirCotOV",
                                    type: "POST",
                                    dataType: "json",
                                    data: datosConv,
                                    beforeSend: function (xhr) {
                                        Materialize.toast('Procesando Orden de venta', 3000);
                                        $("#ConvertirCot-Ov").addClass("disabled");
                                        // loaderSwal();
                                    },    
                                    success: function (data){
                                        if(data.cambio == true){                        
                                           swal('Aviso','Se encontraron diferencias entre la orden de venta y la cotización, favor de corroborar que las cantidades esten correctas', 'warning');
                                        }
                                        if(data.status != 'Fallo'){
                                                dimensionesAtp = atpDimensions;
                                                dimensionesLin = lideartDimensions;
                                                combinado = validarAlmacenes();
                                                if (!combinado.combinado){
                                                    if (combinado.tipo == 'EXHB'){
                                                        $.ajax({
                                                            url: "inicio/setdimensionlines",
                                                            type: "POST",
                                                            dataType: "json",
                                                            data: {dimLin : dimensionesLin,dimAtp : dimensionesAtp, ov : data.msg},
                                                            beforeSend: function (xhr) {
                                                                Materialize.toast('Procesando Orden de venta', 3000);
                                                                $("#ConvertirCot-Ov").addClass("disabled");
                                                            },
                                                            success : function(data2){
                                                                console.log(data2)
                                                            },
                                                            error :function (jqHKR,exception){
                                                                Materialize.toast('WebService Error!.'+catchError(jqHKR,exception), 3000);
                                                                $("#ConvertirCot-Ov").removeClass("disabled");
                                                            }
                                                        });
                                                    }
                                                }
                                                $("#ConvertirCot-Ov").hide();
                                                $("#ConvertirCot-Ov").removeClass("disabled");
                                                Materialize.toast('Cotización convertida a Orden de Venta!', 3000);
                                                
                                                $("#OrdenVentaRem").val(data.msg);                    
                                                $('#DocumentId2').val(data.msg);
                                                $('#DocumentIdResumen').html(data.msg);
                                                $('#tipoDocument').html('Orden de Venta:');
                                                $('#resumenDivTitle').html('Resumen de la Orden de Venta: '+$('#DocumentId2').val());
                                                $("#cuentaCompletoOV").html($('#DocumentId2').val()+':Cliente '+$("#claveclte").val()+' - '+$("#cliente").val());
                                                $('#ATP_Cot').val($('#DocumentId2').val());
                                                //$('#editar2').html("");
                                                $("#DocumentId").val($('#DocumentId2').val());
                                                $("#DocumentType").val('ORDVTA');
                                                $("#DocumentType2").val('ORDVTA');
                                                $('#ConvertirCot-Ov').removeClass('disable_a_href');
                                                $('#propositoContainer').show();
                                                $('#proposito').val(data.proposito);
                                                $('#proposito').material_select('destroy');
                                                $('#proposito').material_select();
                                                var condi = $("#condiEntrega").val();
                                                var dlvmode = $('#modoentregaResumen').html();
                                                var efect = $('#cargodesc').html();
                                                $('#labelDivButton').hide();
                                                if(condi=='CREDITO'){                                        
                                                    $.ajax({
                                                        url: "inicio/mandaralerta",
                                                        type: "POST",
                                                        dataType: "json",
                                                        data: {ov : data.msg, cliente:$("#claveclte").val()},
                                                        success : function(data2){
                                                            console.log(data2)
                                                        }  
                                                    });
                                                }
                                            if (havePermision(14)) {
                                                if (condi == 'CONTADO') {
                                                    if (dlvmode == 'DOMICILIO') {
                                                        if (efect == '04' || efect == '28') {
                                                            $("#GenerarWPAY").show()
                                                            $("#Facturar").show();
                                                            $("#GenerarREM").hide();
                                                        } else if (efect == '01') {
                                                            $("#GenerarREM").show();
                                                        }
                                                        else if (efect == '03') {
                                                            $("#Facturar").hide();
                                                            $("#Remisionar_Facturar").hide();
                                                            $("#GenerarREM").hide();
                                                        }
                                                        else {
                                                            $("#Facturar").show();
                                                            $("#GenerarREM").show();
                                                        }
                                                    } else {
                                                        $("#Facturar").show();
                                                        if (efect == '04' || efect == '28') {
                                                            $("#GenerarWPAY").show()
                                                            $("#GenerarREM").hide();
                                                        }
                                                    }
                                                } else {
                                                    $("#Facturar").show();
                                                    $("#GenerarREM").show();
                                                    if (efect == '04' || efect == '28') {
                                                        $("#GenerarWPAY").show()
                                                    }
                                                }
                                            }
                                            else {
                                                if (condi == 'CREDITO') {
                                                    $("#GenerarREM").show();
                                                } else {
                                                    $("#Facturar").hide();
                                                    $("#GenerarREM").hide();
                                                    if (efect == '04' || efect == '28') {
                                                        $("#GenerarWPAY").show()
                                                    }
                                                }

                                            }
                                            // $('#QuotationId').val();

                                            $('#ImprimirCotizacion').html('<i class="material-icons left">picture_as_pdf</i> IMPRIMIR OV');
                                            $('#QuotationId').val($('#DocumentId').val());
                                            $('#ImprimirCotizacion').parent().attr('action', 'impresion-orden');
                                            $('#ImprimirCotizacion').hide();
                                            //refreshLines();
                                        } else {
                                            if (data.bloqueado == true) {
                                                Materialize.toast('Algo salio mal: ' + data.msg, 5000, 'red');
                                                $('#DocumentIdResumen').html(data.msg);
                                                $('#DocumentIdResumen').css('font-size', '15px');
                                                $('#DocumentIdResumen').css('color', 'red');
                                            } else {
                                                Materialize.toast('Algo salio mal: ' + data.msg, 5000, 'red');
                                                $('#DocumentIdResumen').html(data.msg);
                                                $('#DocumentIdResumen').css('font-size', '15px');
                                                $('#DocumentIdResumen').css('color', 'red');
                                            }
                                        }
                                        $('#preloaderConv').hide();
                                        // swal.close();
                                    },
                                    error: function (jqHKR, exception) {
                                        Materialize.toast('WebService Error!.' + catchError(jqHKR, exception), 3000);
                                        $("#ConvertirCot-Ov").removeClass("disabled");
                                    }
                                });
                            }else{
                                $('#ConvertirCot-Ov').addClass('disabled');
                                swal('Atencion','No es posible convertir cotización, comunicarse a credito y cobranza por limite de credito excedido','warning');
                            }
                        });
                    }else{
                        $.ajax({
                            url: "inicio/convertirCotOV",
                            type: "POST",
                            dataType: "json",
                            data: datosConv,
                            beforeSend: function (xhr) {
                                Materialize.toast('Procesando Orden de venta', 3000);
                                $("#ConvertirCot-Ov").addClass("disabled");
                                // loaderSwal();
                            },    
                            success: function (data){
                                if(data.cambio == true){                        
                                   swal('Aviso','Se encontraron diferencias entre la orden de venta y la cotización, favor de corroborar que las cantidades esten correctas', 'warning');
                                }
                                if(data.status != 'Fallo'){
                                        dimensionesAtp = atpDimensions;
                                        dimensionesLin = lideartDimensions;
                                        combinado = validarAlmacenes();
                                        if (!combinado.combinado){
                                            if (combinado.tipo == 'EXHB'){
                                                $.ajax({
                                                    url: "inicio/setdimensionlines",
                                                    type: "POST",
                                                    dataType: "json",
                                                    data: {dimLin : dimensionesLin,dimAtp : dimensionesAtp, ov : data.msg},
                                                    beforeSend: function (xhr) {
                                                        Materialize.toast('Procesando Orden de venta', 3000);
                                                        $("#ConvertirCot-Ov").addClass("disabled");
                                                    },
                                                    success : function(data2){
                                                        console.log(data2)
                                                    },
                                                    error :function (jqHKR,exception){
                                                        Materialize.toast('WebService Error!.'+catchError(jqHKR,exception), 3000);
                                                        $("#ConvertirCot-Ov").removeClass("disabled");
                                                    }
                                                });
                                            }
                                        }
                                        $("#ConvertirCot-Ov").hide();
                                        $("#ConvertirCot-Ov").removeClass("disabled");
                                        Materialize.toast('Cotización convertida a Orden de Venta!', 3000);
                                        
                                        $("#OrdenVentaRem").val(data.msg);                    
                                        $('#DocumentId2').val(data.msg);
                                        $('#DocumentIdResumen').html(data.msg);
                                        $('#tipoDocument').html('Orden de Venta:');
                                        $('#resumenDivTitle').html('Resumen de la Orden de Venta: '+$('#DocumentId2').val());
                                        $("#cuentaCompletoOV").html($('#DocumentId2').val()+':Cliente '+$("#claveclte").val()+' - '+$("#cliente").val());
                                        $('#ATP_Cot').val($('#DocumentId2').val());
                                        //$('#editar2').html("");
                                        $("#DocumentId").val($('#DocumentId2').val());
                                        $("#DocumentType").val('ORDVTA');
                                        $("#DocumentType2").val('ORDVTA');
                                        $('#ConvertirCot-Ov').removeClass('disable_a_href');
                                        $('#propositoContainer').show();
                                        $('#proposito').val(data.proposito);
                                        $('#proposito').material_select('destroy');
                                        $('#proposito').material_select();
                                        var condi = $("#condiEntrega").val();
                                        var dlvmode = $('#modoentregaResumen').html();
                                        var efect = $('#cargodesc').html();
                                        $('#labelDivButton').hide();
                                        if(condi=='CREDITO'){                                        
                                            $.ajax({
                                                url: "inicio/mandaralerta",
                                                type: "POST",
                                                dataType: "json",
                                                data: {ov : data.msg, cliente:$("#claveclte").val()},
                                                success : function(data2){
                                                    console.log(data2)
                                                }  
                                            });
                                        }
                                    if (havePermision(14)) {
                                        if (condi == 'CONTADO') {
                                            if (dlvmode == 'DOMICILIO') {
                                                if (efect == '04' || efect == '28') {
                                                    $("#GenerarWPAY").show()
                                                    $("#Facturar").show();
                                                    $("#GenerarREM").hide();
                                                } else if (efect == '01') {
                                                    $("#GenerarREM").show();
                                                }
                                                else if (efect == '03') {
                                                    $("#Facturar").hide();
                                                    $("#Remisionar_Facturar").hide();
                                                    $("#GenerarREM").hide();
                                                }
                                                else {
                                                    $("#Facturar").show();
                                                    $("#GenerarREM").show();
                                                }
                                            } else {
                                                $("#Facturar").show();
                                                if (efect == '04' || efect == '28') {
                                                    $("#GenerarWPAY").show()
                                                    $("#GenerarREM").hide();
                                                }
                                            }
                                        } else {
                                            $("#Facturar").show();
                                            $("#GenerarREM").show();
                                            if (efect == '04' || efect == '28') {
                                                $("#GenerarWPAY").show()
                                            }
                                        }
                                    }
                                    else {
                                        if (condi == 'CREDITO') {
                                            $("#GenerarREM").show();
                                        } else {
                                            $("#Facturar").hide();
                                            $("#GenerarREM").hide();
                                            if (efect == '04' || efect == '28') {
                                                $("#GenerarWPAY").show()
                                            }
                                        }

                                    }
                                    // $('#QuotationId').val();

                                    $('#ImprimirCotizacion').html('<i class="material-icons left">picture_as_pdf</i> IMPRIMIR OV');
                                    $('#QuotationId').val($('#DocumentId').val());
                                    $('#ImprimirCotizacion').parent().attr('action', 'impresion-orden');
                                    $('#ImprimirCotizacion').hide();
                                    //refreshLines();
                                } else {
                                    if (data.bloqueado == true) {
                                        Materialize.toast('Algo salio mal: ' + data.msg, 5000, 'red');
                                        $('#DocumentIdResumen').html(data.msg);
                                        $('#DocumentIdResumen').css('font-size', '15px');
                                        $('#DocumentIdResumen').css('color', 'red');
                                    } else {
                                        Materialize.toast('Algo salio mal: ' + data.msg, 5000, 'red');
                                        $('#DocumentIdResumen').html(data.msg);
                                        $('#DocumentIdResumen').css('font-size', '15px');
                                        $('#DocumentIdResumen').css('color', 'red');
                                    }
                                }
                                $('#preloaderConv').hide();
                                // swal.close();
                            },
                            error: function (jqHKR, exception) {
                                Materialize.toast('WebService Error!.' + catchError(jqHKR, exception), 3000);
                                $("#ConvertirCot-Ov").removeClass("disabled");
                            }
                        });
                    }
                }
            }
        }, function(dismiss){
            swal('Precaución!',dismiss,'warning');
        });
    });

$(document).on('click','.BatchAvailable',function(){
    var $tr = $(this).closest('tr');
    var myRow = $tr.index() + 1;
    var item = $('#item'+myRow).val();
    var sitio = $('#sitio'+myRow).val();    
    if (item === "" || sitio==="") {    
        $("#dropdownLote"+myRow).removeClass("active");
        $("#dropdownLote"+myRow).css('display', "none");
        Materialize.toast('Artículo o Sitio vacios, favor de ingresar los datos para visualizar los lotes!', 3000);	
    } else{
        DisponibleLote(item,sitio,myRow);
    }			
});
        $(document).on('click','.lote-dropdown-content', function(event){
		    //The event won't be propagated to the document NODE and 
		    // therefore events delegated to document won't be fired
		    event.stopPropagation();
		});

		$(document).on('click','.comentarios-dropdown-content', function(event){
		    //The event won't be propagated to the document NODE and 
		    // therefore events delegated to document won't be fired
		    event.stopPropagation();
		});

		$(document).on('click','.punitario-dropdown-content', function(event){
		    //The event won't be propagated to the document NODE and 
		    // therefore events delegated to document won't be fired
		    event.stopPropagation();
		});
                
                $(document).on('click','.dropdown-button',function(event){
			//click del text area de las lineas
			var elem = document.getElementById($(this).attr('id'));
			pos = elem.getBoundingClientRect();
			str = $(this).parent().find('.coment').attr('style');
			if (elem.id != 'cliente'){
				var opc = $.map(str.split(';'), function(item,index){
					var strtemp = '';
					if ( item.indexOf('left') >= 0 ){
						var temp = item.split(':');
						temp[1] = pos.left+'px !important';
						var  t2 = temp.join();
						t2 = t2.replace(',',':');
						strtemp += t2;
					}else if( item.indexOf('top') >= 0 ){
						var tempTop = item.split(':');
						tempTop[1] = pos.top - 19;
						var t3 = tempTop.join();
						t3 = t3.replace(',',':');
						strtemp += t3;
					}else{
						strtemp += item;
					}
					return strtemp;
				});
				opc = opc.join();
				opc = opc.replace(/,/g,';');
				$(this).parent().find('.coment').attr('style',opc);
			}
		});
                $(document).keyup(function(e) {
			if (e.which === 17){
				CtrlPressed = false;           		
        		$("#item"+ $("#NumFilas").val()).focus();		
        	}
		});
                ////////////////////////drop direcciones/////////////////////////////////////////////////////////
		$(document).on('click','.dirsClte',function(){
			activates  = $(this).attr('data-activates');
			offsetTop  = $(this)[0].getBoundingClientRect().bottom;
			offsetLeft = $(this)[0].getBoundingClientRect().left;
			offsetDiv  = $('#articulosDiv').offset().top;
			elemTop    = offsetTop - offsetDiv;
			if ( !$('#'+activates).is(':visible') ){
				$('#'+activates).css('position','fixed');
				$('#'+activates).css('opacity','1');
				$('#'+activates).css('top',offsetTop);
				$('#'+activates).css('left',offsetLeft);
				$('#'+activates).show();
			}
                        else{
				$('#'+activates).hide();
				$('#'+activates).css('position','');
			}
		});
                 ///////////////////////////drop cambio precio//////////////////////////////////////////////////////
		$(document).on('click','.preciovta',function(){
			activates = $(this).attr('data-activates');
			offsetTop = $(this).parents('td')[0].getBoundingClientRect().bottom;
			offsetDiv = $('#articulosDiv').offset().top;
			elemTop   = offsetTop - offsetDiv;
			$('#'+activates).css('position','fixed');
			$('#'+activates).css('top',elemTop);
			$('#'+activates).show();
		});
                $(document).on('keyup','.punitario-dropdown-content',function(ev){
			if (ev.which == 27){
				$(this).hide();
				$(this).css('position','');
			}
		});
                $(document).on('click','.closeCambioPrecio',function(){
			$(this).parents('div.punitario-dropdown-content').hide();
			$(this).parents('div.punitario-dropdown-content').css('position','');
		});
                ////////////////////////drop comentarios/////////////////////////////////////////////////////////
		$(document).on('click','.tacoment',function(){
			activates = $(this).attr('data-activates');
			offsetTop = $(this).parents('td')[0].getBoundingClientRect().bottom;
			offsetDiv = $('#articulosDiv').offset().top;
			elemTop   = offsetTop - offsetDiv;
			$('#'+activates).css('position','fixed');
			$('#'+activates).css('top',elemTop);
			$('#'+activates).show();
		});
                $(document).on('keyup','.comentarios-dropdown-content',function(ev){
			if (ev.which == 27){
				$(this).hide();
				$(this).css('position','');
			}
		});

		$(document).on('click','.closeComentLinea',function(){
			$(this).parents('div.comentarios-dropdown-content').hide();
			$(this).parents('div.comentarios-dropdown-content').css('position','');
		});
                
		///////////////////////end drop comentario/////////////////////////////////////////////////////////
		////////////////////////drop lote/////////////////////////////////////////////////////////
		$(document).on('click','.BatchAvailable',function(){
			activates = $(this).attr('data-activates');
			offsetTop = $(this).parents('td')[0].getBoundingClientRect().bottom;
			offsetDiv = $('#articulosDiv').offset().top;
			elemTop   = offsetTop - offsetDiv;
			$('#'+activates).css('position','fixed');
			$('#'+activates).css('top',elemTop);
			$('#'+activates).show();
		});

		$(document).on('keyup','.lote-dropdown-content',function(ev){
			if (ev.which == 27){
				$(this).hide();
				$(this).css('position','');
			}
		});

		$(document).on('click','.closeLoteLinea',function(){
			$(this).parents('div.lote-dropdown-content').hide();
			$(this).parents('div.lote-dropdown-content').css('position','');
		});
		///////////////////////end drop lote/////////////////////////////////////////////////////////
		
		$(window).resize(function(){
			var scrsize = $(window).width() - 250;
			scrsize = scrsize+'px';
			$('#rowResumenPartidas').css('max-width',scrsize);
		});
		/////iniciar el panel navegar//////////////////
                $('#breadInicio').show(function(){
                    $(this).attr('style','display:inline;color:white');
                });
                $('#breadResumen').hide();
                $('#editarDocument').hide();
                if ($('#divInicioSesion').hasClass('s5')){
                    $('#divInicioSesion').removeClass('s5');
                    $('#divInicioSesion').addClass('s9');
                }else if ($('#divInicioSesion').hasClass('s9')){
                   $('#divInicioSesion').removeClass('s9');
                   $('#divInicioSesion').addClass('s5');
                }
                $('.modal-trigger').leanModal();
                $('#articulosDiv').hide();
                $('#resumenTest').hide();
                $('#claveclte').focus();
                function dobleItem(item){
                    var band = false;
			var rows = $('#articulos tbody').find('tr').filter(':visible').filter(':not(.emptyRow)');
			$(rows).each(function(){
				var item1 = $(this).find('td').find('.item').val();
				if (item1 === item){
					band = true;
				}
			});
			return band;
		}
                //Evita que al dar Enter se envíe el formulario	
                $(document).keydown(function(event){
		    if(event.keyCode == 13) {
		      event.preventDefault();
		      return false;
		    }
		});
                //Accesos directos a agregar y quitar linea
		var CtrlPressed = false;
		$(document).keydown(function(e) {
			if (e.which === 17){
				CtrlPressed = true; 
                            }
        	if (CtrlPressed && e.which === 45){
        		agregarLinea(autocomp_opt,bandAgregarFam);
        	}            	
        	else if (CtrlPressed && e.which === 46){                       	
        		quitarLinea();
        	}            	
		});
                //Manda a llamar la función de agregar linea
		$("#AgregarLinea").on('click',function(){
			agregarLinea(autocomp_opt,bandAgregarFam);
		});

		//manda llamar la funcion de quitar linea
		$("#QuitarLinea").on('click',function(){			
			quitarLinea();
		});
function buscarFamilia(){   
    $("#ExistenciaFamilia > tbody").html("");
    var len = $("#familia").val().length;
    if (len>=4) {
        var sitio = $("#sitioLineas").val();
        var familia = $("#familia").val();
        if (sitio != ''){
            $.ajax({url: "inicio",type: "get",dataType: "json",data: { "familia": familia, "sitio": sitio, "token":"existenciasFamilia", "sitioCliente": sitioCliente, "seccionOV": seccionOV },
                beforeSend: function (xhr) {
                    Materialize.toast('Buscando familias!', 2000);
                },
                success: function (data){
                    $("#ExistenciaFamilia > tbody").html("");
                    if (!data["noresult"]){
                        for (var i=0;i<data.length;i++){
                            var disponible = parseFloat(data[i].Existencia);
                            $("#ExistenciaFamilia > tbody").append('<tr><td>' + data[i].Articulo + '</td><td>' + data[i].DescripcionArticulo +'</td><td>' + data[i].Almacen +'</td><td>' + disponible.toFixed(2) +'</td><td><input type="checkbox" data-item="'+data[i].Articulo+'" data-unidad="'+data[i].Unidad+'" class="col l1 s1 m1 checkFam" id="itemCheckFam'+i+'"/><label for="itemCheckFam'+i+'"></label></td></tr>');
                        }					        
                    }
                    else{
                        $("#ExistenciaFamilia > tbody").append('<tr><td colspan="5" class="center-align">'+ data["noresult"] +'</td></tr>');
                    }
                }, error: function(err){
                    console.log("error, ", JSON.stringify(err));
                }
            });
        }else{
            Materialize.toast('Favor de seleccionar sitio para cliente!.',3000);
        }
    }else{
        Materialize.toast('La familia debe ser de minimo 4 caracteres!.',3000);
    }
        
}
//Realiza el cambio de moneda para su posterior actualización de la cabecera y si existen lineas actualiza los precios
$("#monedalineas").on('change',function(){
    //Se obtiene el numero de artículos capturados
    var rows = $("#articulos > tbody >tr").length;
    for (var i = 1; i < rows + 1; i++) {
        var ev = $.Event({'type':'input','enviarExistencias':'1'});
        $("#cantidad"+i).trigger(ev);
    }
});
//Realiza el cambio de forma de pago para su posterior actualización de la cabecera y si existen lineas actualiza los precios segun el cargo
$("#pagolineas").on('change',function(e){
    var rows = $("#articulos > tbody >tr").length;
    if (rows === 0) {
        $("#PorcentCargo").val($(this).val());
        $("#FormaPagoLineas").val($("#pagolineas option:selected").text());
    } else{
        for (var i = 1; i < rows + 1; i++) {
            if ( $('#item'+i).val() !== '' ){
                $("#PorcentCargo").val($(this).val());
                var ev = $.Event({'type':'input','enviarExistencias':'1'});
                $("#cantidad"+i).trigger(ev);
                $("#FormaPagoLineas").val($("#pagolineas option:selected").text());
            }else{
                Materialize.toast('Artículo o Sitio vacios, favor de ingresar los datos para aplicar el cargo!', 3000);						
            }
        }	
    }
    $('#TarjetasParticipantes').hide();
    var option = $('#pagolineas option:selected').data('paymmode')
    if( option == '04-1' || option == '04-2' || option == '04-3' || option == '04-4'){
        $('#TarjetasParticipantes').show();
    }
});
function selectAll(){
    var t=$('#articulos >tbody >tr').length;
    for(var i=0;t>=i;i++){
        if(i>0){ 
            $("#LblNumLinea"+i).click();
        }
    }
}
function imprimirRemi(html,Remi){
    var styleprint = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'
    +'<style>.tr {display: flex;justify-content: space-between;font-size: 12px;font-weight: bold;}'+
    '.clave {width: 15%;}'+
    '.description { width: 63%;}'+
    '.pedido {width: 7%;}'+
    '.unidad {width: 7%;}'+
    '.entregado {width: 8%;}'+
    '.tr > div {text-align: center;}'+
    '*{font-family: arial !important;}'+
    '.description {text-align: left !important;}'+
    '.xxx strong {text-decoration: underline;}'+
    '.xxx span {font-weight: normal !important;}'+
    '.description p:last-child {font-weight: normal;}'+
    '.articulo {margin: 0 0 5px 0;}'+
    '.description > p {margin: 0 0 5px 0;}'+
    '.xxx > span {font-size: 10px;}'+
    'svg#remiTemp {}svg#remiTemp {max-width: 10% !important;transform: translateY(-28%);}</style>';
    html = styleprint+html;
    $("#inputHtml").val(html);
    $('#testForm input#remision').val(Remi);
    $("#testForm").submit();
    $('#preloaderRemi').hide();
}

function rightclick() {
    var e = window.event;       
    var cantThinkOfAName = document.getElementById("rightclicked");
    cantThinkOfAName.style.display = "block";
    cantThinkOfAName.style.left = mouseX(e) + "px";
    cantThinkOfAName.style.top = mouseY(e) + "px";
}
function mouseX(evt) {
    if (evt.pageX) {
        return evt.pageX;
    } else if (evt.clientX) {
        return evt.clientX + (document.documentElement.scrollLeft ?
            document.documentElement.scrollLeft :
            document.body.scrollLeft);
    } else {
        return null;
    }
}

function mouseY(evt) {
    if (evt.pageY) {
        return evt.pageY;
    } else if (evt.clientY) {
        return evt.clientY + (document.documentElement.scrollTop ?
        document.documentElement.scrollTop :
        document.body.scrollTop);
    } else {
        return null;
    }
}
function mapeoClte(valor,arreglo,request){
    return $.map(arreglo,function(clte){
        posNombre = clte.nombre.indexOf(valor.toUpperCase());
    	posArticu = clte.value.indexOf(valor.toUpperCase());
    	if (request.term.indexOf('*') < 0){
                if ( (posNombre >= 0) || (posArticu >= 0) ){
                    return clte;
                }
    	}
        else{
            if ( (posNombre >= 0) ){
		      return clte;
            }
    	}
    });
}

function existe(arreglo,valor){ 
    var band = false;
    $(arreglo).each(function(){
        if (this[0].LINENUM == valor){
            band = true;
	}
    });
    return band;
}
$(document).keypress(function (evt){
    var evento=evt.keyCode;
    switch(evento){
        case 17: $('#SubmitHeader').click();
            break;
    }
});
    /////////////////////////etiquetas//////////////////////////////////////////////////////////////////////
function mostrarModalEti(imgPath = '../application/assets/img') {
    if (parcelLabelSubmited) {
        Materialize.toast('Esta cotizacion ya cuenta con una etiqueta, se actualizara por la generada', 3000, 'blue');
    }
    var sitio = $("#sitioclte").val();
    var ov = $('#DocumentId2').val();
    console.log(ov);
    console.log(ov);
    $('#modalEtiquetas').openModal({
        dismissible: false,
        ready: function () {
            if ($.fn.DataTable.isDataTable('#tablaEtiquetasPaqueteria')) {
                $('#tablaEtiquetasPaqueteria').dataTable().fnClearTable();
                $('#tablaEtiquetasPaqueteria').dataTable().fnDestroy();
                $('#tablaEtiquetasPaqueteria tbody').remove();
                $('.paqyflet').val('');
                $('.tipoentreseg').val('');
                $('.comentario').val('');
            }
            if ($('#previsEtiqueta').is(':checked')) {
                $('#previsEtiqueta').removeProp('checked');
                $('#tablaEtiquetasPaqueteria tbody').remove();
                $('.paqyflet').val('');
                $('.tipoentreseg').val('');
                $('.comentario').val('');
                $('#tablaEtiqueta').hide();
            }
            $('#modalEtiquetas #OVEti').val(ov);
            var htmlsitio = "";
            var selected = "";
            for (var k in sitios2) {
                if (sitios2[k].SITEID == sitio) { selected = "selected"; } else { selected = ""; }
                htmlsitio += '<option value="' + sitios2[k].SITEID + '" ' + selected + '>' + sitios2[k].NAME + '</option>';
            }
            $('#sitioEtiquetas').html(htmlsitio);
            $('#sitioEtiquetas').val(sitio);
            $('#sitioEtiquetas').material_select();
            $('#propositoEtiquetas').material_select('destroy');
            $('#propositoEtiquetas').attr('disabled', 'disabled');
            $('#propositoEtiquetas').html('<option value="">Selecciona...</option>');
            $('#propositoEtiquetas').material_select();
            $.ajax({
                url: "index/datosPropo"
                , type: "POST"
                , dataType: "JSON"
                , data: { "cot": ov, "ov": "" },
                beforeSend: function (xhr) {
                    $("#loadheaderid").html('<img src="../application/assets/img/cargando.gif" style="width: 1em;">');
                },
                success: function (res) {
                    $('#propositoEtiquetas').html(res.optPropo);
                    $('#propositoEtiquetas').removeAttr('disabled');
                    $('#propositoEtiquetas').material_select();
                    var div = $('#propositoEtiquetas').parent('.select-wrapper');
                    var ul = $(div).children('ul').children('li');
                    $(ul).each(function (index) {
                        $(this).children('span').css('font-size', '14px');
                        $(this).children('span').css('text-transform', 'uppercase');
                        if ($(this)[0].innerText != 'Selecciona...' && $(this)[0].innerText != 'Otro') {
                            var proposito = $(this)[0].innerText;
                            var temp = $('#propositoEtiquetas option')[index];
                            var temp = $('#propositoEtiquetas option')[index];
                            var addressLocationId = $(temp).data('addresslocationid');
                            var dirMuestra = $.map(res.datosDirs, function (dirs) {
                                if (dirs.PROPOSITO.includes(proposito) && dirs.ADDRESSLOCATIONID.includes(addressLocationId) ) {
                                    //dirs.STREET   = dirs.STREET.replace(/[\r\n]/g,' | ');
                                    var direccion = '<strong><u>Calle: </u></strong>' + dirs.CALLE + ' <strong><u>Colonia: </u></strong>' + dirs.COLONIA + ' <strong><u>Estado: </u></strong>' + dirs.ESTADO + ' <strong><u>Ciudad: </u></strong>' + dirs.CIUDAD + ' <strong><u>CodigoPostal: </u></strong>' + dirs.CODIGOPOSTAL;
                                    return direccion;
                                }
                            });
                            var content = '<div class="row">' +
                                '<div class="col l12 m12 s 12">' +
                                '   <span style="color:#B4B7B7;font-size:12px;">' + dirMuestra[0] + '</span>' +
                                '</div></div>';
                            $(this).append(content);
                        }
                    });
                    $("#loadheaderid").html('');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $("#loading").html("Ocurrio un error favor de comunicarlo al dpto de sistemas.");
                }
            });
        }
    });
}
    
function descargarExcel(variable_conTabla){
    var tmpElemento = document.createElement('a');
    var data_type = 'data:application/vnd.ms-excel';
    var tabla_div = variable_conTabla;
    var tabla_html = tabla_div.replace(/ /g, '%20');
    tmpElemento.href = data_type + ', ' + tabla_html;
    tmpElemento.download = 'Etiqueta.xls';
    tmpElemento.click();
}
function dataTableEti(){
    if ( $.fn.DataTable.isDataTable( '#tablaEtiquetasPaqueteria' ) ){
            $('#tablaEtiquetasPaqueteria').dataTable().fnDestroy();
    }
    $('#etiquetasPaqueteria .dt-buttons').remove();
    $('#etiquetasPaqueteria .dataTables_filter').remove();
    $('#etiquetasPaqueteria .dataTables_info').remove();
    $('#etiquetasPaqueteria .dataTables_paginate').remove();
    var valido = checarDatosCliente();
    if (valido){
        Materialize.toast('Todo valido puede continuar', 3000,'green');
        var tbody = $('<tbody></tbody>');
        $('#tablaEtiqueta tbody tr').each(function(){
            var td = $(this).find('td').clone();
            var tr = $('<tr></tr>');
            $(td).each(function(){
                if ( !$(this).children().is('input') ){
                    $(tr).append(td);
                }else{
                    var dato = $(this).children('input').val();
                    $(this).find('input').remove();
                    $(this).html(dato);
                    $(tr).append(this);
                }
                $(tbody).append(tr);
            });
        });
        var tr2 = '<tr style="height:20px"><td style="width:49%;height:100%;font-size:12px"></td><td style="width:50%;height:100%;font-size:12px"></td></tr>';
        var tr3 = '<tr style="height:20px"><td style="background-color: #01579B; color: #FFFFFF; border-left: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black">'+company+'</td><td style="background-color: #01579B; color: #FFFFFF; border-left: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black"> </td></tr>';
        var tbodyTemp = $(tbody).html();
        tbodyTemp += tr2;
        tbodyTemp += tr3;
        tbodyTemp += $(tbody).html();
        tbodyTemp += tr2;
        tbodyTemp += tr3;
        tbodyTemp += $(tbody).html();
        $(tbody).html(tbodyTemp);
        $('#tablaEtiquetasPaqueteria tbody').remove();
        $('#tablaEtiquetasPaqueteria').append(tbody);
        tbodyTemp = '';
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('border','1px solid black');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('border','1px solid black');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('height','22px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('height','22px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('text-transform','uppercase');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('text-transform','uppercase');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('font-size','10px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('font-size','10px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('padding-left','5px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('padding-left','5px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('padding-right','5px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('padding-right','5px');
            var style = '<style>'+
                    '   @media print { body { -webkit-print-color-adjust: exact; } }'+
                    '   #tablaEtiquetasPaqueteria tbody tr:nth-child(15) td:nth-child(2){'+
                    '       border-bottom: 1px solid black;'+
                    '       border-top: 1px solid black;'+
                    '       border-left: 1px solid black;'+
                    '       background-color:#01579B;'+
                    '       color: #FFFFFF'+
                    '   }'+
                    '   #tablaEtiquetasPaqueteria tbody tr:nth-child(15) td:nth-child(3){'+
                    '       border-bottom: 1px solid black;'+
                    '       border-top: 1px solid black;'+
                    '       border-right: 1px solid black;'+
                    '       background-color:#01579B;'+
                    '       color: #FFFFFF'+
                    '   }'+
                    '   #tablaEtiquetasPaqueteria tbody tr:nth-child(30) td:nth-child(2){'+
                    '       border-bottom: 1px solid black;'+
                    '       border-top: 1px solid black;'+
                    '       border-left: 1px solid black;'+
                    '       background-color:#01579B;'+
                    '       color: #FFFFFF'+
                    '   }'+
                    '   #tablaEtiquetasPaqueteria tbody tr:nth-child(30) td:nth-child(3){'+
                    '       border-bottom: 1px solid black;'+
                    '       border-top: 1px solid black;'+
                    '       border-right: 1px solid black;'+
                    '       background-color:#01579B;'+
                    '       color: #FFFFFF'+
                    '   }'+
                    '   #tablaEtiquetasPaqueteria tbody tr:nth-child(30) td:nth-child(3){'+
                    '       border-bottom: 1px solid black;'+
                    '       border-top: 1px solid black;'+
                    '       border-right: 1px solid black;'+
                    '       background-color:#01579B;'+
                    '       color: #FFFFFF'+
                    '   }'+
                    '</style>';
        var html = $('#etiquetasPaqueteria').html();
        descargarExcel(html);
    }else{
        Materialize.toast('Existen datos incompletos, favor de ingresarlos', 3000,'red');
    }
}
function checarDatosCliente(){
    var bandDtCte = true;
    $('.datoEtiquetaCliente').each(function(){
        valor = $(this).val();
        if ($(this).val() === '' || $(this).val() === 'null' || $(this).type === 'undefined'){
            bandDtCte = false;
            return bandDtCte;
        }
    });
    return bandDtCte;
}

function generarEtiquetas(sitio, ov, proposito, isCot = false){
    $.ajax({url: "index/datosEtiqueta", type: "POST", dataType: "JSON",
        data: { "sitio": sitio, "ov": ov, "isCot": isCot },

        beforeSend: function (xhr) {
            $("#loading").html('<img src="../application/assets/img/cargando.gif" style="width: 1em;">');
        },
        success: function(res){
            console.log('generar de inicio');
            var datosSucu = res.datosSucu;
            var datosClte = res.datosCte;
            var datosDirs = res.datosDirs;
            var datosMonto = res.datosMonto;
            direccionCliente(proposito, datosDirs);
            $(datosSucu).each(function(){
                console.log(this.calle);
                $('#tablaEtiqueta .calle').val(this.calle);
                $('#tablaEtiqueta .colonia').val(this.colonia);
                $('#tablaEtiqueta .estado').val(this.estado);
                $('#tablaEtiqueta .telefono').val(this.telefono);
            });
            console.log(datosClte[0].CUSTACCOUNT + ' ' + datosClte[0].RFC);
            var clteCompleto = ov + ' - ' + datosClte[0].CUSTACCOUNT + ' - ' + datosClte[0].NOMBRECLIENTE;
            var fecha = new Date();
            var monto = $('#totalResumen').text();
            $('#tablaEtiqueta .monto').val($('#totalResumen').text());
            monto = $('#tablaEtiqueta .monto').val();
            $('#tablaEtiqueta .monto').val('Monto: ' + monto);
            $('#tablaEtiqueta .cliente-top').val(clteCompleto);
            $('#tablaEtiqueta .rfc-cte').val('RFC: ' + datosClte[0].RFC);
            $('#tablaEtiqueta .correo-cte').val('EMAIL: ' + datosClte[0].EMAIL);
            $('#tablaEtiqueta .tel-cte').val('Telefono: ' + datosClte[0].TELEFONO + ' ext: ' + datosClte[0].EXTENSION);
            $('#tablaEtiqueta .cliente-bot').val(clteCompleto);
            $('#tablaEtiqueta .userEti').html('Usuario: ' + usuario + ' (' + datosClte[0].NOMBREVENDEDOR + ')');
            $('#tablaEtiqueta .fechaEti').html('Fecha de creacion: ' + fecha.toLocaleDateString() + ' - ' + fecha.toLocaleTimeString());
            $('#tablaEtiqueta .rfc-cte').removeAttr('readonly');
            $('#tablaEtiqueta .tel-cte').removeAttr('readonly');
            $('#tablaEtiqueta').show();
            $("#loading").html('');
            //$('#resumenTest').hide();
        }
    });
}
    
function direccionCliente(proposito, direcciones) {
    var dirMuestra = $.map(direcciones, function (dirs) {
        if (dirs.PROPOSITO.includes(proposito)) {
            //dirTemp   = dirs.ADDRESS.split("\n");
            dirCalle = dirs.CALLE;
            dirColon = dirs.COLONIA;
            dirEstad = dirs.ESTADO;
            dirCiudad = dirs.CIUDAD;
            dirCodPo = dirs.CODIGOPOSTAL;
            dirPais = dirs.PAIS;
            direccion = { 'calle': dirCalle, 'colonia': dirColon, 'estado': dirEstad, 'ciudad': dirCiudad, 'cp': dirCodPo, 'pais': dirPais };
            return direccion;
        }
    });
    $(dirMuestra).each(function () {
        if (this.calle != 'NoDefinido') {
            $('#tablaEtiqueta .calle-cte').val(this.calle + ' C.P. ' + this.cp);
        } else {
            $('#tablaEtiqueta .calle-cte').removeAttr('readonly');
        }
        if (this.colonia != 'NoDefinido') {
            $('#tablaEtiqueta .colonia-cte').val(this.colonia);
        } else {
            $('#tablaEtiqueta .colonia-cte').removeAttr('readonly');
        }
        if (this.estado) {
            $('#tablaEtiqueta .estado-cte').val(this.ciudad + ',' + this.estado + ',' + this.pais.toUpperCase());
        } else {
            $('#tablaEtiqueta .estado-cte').removeAttr('readonly');
        }
    });
}

function checarNegados(){
    var cantNegados = negados.itemGroup.items.length;
    if (eval(cantNegados) > 0){
        agregarNegados();
        $('#modalNegados').openModal({dismissible : false});
        return true;
    }else{
        return false;
    }
}
function goBack(act,ant){
    $(act).hide();
    $(ant).show(); 
}
function miScroll() { $('#rightclicked').hide();}

$('#sitioEtiquetas,#propositoEtiquetas').on('change',function(){
         $('#previsEtiqueta').removeProp('checked');
         $('#tablaEtiqueta').hide();
     });
    $('#previsEtiqueta').on('change',function(){
        if ($(this).is(':checked')){
            var sitio = $('#sitioEtiquetas :selected').val();
            var proposito = $('#propositoEtiquetas :selected').val();
            var ov = $('#modalEtiquetas #OVEti').val();
            var recid = $('#propositoEtiquetas :selected').attr('data-recid');
            generarEtiquetas(sitio, ov, proposito, true);
            if (proposito == 'Otro'){
                $('.datoEtiquetaCliente').not('.tipoentreseg,.paqyflet,.comentario').prop('readonly', false);
                $('.datoEtiquetaCliente').val('');
            }else{
                $('.datoEtiquetaCliente').not('.tipoentreseg,.paqyflet,.comentario').prop('readonly','readonly');
            }
            
        }else{
            $('#tablaEtiqueta').hide();
        }
    });
$('#genArchEti').on('click',function(){
    var valido = checarDatosCliente();
    if (valido){
        Materialize.toast('Todo valido puede continuar', 3000,'green');
        var tbody = $('<tbody></tbody>');
        $('#tablaEtiqueta tbody tr').each(function(){
            var td = $(this).find('td').clone();
            var tr = $('<tr></tr');
            $(td).each(function(){
                if ( !$(this).children().is('input') ){
                    $(tr).append(td);
                }else{
                    var dato = $(this).children('input').val();
                    $(this).find('input').remove(); 
                    $(this).html(dato);
                    $(tr).append(this);
                }
                $(tbody).append(tr);
            });
        });
        var tr2 = '<tr><td style="width:1%"> </td><td style="width:49%"> </td><td style="width:50%"> </td></tr>';
        var tr3 = '<tr><td style="width:1%"> </td><td style="width:49%">'+company+'</td><td style="width:50%"> </td></tr>';
        var tbodyTemp = $(tbody).html();
        tbodyTemp += tr2;
        tbodyTemp += tr3;
        tbodyTemp += $(tbody).html();
        tbodyTemp += tr2;
        tbodyTemp += tr3;
        tbodyTemp += $(tbody).html();
        $(tbody).html(tbodyTemp);
        $('#tablaEtiquetasPaqueteria tbody').remove();
        $('#tablaEtiquetasPaqueteria').append(tbody);
        dataTableEti();
        tbodyTemp = '';
        $('.btn-etiquetas').trigger('click');
    }else{
        Materialize.toast('Existen datos incompletos, favor de ingresarlos', 3000,'red');
    }
});
$('#impArchEti').on('click',function(){        
    if ( $.fn.DataTable.isDataTable( '#tablaEtiquetasPaqueteria' ) ){
        $('#tablaEtiquetasPaqueteria').dataTable().fnDestroy();
    }
    $('#etiquetasPaqueteria .dt-buttons').remove();
    $('#etiquetasPaqueteria .dataTables_filter').remove();
    $('#etiquetasPaqueteria .dataTables_info').remove();
    $('#etiquetasPaqueteria .dataTables_paginate').remove();
    var valido = checarDatosCliente();
    if (valido){
        Materialize.toast('Todo valido puede continuar', 3000,'green');
        var tbody = $('<tbody></tbody>');
        $('#tablaEtiqueta tbody tr').each(function(){
            var td = $(this).find('td').clone();
            var tr = $('<tr></tr');
            $(td).each(function(){
                if ( !$(this).children().is('input') ){
                    $(tr).append(td);
                }else{
                    var dato = $(this).children('input').val();
                    $(this).find('input').remove();
                    $(this).html(dato);
                    $(tr).append(this);
                }
                $(tbody).append(tr);
            });
        });
        var tr2 = '<tr><td colspan="2">.</td></tr>';
        var tr3 = '<tr><td style="background-color: #01579B; color: #FFFFFF; border-left: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black">' + company + '</td><td style="background-color: #01579B; color: #FFFFFF; border-right: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black"></td></tr>';
        var tbodyTemp = $(tbody).html();
        tbodyTemp += tr2;
        tbodyTemp += tr3;
        tbodyTemp += $(tbody).html();
        tbodyTemp += tr2;
        tbodyTemp += tr3;
        tbodyTemp += $(tbody).html();
        $(tbody).html(tbodyTemp);
        $('#tablaEtiquetasPaqueteria tbody').remove();
        $('#tablaEtiquetasPaqueteria').append(tbody);
        tbodyTemp = '';
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('border','1px solid black');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('border','1px solid black');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('height','22px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('height','22px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('text-transform','uppercase');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('text-transform','uppercase');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('font-size','10px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('font-size','10px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('padding-left','5px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('padding-left','5px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(1)').not('tr:nth-child(15) td:nth-child(1),tr:nth-child(14) td:nth-child(1),tr:nth-child(30) td:nth-child(2),tr:nth-child(29) td:nth-child(1)').css('padding-right','5px');
        $('#tablaEtiquetasPaqueteria tbody tr td:nth-child(2)').not('tr:nth-child(15) td:nth-child(2),tr:nth-child(14) td:nth-child(2),tr:nth-child(30) td:nth-child(3),tr:nth-child(29) td:nth-child(2)').css('padding-right','5px');
        var style = '<style>'+
                    '   @media print { body { -webkit-print-color-adjust: exact; } }'+
                    '   #tablaEtiquetasPaqueteria tbody tr:nth-child(15) td:nth-child(1){'+
                    '       border-bottom: 1px solid black;'+
                    '       border-top: 1px solid black;'+
                    '       border-left: 1px solid black;'+
                    '       background-color:#01579B;'+
                    '       color: #FFFFFF'+
                    '   }'+
                    '   #tablaEtiquetasPaqueteria tbody tr:nth-child(15) td:nth-child(2){'+
                    '       border-bottom: 1px solid black;'+
                    '       border-top: 1px solid black;'+
                    '       border-right: 1px solid black;'+
                    '       background-color:#01579B;'+
                    '       color: #FFFFFF'+
                    '   }'+
                    '   #tablaEtiquetasPaqueteria tbody tr:nth-child(30) td:nth-child(1){'+
                    '       border-bottom: 1px solid black;'+
                    '       border-top: 1px solid black;'+
                    '       border-left: 1px solid black;'+
                    '       background-color:#01579B;'+
                    '       color: #FFFFFF'+
                    '   }'+
                    '   #tablaEtiquetasPaqueteria tbody tr:nth-child(30) td:nth-child(2){'+
                    '       border-bottom: 1px solid black;'+
                    '       border-top: 1px solid black;'+
                    '       border-right: 1px solid black;'+
                    '       background-color:#01579B;'+
                    '       color: #FFFFFF'+
                    '   }'+
                    '   #tablaEtiquetasPaqueteria tbody tr:nth-child(30) td:nth-child(1){'+
                    '       border-bottom: 1px solid black;'+
                    '       border-top: 1px solid black;'+
                    '       border-right: 1px solid black;'+
                    '       background-color:#01579B;'+
                    '       color: #FFFFFF'+
                    '   }'+
                    '</style>';
        //var w = window.open('ETIQUETAS','Etiquetas');
        var html = $('#etiquetasPaqueteria').html();
        html = html.replace('<thead>', '');
        html = html.replace('</thead>', '');
        html = html.replace('<tbody>', '');
        html = html.replace('</tbody>', '');
        html = html.replace('<th', '<td');
        html = html.replace('<th', '<td');
        html = html.replace('</th>', '</td>');
        html = html.replace('</th>', '</td>');
        //w.document.write(style + html);
        //w.print();
        //w.close();
        var dataZPL = getZPLData();
        $.post('inicio/postlabel', { cot: $('#DocumentId2').val(), html: style + html, dataZPL }).done(function (data) {
            data = JSON.parse(data);
            console.log(data);
            if(data.success == 'true'){
                Materialize.toast('Etiqueta generadas correctamente', 3000, 'green');
                parcelLabelSubmited = true;
                $('#modalEtiquetas').closeModal();
            }
        });
    }else{
        Materialize.toast('Existen datos incompletos, favor de ingresarlos', 3000,'red');
    }
});
    document.body.addEventListener('click', function () {
        document.getElementById("rightclicked").style.display = "none";
    });
    document.body.addEventListener('contextmenu', function () {
        document.getElementById("rightclicked").style.display = "none";
    });
    document.getElementById("OV-Cliente").addEventListener('contextmenu', function (ev) {
        ev.stopPropagation();
        ev.preventDefault();
        rightclick();
        return false;
    }, false);
    $('.contextMenuA').tooltip();
    $('.contextMenuA').on("mouseenter", function () {
        $(this).css('background-color', '#337AB7'); 
        $(this).css('color', 'white');        
        $('.tooltip').addClass('col-md-12');
    });
    $('.contextMenuA').on("mouseleave", function () {
        $(this).css('background-color', 'white');
        $(this).css('padding','3px 15px');
        $(this).css('color', '#0275D8');
    });
    ///////////funcion para esconder el context en el scroll del body///////////
    $('body').attr('onscroll','miScroll()');
    $(".dropdown-button").dropdown();
    $.validator.setDefaults({errorClass: 'invalid',validClass: "valid",
        errorPlacement: function (error, element) {
            $(element).closest("form").find("label[for='" + element.attr("id") + "']").attr('data-error', error.text());
        }});
    $('#mapaCliente').removeClass('default');
    $('#mapaCliente').addClass('success');
    $('#mapaCliente2').removeClass('default');
    $('#mapaCliente2').addClass('success');
    $(document).hotkey('alt+n', function(e){ $('#newCustomer').click(); });
    $(document).hotkey('alt+q', function(e){ $('#SubmitHeader').click(); });
    $(document).on('click','.checkopc',function(){
        var row    = $(this).closest('tr');
        var indice = $(row).index(); 
        $('.row'+(indice +1)).removeProp('checked');
        $(this).prop('checked','checked');
    });
    //Se evita que al dar click en el objeto se desaparezca del form
    $(document).on('click','.dir-dropdown-content', function(event){
        //The event won't be propagated to the document NODE and 
        // therefore events delegated to document won't be fired
        event.stopPropagation();
    });
    $("#ElegirDireccion").on('click',function(){
        //Se obtiene la linea seleccionada
        var lineasel = $('input[name="RadioDir"]:checked').val();			
        var recid = $("#RecIdDir"+lineasel).val();
        var nombDir = $("#NombreDir"+lineasel).text();
        var direccion = $("#Dir"+lineasel).text();

        $("#cliente").val(nombDir);
        $("#direccion").val(direccion);
        $("#RecIdDireccion").val(recid);

        //Se remueven las clases que hacen visible el cuadro de direcciones
        $("#dropdownDirecciones").removeClass("active");
        $("#dropdownDirecciones").css("display","none");
    });

function quitarLinea(){
    if(editarOV == "NoResults"){
        var rowchecked = $('#articulos > tbody > tr').has('input[id*="numLinea"]:checked').length;
        var rowschecked = $('#articulos > tbody > tr').has('input[id*="numLinea"]:checked');
        if (rowchecked > 0){
                $('#articulos > tbody > tr').has('input[id*="numLinea"]:checked').remove();
                var numeroLineas = $('#articulos >tbody >tr').length;
                $("#NumFilas").val(numeroLineas);	
                UpdateRowId();
                var subtot = subtotal();
                var tot = total();
                $("#subtotal").html(subtot.toFixed(3)).formatCurrency();		        	
                $("#total").html(tot.toFixed(3)).formatCurrency();		    
                var iva = tot - subtot;
                $("#iva").html(iva.toFixed(3)).formatCurrency();
                $('#QuitarLinea').hide();
                quitarlineasEntity(rowschecked);
        }else{
                Materialize.toast('Debe seleccionar al menos una linea al borrar.', 3000);
        }
    }else{
        ov = $('#DocumentId2').val();    
        if(ov.includes("COTV")){
            var rowchecked = $('#articulos > tbody > tr').has('input[id*="numLinea"]:checked').length;
            var rowschecked = $('#articulos > tbody > tr').has('input[id*="numLinea"]:checked');
            if (rowchecked > 0){
                    $('#articulos > tbody > tr').has('input[id*="numLinea"]:checked').remove();
                    var numeroLineas = $('#articulos >tbody >tr').length;
                    $("#NumFilas").val(numeroLineas);	
                    UpdateRowId();
                    var subtot = subtotal();
                    var tot = total();
                    $("#subtotal").html(subtot.toFixed(3)).formatCurrency();		        	
                    $("#total").html(tot.toFixed(3)).formatCurrency();		    
                    var iva = tot - subtot;
                    $("#iva").html(iva.toFixed(3)).formatCurrency();
                    $('#QuitarLinea').hide();
                    quitarlineasEntity(rowschecked);
            }else{
                    Materialize.toast('Debe seleccionar al menos una linea al borrar.', 3000);
            }
        }else{
            $.ajax({
                url: "inicio/status", 
                type: "post", 
                dataType: "json", 
                data: {'ov': ov},
                success: function (data) { 
                    if(data.ReleaseStatus == "Released" || data.ReleaseStatus == "PartialReleased"){
                        swal('¡Alto!','No se puede editar la Orden de Venta','error')
                        .then(function(result){
                            if(result){
                                window.location.href='../public/inicio?COT'
                                //return;
                            }
                        });
                    }else{
                        var rowchecked = $('#articulos > tbody > tr').has('input[id*="numLinea"]:checked').length;
                        var rowschecked = $('#articulos > tbody > tr').has('input[id*="numLinea"]:checked');
                        if (rowchecked > 0){
                                $('#articulos > tbody > tr').has('input[id*="numLinea"]:checked').remove();
                                var numeroLineas = $('#articulos >tbody >tr').length;
                                $("#NumFilas").val(numeroLineas);	
                                UpdateRowId();
                                var subtot = subtotal();
                                var tot = total();
                                $("#subtotal").html(subtot.toFixed(3)).formatCurrency();		        	
                                $("#total").html(tot.toFixed(3)).formatCurrency();		    
                                var iva = tot - subtot;
                                $("#iva").html(iva.toFixed(3)).formatCurrency();
                                $('#QuitarLinea').hide();
                                quitarlineasEntity(rowschecked);
                        }else{
                                Materialize.toast('Debe seleccionar al menos una linea al borrar.', 3000);
                        }
                    }          
                },
                error: function (jqXHR, exep) {
                    Materialize.toast('Error!' + catchError(jqXHR, exep), 3000);
                }
            }); 
        }

    }        
}

function quitarlineasEntity(rows){
    gaTrigger('SalesQuotationLines','entity',userBranchOffice);
    $(rows).each(function(){
        dataAreaId = $(this).find('.dataAreaId').val();
        InventoryLotId = $(this).find('.InventoryLotId').val();
        documentId = $('#DocumentId2').val();
        $.ajax({
            url: "inicio/eliminar-Lineas",
            type: "post",
            dataType: "json", 
            data: { 'dataAreaId'        : dataAreaId
                    ,'InventoryLotId'   : InventoryLotId
                    ,'tipo'             : docType
                    ,'documentId'       : documentId
                },
            beforeSend: function (xhr){
                
            },
            success: function (data){
                //console.log(data);
            }
        });
    });
}
//Funcion que actualiza las filas de la orden de venta
function UpdateRowId(){
	var numeroLineas = $('#articulos >tbody >tr').length;
	var contador = 1;
	$("#articulos > tbody > tr [id*=numLinea]").each(function(){							
		$(this).attr("id","numLinea" + contador);
		$(this).attr("value",contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=LblNumLinea]").each(function(){									
		$(this).text(contador);
		$(this).attr("id","LblNumLinea" + contador);
		$(this).attr("for","numLinea" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=item]").each(function(){							
		$(this).attr("id","item" + contador);
		$(this).attr("name","item" + contador);
		contador += 1;
	});

	var contador = 1;
	$("#articulos > tbody > tr [id*=descripcion]").each(function(){							
		$(this).attr("id","descripcion" + contador);
		$(this).attr("name","descripcion" + contador);
		$(this).attr("data-activates","dropdownComentarioLinea" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=cantidad]").each(function(){							
		$(this).attr("id","cantidad" + contador);
		$(this).attr("name","cantidad" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=unidad]").each(function(){							
		$(this).attr("id","unidad" + contador);
		$(this).attr("name","unidad" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=sitio]").each(function(){							
		$(this).attr("id","sitio" + contador);
		$(this).attr("name","sitio" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=almacen]").each(function(){							
		$(this).attr("id","almacen" + contador);
		$(this).attr("name","almacen" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=lote]").each(function(){							
		$(this).attr("id","lote" + contador);
		$(this).attr("name","lote" + contador);
		$(this).attr("data-activates","dropdownLote" + contador);
		contador += 1;
	});
	var contador = 1;
    $("#articulos > tbody > tr [id*=localidad]").each(function(){                           
        $(this).attr("id","localidad" + contador);
        $(this).attr("name","localidad" + contador);
        contador += 1;
    });
    var contador = 1;
	$("#articulos > tbody > tr [id*=matricula]").each(function(){							
		$(this).attr("id","matricula" + contador);
		$(this).attr("name","matricula" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr .preciovta").each(function(){							
		$(this).attr("id","preciovta" + contador);
		$(this).attr("name","preciovta" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr .preciovtaiva").each(function(){							
		$(this).attr("id","preciovtaiva" + contador);
		$(this).attr("name","preciovtaiva" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=montocargo]").each(function(){							
		$(this).attr("id","montocargo" + contador);
		$(this).attr("name","montocargo" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=montoiva]").each(function(){							
		$(this).attr("id","montoiva" + contador);
		$(this).attr("name","montoiva" + contador);
		contador += 1;
	});	
	var contador = 1;
	$("#articulos > tbody > tr [id*=dropdownLote]").each(function(){							
		$(this).attr("id","dropdownLote" + contador);
		contador += 1;
	});	
	var contador = 1;
	$("#articulos > tbody > tr [id*=DisponibleLote]").each(function(){							
		$(this).attr("id","DisponibleLote" + contador);
		contador += 1;
	});	
	var contador = 1;
	$("#articulos > tbody > tr [id*=dropdownComentarioLinea]").each(function(){							
		$(this).attr("id","dropdownComentarioLinea" + contador);
		$(this).attr("name","dropdownComentarioLinea" + contador);
		contador += 1;
	});	
	var contador = 1;
	$("#articulos > tbody > tr [id*=comentariolinea]").each(function(){							
		$(this).attr("id","comentariolinea" + contador);
		$(this).attr("name","comentariolinea" + contador);
		contador += 1;
	});
	var contador = 1;
	$("#articulos > tbody > tr [id*=ElegirLote]").each(function(){							
		$(this).attr("id","ElegirLote" + contador);
		contador += 1;
	});
	var contador = 1;
    $("#articulos > tbody > tr [id*=punitariolinea]").each(function(){  
        $(this).attr("id","punitariolinea" + contador);
        $(this).attr("name","punitariolinea" + contador);
        contador += 1;
    });
    var contador = 1;
    $("#articulos > tbody > tr [id*=dataAreaId]").each(function(){  
        $(this).attr("id","dataAreaId" + contador);
        $(this).attr("name","dataAreaId" + contador);
        contador += 1;
    });
    var contador = 1;
    $("#articulos > tbody > tr [id*=InventoryLotId]").each(function(){  
        $(this).attr("id","InventoryLotId" + contador);
        $(this).attr("name","InventoryLotId" + contador);
        contador += 1;
    });
    var contador = 1;
	$("#articulos > tbody > tr [id*=cargoMoneda]").each(function(){	
		$(this).attr("id","cargoMoneda" + contador);
		$(this).attr("name","cargoMoneda" + contador);
		contador += 1;
	});
}

function confirmarCot() {
    //gaTrigger('SalesQuotationHeaders','entity',userBranchOffice);
    //gaTrigger('SetHeaderDefaultDimension','entity',userBranchOffice);
    //gaTrigger('SalesOrderHeaders','entity',userBranchOffice);
    $('#articulos tbody tr input.valido').each(function () {
        if (this.value == '') {
            var row = $(this).closest('tr');
            $(row).remove();
            var numLinea = $('#articulos >tbody >tr').length;
            $("#NumFilas").val(numLinea);
        }
    });
    UpdateRowId();
    var cantRows = $('#articulos tbody tr input.valido').length;
    if (cantRows > 0) {
        var str = $('#FormCabecera').serialize();
        var token = 'inicio/fnt';
        if (setTipo === 'ORDVTA') { token = 'inicio/resumenTest'; }
        var numero = $('#seguridad3Digitos').val();
        if (numero === '') { numero = '0000'; }
        var id = '&edit=0&id=""';
        if (edicion == 1) { id = '&edit=' + edicion + '&id=' + $('#DocumentId2').val(); }
        if (token !== '') {
            var str = $('#FormCabecera').serialize();
            var token = 'inicio/newDocument';
            var numero = $('#seguridad3Digitos').val();
            let promoCodeEnable = false;
            let promoCodeName = '';
            if ($('#promoCode').val() != 'None') {
                promoCodeEnable = true;
                promoCodeName = promoCodes[$('#promoCode').val()].PromoCode;
            }
            if (numero === '') { numero = '0000'; }
            var id = '&edit=0&id=""';
            if (edicion == 1) { id = '&edit=' + edicion + '&id="' + $('#DocumentId2').val() + '"'; }
            $.ajax({
                url: token,
                type: "POST",
                dataType: "json",
                data: str + '&modoentrega=' + escape($('#entregalineas').val()) + '&digitos=' + numero + id + '&origenVenta=' + $("#origenV").val() + '&payment=' + $("#paytermArt").val() + '&MetodoPago=' + $("#pagolineas option:selected").attr('data-paymmode') + '&moneda=' + $('#monedalineas').val() + '&almacen=' + $('#almacen1').val() + '&sitio=' + $('#sitio1').val() + '&tipo=' + setTipo + '&promoCodeEnable=' + promoCodeEnable + '&promoCodeName=' + promoCodeName,
                beforeSend: function (req) {
                    $('#loadTarjeta').html('<center><img style="width: 4%;" src="../application/assets/img/cargando.gif"><br>procesando...</center>');
                    $('#AgregarLinea').addClass('disable_a_href');
                },
                success: function (data) {
                    gaTrigger('Cabezera Creada', 'Cotizaciones', userBranchOffice);
                    if (setTipo === 'ORDVTA') {
                        $('#DocumentId2').val(data.OV); $("#propositoContainer").show();
                    }
                    else { $('#DocumentId2').val(data.CTZN); }
                    var response = $('#DocumentId2').val(); console.log('response: ' + response);
                    if (response !== '') {

                        $("#cabeceraOriginal").val(data.encabezadoOV);
                        $("#DocumentType").val(data.documentType);
                        $("#DocumentId").val($('#DocumentId2').val());
                        initResumenTestDiv();
                    }
                    else {
                        Materialize.toast('Error de confirmacion', 3000);
                        var f = { responseText: "No confirma" };
                        catchError(f, "respuesta vacia");
                        $('#loadNext').html('');
                    }
                },
                error: function (jqXHR, exception) {
                    Materialize.toast('Error de confirmacion', 3000);
                    catchError(jqXHR, exception);
                    $('#loadNext').html('');
                }
            });
        }
        else {
            Materialize.toast('Tipo de Documento desconocido', 3000);
        }
    }
}

function validarAlmacenes(){
    var exhb = 0;
    var cons = 0;
    var comb = true;
    var tipo  = 'combinado';
    var cuantos = $('input.cambioAlmacen').size();
    $('input.cambioAlmacen').each(function(){
        if(this.value.includes('EXHB')){
            exhb ++;
        }else{
            cons ++;
        }
    });

    if (exhb == cuantos){
        comb = false;
        tipo = 'EXHB';
    }
    if (cons == cuantos){
        comb = false;
        tipo = 'CONS';
    }
    return {'combinado':comb,'tipo':tipo};
}

function scrollinDropdown() {
	//console.log("scroll");
    var selects = $('select').not('#secretarioventa,#responsableventa');
    $(selects).each(function(){		
        $(this).material_select(function() {
			console.log("selecciona secretarioventa");
            $('input.select-dropdown').trigger('close');
        });
    var onMouseDown = function(e) {
    if (e.clientX >= e.target.clientWidth || e.clientY >= e.target.clientHeight) {e.preventDefault();}};
    $(this).siblings('input.select-dropdown').on('mousedown', onMouseDown);
    });
}

function nuevoDocumento(){
    var tipo = $('#documentTypeOrigen').val();
    tipo = 'CTZN';
    $('#newDocument #documentType').val(tipo);
    $('#newDocument').submit();
}
function refreshLines(){
    gaTrigger('SalesQuotationLines','entity',userBranchOffice);
    gaTrigger('SalesOrderLines','entity',userBranchOffice);
    $('#resumenTestTablaLineas tbody').empty();
    $('#totalResumen').empty();
    var docId = $('#DocumentId2').val();
    var docType = $("#DocumentType").val();
    if(docId!==""){
        $.ajax({ url:"inicio/refreshLines",
                 type: "POST",
                 dataType: "JSON",
                 data: {'docId':docId,'docType':docType},
        success: function (data){
            var bodyTable  = '';
            var qty = '';
            var montocargo = '';
            var cargoN=Number($("#pagolineas").val());
            var montocargoiva = '';
            var suma = 0;
            if(data.length>0){
                $(data).each(function(index){
                    qty = Number(this.SALESQTY);
                    montocargo = Number(this.MONTOCARGO);
                    //montocargo=montocargo+(montocargo*(cargoN/100));
                    //montocargoiva = montocargo*1.16;
                    montocargoiva = Number(this.MONTOCARGOIVA);
                    suma += montocargoiva;
                    bodyTable += '<tr>';
                    bodyTable += '	<td>'+(index+1)+'</td>';
                    bodyTable += '	<td>'+this.ITEMID+'</td>';
                    bodyTable += '	<td>'+this.NAME+'</td>';
                    bodyTable += '	<td>'+qty.toFixed(2)+'</td>';
                    bodyTable += '	<td style="text-transform:uppercase">'+this.SALESUNIT+'</td>';
                    bodyTable += '	<td>'+this.INVENTSITEID+'</td>';
                    bodyTable += '	<td>'+this.INVENTLOCATIONID+'</td>';
                    bodyTable += '	<td> $'+montocargo.toFixed(2)+'</td>';
                    bodyTable += '	<td> $'+montocargoiva.toFixed(2)+'</td>';
                    bodyTable += '</tr>';
                    bodyTable += '<tr>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td>Comentarios: '+this.STF_OBSERVATIONS+'</td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '</tr>';
                });
                $('#resumenTestTablaLineas tbody').html(bodyTable);
                $('#totalResumen').html('$'+suma.toFixed(2));
            }
            else{
                swal('¡Alto!','No se han registrado los artículos en Dynamics, favor de reportar a sistemas que Dynamics esta borrando partidas','info');
            }
        }
    });
    }
    else{
        swal('¡Alto!','No se han registrado un folio de cotización u orden de venta en Dynamics, favor de verificar contra Dynamics','info');
    }
}
        
function agregarNegados(){
    var table = '';
    var body  = '';
    table  = '<table class="table" id="articulosNegados">';
    table += '	<thead>';
    table += '		<tr style="height:100px;">';
    table += '			<th>Articulo</th>';
    table += '			<th>Cantidad</th>';
    table += '			<th style="-webkit-transform: rotate(-45deg);text-align:left;font-size:10px;width:10%;">Surtir material  cliente lo empezara a usar.</th>';
    table += '			<th style="-webkit-transform: rotate(-45deg);text-align:left;font-size:10px;width:10%;">No surtir, solo cotizo.</th>';
    table += '			<th style="-webkit-transform: rotate(-45deg);text-align:left;font-size:10px;width:10%;">No surtir, cliente lo requería en el momento no puede esperar su envío.</th>';
    table += '			<th style="-webkit-transform: rotate(-45deg);text-align:left;font-size:10px;width:10%;">Surtir, cliente requiere material por temporada.</th>';
    table += '			<th style="-webkit-transform: rotate(-45deg);text-align:left;font-size:10px;width:10%;">No surtir, compra excepcional.</th>';
    table += '			<th style="-webkit-transform: rotate(-45deg);text-align:left;font-size:10px;width:10%;">No surtir, se envió de otra sucursal.</th>';
    table += '			<th style="-webkit-transform: rotate(-45deg);text-align:left;font-size:10px;width:10%;">Surtir material para venta exclusiva de proyecto.</th>';
    table += '		</tr>';
    table += '	</thead>';
    table += '	<tbody>';
    var arrNeg = negados.itemGroup.items
   
    $(arrNeg).each(function(index){
            var unidad      = this.unidad;
            var sitio       = this.sitio;
            var almacen     = this.almacen;
            var articulo    = this.articulo;
            var descripcion = this.descripcion;
            var cliente     = this.cliente;
            var qty         = this.cantidad;
            var disp        = this.disponible;
            var cantNegada  = Number(qty) - Number(disp);
            body  += '		<tr data-articulo="'+articulo+'" data-cantnegada="'+cantNegada+'" data-sitio="'+sitio+'" data-almacen="'+almacen+'" data-disponible="'+disp+'" data-unidad="'+unidad+'" data-descripcion="'+descripcion+'" data-cliente="'+cliente+'">';
            body  += '			<td><span>'+articulo+'</span></td>';
            body  += '			<td><span>'+cantNegada+'</span></td>';
            body  += '			<td><input type="checkbox" id="opc1-'+(index+1)+'" class="row'+(index+1)+'" style="width:100%;display:block;"/><label for="opc1-'+(index+1)+'"></label></td>';
            body  += '			<td><input type="checkbox" id="opc2-'+(index+1)+'" class="row'+(index+1)+'" style="width:100%;display:block;" checked/><label for="opc2-'+(index+1)+'"></label></td>';
            body  += '			<td><input type="checkbox" id="opc3-'+(index+1)+'" class="row'+(index+1)+'" style="width:100%;display:block;"/><label for="opc3-'+(index+1)+'"></label></td>';
            body  += '			<td><input type="checkbox" id="opc4-'+(index+1)+'" class="row'+(index+1)+'" style="width:100%;display:block;"/><label for="opc4-'+(index+1)+'"></label></td>';
            body  += '			<td><input type="checkbox" id="opc5-'+(index+1)+'" class="row'+(index+1)+'" style="width:100%;display:block;"/><label for="opc5-'+(index+1)+'"></label></td>';
            body  += '			<td><input type="checkbox" id="opc6-'+(index+1)+'" class="row'+(index+1)+'" style="width:100%;display:block;"/><label for="opc6-'+(index+1)+'"></label></td>';
            body  += '			<td><input type="checkbox" id="opc7-'+(index+1)+'" class="row'+(index+1)+'" style="width:100%;display:block;"/><label for="opc7-'+(index+1)+'"></label></td>';
            body  += '		</tr>';
    });
    table += body;
    table += '	</tbody>';
    table += '</table>';
    $('#modalNegados .modal-body #contenedorNegado').html(table);
}

function checarStatusBloqueo(ov){
    $.ajax({url: "inicio/checarBloqueo",type: "POST", data: { "ov": ov },
        success: function (data){
            data = JSON.parse(data);
            var bloqueo = data.resultado[0].BLOCKED;
            if (bloqueo != 0){
                setTimeout(function(){ checarStatusBloqueo(ov); }, 2000);
            }else{
                $('#alertaBloqueo').removeClass('orange darken-3');
                $('#alertaBloqueo').addClass('teal lighten-1');
                $('#alertMsjBloq').addClass('off');
                $('#alertMsjDetBloq').addClass('off');
                $('#alertMsjSuccessBloq').removeClass('off');
                $('#iconWaitBloq').hide();
                $('#iconSuccessBloq').show();
                $('#GenerarREM').removeClass('disabled');
            }
        }
    });
}

function sandboxValidate(){
    $.ajax({url: "inicio/sandboxvalidate",type: "GET",data: {},
        success: function (data){
            var mex = '';
            if (data != '') {
                data = JSON.parse(data);
                mex = data[0].PrimaryAddressCountryRegionId;
            }
            if (mex != 'MEX') {
                swal('Dynamics fuera de Servicio','Favor de esperar','warning');
            }
        }
    });
}

function tab1Click(){
    $(document).find('#ExistenciasSitioClte tbody').find('tr').first().focus();
}
function tab2Click(){
    $(document).find('#ExistenciasTodosSitios tbody').find('tr').first().focus();
}

function obtenerHora(){
    var d = new Date();
    var day = d.getDate();
    var month = d.getMonth()+1;
    var year = d.getFullYear();
    var hora = d.getHours();
    var minutos = d.getMinutes();
    var segundos = d.getSeconds();
	if (day < 10){ day = '0'+day;}
	if (month < 10){ month = '0'+month;}
	if (hora < 10){	hora = '0'+hora; }
	if (minutos < 10){ minutos = '0'+minutos; }
	if (segundos < 10){ segundos += '0'; }
    var fecha = day+'/'+month+'/'+year+' '+hora+':'+minutos+':'+segundos;
    return fecha;
}
function detalleClienteVenta(salesId,obj){
    gaTrigger('SalesQuotationLines','entity',userBranchOffice);
    var table = $('#UltimasVentas').DataTable();
    var tr = $(obj).closest('tr');
    var row   = table.row(tr);
    var sitio = $("#sitioclte").val();
    if ( row.child.isShown() ) {
        row.child.hide();
        tr.removeClass('shown');
        $(obj).html('add_circle');
        $(obj).attr('style','color:green;cursor:pointer');
    }
    else {
        $('#waitingDivModalUV').css('display','block');
        var html  = '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px; border-top: solid 1px; border-right: solid 1px; border-left: solid 1px; border-bottom: solid 1px;">';
            html += '<thead>';
            html += '    <th># Linea</th>';
            html += '    <th>Orden de Venta</th>';
            html += '    <th>Codigo Art.</th>';
            html += '    <th>Almacen</th>';
            html += '    <th>Nombre</th>';
            html += '    <th>Cantidad</th>';
            // html += '    <th>Fisica Disponible</th>';
            html += '    <th>Unidad</th>';
            html += '    <th>Agregar</th>';
            html += '</thead>';
            html += '<tbody>';
             $.ajax({ url:"inicio/detalleVenta", type: "POST", dataType: "json",
             data: {"ov":salesId, "token":"detalleVenta","transaction":'ORDVTADET','sitio': sitio},
            success: function (res){
                    $(res).each(function(index){
                    var numlinea   = eval(this.LineCreationSequenceNumber);
                    var qty        = eval(this.OrderedSalesQuantity);
                    // var disponible = eval(this.FisicaDisponible);
                    html += '<tr style="border-bottom:solid 1px">';
                    html += '   <td>'+numlinea.toFixed(2)+'</td>';
                    html += '   <td>'+this.SalesOrderNumber+'</td>';
                    html += '   <td>'+this.ItemNumber+'</td>';
                    html += '   <td>'+sitio+'CONS</td>';
                    html += '   <td>'+this.LineDescription+'</td>';
                    html += '   <td>'+qty.toFixed(2)+'</td>';
                    // html += '   <td>'+disponible.toFixed(2)+'</td>';
                    html += '   <td>'+this.SalesUnitSymbol+'</td>';
                    html += '   <td><input type="checkbox" data-item="'+this.ItemNumber+'" data-unidad="'+this.SalesUnitSymbol+'" class="col l1 s1 m1 checkFamDetVta" id="itemCheckFamDetVta'+this.SalesOrderNumber+'-'+index+'"/><label for="itemCheckFamDetVta'+this.SalesOrderNumber+'-'+index+'"></label></td>';
                    html += '</tr>';
                });
                html += '</tbody>';
                html += '</table>';
                row.child(html).show();
                tr.addClass('shown');
                $('#waitingDivModalUV').css('display','none');
                $(obj).html('remove_circle');
                    $(obj).attr('style','color:red;cursor:pointer');
            }
        });
    }
}

function ultimasVentas(){
    var cliente = $("#claveclte").val();
    if(cliente!==""){
        $.ajax({url: "inicio/getUltimasVentas",type:"POST",dataType: "json",data: { "cliente": cliente },
            beforeSend: function (xhr) {
                $('#modalLoading').openModal({dismissible: false});
            },
            success: function (data){
                if (data != 'NoResults'){
                    $('#UltimasVentas').dataTable().fnClearTable();
                    $('#UltimasVentas').DataTable().destroy();
                    $('#UltimasVentas').dataTable({ "pageLength" : 8}).fnAddData(data); 
                    $('.dataTables_length').hide();
                    $('#UltimasVentasModal').openModal({dismissible: false,ready : function(){$('#btnAgregarDetVta').html('Cerrar');}});
                }
                $('#modalLoading').closeModal();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('#modalLoading').closeModal();
                swal(':(','algo salio mal: '+textStatus,'error');
            }
        });
    }
    else{
        Materialize.toast("Debe seleccionar un cliente",3000,"red");
    }
}
//////////////////comportamiento de navegar con tabs/////////////////////////////////////////////////
	$(document).on('keydown','.collapsible-header',function(event) {
		if (event.which === 9){
			$(this).trigger('click');
		}
	});
/////////////////////////////////////////////////////////////////////////////////////////////////////////	

		function bread1() {
			$('#BusExi').addClass('hide');
                        $('#divInicioSesion').addClass('offset-s5');
			$('#BusFam').addClass('hide');
			$('#UltVen').addClass('hide');
			$('#Agrlin').addClass('hide');
			$('#EliLis').addClass('hide');
			$('#cabeceraDiv').show();
			$('#articulosDiv').hide();
			$('#breadArticulos').hide();
			$('#editarDocument').hide();
		}

		function bread2() {
			$('#cabeceraDiv').hide();
			$('#articulosDiv').show();
			$('#editarDocument').show();
		}

		function bread3() {
            console.log("bread3");
			//docId = sessionStorage.getItem('DocumentId');
			$('#BusExi').addClass('hide');
            $('#divInicioSesion').addClass('offset-s5');
			$('#BusFam').addClass('hide');
			$('#UltVen').addClass('hide');
			$('#Agrlin').addClass('hide');
			$('#EliLis').addClass('hide');
			editarDocumento();
		}
/**
 * junta las 2 listas de articulos en una sola esto para detectar nombre comunes y los asocia al codigo del articulo,
 * se hace en 2 consultas para no dar tanta carga a la base de datos
 * */
function cargarItems(art,comunes){
    var itemsMap = [],
    itemsMap2 = [];
    items = art;
	//console.log("articulos", items);
    $.each(items,function(i, val){
        itemsMap[items[i].value] = items[i].value+' - '+items[i].label;
    });
    artArray=itemsMap;
    var a=items.length;
    var items2=comunes;//este es el arreglo para los articulos relacionados
    var b=items2.length;
    var i=0;
    
    for(var l=items.length;l<(a+b);l++){
        if( typeof itemsMap2[items2[i].value]==='undefined'){
            itemsMap2[items2[i].value] = ' - '+items2[i].label+'';
        }else{
             itemsMap2[items2[i].value] = '  '+itemsMap2[items2[i].value]+' - '+items2[i].label+'';
        }
        i++;
    }
    $.each(items,function(i, val){
        if( typeof itemsMap2[items[i].value]==='undefined'){
            itemsMap2[items[i].value]=" ";
        }
        //items[i].label= itemsMap[items[i].value]+itemsMap2[items[i].value];
        items[i].label= itemsMap[items[i].value];
        items[i].nameAlias = itemsMap[items[i].value]+itemsMap2[items[i].value];
    });
    
}
function historial(parametros){
    // $.ajax({url      : 'inicio/savehistory',
    //         type     : 'POST',
    //         dataType : 'json',
    //         data     : parametros,
    //         beforeSend: function (xhr) {
    //             $('#modalLoading').openModal({dismissible: false});
    //         },
    //         success: function (data){
    //             $('#modalLoading').closeModal();
    //         },
    //         error: function (jqXHR, textStatus, errorThrown) {
    //             $('#modalLoading').closeModal();
    //             // swal(':(','algo salio mal: '+textStatus,' error');
    //         }
    //     });
}
function submitHeader(){
    console.log("click en siguiente");
    lastPromoSelected = $('#promoCode').val();
    $('#promoCode2').val($('#promoCode').val()).trigger('change');
    verifica();
    setTimeout(function (){
        parametros = {
            evento    : 'Nueva/Edicion Cotizacion',
            pantalla  : 'Lineas de cotizacion',
            docCreado :  (docID == '') ? 'N/A':docID
        }
        historial(parametros);
        var band=localStorage.getItem('bandera'); 
        var cliente=$("#claveclte").val();
        //sitioCliente = $("#sitioLineas").val();
        //if(band=='si' && cliente.indexOf('C0')!=-1){//esto es para avance
        if(band=='si' && (cliente.indexOf('CL')!=-1) || (cliente.indexOf('ATP-')!=-1)|| (cliente.indexOf('C')!=-1)){//esto es para Lideart y avance
            $('#opcionesTeclas').removeClass('hide');
            $('#UltVen').removeClass('hide');
            $('#Agrlin').removeClass('hide');
            $('#EliLis').removeClass('hide');
            if('#resumenTitle'){
               $('#resumenTitle').remove();
               // $('#clienteTitle').remove();
            }
            if(edicion === '1'){
                $('#DocumentId2').val(docID);
                $('#articulos tbody').empty();
            }
            $('#paytermArt').trigger('change');
            $('#FormCabecera').submit();
            if ($('#FormCabecera').validate().form()){
                $('#BusExi').removeClass('hide');
                $('#divInicioSesion').removeClass('offset-s5');
                $('#articulos2').removeClass('default');
                $('#articulos2').addClass('success');
                $('#mapaCliente2 a').addClass('link');
                $('#mapaCliente2').click(function(){ 
                    $("#articulosDiv").hide();
                    $("#cabeceraDiv").show();
                    $("#mapaArticulos").remove();
                    $("#opcionesTeclas").addClass('hide');
                    $("#correosList").remove();
                    $("#listClient").remove();
                    $("#baina1").remove();
                    $('#articulos2').removeClass('success');
                    $('#articulos2').addClass('default');
                });
                $('#articulosDiv').show();
                $('#cabeceraDiv').hide();
                cabeceraOn = 1;
                ///////////////empieza la cabecera entity////////////
                initArticulosDiv(lineasArr[2]);
                //console.log("Articulos", lineasArr[2]);
                if ($('#articulos tbody tr').length === 0){
                    $('#AgregarLinea').trigger('click');
                }

                
                /////////////////////////////////////////////////////
                $('#breadArticulos').show(function(){
                    $(this).attr('style','display:inline;color:white');
                    if ($('#breadInicio').is(':visible')){
                        $('#breadInicio').css('color','rgba(255,255,255,0.7)');
                    }
                });
                
                $('body').addClass('theme');
                $('#AgregarLinea').appendTo($('#articulos'));
                $('#TarjetasParticipantes').appendTo($('#articulos'));
                $('#QuitarLinea').html('<i class="mdi-action-delete"></i>Eliminar');
                $('#QuitarLinea').addClass('centerFlex');
                $('#QuitarLinea').hide();
                $(document).on('click', '#articulos tbody tr td:first-child label',rowSelected);
                $('#OrderLine-Header ul').prepend('<li class="col m12" id="aqui"></li>');
                $('#EnviarForm').appendTo('#aqui');
                $('#AgregarLinea').attr('data-shortcut', 'Ctrl + Insert');
                $('form#FormArticulos > .row + .row').prepend('<h4 id="resumenTitle"><i class="mdi-content-content-paste left tooltipped" data-name="mdi-file-document-box"></i> Resumen</h4>');
                // $('form#FormArticulos > .row + .row').append('<h4 id="clienteTitle" class="mt"><i class="mdi-action-account-box left tooltipped" data-name="mdi-file-document-box"></i> Cliente</h4>');
                $('#articulos').colResizable({resizeMode:'overflow'});
                /////////////////////////////////////////////////////////////////////////////////////////
            }
        }
        else{
            swal('Error!','Favor de asegurarse de colocar clave de cliente y no el nombre','error');
            $("#claveclte").focus();
        }        
    },500);    
}
function agregarLinea(autocomp_opt,band){
    $('#AgregarLinea').addClass('load');
    var tmpBandAgregar = band ? band : false;
    if ( ($('.emptyRow').length < 1) || tmpBandAgregar ){
        var numLinea = $('#articulos >tbody >tr').length + 1;
        $("#NumFilas").val(numLinea);
        var newLine = $('<tr class="emptyRow"><td><p id="LineaCheck"><input type="checkbox" id="numLinea'+ numLinea + '" value="'+ numLinea + '" /><label id="LblNumLinea'+ numLinea + '" for="numLinea'+ numLinea + '" onclick="rowSelected()">'+ numLinea + '</label></p></td><td><input class="item input-table valido" type="text" name="item'+ numLinea + '" id="item'+ numLinea + '" /><input type="hidden" class="dataAreaId" name="dataAreaId'+ numLinea +'" id="dataAreaId'+ numLinea +'" value="nodefinido"/><input type="hidden" class="InventoryLotId" name="InventoryLotId'+ numLinea +'" id="InventoryLotId'+ numLinea +'" value="nodefinido"/><input type="hidden" class="cargoMoneda" name="cargoMoneda'+ numLinea +'" id="cargoMoneda'+ numLinea +'" value="0"/></td><td><button class="btn-copiar btn-copiar-no-list" type="button" data-copy><i class="mdi-content-content-copy" data-name="mdi-file-document-box"></i> Copiar</button><textarea rows="1" cols="26" class="input-table valido tacoment"  data-activates="dropdownComentarioLinea'+numLinea+'" readonly style="width: 300px;" type="text" id="descripcion'+ numLinea + '" name="descripcion'+ numLinea + '"></textarea></td><td><input class="input-table getPrice center-align valido" min="1" id="cantidad'+ numLinea + '" name="cantidad'+ numLinea + '" data-disp="" value=""/></td><td><input class="input-table valido" readonly type="text" id="unidad'+ numLinea + '" name="unidad'+ numLinea + '" style="text-transform:uppercase" /></td><td><input class="input-table cambioSitio valido" readonly type="text" id="sitio'+ numLinea + '" name="sitio'+ numLinea + '" /></td><td><input class="input-table cambioAlmacen valido" type="text" readonly data-almacen="" id="almacen'+ numLinea + '" value="" name="almacen'+ numLinea + '" /></td><td><input class="input-table BatchAvailable"  data-activates="dropdownLote'+numLinea+'" type="text" id="lote'+ numLinea + '" name="lote'+ numLinea + '" /></td><td><input class="input-table" readonly type="text" id="localidad'+ numLinea + '" name="localidad'+ numLinea + '" /></td><td><input class="input-table" readonly type="text" id="matricula'+ numLinea + '" name="matricula'+ numLinea + '" readonly/></td><td><select class="browser-default" id="categoria'+ numLinea + '" name="categoria'+ numLinea + '"><option value="">N/A</option><option value="CONSUMIBLE">CONSUMIBLE</option><option value="EQUIPO">EQUIPO</option><option value="REFACCION">REFACCION</option><option value="SERVICIO">SERVICIO</option></select></td><td style="text-align: right !important;"><input class="input-table right-align IsPriceBlocked preciovta valido"  data-activates="dropdownPUnitarioLinea'+numLinea+'"  type="text" readonly id="preciovta'+ numLinea + '" style="text-align: right; margin-right: 0px !important;" /></td><td style="text-align: right !important;"><input class="input-table right-align preciovtaiva valido" type="text" readonly id="preciovtaiva'+ numLinea + '" style="text-align: right; margin-right: 0px !important;" /></td><td style="text-align: right !important;"><input class="input-table right-align valido" type="text" readonly name="montocargo'+ numLinea + '" id="montocargo'+ numLinea + '" style="text-align: right; margin-right: 0px !important;" /></td><td style="text-align: right !important;"><input class="input-table right-align valido" type="text" readonly name="montoiva'+ numLinea + '" id="montoiva'+ numLinea + '" style="text-align: right; margin-right: 0px !important;" /></td></tr>');
        $(".item",newLine).autocomplete(autocomp_opt);
        $("#articulos").append(newLine);        
        $("#preciovta" + numLinea).parent('td').append('<div id="dropdownPUnitarioLinea' + numLinea +'" class="punitario-dropdown-content coment"><div class="input-field" style="margin-top:20px;"><input type="number" id="punitariolinea'+numLinea+'" name="punitariolinea'+numLinea+'" readonly min="0" value="0" /><label for="punitariolinea'+numLinea+'">Precio Unitario<button class="btn-small red right closeCambioPrecio" type="button">X</button></label></div></div>');
        $("#descripcion" + numLinea).parent('td').append('<div id="dropdownComentarioLinea' + numLinea +'" class="comentarios-dropdown-content coment"><div class="input-field" style="margin-top:20px;"><textarea id="comentariolinea'+numLinea+'" name="comentariolinea'+numLinea+'"  class="materialize-textarea"></textarea><label for="comentariolinea'+numLinea+'">Comentarios<button class="btn-small red right closeComentLinea" type="button">X</button></label></div></div>');
        $("#lote" + numLinea).parent('td').append('<div id="dropdownLote' + numLinea +'" class="lote-dropdown-content coment"><div class="row"><h5>Disponible<button class="btn-small red right closeLoteLinea" type="button">X</button></h5><div class="divider"></div><table id="DisponibleLote'+numLinea+'"><thead><tr><th>Almacén</th><th>Número de Lote</th><th>Física Disponible</th><th></th></tr></thead><tbody></tbody></table></div><br/><div class="divider"></div><div class="row" style="margin-top:20px; margin-bottom:10px;"><a class="btn light-blue darken-4 white-text right" id="ElegirLote'+numLinea+'">Aceptar</a></div></div>');	
        $(".dropdown-button").dropdown();
        $("#item"+numLinea).focus();
        bandAgregarFam = false;
        $('#AgregarLinea').removeClass('load');
        setTimeout(function(){$('#AgregarLinea').removeClass('load');}, 500);	
    }else{
        $('.emptyRow').find('.item').focus();
        $('#AgregarLinea').removeClass('load');
    }
}
function getProductDetail (myRow,ui){
    gaTrigger('InventItemPrices','entity',userBranchOffice);

    var art         = ui.item.value;
    var excedido    = ( $('#cantidad' + myRow).hasClass('excedido') ) ? 'excedido' : 'ok';
    var cliente     = $("#cliente").val();
    $.map(items,function(itm){
        if (itm.value == art){
            $("#item" + myRow).val(art);
            $("#item" + myRow).removeClass('invalid');
            $("#descripcion" + myRow).val(itm.productSearchName);
            $("#cantidad" + myRow).val("1");                                                                         
            $("#unidad" + myRow).val(itm.salesUnitSymbol);
            $('#modal1 #preloaderExistencias').show();
            $('#ExistenciasSitioClte tbody').html('');
            $('#ExistenciasTodosSitios tbody').html('');
            $('#modal1 #cancExistLinea').val(myRow);
            getExistencias(art,myRow,itm.salesUnitSymbol,itm.productSearchName,excedido,cliente);//Manda a llamar la funcion de existencias
            $('#modal1').openModal({dismissible: false});
            $('#lean-overlay').remove();
            $('ul.tabs').tabs('select_tab', 'test1');
            ValidarFraccionado(art,myRow);
            $('.lean-overlay').remove();//Se manda a llamar la función que muestra el control de pedacería (FRACCIONADO)
            $("input[name='cantidadSol']").focus();
            $('#AgregarLinea').removeClass('load');
            $('#AgregarLinea').addClass('flete');
        }
    });
    // $.ajax({

    //     url:"inicio/productoDetalle",
    //     type:"post", 
    //     data:{"token":"productdetail","articulo":art},

    //     async: true,
    //     beforeSend: function (xhr) {
    //         Materialize.toast('Procesando detalles de articulo: '+art,3000);
    //     },
    //     success: function (data, textStatus, jqXHR) {
    //         var d=data;
    //         if(data.length>0){
    //             $.each(d,function (i,v){
    //                 $("#item" + myRow).val(art);
    //                 $("#item" + myRow).removeClass('invalid');
    //                 $("#descripcion" + myRow).val(d[i].label);
    //                 $("#cantidad" + myRow).val("1");			    					    			   						
    //                 $("#unidad" + myRow).val(d[i].unidad);
    //                 $('#modal1 #preloaderExistencias').show();
    //                 $('#ExistenciasSitioClte tbody').html('');
    //                 $('#ExistenciasTodosSitios tbody').html('');
    //                 $('#modal1 #cancExistLinea').val(myRow);
    //                 getExistencias(art,myRow,d[i].unidad,d[i].label,excedido,cliente);//Manda a llamar la funcion de existencias
    //                 $('#modal1').openModal({dismissible: false});
    //                 $('#lean-overlay').remove();
    //                 $('ul.tabs').tabs('select_tab', 'test1');
    //                 ValidarFraccionado(art,myRow);
    //                 $('.lean-overlay').remove();//Se manda a llamar la función que muestra el control de pedacería (FRACCIONADO)
    //                 $("input[name='cantidadSol']").focus();
    //                 $('#AgregarLinea').removeClass('load');
    //                 $('#AgregarLinea').addClass('flete');                    
    //             });
    //         }
    //         else{
    //             swal("Alto","Artículo no existe.",'info');
    //         }
    //     },
    //     error: function (jqXHR,exep){
    //        Materialize.toast('Error!'+catchError(jqXHR,exep), 3000);
    //     }                                            
    // });
}
function solicitaTraspaso(item,desc,existencia,id,almacen,diferencia){
    
    var almacenDestino='CEDSCONS';
    if(almacen==='CHIHMRMA'){
        almacenDestino='CEDSMRMA';
    }
    if(almacen==='CHIHESPC'){
        almacenDestino='CEDSESPC';
    }
    if(almacen==='CHIHEQPS'){
        almacenDestino='CEDSEQPS';
    }
    if(almacen==='VEQPCONS' || almacen ==='VEQPEQPS'){
        almacenDestino='CEDSEQPS'
    }
    var solicitar=$('#'+id).val();
    if(diferencia>0){
        solicitar=diferencia;
    }
    if(existencia>=solicitar){
        swal({
            title: 'Seleccione un motivo',
            html:   '<select id="swal-input1" required="" class="browser-default" name="motivo">'+
                        '<option value="NO HAY EXISTENCIA">NO HAY EXISTENCIA</option>'+
                        '<option value="MATERIAL EN PEDACERIA">MATERIAL EN PEDACERIA</option>'+
                        '<option value="MATERIAL CADUCO">MATERIAL CADUCO</option>'+
                        '<option value="MATERIAL INSUFICIENTE">MATERIAL INSUFICIENTE</option>'+
                        '<option value="COMPLETAR PEDIDO">COMPLETAR PEDIDO</option>'+
                        '<option value="EN EL SISTEMA SI HAY, FISICAMENTE NO ESTA">EN EL SISTEMA SI HAY, FISICAMENTE NO ESTA</option>'+
                    '</select>'
                    +'<input id="swal-input3"  name="solicitado" value="'+solicitar+'" class="swal2-input" placeholder="CANTIDAD SOLICITADA">'
                    +'<input id="swal-input2" name="comentarios" class="swal2-input" placeholder="COMENTARIOS">',
            showCancelButton: true,
            confirmButtonText:'Enviar solicitud',
            cancelButtonText:'Cancelar'
        }).then(function (result){ 
            var f = new Date();
            var msj='<table style="width: 100%;border-collapse: collapse;" border="1" cellpadding="5">'+
                '<tbody>'+
                 '   <tr style="background-color: rgb(222, 222, 222);font-weight: bold;">'+
                 '      <td style="text-align: center;">'+
                 '         <span>SOLICITUD DE TRASPASO DE MATERIALES DE CEDIS A ALMACEN CHIHUAHUA</span>'+
                 '      </td>'+
                 '</tr>'+
                   '<tr>'+
                    '   <td>'+
                     '      <table style="width: 100%;border-collapse: collapse;" border="1" cellpadding="5">'+
                      '         <tbody><tr>'+
                       '                <td style="text-align: center;color: #333;font-weight: bold;">'+
                        '                   FECHA DE SOLICITUD:'+
                         '              </td>'+
                          '             <td>'+f.getDate() + "/" + (f.getMonth() +1) + "/" + f.getFullYear()+'</td>'+
                           '        </tr>'+
                            '       <tr>'+
                             '          <td style="text-align: center;color: #333;font-weight: bold;text-align: center;">CÓDIGO DE CLIENTE:</td>'+
                              '         <td>'+$('#claveclte').val()+'</td>'+
                               '    </tr>'+
                                '   <tr>'+
                                 '      <td style="color: #333;font-weight: bold;text-align: center;">'+
                                  '         NOMBRE DEL CLIENTE:'+
                                   '    </td>'+
                                    '   <td>'+$('#cliente').val()+'</td>'+
                                   '</tr>'+
                                  ' <tr>'+
                                   '    <td style="text-align: center;color: #333;font-weight: bold;">'+
                                    '       MOTIVO DE LA SOLICITUD:'+
                                     '  </td>'+
                                      ' <td style="background-color: #ff9800;">'+$('#swal-input1').val()+'</td>'+
                                   '</tr>'+
                                   ' <tr>'+
                                   '    <td style="text-align: center;color: #333;font-weight: bold;">'+
                                    '       MOVIMIENTO:'+
                                     '  </td>'+
                                      ' <td>'+almacenDestino+' A '+almacen+'</td>'+
                                   '</tr>'+
                                   ' <tr>'+
                                   '    <td style="text-align: center;color: #333;font-weight: bold;">'+
                                    '       CANTIDAD PARA VENTA:'+
                                     '  </td>'+
                                      ' <td>'+$('#'+id).val()+'</td>'+
                                   '</tr>'+
                               '</tbody>'+
                           '</table>'+
                       '</td>'+
                   '</tr>'+
                   '<tr>'+
                  '     <td>'+
                    '        <table style="width: 100%;border-collapse: collapse;" border="1" cellpadding="5>'+
                    '           <tr style="background-color: rgb(222, 222, 222);font-weight: bold;"><th>CLAVE ARTÍCULO</th><th>DESCRIPCIÓN DEL ARTICULO</th><th>CANTIDAD</th></tr>'+
                     '          <tr><td>'+item+'</td><td>'+decodeURI(desc)+'</td><td style="text-align: center;">'+$('#swal-input3').val()+'</td></tr>'+
                      '     </table>'+
                       '</td>'+
                   '</tr>'+
                   '<tr>'+
                      '  <td>'+
                    '       <table style="width: 100%;border-collapse: collapse;" border="1" cellpadding="5">'+
                    '           <tbody>'+
                     '              <tr>'+
                      '                 <td style="text-align: center;color: #333;font-weight: bold;text-align: center;">COMENTARIOS</td>'+
                       '                <td>'+$('#swal-input2').val()+'</td>'+
                        '           </tr>'+
                         '          <tr>'+
                          '             <td style="text-align: center;color: #333;font-weight: bold;text-align: center;">VENDEDOR</td>'+
                           '            <td>'+usuario+' - '+userName+'</td>'+
                            '       </tr>'+
                             '  </tbody>'+
                           '</table>'+
                       '</td>'+
                   '</tr>'+
               '</tbody>'+
           '</table>';
           $.ajax({
               url:"index/email",type:"post", 
               data:{
                   titulo:"Solicitud de traspaso",
                   mensaje:msj,
                   asunto:"Solicitud de traspaso "+item,
                    formato:"traspasosSolicitud.html",
                    type:0,
                    cliente:$('#claveclte').val(),
                    user:userMail,
                    item:item,
                    venta:$('#'+id).val(),
                    cant:$('#swal-input3').val(),
                    almacen:almacenDestino+' A '+almacen,
                    vendedor:usuario+' - '+userName,
                    comenta:$('#swal-input2').val(),
                    motivo:$('#swal-input1').val()
                },
                success: function (data, textStatus, jqXHR) {
                    if(data=="enviado"){
                        swal({
                            type: 'success',
                            html: 'Correo enviado favor de verificar en la bandeja d entrada de correo'
                        });
                    }
                    else{
                        swal({
                            type: 'error',
                            html: 'Correo no enviado favor de intentar de nuevo <br>'+data
                        });
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    swal({
                        type: 'error',
                        html: textStatus+'    '+errorThrown
                    });
                }
            });        
        });
    }
    else{
        swal({
            type: 'info',
            html: 'Cantidad a solicitar <b>es mayor</b> que la existencia actual'
        });
    }
}

function guardarNegados(edicion,documentType){
	bandNegados = false;
	$('#modalNegados').closeModal({
		complete : bandNegados = true
	});
	var comentariosNegados  = [];
	comentariosNegados[1]   = 'Surtir material  cliente lo empezara a usar.';
	comentariosNegados[2]   = 'No surtir, solo cotizo.';
	comentariosNegados[3]   = 'No surtir, cliente lo requería en el momento no puede esperar su envío.';
	comentariosNegados[4]   = 'Surtir, cliente requiere material por temporada.';
	comentariosNegados[5]   = 'No surtir, compra excepcional.';
	comentariosNegados[6]   = 'No surtir, se envió de otra sucursal.';
	comentariosNegados[7]   = 'Surtir material para venta exclusiva de proyecto.';
	if (bandNegados){
		var tr              = $('#articulosNegados tbody tr');
                var arr = [];
		$.each(tr,function(v,index){
                    var obj ={ };
			obj.cantNegada  = $(this).attr('data-cantnegada');
			obj.artNegado   = $(this).attr('data-articulo');
			obj.almacen     = $(this).attr('data-almacen');
			obj.sitio       = $(this).attr('data-sitio');
			obj.cantDisp    = $(this).attr('data-disponible');
			obj.unidad      = $(this).attr('data-unidad');
			obj.descripcion = $(this).attr('data-descripcion');
			obj.cliente     = $("#claveclte").val();
			var chk         = $(this).find('td').find('input:checked');
			id              = $(chk).attr('id');
			id              = id.split('-');
			id              = id[0].replace('opc','');
			obj.comentario  = comentariosNegados[id];
                        arr.push(obj);
                    });
                    $.ajax({url: "inicio",type: "post",dataType: "json",
	    	    data: {'token':'generarNegado','data':arr},
	    	    success: function (data){
	    	    	if (data == 'OK'){
	    	    		Materialize.toast('Articulo(s) agregado con exito a negados',3000);
	    	    	}else{
	    	    		Materialize.toast('Articulo no agregado a negados',3000,'red');
	    	    	}
	    	    	var preciosEnCero = checarPrecios();
                        if (!preciosEnCero){
                            $('#seguridad3Digitos').val('0000');
                            confirmarCot(edicion,documentType);
                        }else{
                                swal({
                                    type: 'error',
                                    html: 'Existen precios en cero, favor de verificar.'
                                });
                        }
	    	    }	    	
		});
	}
}
$(document).on('click','#agregarItemsFamilia, #btnAgregarDetVta',function(e){
        if ( $(this).attr('id') == 'agregarItemsFamilia' ){
                $('#ExistenciaFamiliaModal').closeModal();
        }else if ( $(this).attr('id') == 'btnAgregarDetVta'  ){
                $('#UltimasVentasModal').closeModal();
        }   
     $(familiaArr).each(function(){
            var lineaActual = $('#NumFilas').val();
            if ( $('#item'+lineaActual).val() != '' ){
                    bandAgregarFam = true;
                    $('#AgregarLinea').trigger('click');
                    lineaActual = $('#NumFilas').val();
            }
            $('#item'+lineaActual).val(this.item);
            $('#descripcion'+lineaActual).val(this.nombre);
            $('#cantidad'+lineaActual).val(this.qty);
            $('#cantidad'+lineaActual).attr("data-lastvalue",this.qty);
            $('#unidad'+lineaActual).val(this.unidad);
            $('#sitio'+lineaActual).val(this.sitio);
            $('#almacen'+lineaActual).val(this.almacen);
            $('#localidad'+lineaActual).val(this.localidad);
            $('#cantidad'+lineaActual).trigger('change');
        });
        familiaArr = [];
    });
//////////////////script articulos//////////////////////////////////////////////////////

	$('#prestashop1').click(function(){
		var prestashop = 'x';
		url = 'http://svr01:8080/prestashop'+prestashop+'/inicio/'+urlAmigable+'?token=iframe#image-block';
		$('#iframePrestashop').attr('src',url);
	});

	$('#prestashop2').click(function(){
		var prestashop = 'y';
		url = 'http://svr01:8080/prestashop'+prestashop+'/inicio/'+urlAmigable+'?token=iframe#image-block';
		$('#iframePrestashop').attr('src',url);
	});

	$('#prestashop3').click(function(){
		var prestashop = 'z';
		url = 'http://svr01:8080/prestashop'+prestashop+'/inicio/'+urlAmigable+'?token=iframe#image-block';
		$('#iframePrestashop').attr('src',url);
	});
        var urlAmigable = '';
function checarSesion(){
    $.ajax({ url: "inicio",type: "get",dataType: "json",data: { "token":"checarSesion" },
        success: function (data){}
    });
}
        
/////////no ejecuta el submit de la forma///////////////
$('#FormCabecera').on('submit',function(event){
    event.preventDefault();
    if (!$(this).validate().form()){
        if ($('#secretarioventa').val() === ''){
            swal("Alto","El campo Secretario de Venta es requerido!.",'info');
            $('#secretarioventa').focus();
        }
        if ($('#responsableventa').val() === ''){
            swal("Alto","El campo Responsable de venta es requerido!.",'info');
            $('#responsableventa').focus();
        }
        if ($('#origenV').val() === ''){
            swal("Alto",'El campo origen de venta es requerido!.','info');
            $('#origenV').focus();
        }                         
    }
});
function cargarUsuarios(data){ 
    if(data.response=="ok"){
        $('#secretarioventa').html(data.res);
        $('#secretarioventa').select2();
        $('#responsableventa').html(data.res);
        $('#responsableventa').select2();    
        if (edicion == 1) {
            $('#promoCodeInputDiv').hide();
            if (dlvmode == 'PAQUETERIA') {
                $.get('inicio/haslabel', { docID }).done(data => {
                    data = JSON.parse(data);
                    if (data.hasLabel)
                        parcelLabelSubmited = true;
                    else
                        parcelLabelSubmited = false;
                })
            }
            $.get('inicio/getpromos', { edition: true, documentId: docID }).done(function (data) {
                data = JSON.parse(data);
                promoCodes = data;
                if (!data.promoSelected) {
                    selectHtml = '<option value="None" selected>Seleccione codigo promocional</option>';
                    data.promoList.forEach((code, index) => {
                        code.ItemsList = JSON.parse(code.ItemsList);
                        selectHtml += `<option value='${index}'>${code.PromoCode}</option>`;
                    });
                    $('#promoCode').material_select('destroy');
                    $('#promoCode').html(selectHtml);
                    $('#promoCode').select2();
                    $('#promoCode2').material_select('destroy');
                    $('#promoCode2').html(selectHtml);
                    $('#promoCode2').select2();
                } else {

                    selectHtml = '<option value="None">Seleccione codigo promocional</option>';
                    data.promoList.forEach((code, index) => {
                        code.ItemsList = JSON.parse(code.ItemsList);
                        if (code.PromoCode == data.promoSelected[0]['PromoCode']) {
                            editionPromoCodeSelected = index;
                            selectHtml += `<option value='${index}' selected>${code.PromoCode}</option>`;
                        } else {
                            selectHtml += `<option value='${index}'>${code.PromoCode}</option>`;
                        }
                    });
                    $('#promoCode').material_select('destroy');
                    $('#promoCode').html(selectHtml);
                    $('#promoCode').select2();
                    $('#promoCode2').material_select('destroy');
                    $('#promoCode2').html(selectHtml);
                    $('#promoCode2').select2();
                }
                promoCodes = data.promoList;
            });
        } else {
            $.get('inicio/getpromos', { edition: false, documentId: '' }).done(function (data) {
                data = JSON.parse(data);
                promoCodes = data;
                selectHtml = '<option value="None" selected>Seleccione codigo promocional</option>';
                data.forEach((code, index) => {
                    code.ItemsList = JSON.parse(code.ItemsList);
                    selectHtml += `<option value='${index}'>${code.PromoCode}</option>`;
                });
                $('#promoCode').material_select('destroy');
                $('#promoCode').html(selectHtml);
                $('#promoCode').select2();
                $('#promoCode2').material_select('destroy');
                $('#promoCode2').html(selectHtml);
                $('#promoCode2').select2();
            });
        }
    }                                                
    if (data.reg ==1){
        $('#administrarDiv').trigger('click');
        $('#admonMsg').show();
    }else{
        $('#admonMsg').hide();
    }
    $('#modalLoading').closeModal();
}
/**
 * cambia el sitio del docmento
 */
$(document).on('change','#sitioLineas',function(){
    var valor = this.value;
    //console.log("valor", valor);
    $("#almacenes").val(valor+'CONS');
    $("#almacenes").val(valor+'CONS');			
    $('.cambioSitio').val(valor);
    /*$('.cambioAlmacen').each(function(){
            var almac = this.value.slice(-4);
            this.value = valor+almac;
            console.log("cambia almacen onchange: "+this.value)
    });*/    
    $('.cambioAlmacen').each(function(){
        var almac = this.value.slice(-4);
        this.value = valor+almac;
        row = $(this).closest('tr')
        if ( $(row).hasClass('flete') && seccionOV == "LOCAL"){
            index = $(row).find('#LineaCheck').children('label').html();
            $(row).find('#LineaCheck').children('label').click();
            quitarLinea();
        }
            
    });
    $('#pagolineas').change();
});

$(document).on('click',"#GenerarREM",function(e){
    verifica();
    var ov = $("#OrdenVentaRem").val();
    customer = $('#claveclteResumen').html();
    paymentmode = $('#cargodesc').html();
    modoentrega = $('#modoentregaResumen').html();
    condientre = $('#condiEntrega').val();
    $('#proposito').attr('disabled','disabled');
    $('#proposito').material_select('destroy');
    $('#proposito').material_select();
    $.ajax({
        url:'inicio/review-Orden',
        type: 'POST',
        data: {ov:ov,paymode:paymentmode,modoent:modoentrega,condi:condientre},
        beforeSend: function (xhr){
        },
        success : function(data){
            if (data!=""){
                swal("Alerta",data+'<br />inAX fuera de linea, aplicar pago cuando se reestablezca Dynamics 365','warning');
                $('#ImprimirCotizacion').html('<i class="material-icons left">picture_as_pdf</i> IMPRIMIR OV');
                $('#QuotationId').val($('#DocumentId').val());
                $('#ImprimirCotizacion').parent().attr('action','impresion-orden');
                $('#ImprimirCotizacion').show();
            }else{   
                if(localStorage.getItem('bandera')=='si'){
                    $('#preloaderRemision').show();
                    var ov = $("#OrdenVentaRem").val();
                    var condi = $("#condiEntrega").val();
                    if(condi!=="CONTADO"){ 
                        gaTrigger('STF_CustTrans','entity',userBranchOffice);
                        gaTrigger('Customers','entity',userBranchOffice);
                        // ValidarLimiteCredito(ov,usuario,condi);
                        customer = $('#claveclteResumen').html();
                        $.ajax({
                                url:'inicio/review-Credit-Limit',
                                type: 'POST',
                                data: {factura:ov, customer:customer,tipopago:'PPD'},
                                 beforeSend: function (xhr) {
                                    Materialize.toast('Revisando si el cliente puede facturar', 3000);
                                    console.log('Revisando limite de Credito');
                                    //loaderSwal();
                                },
                                success : function(data){
                                    if (data.status){
                                        liberar_a_Almacen(ov);
                                    }else{
                                        //Materialize.toast('No cuenta con credito', 3000);
                                        $('#GenerarREM').hide();
                                        $('#preloaderRemision').hide();
                                        swal('Aviso','El cliente no cuenta con credito disponible para realizar esta transacción, favor de dar aviso al departamento de credito y cobranza','warning');
                                    }
                                }
                            });
                    }
                    else{
                        //generarRemision(ov);
                        liberar_a_Almacen(ov);
                    }        
                }
            }        
        }
    });

});

$(document).on('click','#Remisionar_Facturar',function(e){
    var ov = $('#DocumentId2').val();
    generarRemision(ov);
});

$(document).on('click',"#Facturar",function(e){
    verifica();
    ordenVenta = $('#DocumentIdResumen').html();
    customer = $('#claveclteResumen').html();
    paymentmode = $('#cargodesc').html();
    modoentrega = $('#modoentregaResumen').html();
    condientre = $('#condiEntrega').val();

        $.ajax({
        url:'inicio/review-Orden',
        type: 'POST',
        data: {ov:ordenVenta,paymode:paymentmode,modoent:modoentrega,condi:condientre},
        beforeSend: function (xhr){

        },
        success : function(data){
            if (data!=""){
                swal("Alerta",data+'<br />inAX fuera de linea, aplicar pago cuando se reestablezca Dynamics 365','warning');                            
            }else{  
                if(localStorage.getItem('bandera')=='si'){
                //cargaDatos2Factura();                
                mostrarModalPago(ordenVenta,customer,paymentmode);
                }
            } 
        }
    });    
});


function cargaDatos2Factura(){
    gaTrigger('CustomerPostalAddresses','entity',userBranchOffice);
    $.ajax({
        url:"inicio/get-Direcciones",
        type:"post", 
        data:{ov:$("#DocumentId2").val(),cliente : $('#clte').val()},
        dataType: 'json',
        beforeSend: function (xhr) {
            $("#btnFacturar").hide();
            $('#direccionF').html("");
        },
        success: function (data, textStatus, jqXHR) {
            data = JSON.parse(data);
            var str="";
            $('#direccionF').html(str);
            $.each(data.data,function (i,v){
                str+='<option value="'+i+'">'+v.DESCRIPTION+'</option>';
            });
            $('#direccionF').html(str);   
            $("#btnFacturar").show();
        }            
    });
    $('#modalFactura').openModal({dismissible: false});
}

    function setPayMode(payMode){
        var payArr=[];
        // console.log(cargoOptions);
        if(payMode=='CONTADO'){
            $.each(cargoOptions,function (i,v){
                if(v.PaymMode!=='99'){
                   payArr.push(v); 
                }
            });
        }
        else{
            payArr.push({STF_PercentageCharges:'0.0',PaymMode: "99", NAME: "OTROS"});
        }
        var html='';
        var htmlGroup = '<optgroup label="TARJETA DE CREDITO" class="disabled">';
        $.each(payArr,function (i,v){
        // console.log(Number(v.STF_PercentageCharges));
            var selected='';
            if(paymmodedoc===v.PaymMode){ 
                selected ='selected';
            }
            if (v.PaymMode != '04' && v.PaymMode != '04-1' && v.PaymMode != '04-2' && v.PaymMode != '04-3' && v.PaymMode != '04-4'){
                html += '<option value="'+Number(v.STF_PercentageCharges)+'" data-paymmode="'+v.PaymMode+'" '+selected+'>'+v.NAME+'</option>';
                if (v.PaymMode == '03'){
                    html += 'XX';
                }
            }else{
                htmlGroup += '<option value="'+Number(v.STF_PercentageCharges)+'" data-paymmode="'+v.PaymMode+'" '+selected+' class="TJC">'+v.NAME+'</option>';
            }            
        });
        htmlGroup += '</optgroup>';
        if (payMode == 'CONTADO'){
            html = html.replace('XX',htmlGroup);
        }
        $('#pagolineas').html(html);
        if (edicion != '1'){
            var maxUnitPrice = getMaxUnitPrice();
            disableTJC(maxUnitPrice);
        }
        $('#pagolineas').trigger('change');
        // if ($("#condiEntrega").val() === 'CREDITO') {
        //     $('#pagolineas option').val('0.00');
        // }
    }

    function crearDiario(customer,factura,tipopago){//aqui se crea el diario
        gaTrigger('STF_CustTrans','entity',userBranchOffice);
        gaTrigger('Customers','entity',userBranchOffice);
        gaTrigger('JournalNames','entity',userBranchOffice);
        gaTrigger('CustomerPaymentJournalHeaders','entity',userBranchOffice);
        gaTrigger('CustomerPaymentJournalLines','entity',userBranchOffice);
        gaTrigger('CustomerPaymentJournalLines','entity',userBranchOffice);
        gaTrigger('postPaymentJournal','entity',userBranchOffice);
        try{
            var v = validarDiariosFields();
            if (v){
                if (($('#diarioFPago').val() == 04 || $('#diarioFPago').val() == 28) && !/^(?!(0000))[0-9]{4}$/.test($('#digitosTarjeta').val())) {
                    Materialize.toast("Ingresar Digitos Tarjeta", 3000, 'red');
                    return;
                }
                if($('#diarioFPago').val()=="03"){//si la forma de pago es 03 se realiza una doble validacion en el monto para evitar errores de dedo
                    $('#diarioGuardarBtn').hide();
                    const {value: text0} = swal({//se abre un swal pidiendo la confirmacion
                        type: 'info',
                        title: "Ingrese de nuevo la cantidad",
                        text: "para validación",
                        input:'text',
                        allowOutsideClick: false
                    }).then(function (result){ 
                        if(result==$('#diarioMontoFactura').val()){//si el valor intrducido en el swal es el mismo que esta en el monto continua el proceso
                                var formData = $('#diarioPagoForm').serializeArray();//se guardan los datos del formulario en un arreglo
                                formData.push({'dlvMode':$('#modoentregaResumen').html()});//se agrega el ato de modo de entrega
                                 $.ajax({url:'inicio/review-Credit-Limit',type: 'POST',data: {factura:factura, customer:customer,montocoti:'',tipopago:tipopago},//se revisa si el cliente tiene credito para poder facturar o si es de contado
                                    beforeSend: function (xhr) {
                                        Materialize.toast('Revisando si el cliente puede facturar', 3000);
                                        console.log('Revisando limite de Credito');
                                        $("#diarioPagoForm").trigger('reset');
                                        var totalAmount = $('#diarioMontoFactura').data('totalamount');
                                        $('#diarioMontoFactura').val(totalAmount);   
                                },
                                success: function (data, textStatus, jqXHR) {
                                if(data.status){ //si la revision es satisfactoria continua el proceso
                                    $.ajax({
                                        type: 'POST',
                                        url:'inicio/diario',
                                        data: formData,//se manda la informacion al modelo para pocesar la creacion del diario
                                        dataType: 'json',
                                        beforeSend: function (xhr) {
                                            $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
                                            $('#diarioGuardarBtn').hide();
                                            $('#Facturar').addClass('disabled');
                                        },
                                        success: function (data, textStatus, jqXHR) {
                                             $('#process').html('');
                                             $('#diarioGuardarBtn').show();
                                             if(data.JournalBatchNumber!=''){
                                                totalDiario=data.CreditAmount;
                                                if(totalDiario<=0){
                                                    totalDiario=0;
                                                    $('#diarioGuardarBtn').hide();
                                                }                    
                                                console.log(data.JournalBatchNumber);
                                                if(data.JournalBatchNumber!=undefined){
                                                    $('#closeDiaryButton').show();
                                                    $("#folioDiario").val($("#folioDiario").val()+' '+data.JournalBatchNumber);
                                                    swal("Guardado","Diario creado con exito con folio:"+data.JournalBatchNumber,"info");
                                                    $('#proposito').attr('disabled','disabled');
                                                    $('#proposito').material_select('destroy');
                                                    $('#proposito').material_select();
                                                    $('#diarioMontoFactura').val(totalDiario);
                                                    var ov = $('#DocumentId2').val();
                                                    Materialize.toast('Liberando a Almacen', 3000);
                                                    combinado = validarAlmacenes();
                                                    if (!combinado.combinado){
                                                    if (combinado.tipo == 'CONS'){
                                                    liberar_a_Almacen(ov);
                                                    }else{
                                                    generarRemision(ov);
                                                    }
                                                    }
                                            }else if(data == 'hola'){
                                                swal("Campos requeridos","Verificar documento en Dynamics 365","warning");
                                                $('#diarioGuardarBtn').show();
                                            }else{
                                                swal("No se creeo el diario","Usted no tiene acceso a esta cuenta de contrapartida","warning");
                                                $('#diarioGuardarBtn').show();
                                            }
                                             }
                                             else{
                                                 $('#process').html(data.JournalBatchNumber);
                                                 $('#process').attr("style","color:red");
                                             }
                                             $('#diarioGuardarBtn').hide();
                                        },
                                        error: function (jqXHR, textStatus, errorThrown) {
                                            $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
                                            $('#diarioResult').html(jqXHR.status);
                                            $('#diarioGuardarBtn').show();
                                        }
                                    });        
                                }
                                else{
                                    //Materialize.toast('No cuenta con credito', 3000);
                                    swal('Aviso','El cliente no cuenta con credito disponible para realizar esta transacción, favor de dar aviso al departamento de credito y cobranza','warning');
                                }
                                },
                                error: function (jqXHR, textStatus, errorThrown) {
                                }
                            }); 
                            }else{
                                swal('Las cantidades no coinciden','Intente de nuevo','error');
                                $('#diarioMontoFactura').val('');
                                $('#diarioGuardarBtn').show();
                            }                            
                        });
                }else{//si no es metodo de pago 03 no realiza la doble validacion
                    $('#diarioGuardarBtn').hide();
                    var formData = $('#diarioPagoForm').serializeArray();
                                formData.push({'dlvMode':$('#modoentregaResumen').html()});
                                 $.ajax({url:'inicio/review-Credit-Limit',type: 'POST',data: {factura:factura, customer:customer,montocoti:'',tipopago:tipopago},
                                    beforeSend: function (xhr) {
                                        Materialize.toast('Revisando si el cliente puede facturar', 3000);
                                        console.log('Revisando limite de Credito');
                                        $("#diarioPagoForm").trigger('reset');
                                        var totalAmount = $('#diarioMontoFactura').data('totalamount');
                                        $('#diarioMontoFactura').val(totalAmount); 
                                },
                                success: function (data, textStatus, jqXHR) {
                                if(data.status){  
                                    $.ajax({
                                        type: 'POST',
                                        url:'inicio/diario',
                                        data: formData,
                                        dataType: 'json',
                                        beforeSend: function (xhr) {
                                            $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
                                            $('#diarioGuardarBtn').hide();
                                            $('#Facturar').addClass('disabled');
                                        },
                                        success: function (data, textStatus, jqXHR) {
                                             $('#process').html('');
                                             $('#diarioGuardarBtn').show();
                                             if(data.JournalBatchNumber!=''){
                                                totalDiario=data.CreditAmount;
                                                if(totalDiario<=0){
                                                    totalDiario=0;
                                                    $('#diarioGuardarBtn').hide();
                                                }                    
                                                console.log(data.JournalBatchNumber);
                                                if(data.JournalBatchNumber!=undefined){
                                                    $('#closeDiaryButton').show();
                                                    $("#folioDiario").val($("#folioDiario").val()+' '+data.JournalBatchNumber);
                                                    swal("Guardado","Diario creado con exito con folio:"+data.JournalBatchNumber,"info");
                                                    $('#proposito').attr('disabled','disabled');
                                                    $('#proposito').material_select('destroy');
                                                    $('#proposito').material_select();
                                                    $('#diarioMontoFactura').val(totalDiario);
                                                    var ov = $('#DocumentId2').val();
                                                    Materialize.toast('Liberando a Almacen', 3000);
                                                    combinado = validarAlmacenes();
                                                    if (!combinado.combinado){
                                                    if (combinado.tipo == 'CONS'){
                                                    liberar_a_Almacen(ov);
                                                    }else{
                                                    generarRemision(ov);
                                                    }
                                                    }
                                            }else if(data == 'hola'){
                                                swal("Campos requeridos","Verificar documento en Dynamics 365","warning");
                                            }else{
                                                swal("No se creeo el diario","Usted no tiene acceso a esta cuenta de contrapartida","warning");
                                            }
                                             }
                                             else{
                                                 $('#process').html(data.JournalBatchNumber);
                                                 $('#process').attr("style","color:red");
                                             }
                                             $('#diarioGuardarBtn').hide();
                                        },
                                        error: function (jqXHR, textStatus, errorThrown) {
                                            $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
                                            $('#diarioResult').html(jqXHR.status);
                                            $('#diarioGuardarBtn').show();
                                        }
                                    });        
                                }
                                else{
                                    //Materialize.toast('No cuenta con credito', 3000);
                                    swal('Aviso','El cliente no cuenta con credito disponible para realizar esta transacción, favor de dar aviso al departamento de credito y cobranza','warning');
                                }
                                },
                                error: function (jqXHR, textStatus, errorThrown) {
                                }
                            }); 
                }
            }else{
                swal('Atencion','Existen campos vacios, favor de verificar','warning');
            }
        }
        catch (e){
            console.log(e);
        }              
    }
var descri = $('#descripcion').val();//variable para guardar la descripcion del diario antes de modificarla
    function mostrarModalPago(ordenVenta,customer,paymentmode){
        $('#diarioGuardarBtn').hide();
        gaTrigger('SalesOrderLines','entity',userBranchOffice);
        var factura=ordenVenta;//$("#loadFacturaSt a").text();
        /*falta agregar el monto total*/            
                //Materialize.toast('Si puede facturar', 3000);
                formaPagoFactura="";     
                $('#descripcion').val(descri+" , "+ordenVenta); //agregamos la orden de venta a la descripcion concatenandola con la descripcion guardada en descri           
                $('#modalFactura').closeModal();//se cierra el modal de factura utilizado en el antiguo inax
                $('#diarioPago').openModal({dismissible: false});//se abre el modal de diario de pago 
                $('#folioDiario').val('');//el folio o numero de diario se pone vacio
                $('#diarioMontoFactura').val('');//se vacia el monto del diario 
                $('#customerDiario').val(customer);//se hace la asignacion del cliente al campo de cliente
                $.ajax({url:'inicio/factura-Lines',type: 'POST',data: {ov:ordenVenta},//ajax para obtener los totales de las lineas en la OV
                    beforeSend: function (xhr) {
                        $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
                    },
                    success: function (data, textStatus, jqXHR) {
                        var totalAmount=0;
                        // $.each(data.value,function (i,v){ // ciclo para realizar la suma de las lineas 
                        //     totalAmount+=v.LineAmount;
                        //     sitio = v.ShippingSiteId;
                        // });
                        // var tax = 1.16;
                        // if(sitio.indexOf('TJNA')>=0 ||sitio.indexOf('MEXL')>=0 ||sitio.indexOf('JURZ')>=0){//si la sucursal es mexicali, tijuana o juarez el iva se calcula al 8%
                        //            tax = 1.08;
                        // }
                        // totalAmount=totalAmount*tax;//se hace la operacion para calcular el iva
                        // totalAmount=totalAmount.toFixed(2);

                        totalAmount = $('#totalResumen').html().replace('$','').replace(',','');
                        $('#diarioMontoFactura').val(Number(totalAmount));//se muestra en pantalla el total para generar el diario de pago
                        $('#diarioMontoFactura').attr('data-totalamount',totalAmount);
                        var html='';
                        $('#process').html('');
                        $.each(payModeList.value,function (i,v){//ciclo que genera la lista de formas de pagoe
                            var sel='';
                            if(paymentmode==v.Name){sel='selected="selected"';}
                            html+='<option value="'+v.Name+'" '+sel+'>'+v.Description+'</option>';
                        });
                        formaPagoFactura=paymentmode;
                        $('#diarioFPago').html(html);//se agrega la lista al combo box  de tipos de pago
                        $('#diarioFPago').trigger('change');
                        var selec = $('#diarioFPago option:selected').text();

                        gaTrigger('BankAccounts','entity',userBranchOffice);
                        $.ajax({
                        url:'inicio/cuenta-Contrapartida-Linea',type: 'POST',data: {selec:selec},//obtiene las cuentas de banco a las cuales se va a depositar el dinero
                        beforeSend: function (xhr) {
                            $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
                        },
                        success: function (data, textStatus, jqXHR) {
                            var html='';
                            $('#process').html('');
                            $('#diarioCuentaContra').html(html);
                            $.each(data.value,function (i,v){
                                html+='<option value="'+v.BankAccountId+'">'+v.Name+'</option>'; 
                            });
                            $('#diarioCuentaContra').html(html);
                            $('#diarioGuardarBtn').show();
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
                            $('#diarioResult').html(jqXHR.status);
                        }
                        });

                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
                        $('#diarioResult').html(jqXHR.status);
                    }
                    });
                $('#diarioFacturaFolio').val(factura);
                $.ajax({url:'inicio/cuenta-Contrapartida',type: 'POST',data: {},
                    beforeSend: function (xhr) {
                        $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
                    },
                    success: function (data, textStatus, jqXHR) {
                        $('#process').html('');
                        $('#contraPartida').html('');
                        $.each(data.JournalName.value,function (i,v){
                            var selected='';
                             $.each(data.GroupUser.value,function (e,x){                        
                            if(x.groupId==v.DocumentNumber){// se hace la validacion d los grupos de usuario a los que pertenece el usuario y solo se muestran los que tenga permiso de ver
                            $("#contraPartida").append('<option value="'+v.Name+'" '+selected+'>'+v.Name+"</option>");
                            }
                            });
                        });
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
                        $('#diarioResult').html(jqXHR.status);
                    }
                });                  
    }
function changeCurrency(currency) {
    if (currency == 'USD') {
        $('#currencyInput').val('USD');
        $('#diarioCuentaContra').html('<option value="BANMEX6065" selected>BANMEX6065</option>');
    } else {
        $('#currencyInput').val('MXN');
        $('#diarioFPago').change();
    }
}
    var el_monto;
    function diarioFormaPago(fp,sucu){//cuando se cambia de tipo de pago se ejecuta esta funcion que llena la lista de cuentas de banco para depositar
        gaTrigger('BankAccounts','entity',userBranchOffice);
        // if ($('#currencyInput').val() == 'USD') {
        //     return $('#diarioCuentaContra').html('<option value="BANMEX6065" selected>BANMEX6065</option>');
        // }
         var selec = $('#diarioFPago option:selected').text();
                $.ajax({
                url:'inicio/cuenta-Contrapartida-Linea',type: 'POST',data: {selec:selec},
                beforeSend: function (xhr) {
                    $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
                },
                success: function (data, textStatus, jqXHR) {
                    var html='';
                    $('#diarioCuentaContra').html(html);
                    $.each(data.value,function (i,v){
                        html+='<option value="'+v.BankAccountId+'">'+v.Name+'</option>'; 
                    });
                    $('#diarioCuentaContra').html(html);
                    if(fp  =="03"){ //si la forma de pago es 03 o transferencia se habilita un campo de referencia para que el vendedor pueda teclear el folio de la ficha de deposito
                        descri = $('#descripcion').val();
                        descri = descri.replace("Cobros","");
                        descri = descri.replace(sucu,"");
                        $('#descripcion').val(descri);                   
                        $('#referenciap').show();
                        $('#labelreferencia').show();
                        el_monto = $('#diarioMontoFactura').val();
                        $('#diarioMontoFactura').val('');
                    }else{
                        $('#referenciap').hide();
                        $('#labelreferencia').hide(); 
                    }
                    var totalAmount = $('#diarioMontoFactura').data('totalamount');
                    $('#diarioMontoFactura').val(totalAmount);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
                    $('#diarioResult').html(jqXHR.status);
                }
                });  
    }

function validarDiariosFields(){
    var valido = true;
    $('#diarioPagoForm .required').each(function(){
        if ( this.value == '' ){
            if ( ($('#diarioFPago').val() != '99' || $('#diarioFPago').val() != '30') && this.id != 'diarioCuentaContra'  ){
                valido = false;
            }
        }else if ( this.id == 'diarioMontoFactura' && this.value == 0 ){
            valido = false;
        }
    });
    return valido;
}

function revisionMonto(monto){//esta funcion corrobora que el monto que se desea poner en el diario corresponda a lo pactado en la OV
    rango = (el_monto-(el_monto*1.5))*(-1);//el 1.5 es el porcentaje de rango  +/- que puede poner extra el vendedor
    console.log(monto);
    console.log(el_monto);
    console.log(el_monto-rango);
    console.log(Number(el_monto) + Number(rango));
    if(monto >= (Number(el_monto) + Number(rango)) || monto <= (Number(el_monto) - Number(rango))){
        $('#diarioMontoFactura').val('');
        swal('No coincide','El monto no coincide con la orden de venta','error');
    }
}

function facturar(){
    if(havePermision(14)){
      $('#GenerarREM').click();
    }
    else{
        $.ajax({ 
            url:"inicio/facturar",type:"post", data:{
                ov:$("#DocumentId2").val(),
                remision:$('#PackingSlipId2PDF').val(),
                ordenCliente:$('#OrdenCliente').val(),
                refCliente:$('#ReferenciaCliente').val(),
                comentariosCabecera:$('#comentariosCabecera').val(),
                direccion:$('#direccionF').val(),
                usoCFDi:$('#usoCFDI option:selected').val(),
                pagoModo:$('#pagolineas option:selected').attr('data-paymmode'),
                pago:$('#paytermArt').val()
            },dataType: 'json',
            beforeSend: function (xhr) {
                $('#loadFacturaSt').html('<img src="../application/assets/img/cargando.gif" style="width: 1em;">');
                $("#btnFacturar").hide();
            },
            success: function (data, textStatus, jqXHR) {
                if(data.resultado==="ok"){
                    var pago=$('#entregaF').val();
                    console.log(data);
                    $('#loadFacturaSt').html('<a id="folioFactura" href="http://svr02:8989/FacturacionCajas/PDFFactura.php?ov='+$("#DocumentId2").val()+'&amp;tipo=CLIENTE" target="_blank">'+data.respuesta+'</a>');
                    $("#btnFacturar").hide();
                    refreshLines();
                    if(havePermision(12)){
                        $('#footerFactura').append('<a onclick="mostrarModalPago()" class="waves-effect light-blue darken-4 white-text btn-flat" style="margin-right: 13px;">Asociar Factura a Diario de Pago</a>');
                }
                }
                else if(data.resultado=="bad"){
                    $('#loadFacturaSt').html('<label style="color:red;">'+data.respuesta+'</label>');
                    $("#btnFacturar").show();
                }
                else{
                    $('#loadFacturaSt').html('<a>'+data.respuesta+'</a>');
                    $("#btnFacturar").show();
                }                
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
                $("#btnFacturar").show();
            }
        });  
    }
           
}

$(document).on('click','#actionArticulos',function(e){
    // $('#actionArticulos').attr("disabled",'disabled');//bloqueo de boton siguiente
    if(editarOV == "NoResults"){
        verifica();
        combinado = validarAlmacenes();
        //confirmaDocumento('');
        if(localStorage.getItem('bandera')=='si' && !combinado.combinado){
            var registros = $("#item1").val();
            var entregaF= $("#entregalineas").val();
            var flag=false;
            if(registros!=""){flag=true;}
            if(entregaF===null){flag=false;}
            if(flag==true){
                $('#confirmarDocumento2').removeClass('default');
                $('#confirmarDocumento2').addClass('success');
                $('#editar2').removeClass('default');
                $('#editar2 a').addClass('link');
                $('#mapaCliente2 a').removeClass('link');
                $('#editar2').addClass('success');
                $('#mapaCliente2').unbind('click');
                $('#editar2').click(function (){
                    console.log("click editar2");
                    bread3();
                });
                var negados = checarNegados();
                if (!negados){
                        var preciosEnCero = checarPrecios();
                        if (!preciosEnCero){			
                                tipoCargo = $("#pagolineas option:selected").attr('data-paymmode');
                                Materialize.toast('Comenzando proceso de confirmacion',2000);
                                $('#loadNext').html('<center><img style="width: 12%;" src="../application/assets/img/cargando.gif"></center>');
                                $('#seguridad3Digitos').val('0000');
                                confirmarCot(); 
                                $('#confirmarDocumento2').removeClass('default');
                                $('#confirmarDocumento2').addClass('success');
                                $('#editar2').removeClass('default');
                                $('#editar2').addClass('success');
                                $('#imprimirDocumento2').removeClass('default');
                                $('#imprimirDocumento2').addClass('final');
                        }else{
                                Materialize.toast('Existen precios en cero, favor de verificar.',5000,'red');
                                // $("#actionArticulos").removeAttr('disabled');//bloqueo de boton siguiente
                        }
                }
            }
            else{
                // $("#actionArticulos").removeAttr('disabled');//bloqueo de boton siguiente
                swal('¡Alto!','Debe agregar un artículo o definir modo de entrega','info');
            }
        }else if (combinado.combinado){
            // $("#actionArticulos").removeAttr('disabled');//bloqueo de boton siguiente
            swal('Error!','Asegurese de tener elemento en las lines de un solo almacen.','error');
        }
    }else{
        ov = $('#DocumentId2').val();    
        //console.log("ov", ov);
        if(ov.includes("COTV")){
            verifica();
            combinado = validarAlmacenes();
            //confirmaDocumento('');
            if(localStorage.getItem('bandera')=='si' && !combinado.combinado){
                var registros = $("#item1").val();
                var entregaF= $("#entregalineas").val();
                var flag=false;
                if(registros!=""){flag=true;}
                if(entregaF===null){flag=false;}
                if(flag==true){
                    $('#confirmarDocumento2').removeClass('default');
                    $('#confirmarDocumento2').addClass('success');
                    $('#editar2').removeClass('default');
                    $('#editar2 a').addClass('link');
                    $('#mapaCliente2 a').removeClass('link');
                    $('#editar2').addClass('success');
                    $('#mapaCliente2').unbind('click');
                    $('#editar2').click(function (){
                        console.log("click editar2");
                        bread3();
                    });
                    var negados = checarNegados();
                    if (!negados){
                            var preciosEnCero = checarPrecios();
                            if (!preciosEnCero){			
                                    tipoCargo = $("#pagolineas option:selected").attr('data-paymmode');
                                    Materialize.toast('Comenzando proceso de confirmacion',2000);
                                    $('#loadNext').html('<center><img style="width: 12%;" src="../application/assets/img/cargando.gif"></center>');
                                    $('#seguridad3Digitos').val('0000');
                                    confirmarCot(); 
                                    $('#confirmarDocumento2').removeClass('default');
                                    $('#confirmarDocumento2').addClass('success');
                                    $('#editar2').removeClass('default');
                                    $('#editar2').addClass('success');
                                    $('#imprimirDocumento2').removeClass('default');
                                    $('#imprimirDocumento2').addClass('final');
                            }else{
                                    Materialize.toast('Existen precios en cero, favor de verificar.',5000,'red');
                                    // $("#actionArticulos").removeAttr('disabled');//bloqueo de boton siguiente
                            }
                    }
                }
                else{
                    // $("#actionArticulos").removeAttr('disabled');//bloqueo de boton siguiente
                    swal('¡Alto!','Debe agregar un artículo o definir modo de entrega','info');
                }
            }else if (combinado.combinado){
                // $("#actionArticulos").removeAttr('disabled');//bloqueo de boton siguiente
                swal('Error!','Asegurese de tener elemento en las lines de un solo almacen.','error');
            }
        }else{
            $.ajax({
                url: "inicio/status", 
                type: "post", 
                dataType: "json", 
                data: {'ov': ov},
                success: function (data) { 
                    if(data.ReleaseStatus == "Released" || data.ReleaseStatus == "PartialReleased"){
                        $('#actionArticulos').attr("disabled",'disabled');
                        swal('¡Alto!','No se puede editar la Orden de Venta','error')
                        .then(function(result){
                            if(result){
                                window.location.href='../public/inicio?COT'
                            }
                        });
                    }else{
                        verifica();
                        combinado = validarAlmacenes();
                        //confirmaDocumento('');
                        if(localStorage.getItem('bandera')=='si' && !combinado.combinado){
                            var registros = $("#item1").val();
                            var entregaF= $("#entregalineas").val();
                            var flag=false;
                            if(registros!=""){flag=true;}
                            if(entregaF===null){flag=false;}
                            if(flag==true){
                                $('#confirmarDocumento2').removeClass('default');
                                $('#confirmarDocumento2').addClass('success');
                                $('#editar2').removeClass('default');
                                $('#editar2 a').addClass('link');
                                $('#mapaCliente2 a').removeClass('link');
                                $('#editar2').addClass('success');
                                $('#mapaCliente2').unbind('click');
                                $('#editar2').click(function (){
                                    console.log("click editar2");
                                    bread3();
                                });
                                var negados = checarNegados();
                                if (!negados){
                                        var preciosEnCero = checarPrecios();
                                        if (!preciosEnCero){			
                                                tipoCargo = $("#pagolineas option:selected").attr('data-paymmode');
                                                Materialize.toast('Comenzando proceso de confirmacion',2000);
                                                $('#loadNext').html('<center><img style="width: 12%;" src="../application/assets/img/cargando.gif"></center>');
                                                $('#seguridad3Digitos').val('0000');
                                                confirmarCot(); 
                                                $('#confirmarDocumento2').removeClass('default');
                                                $('#confirmarDocumento2').addClass('success');
                                                $('#editar2').removeClass('default');
                                                $('#editar2').addClass('success');
                                                $('#imprimirDocumento2').removeClass('default');
                                                $('#imprimirDocumento2').addClass('final');
                                        }else{
                                                // $("#actionArticulos").removeAttr('disabled');//bloqueo de boton siguiente
                                                Materialize.toast('Existen precios en cero, favor de verificar.',5000,'red');
                                        }
                                }
                            }
                            else{
                                // $("#actionArticulos").removeAttr('disabled');//bloqueo de boton siguiente
                                swal('¡Alto!','Debe agregar un artículo o definir modo de entrega','info');
                            }
                        }else if (combinado.combinado){
                            // $("#actionArticulos").removeAttr('disabled');//bloqueo de boton siguiente
                            swal('Error!','Asegurese de tener elemento en las lines de un solo almacen.','error');
                        }
                    }          
                },
                error: function (jqXHR, exep) {
                    Materialize.toast('Error!' + catchError(jqXHR, exep), 3000);
                    // $("#actionArticulos").removeAttr('disabled');//bloqueo de boton siguiente
                }
            }); 
        }
    }
});

 function confirmaDocumento(lineaXML){
    gaTrigger('SetSalesQuotationToSend','entity',userBranchOffice);
    var token='';
    if (setTipo === 'ORDVTA') {token= "";}
    else { token= "inicio/confirmar-Cotizacion";$("#ConvertirCot-Ov").hide();}
    if (token != ""){
        $.ajax({
                url: token,
                method: "POST",
                dataType: "JSON",
                data: { "lineaXML": lineaXML ,'metodoPago' : $("#pagolineas option:selected").attr('data-paymmode'),'ctaBanco': $("#ctaBanco").val(),'origenVenta': $("#origenV").val(),'encabezadoov': $("#cabeceraOriginal").val(),'salesQuotationNumber':$('#DocumentId2').val() },
                beforeSend: function (xhr) {
                     Materialize.toast('Procesando peticion!', 3000);
                },
                success: function (data){		        	
                        if(data != 'FAIL'){
                            $("#confirmarDocumento").hide();
                            Materialize.toast('Documento Confirmado!', 3000);
                            if(setTipo === 'ORDVTA'){
                                var condi = $("#condiEntrega").val();
                                var dlvmode = $('#modoentregaResumen').html();
                                var efect = $('#cargodesc').html();
                        if (havePermision(14)) {
                            if (condi == 'CONTADO') {
                                if (dlvmode == 'DOMICILIO') {
                                    if (efect == '04' || efect == '28') {
                                        $("#GenerarWPAY").show()
                                        $("#Facturar").show();
                                        $("#GenerarREM").hide();
                                    }else if (efect == '01') {
                                        $("#GenerarREM").show();
                                    }
                                    else if (efect == '03') {
                                        $("#Facturar").hide();
                                        $("#Remisionar_Facturar").hide();
                                        $("#GenerarREM").hide();
                                    }
                                    else {
                                        $("#Facturar").show();
                                        $("#GenerarREM").show();
                                    }
                                } else {
                                    $("#Facturar").show();
                                    if (efect == '04' || efect == '28') {
                                        $("#GenerarWPAY").show()
                                        $("#GenerarREM").hide();
                                    }
                                }
                            } else {
                                $("#Facturar").show();
                                $("#GenerarREM").show();
                            }
                        } else {

                            if (condi == 'CREDITO') {
                                $("#GenerarREM").show();
                            } else {
                                $("#Facturar").hide();
                                $("#GenerarREM").hide();
                                if (efect == '04' || efect == '28') {
                                    $("#GenerarWPAY").show()
                                }
                            }

                        }

                        $("#OrdenVentaRem").val(data);
                        $("#rutaReal").append(" > <a style=\"color: white;\">Imprimir Orden De Venta</a>");
                    }
                    else {
                        if ($('#modoentregaResumen').html() == 'PAQUETERIA' && !parcelLabelSubmited) {
                            $('#labelDivButton').show();
                        }
                        $("#ImprimirCotizacion").show();
                        $("#ConvertirCot-Ov").show();
                        $("#DocumentoConfirmado").val(data);
                        $("#QuotationId").val(data);
                        $("#rutaReal").append(' > <a style="color: white;">Imprimir Cotización</a>');
                        //refreshLines();
                    }
                }
                else {
                    Materialize.toast('Intente de nuevo!', 3000);
                    // $("#actionArticulos").removeAttr('disabled');//bloqueo de boton siguiente
                }
            }, error: function (data) {
                Materialize.toast('WebService Error!.', 3000);
                // $("#actionArticulos").removeAttr('disabled');//bloqueo de boton siguiente
            }
        });
     }
 }
$(document).on('keydown','#num3DigCR',function(){
		$('#num3CRMensaje').html('');
	});
$(document).on('click','#num3CRAceptar',function(){
    $('#loadTarjeta').html('<center><img style="width: 4%;" src="../application/assets/img/cargando.gif"><br>procesando...</center>');                
    var numero = $('#num3DigCR').val();
    if ( numero === '' || numero.length != 4){
            $('#num3CRMensaje').html('Debe ingresar un valor en el campo de codigo de seguridad, o la cantidad de digitos no corresponde.');
            codigoSegValido = '0';
            $("#ctaBanco").val('0000');
    }else{
            $('#num3CRMensaje').html('');
            codigoSegValido = '1';
            $("#ctaBanco").val(numero);
            Materialize.toast('Comenzando proceso de confirmacion',2000);
            confirmarCot(); 
    }
});

//Funcion que actualiza el almacen y selecciona el asignado al cliente
function actualizarAlmacen(alm){
    $("#almacenes > option").each(function() {															
        if (this.value === alm){				    				    
            $( this ).attr("selected","selected");					    
        }
    });
    $("#almacenes").trigger('contentChanged');
    $('#modalLoading').closeModal();
    $('#lean-overlay').remove();
    $('.lean-overlay').remove();
}
function mapeo(valor,arreglo,request){
    var arreglo_nuevo = [];
    if(seccionOV == "LOCAL"){
        var sitioSeleccionado = $("#sitioLineas").val();
        console.log("sitioCliente: ", sitioCliente);
        console.log("sitioSeleccionado: ", sitioSeleccionado);
        if(sitioCliente === sitioSeleccionado){
            return $.map(arreglo,function(itm){
                var posNombre = itm.nameAlias.indexOf(valor.toUpperCase());
                var posArticu = itm.value.indexOf(valor.toUpperCase());
                if (request.term.indexOf('*') < 0){
                    if ( (posNombre >= 0) || (posArticu >= 0) ){
                        return itm;
                    }
                }
                else{
                    if ( (posNombre >= 0) ){
                        return itm;
                    }
                }
            });
        }else{
            /*arreglo.forEach(function(element) {
                if(!element.nameAlias.includes("FLETE")){
                    arreglo_nuevo.push({value: element.value, label: element.label, nameAlias: element.nameAlias});
                    //console.log("arreglo nuevo tiene: ", arreglo_nuevo.length);
                }
            });*/
			arreglo_nuevo = arreglo;
            return $.map(arreglo_nuevo,function(itm){
                var posNombre = itm.nameAlias.indexOf(valor.toUpperCase());
                var posArticu = itm.value.indexOf(valor.toUpperCase());
                if (request.term.indexOf('*') < 0){
                    if ( (posNombre >= 0) || (posArticu >= 0) ){
                        return itm;
                    }
                }
                else{
                    if ( (posNombre >= 0) ){
                        return itm;
                    }
                }
            });
        }
    }else{
        return $.map(arreglo,function(itm){
            var posNombre = itm.nameAlias.indexOf(valor.toUpperCase());
            var posArticu = itm.value.indexOf(valor.toUpperCase());
            if (request.term.indexOf('*') < 0){
                if ( (posNombre >= 0) || (posArticu >= 0) ){
                    return itm;
                }
            }
            else{
                if ( (posNombre >= 0) ){
                    return itm;
                }
            }
        });
    }
    
}
autocomp_opt = {
    html:true,
    autoFocus : true,
    minLength: 3,
    focus: function(event,ui){
            event.preventDefault();
            $(this).removeClass('ui-menu-item');
    },
    source: function(request,response){
            var buscar = request.term.split('*');
            var itemData = [];
            $(buscar).each(function(index,valor){
                    if ( index == 0){
                        itemData = mapeo(valor,items,request);
                    }else{
                        itemData = mapeo(valor,itemData,request);
                    }
            });
            response(itemData);
    },
    response : function(event,ui){
    if (ui.content.length === 0){
            ui.content.push({label: "No hay Resultados",value: " "});
    }
    },
    select: function (event, ui){	
        //Al seleccionar una opción se asigna la información a los campos del formulario
        //Se obtiene la fila en la que se esta posicionado
        var $tr = $(this).closest('tr'); //Se obtiene el index de la fila        
        var myRow = $tr.index() + 1;
        getProductDetail (myRow,ui);                                							    
    }
};

function initResumenTestDiv() {
    gaTrigger('SalesQuotationLines', 'entity', userBranchOffice);
    var str = $('#FormArticulos').serializeObject();
    metodo = $("#pagolineas option:selected").attr('data-paymmode');
    if ($("#pagolineas option:selected").attr('data-paymmode') == '02a') {
        metodo = '02';
    }
    $("#GenerarREM").hide();
    $("#DescRemision").hide();
    $("#DescFactura").hide();
    $("#ImprimirREM").hide();
    $("#ConvertirCot-Ov").hide();
    $("#ImprimirCotizacion").hide();
    $("#claveclteResumen").html($("#claveclte").val());
    $("#desccliente").html($("#cliente").val());
    $("#DocumentIdResumen").html($('#DocumentId2').val());
    $("#monedaResumen").html($("#monedalineas").val());
    $("#cargodesc").html(metodo);
    $("#modoentregaResumen").html($("#entregalineas").val());
    $("#direccionclte").html($("#direccion").val());
    $("#vendedor").html($("#responsableventa option:selected").text());
    $('#resumenDivTitle').append(' ' + $('#DocumentId2').val());
    let promoCodeEnable = false;
    if ($('#promoCode').val() != 'None') {
        promoCodeEnable = promoCodes[$('#promoCode').val()];
    }
    if (editionPromoCodeSelected != 'None' && $('#promoCode').val() == "None") {
        promoCodeBeenRemoved = true;
    }
    str[{ direccion: $("#direccion").val() }];
    $.ajax({
        url: "inicio/resumenTestLineas",
        method: "POST",
        dataType: 'json',
        data: { str: str, dire: $("#direccion").val(), moneda: $('#monedalineas').val(), promoCodeEnable, promoCodeBeenRemoved, itemsList: editionPromoCodeSelected != 'None' ? promoCodes[editionPromoCodeSelected].ItemsList : '' },
        async: false,
        beforeSend: function (xhr) {
            Materialize.toast('Agregando partidas al documento!', 3000);
        },
        success: function (data) {
            if (!data.fail) {
                $('body').removeClass('theme');
                $('#articulosDiv').hide();
                $('#resumenTest').show(function () {
                    $(this).attr('style', 'display:inline;color:white');
                    $('#breadInicio').attr('onclick', '');
                    $('#breadArticulos').attr('onclick', '');
                });
                $('#breadResumen').show(function () {
                    $(this).attr('style', 'display:inline;color:white');
                    $('#breadInicio').css('color', 'rgba(255,255,255,0.7)');
                    $('#breadArticulos').css('color', 'rgba(255,255,255,0.7)');
                    $('#editarDocument').show();
                });
                // $("#cabeceraOriginal").val(data.encabezadoOV);
                // $("#DocumentType").val(data.documentType);
                // $("#DocumentId").val($('#DocumentId2').val());
                $(data.lineAttr).each(function (index) {
                    $('#dataAreaId' + (index + 1)).val(this.dataAreaId);
                    $('#InventoryLotId' + (index + 1)).val(this.InventoryLotId);
                });
                var lenLineas = Number($("#NumFilas").val());
                var bodyTable = '';
                var mt = 0;
                for (var i = 1; i < lenLineas + 1; i++) {
                    var artiLineas = $('#item' + i).val();
                    var descLineas = $('#descripcion' + i).val();
                    var cantiLineas = $('#cantidad' + i).val();
                    var uniLineas = $('#unidad' + i).val();
                    var sitioLineas = $('#sitio' + i).val();
                    var almacenLineas = $('#almacen' + i).val();
                    var ComentarioLineas = $('#comentariolinea' + i).val();
                    var monto = $('#montoiva' + i).val();
                    mt += Number(monto.substring(1).replace(',', ''));
                    bodyTable += '<tr>';
                    bodyTable += '	<td>' + i + '</td>';
                    bodyTable += '	<td>' + artiLineas + '</td>';
                    bodyTable += '	<td>' + descLineas + '</td>';
                    bodyTable += '	<td>' + cantiLineas + '</td>';
                    bodyTable += '	<td style="text-transform:uppercase">' + uniLineas + '</td>';
                    bodyTable += '	<td>' + sitioLineas + '</td>';
                    bodyTable += '	<td>' + almacenLineas + '</td>';
                    bodyTable += '	<td>' + $('#montocargo' + i).val(); +'</td>';
                    bodyTable += '	<td>' + $('#montoiva' + i).val(); +'</td>';
                    bodyTable += '</tr>';
                    bodyTable += '<tr>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td>Comentarios: ' + ComentarioLineas + '</td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '	<td></td>';
                    bodyTable += '</tr>';
                };
                $('#resumenTestTablaLineas tbody').html(bodyTable);
                $('#totalResumen').html(mt.toFixed(3)).formatCurrency({ roundToDecimalPlace: '3' });
                $('#comentarioCabecera').val($('#comentariosCabecera').val());
                $('#ordenClte').val($('#OrdenCliente').val());
                $('#refClte').val($('#documenType').val());
                $('#comentarioCabeceraCOT').val($('#comentariosCabecera').val());
                $('#ordenClteCOT').val($('#OrdenCliente').val());
                $('#refClteCOT').val($('#ReferenciaCliente').val());
                $("#cuentaCompleto").html($('#DocumentId2').val() + ':Cliente ' + $("#claveclte").val() + ' - ' + $("#cliente").val());
                $('#ATP_Cot').val($('#DocumentId2').val());
                ////confirmar documento
                $("#confirmarDocumento").hide();
                Materialize.toast('Documento Confirmado!', 3000);
                if (setTipo === 'ORDVTA') {
                    var condi = $("#condiEntrega").val();
                    var dlvmode = $('#modoentregaResumen').html();
                    var efect = $('#cargodesc').html();
                    if (havePermision(14)) {
                        if (condi == 'CONTADO') {
                            if (dlvmode == 'DOMICILIO') {
                                if (efect == '04' || efect == '28') {
                                    $("#GenerarWPAY").show()
                                    $("#Facturar").show();
                                    $("#GenerarREM").hide();
                                } else if (efect == '01') {
                                    $("#GenerarREM").show();
                                } else if (efect == '03') {
                                    $("#Facturar").hide();
                                    $("#Remisionar_Facturar").hide();
                                    $("#GenerarREM").hide();
                                } else {
                                    $("#Facturar").show();
                                    $("#GenerarREM").show();
                                }
                            } else {
                                $("#Facturar").show();
                                if (efect == '04' || efect == '28') {
                                    $("#GenerarWPAY").show()
                                    $("#GenerarREM").hide();
                                }
                            }
                        } else {
                            $("#Facturar").show();
                            $("#GenerarREM").show();
                            if (efect == '04' || efect == '28') {
                                $("#GenerarWPAY").show()
                            }
                        }
                    }
                    else {
                        if (condi == 'CREDITO') {
                            $("#GenerarREM").show();
                        } else {
                            $("#Facturar").hide();
                            $("#GenerarREM").hide();
                            if (efect == '04' || efect == '28') {
                                $("#GenerarWPAY").show()
                            }
                        }
                    }
                    $("#OrdenVentaRem").val($('#DocumentId2').val());
                    $("#rutaReal").append(" > <a style=\"color: white;\">Imprimir Orden De Venta</a>");
                }
                else {
                    $("#ImprimirCotizacion").show();
                    $("#ConvertirCot-Ov").show();
                    $("#DocumentoConfirmado").val($('#DocumentId2').val());
                    $("#QuotationId").val($('#DocumentId2').val());
                    $("#rutaReal").append(' > <a style="color: white;">Imprimir Cotización</a>');
                }
                refreshLines();
                ///////////////////////
                $('#rutaReal').append(' > <a style="color: white;">Confirmar Documento</a>');
                confirmaDocumento('');
            } else {
                Materialize.toast('Lineas no agregadas, intente de nuevo!', 3000, 'red');
                $('#loadNext').html('');
            }
        },
        error: function (jqXHR, exception) {
            Materialize.toast('WebService Error!:' + catchError(jqXHR, exception), 3000);
        }
    });
}
function modalLimiteCreditoClose(){$('#modalLimiteCredito').closeModal();}

function cargarEdicion(){
	//console.log("cargarEdicion");
    $('#claveclte').val(cliente);
    $('#claveclte').trigger('keydown');
    setTimeout(function() {
        $('#ui-id-2').click();
    }, 500);      
    $('#claveclte').prop('readonly','1');
}
var primeraVez = true;
function cargarClientes(data){
	//console.log("cargar clientes", data);
    clients = [];
    $.map(data, function (item){		
        var info = {label: "No hay Resultados",value: " "};
        info["label"] = $.trim(item.ClaveCliente) + " - " + $.trim(item.Nombre);
        info["value"] = $.trim(item.ClaveCliente);
        info["nombre"] = $.trim(item.Nombre);
        clients.push(info);
    });
    $('#modalLoading').closeModal();
    if((edicion === '1' & primeraVez)){
        cargarEdicion();
    }
    scrollinDropdown();
}
$("#claveclte").focus();

function verMerma(itemId,almacen,localidad){
    $.ajax({
        url:"merma/get-Pictures",type: 'POST',dataType: 'json',data:{itemid:itemId,almacen:almacen,local:localidad},
        success: function (data, textStatus, jqXHR) {
            var html='';
            $('#imageContent').html(html);
            var sh = data.length;     
            if(sh != 0){
                html+= '<div class="right-align" style="width:100%;float: right;"><a class="btn waves-effect waves-light" href="merma/download-zip?itemid='+itemId+'&almacen='+almacen+'&local='+localidad+'">Descargar Zip <i class="fa fa-download"></i></a></div>';           
                $("#downBtn").css("display","block");
            }else{
                 html+= '<div style="width:100%;display: inline-flex;" class="btn red">Sin imagenes</div>';
            }
            $(data).each(function( index ) {
                html+='<div class="col s3">'
                        +'<img onclick="quitarModal($(this))" width="100%" height="100%" style="height: 150px;" id="imageContent'+index+'" class=" responsive" src="'+data[index].RUTA+'" data-comment="'+data[index].COMENTARIOS+'">'
                        +'<a href="merma/download-img?id='+data[index].ID+'" style="position: relative;bottom: 38px;left: -6px;" class="btn-floating btn red"><i class="fa fa-download"></i></a>'
                        +'</div>';
            });
             
            
            $('#ExistenciasSitioClte > tfoot > #imagenes').remove();
            $('#ExistenciasSitioClte > tfoot').append('<tr id="imagenes"><td colspan="6" style="padding:20px 3px 3px 3px;">'+html+'</td></tr>');
            $('#imageContent').html(html);
            $("#imgArt").html(itemId);   
            
            bajar('#modalexistenciasss');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            
        }
    });    
}
function quitarModal(ts){
    var src = ts.attr("src");
    $("#image-hack").attr("src",src);
    $("#picture-comment").html(ts[0].dataset.comment);
    $("#modal1-hack").openModal();
}
function getPriceMerma(itemid,almacen){
    var result=0;
    if(itemid!='' & almacen!='' ){
        $.ajax({
            url:'merma/get-Price-Merma',
            type: 'POST',
            dataType: 'JSON',
            async: false,
            data:{item:itemid,almacen:almacen},
            success: function (d, textStatus, jqXHR) {
                result=d.precio;
               return d.precio; 
            },
            error: function (jqXHR, textStatus, errorThrown) {
                return result; 
            }
        });
    }
    return result;    
} 

$('.materialboxed').materialbox();

function getPriceMermaPreview(itemid,cliente,moneda,sitio,almacen,localidad,unidad){ 
    console.log('precio obtenido de merma');
    var result=[];
    if(itemid!='' & almacen!='' ){
       var precioWS= getPrecios(itemid,cliente,moneda,sitio,unidad,almacen);
        $.ajax({
            url:'merma/get-Price-Merma',
            type: 'POST',dataType: 'JSON',
            async: false,
            data:{item:itemid,almacen:almacen,loc:localidad},
            success: function (d, textStatus, jqXHR) {
                var impuMerma = precioWS.impuestos;
                if(Number(d.utilidad)>=0){                    
                    var precioUtilidad=Number(d.costo/(1-d.utilidad));
                    var precioCliente=Number(precioWS.preciocargo);
                    if(precioUtilidad>precioCliente){
                        var precioMerma=precioCliente-(precioCliente*d.utilidad);
                        result.precio=precioCliente-(precioCliente*Number(d.utilidad));
                        result.dif=precioCliente*Number(d.utilidad);
                        result.porcentaje=(precioCliente*Number(d.utilidad)/precioCliente);
                        result.impuestos = impuMerma;
                    }
                    else{
                        var precioMerma=Number(d.costo)/(1-(Number(d.utilidad)));
                        result.precio=Number(d.costo)/(1-Number(d.utilidad));
                        result.dif=precioCliente-precioMerma;
                        result.porcentaje=(precioCliente-precioMerma)/precioCliente;
                        result.impuestos = impuMerma;
                    }
                }
                else{
                    var precioUtilidad=Number(d.costo)*(1+(Number(d.utilidad)));                    
                    var precioCliente=Number(precioWS.preciocargo);
                    if(precioUtilidad>precioCliente){
                        console.log('utilidad negativa - precio de merma mayor que el de cliente');
                        result.precio=precioCliente-(precioCliente*Number(d.utilidad));
                        result.dif=precioCliente*(-(Number(d.utilidad)));
                        result.porcentaje=(precioCliente*Number(d.utilidad)/precioCliente);
                        result.impuestos = impuMerma;
                        console.log("Precio Cliente: "+precioCliente+' < '+precioUtilidad+'-'+result.precio+' - '+result.dif+' - '+result.porcentaje);
                    }
                    else{
                        console.log('utilidad negativa - precio de merma menor que cliente');                        
                        result.precio=precioUtilidad;
                        result.dif=precioCliente-precioUtilidad;
                        result.porcentaje=(precioCliente-precioUtilidad)/precioCliente;
                        result.impuestos = impuMerma;   
                        console.log("Precio Cliente: "+precioCliente+' < '+precioUtilidad+'-'+result.precio+' - '+result.dif+' - '+result.porcentaje);
                    }                    
                }
                /*** para deshabiltar merma descomentar las siguientes lines
                /**
                **/
                // result.precio = precioWS.preciocargo;
                // result.dif = 0;
                // result.porcentaje = 0;
                return result; 
            },
            error: function (jqXHR, textStatus, errorThrown) {
                return result; 
            }
        });
    }
    return result;    
}
function getPrecios(itemid,cliente,moneda,sitio,unidad,almacen){
    gaTrigger('getSalesPriceLineAmount','entity',userBranchOffice);
    gaTrigger('getSalesPriceUnitPrice','entity',userBranchOffice);
    gaTrigger('ReleasedDistinctProducts','entity',userBranchOffice);
    var result=[];
    if(itemid!='' ){
        var docId = $('#DocumentId2').val();
        var docType = $("#DocumentType2").val();
        var itemGroupType = $.map(items,function(articulo){
                            if ( itemid == articulo.value ){
                                return {productGroupId : articulo.productGroupId,productType : articulo.productType}
                            };
                        });
        $.ajax({
            url:'inicio/precios',
            type: 'POST',dataType: 'JSON',
            async: false,
            data:{item:itemid,
                cliente:cliente,
                moneda:moneda,
                qty:'1',
                cargo:$("#pagolineas").val(),
                sitio:sitio,
                almacen:almacen,
                punitario:0,
                localidad:'GRAL',
                'documentId': docId,
                'documenType': docType,
                'paymMode' : $('#pagolineas :selected').attr('data-paymmode'),
                unidad     : unidad,
                'productGroupId' : itemGroupType[0].productGroupId,
                'productType' : itemGroupType[0].productType
            },
            success: function (d, textStatus, jqXHR) {
                return result=d; 
            },
            error: function (jqXHR, textStatus, errorThrown) {
                return result; 
            }
        });
    }
    return result;
}
function bajar(element){
    $(element).animate({scrollTop:$(element)[0].scrollHeight}, 800);
}
function setDomicilios(payMode){
    if(payMode==='DOMICILIO'){
        if($("#paytermArt").val()==='CONTADO'){
            $("#paytermArt").val('CONTADO PD');
            setPayMode('CONTADO PD');
        }
    }
}

var clientReference = '';
$('#GenerarWPAY').on('click', function () {
    var clientCode = $('#claveclteResumen').text();
    $('#webPayModal').openModal({
        ready: function () {
            $.get('inicio/getclientinfo', { clientCode: clientCode }, function (data) {
                data = JSON.parse(data);
                console.log(data);
                $('#clientPayEmail').val(data.PrimaryContactEmail);
                $('#clientPaySMS').val(data.PrimaryContactPhone);
                clientReference = data.OrganizationNumber;
            });
        }
    });
    //console.log('Boton WEB PAY LOCO!!');
});

var sendMail = false;
var sendSMS = false;
$('#checkMail').on('click', function () {
    if (sendMail == true) {
        sendMail = false;
    } else {
        sendMail = true;
    }
    console.log(sendMail);
});
$('#checkSMS').on('click', function () {
    if (sendSMS == true) {
        sendSMS = false;
    } else {
        sendSMS = true;
    }
    console.log(sendSMS);
});

$('#sendMessages').on('click', function () {
    console.log('Click enviar perro');
    var clientCode = $('#claveclteResumen').text();
    var purchaseOrder = $('#DocumentIdResumen').text();
    var emailInput = $('#clientPayEmail').val();
    var smsInput = $('#clientPaySMS').val();
    var sitioLineas = $('#sitioLineas').val();
    var totalOvAmount = $('#totalResumen').text();
    console.log(sitioLineas);
    var regex = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
    if (!sendSMS && !sendMail) {
        swal("Error!", 'Favor de seleccionar uno de los metodos a enviar', "error");
    } else if (sendMail && emailInput == '') {
        swal("Error!", 'Favor de introducir un correo', "error");
    } else if (sendMail && !regex.test(emailInput)) {
        swal("Error!", 'Favor de introducir un correo valido', "error");
    } else if (sendSMS && smsInput == '') {
        swal("Error!", 'Favor de introducir un numero celular a 10 digitos', "error");
    } else if (sendSMS && (smsInput.length < 10 || smsInput.length > 10)) {
        swal("Error!", 'Favor de introducir el numero a 10 digitos', "error");
    } else {
        $('#sendMessages').hide();
        $('#webpayModalContent').addClass("se-pre-cont");
        $.get('inicio/getwebpay', { clientCode, ov: purchaseOrder, sendMail, sendSMS, emailInput, smsInput, sitioLineas, clientReference, totalOvAmount }, function (data) {
            data = JSON.parse(data);
            console.log(data.response);
            if (data.response) {
                $('#webpayModalContent').removeClass("se-pre-cont");
                swal("Enviado!", 'Se envio la informacion por los medios seleccionados', "success");
                $('#webPayModal').closeModal();
                $('#GenerarWPAY').hide();
            } else {
                swal("Error!", 'Ocurrio un error favor de contactac a sistemas', "error");
            }
        });
    }
});

$('#promoCode2').on('select2:select', function (e) {
    lastPromoSelected = $('#promoCode').val();
    var data = e.params.data;
    console.log(data);
    $('#promoCode').val(data.id).trigger('change');
    var rows = $("#articulos > tbody >tr").length;
    if (rows === 0) {
    } else {
        for (var i = 1; i < rows + 1; i++) {
            if ($('#item' + i).val() !== '') {
                var ev = $.Event({ 'type': 'input', 'enviarExistencias': '1' });
                $("#cantidad" + i).trigger(ev);
            } else {
                Materialize.toast('Artículo o Sitio vacios, favor de ingresar los datos para aplicar el cargo!', 3000);
            }
        }
    }
});

$('#diarioFPago').on('change',function(){
    let fpago = $(this).val();
    if(fpago == 04 || fpago == 28){
        $('#digitosTarjetaDiv').show();
    }else{
        $('#digitosTarjeta').val('');
        $('#digitosTarjetaDiv').hide();
    }
});

/** Funcion para validar email con una expresion regular
*** acepta String y devuelve boolean
*** hace un split de los correos por el ';'
**/
function validateEmail(email) {
    var emailTest = email.split(';');
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    var valido = true;
    $(emailTest).each(function(){
        valido = re.test(String(this).toLowerCase());
        if (!valido){
            return valido;
        }
    });
    
    return valido;
}

/** funcion para guardar los datos de cyr
*** acepta string correo y string telefono
**/
function guardarCYR(correo,telefono,customer,customerName){
    $.ajax({
        url:'inicio/guardarcyr',
        type: 'POST',dataType: 'JSON',
        data:{correo,telefono,customer,customerName},
        success: function (res) {
            console.log(res); 
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR); 
        }
    });
}

/** funcion para traer los datos de cyr
*** acepta string codigo cliente
**/
function getInfoCYR(customer){
    $.ajax({
        url:'inicio/existeinfocyr',
        type: 'POST',dataType: 'JSON',
        data:{customer},
        success: function (res) {
            console.log(res);
            if(res.existe){
                if(res.correo != ''){
                    $('#correo').val(res.correo);
                }
                if (res.telefono != ''){
                    $('#telefono').val(res.telefono);
                }
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR); 
        }
    });
}

/**
*** funcion para mostrar el swal de la captura de correo
**/
function showCyRDialog(){
    return new Promise(function (resolve,reject){
        var modoEnt = document.getElementById('modoentregaResumen').innerText;
        if (modoEnt != 'C&R'){
            if (modoEnt == 'DOMICILIO'){
                ////////////////check montos minimos en domicilio/////////////////
                modoEntregaMM = $('#entregalineas').val();
                totalAmountMM = $('#totalResumen').text().replace('$','').replace(',','');
                metodoPayCdMM = $('#pagolineas option:selected').attr('data-paymmode');
                sitioClientMM = $('#sitioLineas option:selected').val();
                if (modoEntregaMM == 'DOMICILIO'){
                    $.ajax({
                        url      : 'minimoventasdomicilio/getmininosventasdata',
                        type     : "POST",
                        dataType : "json",
                        data     : {'sitio' : sitioClientMM},
                        success  : function(res){
                            if (res != 'SinResultado'){
                                if (metodoPayCdMM == '01' && eval(totalAmountMM) > 5000){
                                    reject('Excede el <b>MONTO MÁXIMO ($5,000.00)</b> permitido en domicilios para la forma de pago efectivo.');
                                }else{
                                    if ( metodoPayCdMM == '01' ){
                                        if ( eval(totalAmountMM < eval(res.montoMinimoEfectivo) ) ){
                                            reject('No cumple con el <b>MONTO MÍNIMO ('+parseFloat(eval(res.montoMinimoEfectivo).toFixed(2)).toLocaleString('en-US',{style: 'currency',currency: 'USD'})+')</b> de compra para el modo de entrega seleccionado, sucursal: <b>'+res.sucursal+'</b>');
                                        }else{
                                            resolve(true);
                                        }
                                    }else{
                                        if ( eval(totalAmountMM < eval(res.montoMinimoTarjeta) ) ){
                                            reject('No cumple con el <b>MONTO MÍNIMO ('+parseFloat(eval(res.montoMinimoTarjeta).toFixed(2)).toLocaleString('en-US',{style: 'currency',currency: 'USD'})+')</b> de compra para el modo de entrega seleccionado, sucursal: <b>'+res.sucursal+'</b>');
                                        }else{
                                            resolve(true);
                                        }
                                    }
                                }
                            }else{
                                resolve(true);
                            }
                        }
                    });
                }
                //////////////////////////////////////////////////////////////////
            }else{
                resolve(true);
            }
        }else{
            htmlCYR = ''
            htmlCYR += '<div class="row">';
            htmlCYR += '    <div class="col-md-12">';
            htmlCYR += '        <label class="form-label" for="correo" style="font-size:25px; font-weight:bold;">Correo</label>';
            htmlCYR += '        <input type="email" style="width:100%" class="form-control" id="correo" value="" placeholder="Ingrese correo electronico" />';
            htmlCYR += '    </div>';
            htmlCYR += '</div>';
            htmlCYR += '<div class="row">';
            htmlCYR += '    <div class="col-md-12">';
            htmlCYR += '        <label class="form-label" for="telefono" style="font-size:25px; font-weight:bold;">Telefono</label>';
            htmlCYR += '        <input type="text" style="width:100%" class="form-control" id="telefono" value="" placeholder="Ingrese numero telefonico" />';
            htmlCYR += '    </div>';
            htmlCYR += '</div>';
            swal({
                type: 'question',
                title: 'Confirmación Entrega C&R',
                html : htmlCYR,
                showCancelButton  : false,
                confirmButtonText : 'Guardar',
                allowOutsideClick : false,
                preConfirm : function(result){
                    return new Promise(function(resolve,reject){
                                    email = $('#correo').val();
                                    if($('#correo').val() == '' || $('#telefono').val() == ''){
                                        reject('El campo no puede ir vacio.');
                                    }
                                    if(!validateEmail(email)){
                                        reject('Ingrese una direccion de correo valida.');
                                    }
                                    resolve(result);
                                });
                },
                onOpen : function(res){
                    cliente = $('#claveclteResumen').html();
                    var info = getInfoCYR(cliente);
                    console.log(info);
                }
            }).then(
                function(result){
                    if (result){
                        cliente = $('#claveclteResumen').html();
                        correo = $('#correo').val();
                        telefono = $('#telefono').val();
                        nombre = $('#desccliente').html()
                        guardarCYR(correo,telefono,cliente,nombre);
                        resolve(true);
                    } 
                }, 
                function(dismiss){
                    if (dismiss == 'cancel'){
                        console.log('cancelo guaradar CYR');
                    }
            });
        }
    });
}

/**
*** funcion para obtener el objeto de los datos para generar el
*** archivo zpl
**/
function getZPLData(){
    var rows = $('#tablaEtiqueta tbody tr');
    var tdRfcCompany = $($(rows)[0]).find('td')[0];
    var RFCCompany = $(tdRfcCompany).find('input').val().trim();

    var tdCalleCompany = $($(rows)[1]).find('td')[0];
    var calleCompany = $(tdCalleCompany).find('input').val().trim();

    var tdColCompany = $($(rows)[2]).find('td')[0];
    var colCompany = $(tdColCompany).find('input').val().trim();

    var tdEstadoCompany = $($(rows)[3]).find('td')[0];
    var estadoCompany = $(tdEstadoCompany).find('input').val().trim();

    var tdTelCompany = $($(rows)[4]).find('td')[0];
    var telCompany = $(tdTelCompany).find('input').val().trim();

    var tdUsuarioVenta = $($(rows)[11]).find('td')[0];
    var usuarioVenta = $(tdUsuarioVenta).html().trim();

    var tdCreacionFecVenta = $($(rows)[12]).find('td')[0];
    var creacionFecVenta = $(tdCreacionFecVenta).html().trim();

    var tdOvCompleto = $($(rows)[0]).find('td')[1];
    var ovCompleto = $(tdOvCompleto).find('input').val().trim();

    var tdMontoVenta = $($(rows)[1]).find('td')[1];
    var montoVenta = $(tdMontoVenta).find('input').val().trim();

    var tdPrecaucion = $($(rows)[2]).find('td')[1];
    var precaucion = $(tdPrecaucion).find('input').val().trim();

    var tdEmailCliente = $($(rows)[3]).find('td')[1];
    var emailCliente = $(tdEmailCliente).find('input').val().trim();

    var tdComentarios = $($(rows)[4]).find('td')[1];
    var comentarios = $(tdComentarios).find('input').val().trim();

    var tdRfcCliente = $($(rows)[6]).find('td')[1];
    var RFCCliente = $(tdRfcCliente).find('input').val().trim();

    var tdCalleCliente = $($(rows)[7]).find('td')[1];
    var calleCliente = $(tdCalleCliente).find('input').val().trim();

    var tdColCliente = $($(rows)[8]).find('td')[1];
    var colCliente = $(tdColCliente).find('input').val().trim();

    var tdEstadoCliente = $($(rows)[9]).find('td')[1];
    var estadoCliente = $(tdEstadoCliente).find('input').val().trim();

    var tdTelefonoCliente = $($(rows)[10]).find('td')[1];
    var telefonoCliente = $(tdTelefonoCliente).find('input').val().trim();

    var tdPaqueteriaVenta = $($(rows)[11]).find('td')[1];
    var paqueteriaVenta = $(tdPaqueteriaVenta).find('input').val().trim();

    var tdSeguroVenta = $($(rows)[12]).find('td')[1];
    var seguroVenta = $(tdSeguroVenta).find('input').val().trim();

    var dataZPL = {RFCCompany,calleCompany,colCompany,estadoCompany,telCompany,usuarioVenta,creacionFecVenta,ovCompleto,montoVenta,precaucion,emailCliente,comentarios,RFCCliente,calleCliente,colCliente,estadoCliente,telefonoCliente,paqueteriaVenta,seguroVenta};

    return dataZPL;
}