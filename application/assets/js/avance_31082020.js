function loaderSwal(){
    Swal.fire({
      title: 'Cargando',
      text: 'Por favor espere',
      imageUrl: '../application/assets/img/loading.gif',
      imageWidth: 200,
      imageHeight: 200,
      imageAlt: 'Custom image',
      animation: false,
      allowOutsideClick: false
    })
    $('.swal2-actions').hide();
}

let noLpViewArray = ['SAN', 'GUADALAJARA'];

var tmpPunitario = 0;
var bandAgregarFam = false;
var bandNegados = false;
var lideartDimensions = {'lote':'18260810926','localidad': 'GRAL','licensePlate':'CHIHEXHB_RECEPCION'};
var atpDimensions = {'lote':'CHIHEXHBLOTE','localidad': 'P99-R99-A9','licensePlate':'CHIHEXHB'};

var negados = {
    'addArticulo': function(item){
        var existe = this.existeArticulo(item.articulo); 
        if (existe){
                this.removeArticulo(item.articulo);
        }
        this.itemGroup.items.push(item);
        existe = '';
    },
    'removeArticulo' : function(articulo){
        var indice = $.map(negados.itemGroup.items,function(item,index){
            if ( item.articulo == articulo){
                    return index
            }
        });
        this.itemGroup.items.splice(indice,1);
    },
    'existeArticulo': function(articulo){
        var exist = $.map(negados.itemGroup.items,function(item,index){
            if ( item.articulo == articulo ){
                return index;
            }
        });
        var band = false;
        if ( exist.length > 0 ){
                band = true;
        }
        return band;
    },
    'calcularNegado': function(articulo,cantidad){
        var existe = this.existeArticulo(articulo);
        if ( existe ){
            var indice = $.map(negados.itemGroup.items,function(item,index){
                    if ( item.articulo == articulo ){
                            return index;
                    };
            });
            var disponible = negados.itemGroup.items[indice].disponible;
            negados.itemGroup.items[indice].cantidad = cantidad;
            negados.itemGroup.items[indice].cantnegada = cantidad-disponible;
        };
    },
    'itemGroup':{'items':[]}
};


$.fn.serializeObject = function(){
   var o = {};
   var a = this.serializeArray();
   $.each(a, function() {
       if (o[this.name]) {
           if (!o[this.name].push) {
               o[this.name] = [o[this.name]];
           }
           o[this.name].push(this.value || '');
       } else {
           o[this.name] = this.value || '';
       }
   });
   return o;
};

Number.prototype.formatMoney = function(c, d, t){
var n = this, 
    c = isNaN(c = Math.abs(c)) ? 2 : c, 
    d = d == undefined ? "." : d, 
    t = t == undefined ? "," : t, 
    s = n < 0 ? "-" : "", 
    i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))), 
    j = (j = i.length) > 3 ? j % 3 : 0;
   return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
 };
 function showOption(txt){
     document.write(txt);
 }
function modalPrestashop(urlAmiga) {
    urlAmigable = urlAmiga;
    $("#modalPrestashop").openModal();
}

$(document).on('keydown','.getPrice',function(e){
	switch ( e.which ){
		case 13:
			//$(this).trigger('change');
		break;
		case 40:
			tr = $(this).closest('tr');
			row = tr.index() + 1;
			targetRow = row + 1;
			lastrow = $('#articulos tbody tr').length / 2;
			if ( row < lastrow ){
				$('#cantidad'+targetRow).focus();
			}else{
				$('#AgregarLinea').trigger('click');
			}
		break;
		case 38:
			tr = $(this).closest('tr');
			row = tr.index() + 1;
			targetRow = row - 1;
			firstrow = 1;
			if ( row > firstrow ){
				$('#cantidad'+targetRow).focus();
			}
		break;
        default:
            if (edicion == '11111') {
                setTimeout(function(){ swal.close(); }, 500);
                setTimeout();
            }
        break;
	}
});

$(document).on('keydown input change', ".getPrice", function (ev) {
    let promoCodeSelected = promoCodes[$('#promoCode').val()];
    let promoCodeItems = [];
    if ($('#promoCode').val() != 'None'){
        promoCodeItems = promoCodeSelected.ItemsList.split(',');
    }
    if (edicion == '11111') {
        loaderSwal();
    }
    var bandSrc;
    var bandChng = false;
    if (ev.type == 'input') {
        src = ev.originalEvent.enviarExistencias;
        if (typeof src != 'undefined') {
            bandSrc = true;
        } else {
            bandSrc = false;
        }
    }
    if (ev.type == 'change') {
        $(this).attr('data-change', '1');
        bandChng = true;
    }
    /**
     * Consulta de precios 
     */
    if ((this.value != '') && ((ev.type == 'keydown' && (ev.which == 13 || ev.which == 40 || ev.which == 9)) || (bandSrc) || (bandChng))) {
        //$(this).attr('disabled','disabled');
        var $tr = $(this).closest('tr');
        var myRow = $tr.index() + 1;
        var item = $("#item" + myRow).val();
        var cliente = $("#claveclte").val();
        var moneda = $("#monedalineas").val();
        var unidad = $("#unidad" + myRow).val();
        var qty = $(this).val();
        var disponible = $(this).attr('disp');
        var cargo = $("#pagolineas").val();
        var sitio = $("#sitio" + myRow).val();
        var almacen = $("#almacen" + myRow).val();
        var localidad = $("#localidad" + myRow).val();
        var descripcion = $("#descripcion" + myRow).val();
        var punitario = (tmpPunitario != 0) ? tmpPunitario : 0;
        var artName = artArray[item];
        var inputCantidad = $(this);
        var almacenAnt = $("#almacen" + myRow).data("almacen");
        console.log("cargando informacion");
        $($tr).addClass('load');
        if ($("#punitariolinea" + myRow).val() != '') {
            if (edicion != '1') {
                if (almacen.indexOf('MRMA') != -1) {
                    punitario = $("#punitariolinea" + myRow).val(); //para no cambiar el precio poner 0 (cero)
                }
                else {
                    punitario = $("#punitariolinea" + myRow).val();
                }
            } else {
                if (almacen.indexOf('MRMA') != -1) {
                    punitario = $("#punitariolinea" + myRow).val();//para no cambiar el precio poner 0 (cero)
                }
                else if (tmpPunitario != '0') {
                    punitario = tmpPunitario;
                } else {
                    punitario = $("#punitariolinea" + myRow).val();
                }
            }
        }

        // if((almacenAnt!=almacen)  && (edicion != '1') ){
        //     punitario=0;
        //     punitario = $("#punitariolinea"+myRow).val();
        // } 

        var t = $("#punitariolinea" + myRow).val();
        if ((Number(t) > 0) && (almacen.indexOf('MRMA') != 4)) {
            if (!havePermision(9)) {
                var artName = artArray[item];
                if (artName.indexOf('9999-') !== -1) {
                    console.log('guia :' + artName);
                }
                else {
                    var reset = true;
                    $.each(itemsNotLocked, function (i, v) {
                        if (item === v) {
                            reset = false;
                        }
                    });
                    if (reset) {
                        $("#punitariolinea" + myRow).val('0');
                        punitario = 0;
                    }
                }
            }
        }
        console.log('punitario:: ' + punitario);
        if (eval(punitario) == 0) {
            console.log('si entro');
            var flag = true;
            $($tr).removeClass('mermaTr');
            if (almacen.indexOf('MRMA') != -1 && flag == true) {
                var precioMerma = getPriceMermaPreview(item, $('#claveclte').val(), $('#monedalineas').val(), $("#sitioLineas").val(), almacen, localidad, unidad);
                var preciocargo = precioMerma.precio;
                $($tr).addClass('mermaTr');
                $($tr).addClass('tooltipped');
                $($tr).attr('data-tooltip', 'Descuento de ' + Number(precioMerma.porcentaje * 100).toFixed(0) + '% , $' + Number(precioMerma.dif).toFixed(2) + ' pesos');
                $($tr).attr('data-position', 'bottom');
                $('.tooltipped').tooltip({ delay: 50 });
                if (promoCodeItems.includes(item) && almacen.indexOf('MRMA') == -1 && almacen.indexOf('TRAS') == -1 && almacen.indexOf('EXHB') == -1 && (promoCodeSelected.SiteId = 'All' || promoCodeSelected.SiteId == $("#sitioLineas").val())) {
                    preciocargo = preciocargo - (preciocargo * (promoCodeSelected.Discount / 100));
                    $("#preciovta" + myRow).val(preciocargo).formatCurrency({ roundToDecimalPlace: '3' });
                    $("#punitariolinea" + myRow).val(preciocargo.toFixed(3));
                } else {
                    $("#preciovta" + myRow).val(preciocargo).formatCurrency({ roundToDecimalPlace: '3' });
                }
                var precioiva = preciocargo * 1.16;
                if (almacen.indexOf('TJNA') >= 0 || almacen.indexOf('MEXL') >= 0 || almacen.indexOf('JURZ') >= 0) {
                    if (precioMerma.impuestos == 'FRONT'){
                        var precioiva = preciocargo * 1.08;
                    }
                }
                $("#preciovtaiva" + myRow).val(precioiva).formatCurrency({ roundToDecimalPlace: '3' });
                var montocargo = preciocargo * qty;
                $("#montocargo" + myRow).val(montocargo).formatCurrency({ roundToDecimalPlace: '3' });
                var montoiva = precioiva * qty;
                $("#montoiva" + myRow).val(montoiva).formatCurrency({ roundToDecimalPlace: '3' });
                var subtot = subtotal();
                var tot = total();
                $("#subtotal").html(subtot).formatCurrency();
                $("#total").html(tot).formatCurrency();
                var iva = tot - subtot;
                $("#iva").html(iva).formatCurrency();
                tmpPunitario = 0;
                row = $('#cantidad' + myRow).closest('tr');
                $(row).removeClass('emptyRow');
                $(row).removeClass('load');
                $('#cantidad'+myRow).removeAttr('disabled');
                $("#preciovta" + myRow).removeClass('invalid');
                $("#punitariolinea" + myRow).val(preciocargo);
            }
            else {
                var docId = $('#DocumentId2').val();
                // var dataAreaId     = ( typeof($($tr).attr('data-dataareaid')) == 'undefined') ? 'nodefinido' : $($tr).attr('data-dataareaid');
                // var InventoryLotId = ( typeof($($tr).attr('data-inventorylotid')) == 'undefined' ) ? 'nodefinido' : $($tr).attr('data-inventorylotid');
                var itemGroupType = $.map(items,function(articulo){
                    if ( item == articulo.value ){
                        return {productGroupId : articulo.productGroupId,productType : articulo.productType}
                    };
                });
                $.ajax({
                    url: "inicio/precios",
                    type: "post",
                    dataType: "json",
                    data: {
                        "item": item
                        , "cliente": cliente
                        , "moneda": moneda
                        , "qty": qty
                        , "cargo": cargo
                        , "sitio": sitio
                        , "almacen": almacen
                        , "punitario": punitario
                        , 'localidad': localidad
                        , 'documentId': docId
                        , 'unidad': unidad
                        // ,'dataAreaId' : dataAreaId
                        // ,'InventoryLotId' : InventoryLotId
                        , 'productGroupId' : itemGroupType[0].productGroupId
                        , 'productType' : itemGroupType[0].productType
                        , 'documenType': docType
                        , 'paymMode': $('#pagolineas :selected').attr('data-paymmode')
                    },
                    beforeSend: function (xhr) {
                        $("#actionArticulos").attr('disabled', 'disabled');
                    },
                    success: function (data) {
                        //var preciocargo = Number(data['preciocargo']);
                        var preciocargo = Number(data['precioiva']);
                        // var dataAreaId = data['dataAreaId'];
                        var diferencia = data['diferencia'];
                        if (preciocargo === 0 && data['error'] !== "") { catchError(1, data['error']); }
                        if (preciocargo === 0 && data['error'] === "") { Materialize.toast('Precio no configurado en Dynamics', 5000, 'red'); }
                        $('#cargoMoneda' + myRow).val(diferencia);
                        if (promoCodeItems.includes(item) && almacen.indexOf('MRMA') == -1 && almacen.indexOf('TRAS') == -1 && almacen.indexOf('EXHB') == -1 && (promoCodeSelected.SiteId = 'All' || promoCodeSelected.SiteId == $("#sitioLineas").val())) {
                            preciocargo = preciocargo - (preciocargo * (promoCodeSelected.Discount / 100));
                            $("#preciovta" + myRow).val(preciocargo).formatCurrency({ roundToDecimalPlace: '3' });
                            $("#punitariolinea" + myRow).val(preciocargo.toFixed(3));
                        } else {
                            $("#preciovta" + myRow).val(preciocargo).formatCurrency({ roundToDecimalPlace: '3' });
                        } 
                        //var precioiva = preciocargo*1.16;
                        var precioiva = preciocargo * 1.16;
                        if (almacen.indexOf('TJNA') >= 0 || almacen.indexOf('MEXL') >= 0 || almacen.indexOf('JURZ') >= 0) {
                            if (data['impuestos'] == 'FRONT'){
                                var precioiva = Number(data['precioiva']) * 1.08;
                            }
                        }
                        $("#preciovtaiva" + myRow).val(precioiva).formatCurrency({ roundToDecimalPlace: '3' });
                        //var montocargo = data['preciocargo']* qty; 
                        var montocargo = preciocargo * qty;
                        $("#montocargo" + myRow).val(montocargo).formatCurrency({ roundToDecimalPlace: '3' });
                        var montoiva = precioiva * qty;
                        $("#montoiva" + myRow).val(montoiva).formatCurrency({ roundToDecimalPlace: '3' });
                        var subtot = subtotal();
                        var tot = total();
                        $("#subtotal").html(subtot).formatCurrency();
                        $("#total").html(tot).formatCurrency();
                        var iva = tot - subtot;
                        $("#iva").html(iva).formatCurrency();
                        var documenType = $("#DocumentType2").val();
                        if ($("#condiEntrega").val() == 'CREDITO') {
                            /////////////verifica limite de credito del cliente//////////////////
                            // $.ajax({url: "inicio/verCredito",type: "post",dataType: "json", data: { "cliente":cliente},
                            //     beforeSend: function (xhr) {
                            //         $("#actionArticulos").attr('disabled','disabled');
                            //         $('.progress .determinate').css('background-color','#F44336');
                            //         $('.progress').css('background-color','#E57373');
                            //     },
                            //     success: function (data, textStatus, jqXHR) {
                            //         var limiteDeCredito = data[0].LimiteDeCredito;
                            //         var saldo       = Number(data[0].Saldo) + tot;
                            //         var limiteC     = ((saldo * 100) / limiteDeCredito);
                            //         if (limiteC >= 100 && documenType == 'ORDVTA'){
                            //             $('#modalLimiteCredito').openModal();
                            //         } 
                            //         $('#limitedecredito').css('width', 'calc('+limiteC+'%)');
                            //         $('#limitedecredito').css('text-align', 'center');
                            //         limiteC = limiteC.toFixed(2);
                            //         $('#limitedecredito').html(limiteC+'%');
                            //         $("#actionArticulos").removeAttr('disabled');
                            //         ////////////////////////////////////////////////////
                            //     },      
                            //     error: function (jqXHR,exep){
                            //         $('#modalLoading').closeModal();
                            //         Materialize.toast('Error!'+catchError(jqXHR,exep), 3000);
                            //     }
                            // });
                        }
                        $('#modalLoading').closeModal();
                        $('#lean-overlay').remove();
                        $('.lean-overlay').remove();
                        tmpPunitario = 0;
                        row = $('#cantidad' + myRow).closest('tr');
                        // $(row).removeClass('emptyRow');
                        // $(row).removeClass('load');
                        // $(row).attr('data-dataAreaId',dataAreaId);
                        // $(row).attr('data-InventoryLotId',InventoryLotId);
                        $("#preciovta" + myRow).removeClass('invalid');
                        var interval = setInterval(function () {
                            if (jQuery.active === 0) {
                                swal.close();
                                $(row).parent().find('tr').removeClass('load');
                                $(row).parent().find('tr').removeClass('emptyRow');
                                $("#actionArticulos").removeAttr('disabled');
                                $('#cantidad'+myRow).removeAttr('disabled');
                                clearInterval(interval);
                            }
                        }, 300);
                        //}                              
                    },
                    error: function (jqXHR, exep) {
                        $('#modalLoading').closeModal();
                        Materialize.toast('Error!' + catchError(jqXHR, exep), 3000);
                    }
                });
            }
        }
        else {
            console.log('No entro');
            var cargoN = Number(cargo);
            var punitarioN = Number(punitario);
            service = 'false';
            $.ajax({
                url: "inicio/is-Service",
                type: "post",
                dataType: "json",
                data: { "item": item },
                success: function (data) {
                    service = data;
                    // if(cargoN!==0 ){
                    //     //calcula cargos
                    //      punitario=punitarioN+(punitarioN*(cargoN/100));
                    // }
                    var preciocargo = punitario;
                    if (promoCodeItems.includes(item) && almacen.indexOf('MRMA') == -1 && almacen.indexOf('TRAS') == -1 && almacen.indexOf('EXHB') == -1 && (promoCodeSelected.SiteId = 'All' || promoCodeSelected.SiteId == $("#sitioLineas").val())) {
                        preciocargo = preciocargo - (preciocargo * (promoCodeSelected.Discount / 100));
                        $("#preciovta" + myRow).val(preciocargo).formatCurrency({ roundToDecimalPlace: '3' });
                        $("#punitariolinea" + myRow).val(preciocargo.toFixed(3));
                    } else {
                        $("#preciovta" + myRow).val(preciocargo).formatCurrency({ roundToDecimalPlace: '3' });
                    }
                    var precioiva = preciocargo * 1.16;
                    if (almacen.indexOf('TJNA') >= 0 || almacen.indexOf('MEXL') >= 0 || almacen.indexOf('JURZ') >= 0) {
                        if (data['impuestos'] == 'FRONT'){
                            var precioiva = Number(data['precioiva']) * 1.08;
                        }
                    }
                    $("#preciovtaiva" + myRow).val(precioiva).formatCurrency({ roundToDecimalPlace: '3' });
                    var montocargo = preciocargo * qty;
                    $("#montocargo" + myRow).val(montocargo).formatCurrency({ roundToDecimalPlace: '3' });
                    var montoiva = precioiva * qty;
                    $("#montoiva" + myRow).val(montoiva).formatCurrency({ roundToDecimalPlace: '3' });
                    var subtot = subtotal();
                    var tot = total();
                    $("#subtotal").html(subtot).formatCurrency();
                    $("#total").html(tot).formatCurrency();
                    var iva = tot - subtot;
                    $("#iva").html(iva).formatCurrency();
                    $('#modalLoading').closeModal();
                    $('#lean-overlay').remove();
                    $('.lean-overlay').remove();
                    row = $('#cantidad' + myRow).closest('tr');
                    // $(row).removeClass('emptyRow');
                    var descripcionArticulo = $("#descripcion" + myRow).val();
                    if (descripcionArticulo.includes("FLETE")) {
                        $(row).addClass('flete');
                    }

                    $("#preciovta" + myRow).removeClass('invalid');

                    var docId = $('#DocumentId2').val();
                    var dataAreaId = (typeof ($(row).attr('data-dataareaid')) == 'undefined') ? 'nodefinido' : $(row).attr('data-dataareaid');
                    var InventoryLotId = (typeof ($(row).attr('data-inventorylotid')) == 'undefined') ? 'nodefinido' : $(row).attr('data-inventorylotid');
                    $($tr).removeClass('mermaTr');
                    if (almacen.indexOf('MRMA') != -1) {
                        $($tr).addClass('mermaTr');
                        var interval = setInterval(function () {
                            if (jQuery.active === 0) {
                                swal.close();
                                $(row).parent().find('tr').removeClass('load');
                                $(row).parent().find('tr').removeClass('emptyRow');
                                $("#actionArticulos").removeAttr('disabled');
                                clearInterval(interval);
                            }
                        }, 300);
                    }
                    else {
                        var itemGroupType = $.map(items,function(articulo){
                            if ( item == articulo.value ){
                                return {productGroupId : articulo.productGroupId,productType : articulo.productType}
                            };
                        });
                        $.ajax({
                            url: "inicio/precios",
                            type: "post",
                            dataType: "json",
                            data: {
                                "item": item
                                , "cliente": cliente
                                , "moneda": moneda
                                , "qty": qty
                                , "cargo": cargo
                                , "sitio": sitio
                                , "almacen": almacen
                                , "punitario": punitario
                                , 'localidad': localidad
                                , 'documentId': docId
                                , 'dataAreaId': dataAreaId
                                , 'InventoryLotId': InventoryLotId
                                , 'documenType': docType
                                , 'unidad': unidad
                                , 'productGroupId' : itemGroupType[0].productGroupId
                                , 'productType' : itemGroupType[0].productType
                            },
                            beforeSend: function (xhr) {
                                $("#actionArticulos").attr('disabled', 'disabled');
                            },
                            success: function (data) {
                                var preciocargo = Number(data['preciocargo']);
                                var dataAreaId = data['dataAreaId'];
                                var InventoryLotId = data['InventoryLotId'];
                                var diferencia = data['diferencia'];
                                $('#cargoMoneda' + myRow).val(diferencia);
                                if (preciocargo === 0 && data['error'] !== "") { catchError(1, data['error']); }
                                if (preciocargo === 0 && data['error'] === "") { Materialize.toast('Precio no configurado en Dynamics', 5000, 'red'); }
                                if (promoCodeItems.includes(item) && almacen.indexOf('MRMA') == -1 && almacen.indexOf('TRAS') == -1 && almacen.indexOf('EXHB') == -1 && (promoCodeSelected.SiteId = 'All' || promoCodeSelected.SiteId == $("#sitioLineas").val())) {
                                    preciocargo = preciocargo - (preciocargo * (promoCodeSelected.Discount / 100));
                                    $("#preciovta" + myRow).val(preciocargo).formatCurrency({ roundToDecimalPlace: '3' });
                                    $("#punitariolinea" + myRow).val(preciocargo.toFixed(3));
                                } else {
                                    $("#preciovta" + myRow).val(preciocargo).formatCurrency({ roundToDecimalPlace: '3' });
                                }
                                var precioiva = preciocargo * 1.16;
                                if (almacen.indexOf('TJNA') >= 0 || almacen.indexOf('MEXL') >= 0 || almacen.indexOf('JURZ') >= 0) {
                                    if (data['impuestos'] == 'FRONT'){
                                        var precioiva = Number(data['precioiva']) * 1.08;
                                    }
                                }
                                $("#preciovtaiva" + myRow).val(precioiva).formatCurrency({ roundToDecimalPlace: '3' });
                                var montocargo = preciocargo * qty;
                                $("#montocargo" + myRow).val(montocargo).formatCurrency({ roundToDecimalPlace: '3' });
                                var montoiva = precioiva * qty;
                                $("#montoiva" + myRow).val(montoiva).formatCurrency({ roundToDecimalPlace: '3' });
                                var subtot = subtotal();
                                var tot = total();
                                $("#subtotal").html(subtot).formatCurrency();
                                $("#total").html(tot).formatCurrency();
                                var iva = tot - subtot;
                                $("#iva").html(iva).formatCurrency();
                                var documenType = $("#DocumentType2").val();
                                if ($("#condiEntrega").val() == 'CREDITO') {
                                }
                                $('#modalLoading').closeModal();
                                $('#lean-overlay').remove();
                                $('.lean-overlay').remove();
                                tmpPunitario = 0;
                                row = $('#cantidad' + myRow).closest('tr');
                                //$(row).removeClass('emptyRow');
                                //$(row).removeClass('load');
                                $(row).attr('data-dataAreaId', dataAreaId);
                                $(row).attr('data-InventoryLotId', InventoryLotId);
                                $("#preciovta" + myRow).removeClass('invalid');
                                var interval = setInterval(function () {
                                    if (jQuery.active === 0) {
                                        swal.close();
                                        $(row).parent().find('tr').removeClass('load');
                                        $(row).parent().find('tr').removeClass('emptyRow');
                                        $("#actionArticulos").removeAttr('disabled');
                                        clearInterval(interval);
                                    }
                                }, 300);
                            },
                            error: function (jqXHR, exep) {
                                $('#modalLoading').closeModal();
                                Materialize.toast('Error!' + catchError(jqXHR, exep), 3000);
                            },
                            complete: function (data) {
                            }
                        });
                    }
                },
                error: function (jqXHR, exep) {
                    console.log(jqXHR); console.log(exep);
                    $('#modalLoading').closeModal();
                    Materialize.toast('Error!' + catchError(jqXHR, exep), 3000);
                }
            });
        }
        /* inicia procedimiento lento de existencias */

        var excedido = (qty > disponible) ? 'excedido' : 'OK';
        if (excedido == 'excedido'){
            $(inputCantidad).addClass('excedido');
            negados.calcularNegado(item, qty);
            $('#modalNegados #inpArtNeg').val(item);
            $('#modalNegados #inpCantExs').val(disponible);
            $('#modalNegados #inpAlmaNeg').val(almacen);
            $('#modalNegados #inpClteNeg').val(cliente);
            $('#modalNegados #inpNameNeg').val(descripcion);
            $('#modalNegados #inpCantidad').val($(inputCantidad).attr('id'));
            $(inputCantidad).attr('data-disp', eval(disponible).toFixed(3));
            $('#modalNegados #articuloNegado').html(item);
            Materialize.toast('Articulo: ' + item + ' solo hay disponible ' + eval(disponible).toFixed(3) + ' ' + unidad + '!.', 3000);
        }else if (excedido == 'OK') {
            $(inputCantidad).removeClass('excedido');
        }

        // $.ajax({
        //     url: "inicio/devexistencias",
        //     type: "post",
        //     dataType: "json",
        //     data: { "item": item, "cant": qty, "sitio": sitio, "almacen": almacen, 'localidad': localidad, "token": 'devExistencias' },
        //     success: function (data, textStatus, jqXHR) {
        //         if (data['disponible'] == 'excedido') {
        //             $(inputCantidad).addClass('excedido');
        //             negados.calcularNegado(item, qty);
        //             $('#modalNegados #inpArtNeg').val(item);
        //             $('#modalNegados #inpCantExs').val(data['cantDisp']);
        //             $('#modalNegados #inpAlmaNeg').val(almacen);
        //             $('#modalNegados #inpClteNeg').val(cliente);
        //             $('#modalNegados #inpNameNeg').val(descripcion);
        //             $('#modalNegados #inpCantidad').val($(inputCantidad).attr('id'));
        //             $(inputCantidad).attr('data-disp', eval(data['cantDisp']).toFixed(3));
        //             $('#modalNegados #articuloNegado').html(item);
        //             Materialize.toast('Articulo: ' + item + ' solo hay disponible ' + eval(data['cantDisp']).toFixed(3) + ' ' + unidad + '!.', 3000);
        //         } else if (data['disponible'] == 'OK') {
        //             $(inputCantidad).removeClass('excedido');
        //         }
        //     },
        //     error: function (jqXHR, textStatus, errorThrown) {
        //         $('#modalLoading').closeModal();
        //         alert("error favor de reportar  sistemas");
        //     }
        // });
    }
});

//checkStep
$(document).on('blur','.checkStep',function(e){  
    var step = $(this).data("step");
    var lastvalue = $(this).data("lastvalue");
    var val = $(this).val();
    
    if((val%step) != 0 && step >= 1){
        swal('Error!','El valor debe de ser un múltiplo de '+step,'error');
        $(this).val(lastvalue);
        $(this).change();
    }
    
});

$(document).on('change','.checkStep',function(e){  

    var val = $(this).val();
    var step = $(this).data("step");
    if((val%step) == 0 ){
        var lastvalue = $(this).data("lastvalue",val);
    }
    
});

$(document).on('keydown','.checkStep',function(e){  
    if (e.which === 38 || e.which === 40) {
        e.preventDefault();
    }
});

$(document).on('click','.IsPriceBlocked',function(e){        
    var $tr = $(this).closest('tr');
    var myRow = $tr.index() + 1; 
    var item = $("#item"+myRow).val();
    var almacen = $("#almacen"+myRow).val();
    var localidad=$("#localidad"+myRow).val();
    var unidad  = $("#unidad"+myRow).val()
    var flag=true;
    if (almacen.indexOf('MRMA')!=-1 && flag==true) {
        var precioMerma=getPriceMermaPreview(item,$('#claveclte').val(),$('#monedalineas').val(),$("#sitioLineas").val(),almacen,localidad,unidad);
        $("#punitariolinea"+myRow).val(precioMerma.precio);
        // $("#punitariolinea"+myRow).val(0);
    }
    var artName= artArray[item];
    if(havePermision(9)){
        $("#punitariolinea"+myRow).removeAttr('readonly');
    }
    if(artName.indexOf('9999-')!==-1){
        $("#punitariolinea"+myRow).removeAttr('readonly');
    }        
    $.each(itemsNotLocked,function (i,v){
        if(item===v){
            $("#punitariolinea"+myRow).removeAttr('readonly');
        }
    });
});
/* aqui verificar cambio */
$(document).on('keydown','[id*="punitariolinea"]',function(ev){
	if ( ev.which == 13 ){
		var $tr = $(this).closest('tr');
		var myRow = $tr.index() + 1;
		var precio = $(this).val();
		var ev = $.Event({'type':'input','enviarExistencias':'1'});
		if (precio === "" || (precio < 1 && precio >0) || precio < 0) {
			Materialize.toast('El precio unitario no debe ser vacío o menor de 1, ingrese 0 para utilizar el asignado en la lista de precios!.', 7000);
			$("#preciovta"+myRow).val("");
			$("#preciovtaiva"+myRow).val("");
			$("#montocargo"+myRow).val("");
			$("#montoiva"+myRow).val("");

		}else{
            tmpPunitario=precio;
            punitario=precio;
            $("#cantidad"+myRow).trigger(ev);
		}
		$('.closeCambioPrecio').click();
	}
});

function subtotal(){
	var subtotal = 0;
	$('input[id*="montocargo"]').each(function(index){
		var str = $(this).val();
		if (str === "") {
			str = '$0';
		}
		var st = str.replace("$","");
		var st = st.replace(/,/g,"","gi");
		subtotal += parseFloat(st);
	});
	return subtotal;
}	

function total(){
	var total = 0;
	$('input[id*="montoiva"]').each(function(index){
		var str = $(this).val();
		if (str === "") {
			str = '$0';
		}
		var st = str.replace("$","");
		var st = st.replace(/,/g,"","gi");
		total += parseFloat(st);				
	});
	return total;			
}

function seleccionar(tr){
	var myRow = 0;
	var $tr = $(tr).closest('tr');
	myRow = $tr.index() + 1;
	alert("se selecciono el tr numero:" + (myRow-1));
}

function getExistencias(item, myRow, unidadNeg, descripcion, excedido, cliente, almacen, type) {
    $('#modalHeaderText').html("Existencias: " + item + " " + descripcion);
    $('#platesTitle').html("Existencias: " + item + " " + descripcion);
    $('#banerMerma').remove();
    $('#imagenes').remove();
    $("#downBtn").css("display", "none");
    var sitio = $("#sitioLineas").val();
    if (typeof (almacen) == 'undefined') {
        almacen = null;
        almacen = sitio + 'CONS';
    }
    // Obtener el tipo de Documento
    var documenType = $("#DocumentType2").val();

    $("#LineaArticulo").val(myRow);

    $.ajax({
        url: "inicio/existencias",
        type: "post",
        dataType: "json",
        data: { "item": item, "documenType": documenType, 'sitio': sitio, 'almacen': almacen },
        beforeSend: function (xhr) {
            Materialize.toast("Obteniendo existencias", 3000);
        },
        success: function (data) {
            $('#ExistenciasSitioClte > tbody').html("");

            $('#ExistenciasTodosSitios > tbody').html("");
            var cantSitioClte = ($.grep(data.datos, function (a) { return a.Sitio === sitio })).length;
            var vencidos = false;
            var focus = "";
            var existenciaCedis = 0;
            var existenciaCedisEsp = 0;
            var existenciaCedisMerma = 0;
            var existenciaCedisEqps = 0;
            var datosArray = data.datos;
            //var esMerma = false;
            //EVALUAR Y SEPARAR EXISTENCIA DE CEDIS Y LA DE CEDIS MERMA
            $.each(datosArray, function (i, v) {
                if (v.Sitio === 'CEDSCHI') {
                    if (v.Almacen === 'CEDSMRMA') {
                        existenciaCedisMerma = existenciaCedisMerma + Number(v.Existencia);
                    }
                    if (v.Almacen === 'CEDSCONS') {
                        existenciaCedis = existenciaCedis + Number(v.Existencia);
                    }
                    if (v.Almacen === 'CEDSESPC') {
                        existenciaCedisEsp = existenciaCedisEsp + Number(v.Existencia);
                    }
                    if (v.Almacen === 'CEDSEQPS') {
                        existenciaCedisEqps = existenciaCedisEqps + Number(v.Existencia);
                    }
                }
            });
            var exCedis = 0;
            for (var i = 0; i < data.datos.length; i++) {
                var solicitaTraspaso = '';
                if (data.datos[i].cantCaduco > 0) {
                    var color = '#F79F81';
                    vencidos = true;
                }
                if (!(data.datos[i].Almacen.indexOf('AUDI') != -1) && !(data.datos[i].Almacen.indexOf('TRAS') != -1) && !(data.datos[i].Almacen.indexOf('RECM') != -1)) {

                    if (data.datos[i].Sitio === sitio) {
                        if (Number(data.datos[i].Existencia) === 0 & data.datos[i].Sitio === 'CHIH' & (data.datos[i].Almacen.indexOf('MRMA') != -1 | data.datos[i].Almacen.indexOf('CONS') != -1 | data.datos[i].Almacen.indexOf('ESPC') != -1 | data.datos[i].Almacen.indexOf('EQPS') != -1)) {
                            if (data.datos[i].Almacen.indexOf('MRMA') != -1) {
                                exCedis = existenciaCedisMerma;
                                solicitaTraspaso = '<a onclick="solicitaTraspaso(\'' + item + '\',\'' + escape(descripcion) + '\',' + existenciaCedisMerma + ',\'c' + i + '\',\'' + data.datos[i].Almacen + '\',0)" style="line-height:  40px;">solicita traspaso</a>';
                                if (existenciaCedisMerma <= 0) {
                                    solicitaTraspaso = '';
                                }
                            } else {
                                exCedis = existenciaCedis;
                                solicitaTraspaso = '<a onclick="solicitaTraspaso(\'' + item + '\',\'' + escape(descripcion) + '\',' + existenciaCedis + ',\'c' + i + '\',\'' + data.datos[i].Almacen + '\',0)" style="line-height:  40px;">solicita traspaso</a>';
                                if (existenciaCedis <= 0) {
                                    solicitaTraspaso = '';
                                }
                            }
                            if (data.datos[i].Almacen.indexOf('ESPC') != -1) {
                                exCedis = existenciaCedisEsp;
                                solicitaTraspaso = '<a onclick="solicitaTraspaso(\'' + item + '\',\'' + escape(descripcion) + '\',' + existenciaCedisEsp + ',\'c' + i + '\',\'' + data.datos[i].Almacen + '\',0)" style="line-height:  40px;">solicita traspaso</a>';
                                if (existenciaCedisEsp <= 0) {
                                    solicitaTraspaso = '';
                                }
                            }
                            if (data.datos[i].Almacen.indexOf('EQPS') != -1) {
                                exCedis = existenciaCedisEqps;
                                solicitaTraspaso = '<a onclick="solicitaTraspaso(\'' + item + '\',\'' + escape(descripcion) + '\',' + existenciaCedisEqps + ',\'c' + i + '\',\'' + data.datos[i].Almacen + '\',0)" style="line-height:  40px;">solicita traspaso</a>';
                                if (existenciaCedisEqps <= 0) {
                                    solicitaTraspaso = '';
                                }
                            }
                        } else {
                            if (data.datos[i].Almacen.indexOf('MRMA') != -1) {
                                exCedis = existenciaCedisMerma;
                            } else if (data.datos[i].Almacen.indexOf('ESPC') != -1) {
                                exCedis = existenciaCedisEsp;
                            } else {
                                exCedis = existenciaCedis;
                            }
                        }
                        var selected = '';
                        if (cantSitioClte == 1) {
                            selected = 'checked';
                        } else {
                            var bandAlmacen = data.datos[i].Almacen.indexOf('CONS');
                            if (bandAlmacen >= 0) {
                                selected = 'checked';
                            }
                        }
                        var btn = '';
                        var habilita = true;
                        if (data.datos[i].Almacen.indexOf('MRMA') != -1 & data.datos[i].Sitio === sitio & Number(data.datos[i].Existencia) > 0 & habilita) {
                            selected = 'checked';
                            $('#ExistenciasSitioClte > tbody').append('<tr id="banerMerma" ><td colspan="6" style="background-color: #62ff0061;"><label style="color:black;font-size: 2em;">Existe merma por vender</label></td></tr>');
                            btn = "<a onclick=\"verMerma('" + item + "','" + data.datos[i].Almacen + "','" + data.datos[i].Localidad + "');\" ><span class=\"chip\">ver imagenes</span></a>";
                            btn += "<a target=\"_blank\" href=\"../public/imageDetail?item=" + item + "&almacen=" + data.datos[i].Almacen + "&local=" + data.datos[i].Localidad + "\" ><span class=\"chip\">  ver imagenes en otra pantalla</span></a>";
                            focus = "#trE" + i;
                        }
                        if (Number(data.datos[i].Existencia) === 0 && data.datos[i].Almacen.indexOf('CONS') > -1) {
                            $.ajax({
                                url: 'inicio/getalternativos', type: 'post', async: true, data: { 'itemId': item, 'sitio': sitio },
                                beforeSend: function (xhr) {

                                },
                                success: function (res) {
                                    if (res != '') {
                                        $('#ExistenciasSitioClte > tfoot').html(res);
                                        $('#ExistenciasSitioClte > tfoot').addClass('alternativo');
                                        $('input[name="RadioExist"]:checked').parent().find('label').html('**');
                                        $('input[name="RadioExist"]:checked').parent().find('label').addClass('blk');
                                    } else {
                                        $('#ExistenciasSitioClte > tfoot').html('');
                                        $('#ExistenciasSitioClte > tfoot').removeClass('alternativo');
                                        $('input[name="RadioExist"]:checked').parent().find('label').html('');
                                        $('input[name="RadioExist"]:checked').parent().find('label').removeClass('blk');
                                    }
                                    $('#ExistenciasSitioClte:first *:input[type=text]:first').focus();
                                },
                                error: function (jqXHR, exep) {
                                    Materialize.toast('Error! token : getAlternativos ' + catchError(jqXHR, exep), 3000);
                                }
                            });
                        }
                        var cantItem = Number(data.datos[i].Existencia).toFixed(2);
                        var itemNeg = item;
                        var sitioNeg = data.datos[i].Sitio;
                        var almacenNeg = data.datos[i].Almacen;
                        var classSelected = '';
                        var but = '';
                        var cantidadOriginal = $('#cantidad' + myRow).val();
                        var cantidadValue = cantidadOriginal;
                        var step = 0;
                        var checkStepClass = "checkStep";

                        if (data.datos[i].minimo) {
                            cantidadOriginal = Number(data.datos[i].minimo);
                            step = Number(data.datos[i].minimo);
                            cantidadValue = cantidadOriginal;
                        } else {
                            checkStepClass = "";
                            cantidadOriginal = 0;
                            cantidadValue = 1;
                        }

                        var lv = $('#cantidad' + myRow).data("lastvalue");
                        if (lv !== undefined) {
                            cantidadValue = lv;
                        }

                        var cantidadTecleada = '';
                        if (selected === 'checked') {
                            cantidadTecleada = '<input style="max-width: 3em;text-align: center;" type="number" onchange="traspasoCantidad(this.id)" name="cantidadSol" value="' + cantidadValue + '" id="c' + i + '" min="0" step="' + step + '" class="' + checkStepClass + '" data-step="' + cantidadOriginal + '" data-lastvalue="' + cantidadOriginal + '" data-item="' + item + '" data-existencia="' + cantItem + '" data-almacen="' + almacenNeg + '" data-desc="' + escape(descripcion) + '"data-unidad="' + unidadNeg + '"  data-cedis="' + exCedis + '" />';
                            focus = '#c' + i;
                        }
                        var existeItem = negados.existeArticulo(item);
                        if (almacen === almacenNeg && existeItem) {
                            classSelected = 'selected';
                            color = '#A4A4A4';
                        } else {
                            color = '#FFFFFF';
                        }
                        if (!(data.datos[i].Almacen.indexOf('MRMA') != -1) && solicitaTraspaso === '') {
                            but = '<button type="button" class="btn red btnNegadoLinea" onclick="negarRow(this);" data-itemneg="' + itemNeg + '" data-sitioneg="' + sitioNeg + '" data-almacenneg="' + almacenNeg + '" data-unidadneg="' + unidadNeg + '" data-descripcion="' + descripcion + '" data-excedido="' + excedido + '" data-cliente="' + cliente + '"><i class="material-icons">remove_shopping_cart</i> Negar</button>';
                        }
                        var lpHtml = (!noLpViewArray.includes(userBranchOffice)) ? '<span class="btn blue getLpInfo" data-localidad="' + data.datos[i].Sitio + '" data-almacen="' + data.datos[i].Almacen + '" data-item="' + item + '" data-unidad="' + unidadNeg + '" >VER EXISTENCIA POR LP</span>' : '';
                        var newLine2 = '<tr id="trE' + i + '" style="background-color:' + color + '"  class="' + classSelected + '">'
                            + '<td><p id="Existsitio' + i + '" data-localidad="' + data.datos[i].Localidad + '" data-categoria="' + data.datos[i].categoria + '">' + data.datos[i].Sitio + ' </p></td>'
                            + '<td><span id="Existalmacen' + i + '" >' + data.datos[i].Almacen + '</span> - ' + data.datos[i].Localidad + '      ' + btn + '</td>'
                            + '<td><p id="existencia' + i + '" >' + cantItem + '</p></td>'
                            + '<td id="td' + i + '"><p id="nl' + myRow + '">' + cantidadTecleada + '</p></td>'
                            + '<td><p><input type="radio" name="RadioExist" Value="' + i + '" class="ExistRadio" id="radio' + i + '" ' + selected + '/>'
                            + '<label onclick="limpiarCantidad(' + i + ',' + cantidadValue + ',' + cantidadOriginal + ',' + step + ',\'' + checkStepClass + '\',\'' + item + '\',\'' + cantItem + '\',\'' + almacenNeg + '\',\'' + escape(descripcion) + '\',\'' + exCedis + '\',\'' + unidadNeg + '\')" for="radio' + i + '"></label></p></td>'
                            + '<td id="existenciaOption' + i + '">' + but + solicitaTraspaso + lpHtml + '</td></tr>';
                        $("#ExistenciasSitioClte").append(newLine2);
                        if (focus != '') {
                            $(focus).focus();
                        }
                    } else {
                        var newLine = "";
                        var LineaArticulo = parseInt($("#NumFilas").val());
                        var lpHtml = (!noLpViewArray.includes(userBranchOffice)) ? '<span class="btn blue getLpInfo" data-localidad="' + data.datos[i].Sitio + '" data-almacen="' + data.datos[i].Almacen + '" data-item="' + item + '" data-unidad="' + unidadNeg + '" >VER EXISTENCIA POR LP</span>' : '';
                        if (LineaArticulo == 1) {
                            if (data.datos[i].Sitio != 'CEDSCHI') {
                                btn = '';
                                var flag = true;
                                if (data.datos[i].Almacen.indexOf('MRMA') != -1 & data.datos[i].Existencia != '.0000000000000000' & flag == true) {
                                    btn = "<a onclick=\"verMerma('" + item + "','" + data.datos[i].Almacen + "','" + data.datos[i].Localidad + "');\" >ver imagenes</a>";
                                    btn += "<a target=\"_blank\" href=\"../public/imageDetail?item=" + item + "&almacen=" + data.datos[i].Almacen + "\" >  ver imagenes en otra pantalla</a>";
                                }
                                newLine = $('<tr style="background-color:' + color + '" ><td><p id="Existsitio' + i + '" data-localidad="' + data.datos[i].Localidad + '">' + data.datos[i].Sitio + '</p></td><td><p id="Existalmacen' + i + '" >' + data.datos[i].Almacen + '</p>' + btn + '</td><td><p id="existencia' + i + '" >' + Number(data.datos[i].Existencia).toFixed(2) + '</p></td><td><input type="radio" name="RadioExist" Value="' + i + '" class="ExistRadio" id="radio' + i + '" /><label style="width: 10%" for="radio' + i + '"></label>' + lpHtml +'</td></tr>');
                            } else {
                                newLine = $('<tr style="background-color:' + color + '" ><td><p id="Existsitio' + i + '" data-localidad="' + data.datos[i].Localidad + '">' + data.datos[i].Sitio + '</p></td><td><p id="Existalmacen' + i + '" >' + data.datos[i].Almacen + '</p></td><td><p id="existencia' + i + '" >' + Number(data.datos[i].Existencia).toFixed(2) + '</p></td><td><p></p>' + lpHtml +'</td></tr>');
                            }
                        } else {
                            newLine = $('<tr style="background-color:' + color + '"><td><p id="Existsitio' + i + '" data-localidad="' + data.datos[i].Localidad + '">' + data.datos[i].Sitio + '</p></td><td><p id="Existalmacen' + i + '" >' + data.datos[i].Almacen + '</p></td><td><p id="existencia' + i + '" >' + Number(data.datos[i].Existencia).toFixed(2) + '</p></td><td><p></p>' + lpHtml +'</td></tr>');
                        }
                        $("#ExistenciasTodosSitios").append(newLine);
                    }
                }
            }
            //////////////////////tab del sitio cliente///////////////////////////////////////////////////////
            var rowsClte = $(document).find('#ExistenciasSitioClte tbody').find('tr').filter(':visible');
            var firstrowClte = rowsClte.first();
            var lastrowClte = rowsClte.last();

            $(rowsClte).on('keydown', function (e) {
                var radio = $(this).find('td').find('input.ExistRadio');
                if (e.which === 32) {
                    e.preventDefault();
                    if ($(radio).is(':checked')) {
                        $(radio).removeProp('checked');
                    } else {
                        $(radio).prop('checked', 'checked');
                    }
                }
                if (e.which === 13) {
                    e.preventDefault();
                    if ($(radio).is(':checked')) {
                        $('#EnviarExistencias').trigger('click');
                    }
                }
                if (e.which === 40) {
                    $(this).next().focus();
                }
                if (e.which === 38) {
                    $(this).prev().focus();
                }
                if (e.which === 27) {
                    $('#CancelarExistencias').trigger('click');
                }
            });
            firstrowClte.focus();
            lastrowClte.on('keydown', function (e) {
                if ((e.which === 9 && !e.shiftKey) || (e.which === 40)) {
                    e.preventDefault();
                    firstrowClte.focus();
                }
            });
            firstrowClte.on('keydown', function (e) {
                if ((e.which === 9 && e.shiftKey) || (e.which === 38)) {
                    e.preventDefault();
                    lastrowClte.focus();
                }
            });
            ////////////////////////////////tab de todos los sitios//////////////////////////////////////////////////
            var rowsTodosSitios = $(document).find('#ExistenciasTodosSitios tbody').find('tr');
            var firstrowTodosSitios = rowsTodosSitios.first();
            var lastrowTodosSitios = rowsTodosSitios.last();
            $(rowsTodosSitios).on('keydown', function (e) {
                var radio = $(this).find('td').find('input.ExistRadio');
                if (e.which === 32) {
                    e.preventDefault();
                    if ($(radio).is(':checked')) {
                        $(radio).removeProp('checked');
                    } else {
                        $(radio).prop('checked', 'checked');
                    }
                }
                if (e.which === 13) {
                    e.preventDefault();
                    if ($(radio).is(':checked')) {
                        $('#EnviarExistencias').trigger('click');
                    }
                }
                if (e.which === 40) {
                    $(this).next().focus();
                }
                if (e.which === 38) {
                    $(this).prev().focus();
                }
                if (e.which === 27) {
                    $('#CancelarExistencias').trigger('click');
                }
            });

            /*set focus on first input*/
            firstrowTodosSitios.focus();

            /*redirect last tab to first input*/
            lastrowTodosSitios.on('keydown', function (e) {
                if ((e.which === 9 && !e.shiftKey) || (e.which === 40)) {
                    e.preventDefault();
                    firstrowTodosSitios.focus();
                }
            });

            /*redirect first shift+tab to last input*/
            firstrowTodosSitios.on('keydown', function (e) {
                if ((e.which === 9 && e.shiftKey) || (e.which === 38)) {
                    e.preventDefault();
                    lastrowTodosSitios.focus();
                }
            });
            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            if (vencidos) {
                $('#modal1 #divLotesVencidos').show();
            } else {
                $('#modal1 #divLotesVencidos').hide();
            }
            $('#modal1 #preloaderExistencias').hide();
        },
        error: function (jqXHR, exep) {
            Materialize.toast('Error!' + catchError(jqXHR, exep), 3000);
        }
    });
    $('#edicionExis').val(type);
}
function candidadDisponible(cliente,existencia,cedis){
    var res=0;
    var limite=existencia+cedis;
    var pedido=cliente-existencia;
    if(pedido<limite){
      res=pedido; 
    }
    return res.toFixed(3);
}
function traspasoCantidad(id) {
    id = id.replace("c", "");
    var element = $("#c" + id);
    var conentDataElement = element[0].dataset;
    var item = conentDataElement.item;
    var descripcion = conentDataElement.desc;
    var existencia = Number(conentDataElement.existencia);
    var existenciaCedis = Number(conentDataElement.cedis);
    var sitio = $("#sitioLineas").val();
    var necesita = $("#c" + id).val();
    var almacen = conentDataElement.almacen;
    var unidadNeg = conentDataElement.unidad;
    if ((almacen === 'CHIHCONS' || almacen === 'CHIHMRMA' || almacen === 'CHIHESPC' || almacen === 'CHIHEQPS')) {
        var limite = existencia + existenciaCedis;
        var pedidoCliente = candidadDisponible(Number(necesita), existencia, existenciaCedis);
        if (existencia > 0) {
            var lpHtml = (!noLpViewArray.includes(userBranchOffice)) ? '<span class="btn blue getLpInfo" data-localidad="' + sitio + '" data-almacen="' + almacen + '" data-item="' + item + '" data-unidad="' + unidadNeg + '" >VER EXISTENCIA POR LP</span>' : '';
            if (necesita > limite) {
                var act = limite - necesita;
                if (act < 0 && existenciaCedis === 0) {
                    swal('¡Alto!', 'Solamente se puede pedir como maximo' + limite, 'info');
                }
                //$('#existenciaOption'+id).html('<button type="button" class="btn red btnNegadoLinea" onclick="negarRow(this);" data-itemneg="'+item+'" data-sitioneg="'+sitio+'" data-almacenneg="'+almacen+'" data-unidadneg="" data-descripcion="" data-excedido="" data-cliente="'+$('#cliente').val()+'"><i class="material-icons">remove_shopping_cart</i> Negar</button>');
            }
            else if (necesita > existencia && necesita <= limite) {
                $('#existenciaOption' + id).html('<a onclick="solicitaTraspaso(\'' + item + '\',\'' + escape(descripcion) + '\',' + existenciaCedis + ',\'c' + id + '\',\'' + almacen + '\',' + pedidoCliente + ')" style="line-height:  40px;">solicita traspaso</a>' + lpHtml);
            }
            else {
                $('#existenciaOption' + id).html('<button type="button" class="btn red btnNegadoLinea" onclick="negarRow(this);" data-itemneg="' + item + '" data-sitioneg="' + sitio + '" data-almacenneg="' + almacen + '" data-unidadneg="" data-descripcion="" data-excedido="" data-cliente="' + $('#cliente').val() + '"><i class="material-icons">remove_shopping_cart</i> Negar</button>' + lpHtml);
            }

        }
        else {
            if (pedidoCliente >= 0) {
                var limiteAct = limite - necesita;
                if (limiteAct >= 0) {
                    $('#existenciaOption' + id).html('<a onclick="solicitaTraspaso(\'' + item + '\',\'' + escape(descripcion) + '\',' + existenciaCedis + ',\'c' + id + '\',\'' + almacen + '\',' + pedidoCliente + ')" style="line-height:  40px;">solicita traspaso</a>');
                }
                else {
                    $('#existenciaOption' + id).html('<button type="button" class="btn red btnNegadoLinea" onclick="negarRow(this);" data-itemneg="' + item + '" data-sitioneg="' + sitio + '" data-almacenneg="' + almacen + '" data-unidadneg="" data-descripcion="" data-excedido="" data-cliente="' + $('#cliente').val() + '"><i class="material-icons">remove_shopping_cart</i> Negar</button>');
                }
            }
            else {
                if (necesita > limite) {
                    swal('¡Alto!', 'Solamente se puede pedir como limite:' + limite, 'info');
                    $('#existenciaOption' + id).html('<button type="button" class="btn red btnNegadoLinea" onclick="negarRow(this);" data-itemneg="' + item + '" data-sitioneg="' + sitio + '" data-almacenneg="' + almacen + '" data-unidadneg="" data-descripcion="" data-excedido="" data-cliente="' + $('#cliente').val() + '"><i class="material-icons">remove_shopping_cart</i> Negar</button>');
                } else {
                    $('#existenciaOption' + id).html('<a onclick="solicitaTraspaso(\'' + item + '\',\'' + escape(descripcion) + '\',' + existenciaCedis + ',\'c' + id + '\',\'' + almacen + '\',0)" style="line-height:  40px;">solicita traspaso</a>');
                }
            }
        }
    }
}
function limpiarCantidad(id, cantV, cant, step, checkStep, item, cantItem, almacenNeg, desc, exCedis, unidadNeg) {
    var limp = 0;
    var checkStepClass = checkStep;
    while (limp < 50) {
        $('#nl' + limp).empty();
        limp++;
    }
    $('#td' + id).html('<p id="nl' + id + '"><input style="max-width: 4em;text-align: center;" type="number" onchange="traspasoCantidad(this.id)" name="cantidadSol" value="' + cantV + '" id="c' + id + '" min="0" step="' + step + '" class="' + checkStepClass + '" data-step="' + cant + '" data-lastvalue="' + cant + '" data-item="' + item + '" data-existencia="' + cantItem + '" data-almacen="' + almacenNeg + '" data-desc="' + desc + '"data-unidad="' + unidadNeg + '" data-cedis="' + exCedis + '" /></p>');
}
$(document).on('click','#EnviarExistencias',function(e){
    var bandAlter = false;
    var itemAlter = $('input[name="RadioExist"]:checked');
    if ( $(itemAlter).hasClass('alternativo') ){ bandAlter = true;}
    var myRow = $('#LineaArticulo').val();
    var LineaArticulo = parseInt($("#NumFilas").val());
    var SitioClteEncabezado = $("#sitioLineas").val();
    var lineasel = $('input[name="RadioExist"]:checked').val();
    var cant=$('#c'+lineasel).val();
    var step = $('#c'+lineasel).data("step");
    var disp = $('#c'+lineasel).data("existencia");
    
    if((cant%step) != 0 && step >= 1){
        Materialize.toast('Error! El valor debe de ser un múltiplo de '+step, 3000);
         return false;
    }
    
    if ( !bandAlter ){
        var ExistItemId    = $('#item'+myRow).val();
        var ExistNombre    = $('#descripcion'+myRow).val();
        var ExistSitio     = $("#Existsitio"+lineasel).text();
        var ExistAlmacen   = $("#Existalmacen"+lineasel).text();
        var ExistLocalidad = $("#Existsitio"+lineasel).attr("data-localidad");
        var Excedido       = $('#existencia'+lineasel).attr('data-excedido');
        var Categoria      = $("#Existsitio"+lineasel).attr("data-categoria");
    }else{
        var ExistItemId    = $(itemAlter).parents().closest('tr').find('.itemid').html();
        var ExistNombre    = $(itemAlter).parents().closest('tr').find('.namealias').html();
        var ExistSitio     = $(itemAlter).parents().closest('tr').find('.sitio').html();
        var ExistAlmacen   = $(itemAlter).parents().closest('tr').find('.almacen').html();
        var ExistLocalidad = $(itemAlter).parents().closest('tr').find('.sitio').attr('data-localidad');
        var Categoria      = $(itemAlter).parents().closest('tr').find('.sitio').attr('data-categoria');
        cant=1;
    }
    if (lineasel == null) {
        alert("Debes seleccionar un sitio.");
        return;
    }
    $('#cantidad'+myRow).val(cant);
    $("#cantidad"+myRow).data("lastvalue",cant);
    $('#modal1').closeModal();
    var ev = $.Event({'type':'input','enviarExistencias':'1'});
    if (LineaArticulo === 1){
        if (ExistSitio.trim() != SitioClteEncabezado.trim()){			    
            $("#SitioEncabezadoModal").openModal();
            $('.lean-overlay').remove();
            $(document).on('click','#AceptarActualizarEncabezado',function(){
                $("#sitioLineas").val(ExistSitio);
                $("#sitioLineas").material_select();
                $("#sitioLineas").trigger('change');
                $("#sitioLineas").val(ExistSitio);
                $("#almacenes").val(ExistSitio+'CONS');
                $("#articulos tr").removeClass("load");
                $("#SitioEncabezadoModal").closeModal();
            });
        }
  
    $("#item"+myRow).val(ExistItemId);
	$("#descripcion"+myRow).val(ExistNombre);
	$("#sitio"+myRow).val(ExistSitio);
	$("#almacen"+myRow).val(ExistAlmacen);   
    if($("#almacen"+myRow).data('almacen')===''){
       $("#almacen"+myRow).data('almacen',ExistAlmacen); 
    }
    if (ExistAlmacen.includes('EXHB')){
        if (comp == 'LIN'){
            $('#lote'+myRow).val(lideartDimensions.lote);
            $('#localidad'+myRow).val(lideartDimensions.localidad);
            $('#matricula'+myRow).val(lideartDimensions.licensePlate);
        }else{
            $('#lote'+myRow).val(ExistAlmacen+"LOTE");
            atpDimensions.lote=ExistAlmacen+"LOTE";
            atpDimensions.licensePlate=ExistAlmacen;
            $('#localidad'+myRow).val(atpDimensions.localidad);
            $('#matricula'+myRow).val(ExistAlmacen);
        }
    }
	$("#cantidad"+myRow).addClass(Excedido);
	$("#cantidad"+myRow).trigger(ev);
	$("#cantidad"+myRow).focus();
	$("#cantidad"+myRow).select();
    $("#cantidad"+myRow).attr("min",step);
    $("#cantidad"+myRow).attr("type","number");
    $("#cantidad"+myRow).attr("step",step);
    $("#cantidad"+myRow).attr("disp",disp);
    $("#cantidad"+myRow).attr("data-step",step);
    $("#cantidad"+myRow).attr("data-lastvalue",cant);
    $("#cantidad"+myRow).addClass('checkStep');
    $("#categoria"+myRow).val(Categoria);
    var row = $('#cantidad'+myRow).closest('tr');
    var descripcionArticulo = $('#descripcion'+myRow).val();
    if(descripcionArticulo.includes("FLETE")){
        $(row).removeClass('emptyRow');	
        $(row).addClass('flete');	
    }else{
        $(row).removeClass('emptyRow');	
    }    
    
}else if (LineaArticulo != 1){
    var SitioLinea1 = $("#sitio1").val();
    if (SitioLinea1 != ""){
        if (ExistSitio.trim() != SitioLinea1.trim()){
            Materialize.toast('No se puede seleccionar un sitio distinto a la primer línea!', 3000);
            $("#sitio"+myRow).val("");
            $("#almacen"+myRow).val("");
	}else{
            $("#item"+myRow).val(ExistItemId);
            $("#descripcion"+myRow).val(ExistNombre);
            $("#sitio"+myRow).val(ExistSitio);
            $("#almacen"+myRow).val(ExistAlmacen);
            if($("#almacen"+myRow).data('almacen')===''){
                $("#almacen"+myRow).data('almacen',ExistAlmacen); 
            } 
            if (ExistAlmacen.includes('EXHB')){
                if (comp == 'LIN'){
                    $('#lote'+myRow).val(lideartDimensions.lote);
                    $('#localidad'+myRow).val(lideartDimensions.localidad);
                    $('#matricula'+myRow).val(lideartDimensions.licensePlate);
                }else{
                    $('#lote'+myRow).val(ExistAlmacen+"LOTE");
                    atpDimensions.lote=ExistAlmacen+"LOTE";
                    atpDimensions.licensePlate=ExistAlmacen;
                    $('#localidad'+myRow).val(atpDimensions.localidad);
                    $('#matricula'+myRow).val(ExistAlmacen);
                }
            }
            //$("#localidad"+myRow).val(ExistLocalidad);
            $("#cantidad"+myRow).addClass(Excedido);
            $("#cantidad"+myRow).trigger(ev);
            $("#cantidad"+myRow).focus();
            $("#cantidad"+myRow).select();
            $("#cantidad"+myRow).attr("min",step);
            $("#cantidad"+myRow).attr("type","number");
            $("#cantidad"+myRow).attr("step",step);
            $("#cantidad"+myRow).attr("disp",disp);
            $("#cantidad"+myRow).attr("data-step",step);
            $("#cantidad"+myRow).attr("data-lastvalue",cant);
            $("#cantidad"+myRow).addClass('checkStep');
            $("#categoria"+myRow).val(Categoria);
            var row = $('#cantidad'+myRow).closest('tr');
            var descripcionArticulo = $('#descripcion'+myRow).val();
            if(descripcionArticulo.includes("FLETE")){
                $(row).removeClass('emptyRow');	
                $(row).addClass('flete');	
            }else{
                $(row).removeClass('emptyRow');	
            }         
        }
    }else{
        swal('¡Alto!','Debe Seleccionar un Sitio en la primer línea!','info');
    }
}
});

function shortcuts(){	
	$(document).keydown(function(e) {			
        if (e.which === 113){
        	$('#F2pressed').val('1');
                var type="edit";
        	var $tr = $(document.activeElement).closest('tr');
			var myRow = $tr.index() + 1;				
			if(myRow === 0){
				Materialize.toast('Favor de posicionarse en una lÃ­nea!', 3000,'red');
			}else{
				var item = $('#item'+myRow).val();
                                var cantidad=$("#cantidad"+myRow).val();
				if (item === "") {
					Materialize.toast('Ingresa un artÃ­culo para ver su existencia!', 3000);	
				} else{
					var unidad      = $('#unidad'+myRow).val();
					var descripcion = $('#descripcion'+myRow).val();
					var almacen     = $('#almacen'+myRow).val();
					var excedido    = ( $('#cantidad' + myRow).hasClass('excedido') ) ? 'excedido' : 'ok';
					var cliente     = $("#cliente").val();
					$('#modal1 #preloaderExistencias').show();
					$('#ExistenciasSitioClte tbody').html('');
					$('#ExistenciasTodosSitios tbody').html('');
	            	getExistencias(item,myRow,unidad,descripcion,excedido,cliente,almacen,type);		            	
                        $('#modal1').openModal({dismissible: false});
                        $('#lean-overlay').remove();
	            	$('.lean-overlay').remove();
	            	$('ul.tabs').tabs('select_tab', 'test1');
            	}
        	}
        }
        else if (e.which === 115){
            console.log("presione f4");
            /*TODO: seleccionar valor de input focus para agilizar el proceso de las familias*/
            if ($('#articulosDiv').is(':visible')){
                $("#ExistenciaFamilia > tbody").html("");
                $("#familia").val("");
                $("#ExistenciaFamiliaModal").openModal();
                $('#lean-overlay').remove();
                $('.lean-overlay').remove();
                $("#familia").focus();
            }
        }
        else if (e.which === 117){
        	e.stopPropagation();
        	ultimasVentas();
        }
        else if (e.which === 116){
        	e.preventDefault();
        	e.stopPropagation();
        }
    });
}

function getDirecciones(cliente){
	$('#titleDirecciones').addClass('load-title');

        $.ajax({ 
            url: "inicio/direcciones",
            type: "post", 
            dataType: "json", 
            data: { "cliente": cliente, "token":"direcciones" },

        success: function (data){
            $("#DireccionesClte > tbody").html("");
            if (data.resultado != 'NoResults'){
                // if (data.size == 1){$('#dropdownDirecciones').css('display','none');}
                // else{$('.DirCliente').click();}
                for (var i=0;i<(data.size-1);i++){
                    if (data.data[i].flagInvoice != 0){
                        $("#DireccionesClte > tbody").append('<tr><td><p style="padding:45px 0px 0px 0px;"><input type="radio" name="RadioDir" Value="'+i+'" class="DirRadio" id="radioDir'+ i +'" checked/><label for="radioDir' + i +'"></label></p></td><td id="NombreDir'+i+'">' + data.data[i].ADDRESS + '</td><td id="Dir'+i+'">' + data.data[i].DESCRIPTION + '</td><td id="PropDir'+i+'">' + data.data[i].Proposito + '</td><input type="hidden" id="RecIdDir'+i+'" value="'+ data.data[i].RECID +'" /></tr>');
                    }else{
                        $("#DireccionesClte > tbody").append('<tr><td><p style="padding:45px 0px 0px 0px;"><input type="radio" name="RadioDir" Value="'+i+'" class="DirRadio" id="radioDir'+ i +'" /><label for="radioDir' + i +'"></label></p></td><td id="NombreDir'+i+'">' + data.data[i].ADDRESS + '</td><td id="Dir'+i+'">' + data.data[i].DESCRIPTION + '</td><td id="PropDir'+i+'">' + data.data[i].Proposito + '</td><input type="hidden" id="RecIdDir'+i+'" value="'+ data.data[i].RECID +'" /></tr>');
                    }
                }
                $('#DireccionesClte tbody tr').on('click', function() {
                    op1 = $(this).children('td:not([id])').children('p').children('input');
                    op1.prop('checked', true);
                    $('#ElegirDireccion').click();
                });
	    }
            $('#titleDirecciones').removeClass('load-title');
        },
        error: function (jqXHR,exep){
            Materialize.toast('Error!'+catchError(jqXHR,exep), 3000);
	}
    });
}

function getFraccionado(item,almacen,fila){	
	$.ajax({url: "inicio/fraccionado",type: "post",dataType: "json",data: { "item": item, "almacen": almacen},
        success: function (data){
            $("#ExistenciaFraccionado > tbody").html("");
            if (!data["noresult"]){
                for (var i=0;i<data.length;i++){
                    var disponible = parseFloat(data[i].FisicaDisponible);
                    $("#ExistenciaFraccionado > tbody").append('<tr><td>' + data[i].Articulo + '</td><td>' + data[i].DescripcionArticulo +'</td><td>' + data[i].Almacen +'</td><td>' + data[i].Descripcion +'</td><td>' + disponible.toFixed(2) +'</td></tr>');			
                }
                $('#modal2 .modal-footer #fila').val(fila);
                $('#modal2').openModal();
                $('#lean-overlay').remove();
                $('.lean-overlay').remove();
            }
        },
        error: function (jqXHR,exep){
            Materialize.toast('Error!'+catchError(jqXHR,exep), 3000);
        }
    });
}



function ValidarFraccionado(item,fila){	
    if(fila === 0){ Materialize.toast('Favor de posicionarse en una línea!', 3000);}
    else{
        var almacen = $("#almacenes").val();
        if (item === "") {	swal('¡Alto!','Ingresa un artículo para ver su existencia!','info'); } 
        else{  getFraccionado(item,almacen,fila); }
    }	
}
$(document).on('click','#AceptarFraccionado',function(e){
    e.preventDefault();
    var fila = $('#modal2 .modal-footer #fila').val();
    $("#lote"+fila).val("FRACCIONADO");
    $('#modal2').closeModal();
    $('#modal1').openModal({dismissible : false,ready : function(){$('#lean-overlay').remove();	$('.lean-overlay').remove();}});
});

function DisponibleLote(item,sitio,myRow){
    $.ajax({url: "inicio",type: "get",dataType: "json",data: { "item": item, "sitio": sitio, "token": 'existenciaLote' },
    success: function (data){
        $("#DisponibleLote"+myRow+" > tbody").html("");
        if (data[0].Articulo !== ""){
            for (var i=0;i<data.length;i++){
                var existencia = parseFloat(data[i].Existencia);
                $("#DisponibleLote"+myRow+" > tbody").append('<tr><td><p>' + data[i].Almacen + '</p></td><td><p id="BatchAvail'+i+'">' + data[i].Lote +'<p></td><td><p>' + existencia.toFixed(2) +'</p></td><td><p><input type="radio" name="RadioExistLote'+myRow+'" Value="'+i+'" id="radioLote'+myRow +'-'+ i + '" /><label for="radioLote'+myRow +'-' + i +'"></label></p></td></tr>');			
            }
        }
    },
    error: function (data){
        Materialize.toast('WebService Error!.', 3000);
    }});
    $(document).on("click","#ElegirLote"+myRow,function(e){
        e.preventDefault();
        var lineasel = $('input[name="RadioExistLote' + myRow+'"]:checked').val();
        if (lineasel){ 
            var BatchAvail = $("#DisponibleLote"+myRow+" #BatchAvail"+lineasel).text();
            $("#lote"+myRow).val(BatchAvail);
            $("#dropdownLote"+myRow).removeClass("active");
            $("#dropdownLote"+myRow).css("display","none");
        } else{
            Materialize.toast('Seleccione una opción o de click fuera del recuadro!', 3000);
	}
    });
}
function errorMsg(msg){
    $("#errorLogModal").openModal();
    $('#errorContent').html(msg);
}
function havePermision(id){
    var flag=false;
    $.each(flagFactura,function (i,v){
        if(id==v){
            flag=true;
        }
    });
    return flag;
}
function ValidarLimiteCredito(ov,usuario,condi){
    
    if( !$( "#GenerarREM" ).hasClass( "disabled" )){        
    $.ajax({url: "inicio/ValidarLimiteCredito",type:"post",dataType: "json",data: { "ov": ov, "usuario" : usuario, "condiEntrega" : condi },
        beforeSend: function (xhr) {
            Materialize.toast("Validando Limite de credito",3000);
            $("#GenerarREM").addClass("disabled");
            $("#GenerarREM").removeClass("cyan");
            $("#GenerarREM").removeClass("darken-4");
        },
        success: function(data){
            switch (data.res){ 
                case 'OK':
                    generarRemision(ov);
                break;
                case 'error':
                    Materialize.toast(data.msj, 10000);
                    $('#preloaderRemision').hide();
                    $('#GenerarREM').removeClass('disable_a_href');
                break;
                case 'FAIL_BLOCK':
                    $('#alertaCredito').removeClass('teal lighten-1');
                    $('#alertaCredito').addClass('orange darken-3');
                    $('#alertMsj').removeClass('off');
                    $('#alertMsjDet').removeClass('off');
                    $('#alertMsjSuccess').addClass('off');
                    $('#iconWait').show();
                    $('#iconSuccess').hide();
                    $('#alertaCredito').show();
                    checarStatusAlerta(ov);
                    $('#preloaderRemision').hide();
                    $('#GenerarREM').removeClass('disable_a_href');
                    $('#GenerarREM').addClass('disabled');
                break;
                case 'bloqueado':
                    Materialize.toast(data.msj, 10000);
                    $('#alertaCredito').removeClass('teal lighten-1');
                    $('#alertaCredito').addClass('orange darken-3');
                    $('#alertMsj').removeClass('off');
                    $('#alertMsjDet').removeClass('off');
                    $('#alertMsjSuccess').addClass('off');
                    $('#iconWait').show();
                    $('#iconSuccess').hide();
                    $('#alertaCredito').show();
                    checarStatusAlerta(ov);
                    $('#preloaderRemision').hide();
                break;
                default :
                    Materialize.toast('Cliente bloqueado para Factura.!',5000);
                    $('#alertaBloqueo').removeClass('teal lighten-1');
                    $('#alertaBloqueo').addClass('orange darken-3');
                    $('#alertMsjBloq').removeClass('off');
                    $('#alertMsjDetBloq').removeClass('off');
                    $('#alertMsjSuccessBloq').addClass('off');
                    $('#iconWait').show();
                    $('#iconSuccess').hide();
                    $('#alertaBloqueo').show();
                    checarStatusBloqueo(ov);
                    $('#preloaderRemision').hide();
                    $('#GenerarREM').removeClass('disable_a_href');
                    $('#GenerarREM').addClass('disabled');
                break;
            }            
        },
        error: function (jqXHR,exep){
            Materialize.toast('WebService Error!'+catchError(jqXHR,exep), 3000);
            $("#GenerarREM").removeClass("disabled");
        }
    });
    
    }
}
function checarStatusAlerta(ov){
    $.ajax({ url: "inicio/checarAlerta",type: "POST",dataType: 'json',
        data: { "ov": ov },
        success: function (data){
            var creditStatus = data.resultado[0].CREDITSTATUS;
            if (creditStatus == 0){
                setTimeout(function(){checarStatusAlerta(ov);}, 2000);
            }
            else{
                $('#alertaCredito').removeClass('orange darken-3');
                $('#alertaCredito').addClass('teal lighten-1');
                $('#alertMsj').addClass('off');
                $('#alertMsjDet').addClass('off');
                $('#alertMsjSuccess').removeClass('off');
                $('#iconWait').hide();
                $('#iconSuccess').show();
                $('#GenerarREM').removeClass('disabled');
                generarRemision(ov); 
            }
        }
    });
}
function catchError(jqXHR,exception){
   var msg ='';
    if (jqXHR.status === 0) {
        msg = 'Network Error.';
    } else if (jqXHR.status == 404) {
        msg = 'Pagina no existe. [404]';
    } else if (jqXHR == 1) {
        msg = 'Precios en cero';
    } else if (jqXHR.status == 500) {
        msg = 'Internal Server Error [500] verificar log.';        
    } else if (exception === 'parsererror') {
        msg = 'Requested JSON parse failed.';
    } else if (exception === 'timeout') {
        msg = 'Tiempo de espera excedido.';
    } else if (exception === 'abort') {
        msg = 'Peticion cancelada.';
    } else {
        msg = 'Uncaught Error: ' + jqXHR.responseText+"<br><b>"+exception+"</b>";
    }
    // $.ajax({url:'inicio/email',type:"POST",async:true,data:"&titulo="+msg+" - Alerta de error inax&mensaje="+msg+"<br><br><b>Excepción: </b>"+exception+"<br>Usuario:"+usuario+"<br><b>Response text:</b>"+jqXHR.responseText});
    return msg;
}

function ValidarLimiteCreditoRemision(ov,usuario,idbtn){
	$.ajax({url: "inicio/ValidarLimiteCredito",type: "post",dataType: "json",data: { "ov": ov, "usuario" : usuario },
            beforeSend: function (xhr) {
               Materialize.toast("Validando Limite de crédito",3000);
            },
            success: function(data){
                if (data.res==="OK") {
                    $.ajax({url: "inicio/GenerarRemision",type: "post",dataType: "json",data: { "ov": ov},
                        beforeSend: function (xhr) {
                            Materialize.toast("Procesando Remisión",3000);
                        },
                        success: function (data){
                            if(data.status == 'Exito'){
                                $.ajax({
                                    url:"inicio/facturar",type:"post", data:{
                                        ov:$('#ovLbl').text(),
                                        remision:data.msg,
                                        ordenCliente:$('#OrdenCliente').val(),
                                        refCliente:$('#ReferenciaCliente').val(),
                                        comentariosCabecera:$('#comentariosCabecera').val(),
                                        direccion:$('#direccionF').val(),
                                        usoCFDi:$('#usoCFDI option:selected').val(),
                                        pagoModo:$('#tipoCobro option:selected').attr('data-paymmode'),
                                        pago:$('#entregaF').val()
                                    },dataType: 'json',
                                    beforeSend: function (xhr) {
                                        $('#loadFacturaSt').html('<img src="../application/assets/img/cargando.gif" style="width: 1em;">');
                                    },
                                    success: function (data, textStatus, jqXHR) {
                                        if(data.resultado=="ok"){
                                            Materialize.toast("Factura "+data.respuesta,10000);
                                            $('#loadFacturaSt').html('<a href="http://svr02:8989/FacturacionCajas/PDFFactura.php?ov='+$('#ovLbl').text()+'&amp;tipo=CLIENTE" target="_blank">'+data.respuesta+'</a>');
                                            $("#btnFacturar").hide();
                                            if(havePermision(12)){
                                                    $('#footerFactura').append('<a onclick="mostrarModalPago()" id="btnDiarioIndex" class="waves-effect light-blue darken-4 white-text btn-flat" style="margin-right: 13px;">Asociar Factura a Diario de Pago</a>');
                                                }
                                        }
                                        else if(data.resultado=="bad"){
                                            $('#loadFacturaSt').html('<label style="color:red;">'+data.respuesta+'</label>');
                                        }
                                        else{
                                            $('#loadFacturaSt').html('<a>'+data.respuesta+'</a>');
                                        }                
                                    }            
                                });
                            }
                            else{
                                facturarClose();
                            }
                        },
                        error: function (jqXHR,exep){
                          Materialize.toast('WebService Error!'+catchError(jqXHR,exep), 3000);
                        }
                    });			
                } else{
                    Materialize.toast(data, 3000);				
                }
            },
            error: function (jqXHR,exep){
                Materialize.toast('WebService Error!'+catchError(jqXHR,exep), 3000);
	    }   
        });
}
		
function initArticulosDiv(lineasArr){
	var clvclte = $("#claveclte").val();
	// if (cabeceraOn == '1'){
 //            $.ajax({url: "inicio/clavecliente",
 //                    type: "post", 
 //                    dataType: "json", 
 //                    data: { "clveclte":  clvclte},

 //                success: function (data){
 //                    var info = "RFC: " + data[0].RFC_MX + "\nTELEFONO: " + data[0].Telefono + "\nCORREO: " + data[0].Correo + "\nDIRECCION:" + $("#direccion").val() ;
 //                    var tmplInfoClient = '<ul id="listClient" class="list-client"><li>CLIENTE: <span data-copy-info>' + OVCliente + '</span><button class="btn-copiar btn-copiar-list" type="button" data-copy>Copiar</button></li>'+
 //    	        		'<li>RFC: <span data-copy-info>' + data[0].RFC_MX + '</span><button class="btn-copiar btn-copiar-list" type="button" data-copy>Copiar</button></li>'+
 //    	        		'<li>TELEFONO: <span data-copy-info>' + data[0].Telefono + '</span><button class="btn-copiar btn-copiar-list" type="button" data-copy>Copiar</button></li>'+
 //    	        		'<li class="children">CORREO: <ul id="correosList"></ul></li><li>DIRECCION: <span data-copy-info>' + $("#direccion").val() + '</span><button class="btn-copiar btn-copiar-list" type="button" data-copy>Copiar</button></li>'+
 //    	        	'</ul><button class="btn view" id="baina1" type="button" onclick="ultimasVentas()">Ver ultimas ventas</button>';
 //                x = data[0].Correo;x.split(';');xx = x.split(';');
 //    	        //Imprime la informaciÃ³n en el tooltip
 //    	        $("#infoCliente").attr('data-tooltip',info);
 //    	        $("body.theme form#FormArticulos > .row + .row").append(tmplInfoClient);
 //    	        $.each(xx, function(index, val){
 //                    if(val !== '') {
 //                        $('#correosList').append('<li><a href="mailto:' + val + '" title="' + val + '" data-copy-info>' + val + '</a><button class="btn-copiar btn-copiar-list" type="button" data-copy>Copiar</button></li>')
 //                    }
 //    	        });
 //    	    },
 //            error: function (jqXHR,exep){
 //                Materialize.toast('Error!'+catchError(jqXHR,exep), 3000);
 //            }
 //    	});
	// }
	//Se obtienen las variables de la cabecera de la orden de venta
	var OVCliente = $("#claveclte").val() + " - " + $("#cliente").val();
	$("#OV-Cliente").html(OVCliente);
    docu = 'Cotizacion: ';
    if(docType != 'CTZN'){
        docu = 'Orden de Venta: ';
    }
    $('#OV-Cliente').parent().prepend('<div class="col l6 m6 s6"><span>'+docu+$('#DocumentId2').val()+'</div>')
	$("#DocumentId").val($('#DocumentId2').val());
	$("#clte").val($("#claveclte").val());
	$("#sitioclte").val($("#sitioLineas").val());
	var porcCargo = $("#pagolineas").val();
	$("#PorcentCargo").val(porcCargo.substring(0,6));
        /*
	$("#LineasEntrega").val($("#modoentrega").val());
	$('#entregalineas').val($("#modoentrega").val());
        */       
	if (lineasArr !== 'NoResults'){
		lineasTotArr = [];
		lineas = lineasArr;
		$(lineas).each(function(){
                    var loc='GRAL';
                     if(this.INVENTLOCATIONID.indexOf('MRMA')!=-1 ){
                         loc='MRMA';
                     }
			var qty = (Math.floor(Number(this.SALESQTY)*1000)/1000);
			itemFam = {					
					item            : this.ITEMID,
					nombre          : this.NAME,
					sitio           : this.INVENTSITEID,
					almacen         : this.INVENTLOCATIONID,
					cliente         : $("#claveclte").val(),
					qty             : qty,
                    localidad       : loc,
					unidad          : this.SALESUNIT,
					punitario       : this.STF_SALESPRICE / this.SALESQTY,
					cambioWS        : this.CAMBIOWS,
					comentarioLinea : this.STF_OBSERVATIONS,
                    dataareaid      : this.DATAAREAID,
                    inventorylotid  : this.INVENTORYLOTID
				};
			lineasTotArr.push(itemFam);
		});
		//$(document).ajaxStart( $('#modalLoading').openModal({dismissible: false}) ).ajaxStop(function(){ $('#modalLoading').closeModal(); });
        $(lineasTotArr).each(function(){
            var lineaActual = $('#NumFilas').val();
            if ( $('#item'+lineaActual).val() != '' ){
                bandAgregarFam = true;
                $('#AgregarLinea').trigger('click');
                lineaActual = $('#NumFilas').val();
            }
            var row = $('#item'+lineaActual).closest('tr');
            $(row).attr('data-dataareaid',this.dataareaid);
            $(row).attr('data-inventorylotid',this.inventorylotid);
			$('#item'+lineaActual).val(this.item);
            $('#item'+lineaActual).prop('readonly','readonly');
            $('#item'+lineaActual).css('cursor','not-allowed');
			$('#descripcion'+lineaActual).val(this.nombre);
			$('#cantidad'+lineaActual).val(this.qty);
            $('#cantidad'+lineaActual).attr("data-lastvalue",this.qty);
			$('#unidad'+lineaActual).val(this.unidad);
            if (this.almacen.includes('EXHB')){
                dimensiones = atpDimensions;
                if (comp == 'LIN'){
                    dimensiones = lideartDimensions;
                }
                dimensiones.lote = this.almacen + 'LOTE';
                dimensiones.licensePlate = this.almacen;
			    $('#localidad'+lineaActual).val(dimensiones.localidad);
                $('#lote'+lineaActual).val(dimensiones.lote);
                $('#matricula'+lineaActual).val(dimensiones.licensePlate);
            }else{
                $('#localidad'+lineaActual).val(this.localidad);
            }
            $('#sitio'+lineaActual).val(this.sitio);
            $('#almacen'+lineaActual).val(this.almacen);
            $('#comentariolinea'+lineaActual).html(this.comentarioLinea);
            $('#dataAreaId'+lineaActual).val(this.dataareaid);
            $('#InventoryLotId'+lineaActual).val(this.inventorylotid);
			if ( eval(this.cambioWS) > 0 ){
				$('#punitariolinea'+lineaActual).val(this.punitario);
            }else{
                if (eval(this.punitario)>0){
                    $('#punitariolinea'+lineaActual).val(this.punitario);
                }else{
				    $('#punitariolinea'+lineaActual).val(0);
                }
			}           
			tmpPunitario = this.punitario;
			$('#cantidad'+lineaActual).trigger('change');
		});
		lineasTotArr = [];
	}
}


function negarRow(obj){
	row             = $(obj).closest('tr');
	var articulo    = $(obj).data('itemneg');
	var cantnegada  = '1';
	var sitio       = $(obj).data('sitioneg');
	var almacen     = $(obj).data('almacenneg');
	var disponible  = $(row).find('td').eq(2).find('p').html();
	var unidad      = $(obj).data('unidadneg');
	var descripcion = $(obj).data('descripcion');
	var cliente     = $(obj).data('cliente');
	var excedido    = $(obj).data('excedido');
	$(row).siblings().removeClass('selected');
	$(row).siblings().css('background-color','#FFFFFF');
	$(row).toggleClass('selected');
	if ( $(row).hasClass('selected') ){
		$(row).css('background-color','#A4A4A4');
		var itemNegado  = {
			'articulo'    : articulo,
			'cantidad'    : '1',
			'cantnegada'  : cantnegada,
			'sitio'       : sitio,
			'almacen'     : almacen,
			'disponible'  : disponible,
			'unidad'      : unidad,
			'descripcion' : descripcion,
			'cliente'     : cliente
		};
		negados.addArticulo(itemNegado);
	}else{
		negados.removeArticulo(articulo);
		$(row).css('background-color','#FFFFFF');
	}
}

function checarPrecios(){
    var bandCeros = false;
    var rows = $('#articulos tbody tr').filter(':visible');
    $(rows).each(function(){
        var price = $(this).find('td').find('.preciovta').val();
        var inputPrice = $(this).find('td').find('.preciovta');
        price = price.replace('$','');
        price = price.replace(',','');
        if (Number(price) === 0){
            bandCeros = true;
            $(inputPrice).addClass('invalid');
        }
        if ($(this).hasClass('load')){
            swal('¡Alto!','se estan calculando precios por favor espere...','info');
            bandCeros=true;
        }
    });
    return bandCeros;
};

////////////////////////edgar changes to new visual appeareance///////////////////////////
function rowSelected() {
	$(this).parent().parent().parent().toggleClass('select');
	rowSelect = $('#articulos tbody tr.select').size();
	if(rowSelect > 0) {
		$('#QuitarLinea').show();
	} else {
		$('#QuitarLinea').hide();
	}
}

$(document).on('click','.btn-copiar-no-list',function(event) {
	var temp = $('<input>');
	$('body').append(temp);
	infoCopy = $(this).parent().children('textarea').val();
	temp.val(infoCopy).select();
	document.execCommand('copy');
	temp.remove();
	Materialize.toast('Copiado', 3000);
});

$(document).on('click','.btn-copiar-list',function(event) {
	var temp = $('<input>');
	$('body').append(temp);
	infoCopy = $(this).parents('li').children('[data-copy-info]').text();
	temp.val(infoCopy).select();
	document.execCommand('copy');
	temp.remove();
	Materialize.toast('Copiado', 3000);
});
/////////////////////// para dibujar graficas con barras//////////////////////////////////////////////////////////////////////
function paintGraficaBarras(id,label,data,colors,nombre){ 
    
    var ctx = document.getElementById(id).getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'horizontalBar',
        data: {
            labels: label,
            datasets: [{
                label: nombre,
                data: data,
                backgroundColor: colors,
                borderColor: colors,
                borderWidth: 1
            }]
        },
        options: {
        scales: {
            xAxes: [{
                ticks: {
                    autoSkip: false
                }
            }]
        }
    }
    });
}

function paintGraficaBarrasMix(id,labels,data,data2,colors,nombre,nombre2){ 
    var ctx = document.getElementById(id).getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'horizontalBar',
        data: {
            labels: labels,
            datasets: [{
                label: nombre,
                data: data,
                backgroundColor: '#01579b',
                borderColor: '#01579c',
                borderWidth: 1
            },{
                label: nombre2,
                data: data2,
                backgroundColor: '#4ed8cf',
                borderColor: '#35918b',
                borderWidth: 1
            }]
        },
        options: {
        scales: {
            xAxes: [{
                ticks: {
                    autoSkip: false
                }
            }]
        }
    }
    });
}

function paintGraficaBarrasMix2(id,labels,data,data2,data3,data4,nombre,nombre2,nombre3,nombre4){ 
    var ctx = document.getElementById(id).getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'horizontalBar',
        data: {
            labels: labels,
            datasets: [{
                label: nombre,
                data: data,
                backgroundColor: '#01579b',
                borderColor: '#01579c',
                borderWidth: 1
            },{
                label: nombre2,
                data: data2,
                backgroundColor: '#ffb226',
                borderColor: '#ffb226',
                borderWidth: 1
            },{
                label: nombre3,
                data: data3,
                backgroundColor: '#003319',
                borderColor: '#003319',
                borderWidth: 1
            },{
                label: nombre4,
                data: data4,
                backgroundColor: '#4ed8cf',
                borderColor: '#35918b',
                borderWidth: 1
            }]
        },
        options: {
        scales: {
            xAxes: [{
                ticks: {
                    autoSkip: false
                }
            }]
        }
    }
    });
}
function llenarTablaNegados(user){
        $('#modalLoading').openModal();
        var date = new Date();
        var year = date.getFullYear();
        var day  = date.getDate();
        var month= date.getMonth() < 10 ? '0'+date.getMonth().toString() : date.getMonth();
        var fecha= year.toString()+month.toString()+day.toString();
        $('#tablaNegadosExcel').dataTable().fnClearTable();
        $('#tablaNegadosExcel').dataTable().fnDestroy();
        $.ajax({url:"inicio",type: "GET",dataType: "JSON",
            data: {"token":"detDataNegados","user":user},
            beforeSend: function (xhr) {
                Materialize.toast("Procesando Negados...!");
            },
            success: function (res){
                var t = $('#tablaNegadosExcel').DataTable( {
                    dom: 'Bfrtip',
                    buttons: [ {
                        extend: 'excelHtml5',
                        sheetName: "REGISTRO DE PRODUCTOS NEGADOS",
                        filename: "RegNegados_"+user+"_"+fecha,
                        mergeCells: [['A1','I1']],
                        customize: function ( xlsx ){
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];
                            var colWidths = [16,20,53,40,15,16,12,28,12];
                            var row = '';                            
                            $('row', sheet).each(function(){                                
                                var renglon = $(this).attr('r');
                                if (renglon == '2'){
                                    $(this).attr('customFormat','1');
                                    $(this).attr('spans','1:11');
                                    $(this).attr('customHeight','1');
                                    $(this).attr('ht','46.5');
                                    $('row c',this).each(function(){
                                        $(this).attr('s','56');
                                    });
                                }
                                else if (renglon == '1'){
                                    $(this).attr('customFormat','1');
                                    $(this).attr('spans','1:11');
                                    $(this).attr('customHeight','1');
                                    $(this).attr('ht','70');
                                    $('row c',this).each(function(){
                                        $(this).attr('s','57');
                                    });
                                }
                                else{
                                    $('row c',this).each(function(){
                                        $(this).attr('s','25');
                                    });
                                }
                            });
                            $('col',sheet).each(function(index){
                                $(this).attr('width',colWidths[index]);
                            });
                        }
                    } ]
                } );
                $('#tablaNegadosExcel').dataTable().fnAddData(JSON.parse(res.data));
                $('#modalLoading').closeModal();
                $('.buttons-excel').trigger('click');
            },
            error: function (jqXHR,exep){
                Materialize.toast('Error! '+catchError(jqXHR,exep), 3000);
            }
        });
    }

function abrirEnPestana(url) {
        var a = document.createElement("a");
        a.target = "_blank";
        a.href = url;
        a.click();
}
function notificacionDeCedis(text){
    if (Notification) {
        if (Notification.permission !== "granted") {
            Notification.requestPermission();
        }        
    }
    var options = {
        icon: "http://intra/inax/application/assets/img/vlogo-66x66.jpg",
        body: text
    }; 
    var noti = new Notification("InAX", options);
    noti.onclick = {
       
    };
        noti.onclose = {
        // Al cerrar
        };
        var title = 'Icon Notification';
        var options = {
            icon: '/images/demos/icon-512x512.png'
        };
}
function liberar_a_Almacen(ov){
    datosLib = {
        'ov':ov,
        'sitio' : $('#sitioLineas option:selected').val(),
        'dlvMode':$('#entregalineas').val(),
        'condiEntrega' : $('#condiEntrega').val(),
        'moneda' : $('#monedalineas').val(),
        'metodoPagoCode':$('#pagolineas option:selected').attr('data-paymmode'),
        'metodoPago' : $("#pagolineas option:selected").text(),
        'proposito':$("#proposito").val(),
        'paymentTermName' : $('#paytermArt option:selected').val()
    };
    $.ajax({
            url: "inicio/liberar-Almacen",
            type: "post", 
            data: datosLib,
        beforeSend: function (xhr) {
            Materialize.toast("Procesando Remisión",3000);
            $("#GenerarREM").addClass("disabled");
            $("#GenerarREM").removeClass("cyan");
            $("#GenerarREM").removeClass("darken-4");
            $("#Facturar").addClass("disabled");
            $("#Facturar").removeClass("green");
            $("#Facturar").removeClass("darken-1");
        },
        success: function (data){
            data = JSON.parse(data);
            if (data.status == 'exito'){
                $('#preloaderRemision').hide();
                
                var reciboOrden = $('#DocumentId2').val();
                var reciboDiario = $('#cuentaCompleto').html();
                var x = reciboDiario.split(':');
                reciboDiario = x[0];

                var btnRecibo = '<a href="impresion-recibo?id=' + reciboOrden + '&diario=' + reciboDiario + '" target="_blank" class="deep-purple darken-3 waves-effect waves-light btn" style="width: 100%;text-align: left;"><i class="material-icons left">receipt</i> Impresión Recibo</a>';

                $('#Remision').html('<div class="green darken-1" style="color: White; padding: 10px;"><i class="material-icons left">done</i> ORDEN DE VENTA LIBERADA A ALMACEN </div>' + btnRecibo);
                Materialize.toast('Orden de Venta Liberada a Almacen', 3000);
                $('#editar2 a').removeClass('link');
                $('#editar2').unbind('click');
            }else{
                $('#Remision').html('<div class="red darken-1" style="color: White; padding: 10px;text-align:center"> ORDEN DE VENTA NO LIBERADA A ALMACEN <br>(O NO SE GENERARON LOS TRABAJOS)<br> FAVOR DE VERIFICAR LA ORDEN DE VENTA. </div>');
                $('#preloaderRemision').hide();
                $('#GenerarREM').removeClass('disabled');
                $("#Facturar").removeClass("disabled");
            }
            $('#ImprimirCotizacion').html('<i class="material-icons left">picture_as_pdf</i> IMPRIMIR OV');
            $('#QuotationId').val($('#DocumentId').val());
            $('#ImprimirCotizacion').parent().attr('action','impresion-orden');
            $('#ImprimirCotizacion').show();
        }
    });
}

function generarRemision(ov){
    dimensiones = atpDimensions;
    if (comp == 'LIN'){
        dimensiones = lideartDimensions;
    }
    if(havePermision(14)){
        $.ajax({
            url: 'inicio/GenerarRemision',
            type: 'post', 
            data: { 'ov': ov,'dimensiones':dimensiones},
        beforeSend: function (xhr) {
            Materialize.toast("Procesando Remisión",3000);
        },
        success: function (data){
            data = JSON.parse(data);
            if(data.status == 'Exito'){
                var modoEntrega = $("#entregalineas").val();
                $("#DescRemision").show();
                $("#DescFactura").show();
                var dataRemFact = data.msg.split(',');
                var rem = dataRemFact[0].split(':');
                var fact = dataRemFact[1].split(':');
                $("#Remision").html(rem[1]);
                $("#FacturaDoc").html(fact[1].substring(0,fact[1].length-1));
                $("#PackingSlipId2PDF").val(rem[1]);
                //$("#ImprimirREM").show();
                $('#GenerarREM').hide();
                $('#GenerarWPAY').hide();
                if (modoEntrega == 'PAQUETERIA'){ $('#generaEti').show();}
                else{ $('#generaEti').hide(); }
                $('#alertaCredito').hide();
                $('#alertaBloqueo').hide();
                $("#usuarioov").click(); 
                $('#ImprimirCotizacion').hide();
                $('#preloaderRemision').hide();
                $("#Remisionar_Facturar").hide();
                // $.ajax({url:"inicio/facturar",type:"post", data:{
                //         ov:$("#DocumentId2").val(),
                //         remision:data.msg,
                //         ordenCliente:$('#OrdenCliente').val(),
                //         refCliente:$('#ReferenciaCliente').val(),
                //         comentariosCabecera:$('#comentariosCabecera').val(),
                //         direccion:$('#direccionF').val(),
                //         usoCFDi:$('#usoCFDI option:selected').val(),
                //         pagoModo:$('#pagolineas option:selected').attr('data-paymmode'),
                //         pago:$('#paytermArt').val()
                //     },dataType: 'json',
                //     beforeSend: function (xhr) {
                //         $('#loadFacturaSt').html('<img src="../application/assets/img/cargando.gif" style="width: 1em;">');
                //     },
                //     success: function (data, textStatus, jqXHR) {
                //         if(data.resultado==="ok"){
                //             var pago=$('#entregaF').val();
                //             $('#loadFacturaSt').html('<a id="folioFactura" href="http://svr02:8989/FacturacionCajas/PDFFactura.php?ov='+$("#DocumentId2").val()+'&amp;tipo=CLIENTE" target="_blank">'+data.respuesta+'</a>');
                //             $("#btnFacturar").hide();
                //             refreshLines();
                //             if(havePermision(12)){
                //                 $('#footerFactura').append('<a onclick="mostrarModalPago()" class="waves-effect light-blue darken-4 white-text btn-flat" style="margin-right: 13px;">Asociar Factura a Diario de Pago</a>');
                //         }
                //         }
                //         else if(data.resultado=="bad"){
                //             $('#loadFacturaSt').html('<label style="color:red;">'+data.respuesta+'</label>');
                //         }
                //         else{
                //             $('#loadFacturaSt').html('<a>'+data.respuesta+'</a>');
                //         }                
                //     }            
                // });
            }else{
                errorMsg('<h5>Ocurrio un problema al remisionar o facturar, intente de nuevo por favor. '+data.msg+'</h5>');
                $('#preloaderRemision').hide();
                $('#GenerarREM').removeClass('disable_a_href');
                $('#Facturar').hide();
                $("#Remisionar_Facturar").show();
            }  
        },
        error: function (jqXHR,exep){
            $("#GenerarREM").removeClass("disabled");
            Materialize.toast('WebService Error!'+catchError(jqXHR,exep), 3000);
        }
    });
    }
    else{
        $.ajax({url: "inicio/GenerarRemision",type: "post", data: { "ov": ov,'dimensiones':dimensiones},
        beforeSend: function (xhr) {
            Materialize.toast("Procesando Remisión",3000);
        },
        success: function (data){
            $("#GenerarREM").removeClass("disabled");
            data = JSON.parse(data);
            if(data.status == 'Exito'){
                var modoEntrega = $("#entregalineas").val();
                $("#confirmarDocumento").hide();
                $("#GenerarREM").hide();
                $("#DescRemision").show();
                $("#Remision").html(data.msg);		        		
                $("#PackingSlipId2PDF").val(data.msg);
                $("#ImprimirREM").show();
                $('#ImprimirCotizacion').hide();
                $('#preloaderRemision').hide();
                if (modoEntrega == 'PAQUETERIA'){ $('#generaEti').show();}
                else{ $('#generaEti').hide(); }
                if(!havePermision(1)){Materialize.toast('Orden de Venta Remisionada!', 3000);}
                $('#alertaCredito').hide();
                $('#alertaBloqueo').hide();
                $("#usuarioov").click();
                refreshLines();
                if(havePermision(1)){
                    $("#Facturar").show();                                      
                }                                
            }else{
                errorMsg('<h5>Ocurrio un problema al remisionar o facturar, intente de nuevo por favor. '+data.msg+'</h5>');
                $('#preloaderRemision').hide();
                $('#GenerarREM').removeClass('disable_a_href');
                $('#Facturar').hide();
                $("#Remisionar_Facturar").show();
            }  
        },
        error: function (jqXHR,exep){
            $("#GenerarREM").removeClass("disabled");
            Materialize.toast('WebService Error!'+catchError(jqXHR,exep), 3000);
        }
    });
    }    
}

function formatNumber(num) {
    return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
}

$(document).on('click', '.getLpInfo', function (e) {
    //console.log('Le dieron a get lpssss perro');
    e.preventDefault();
    sitio = $(this).data('localidad');
    almacen = $(this).data('almacen');
    item = $(this).data('item');
    unidad = $(this).data('unidad');
    documentType = $(this).data('documenttype');
    totalFreePlates = 0;
    totalReservedPlates = 0;
    $.ajax({
        url: "inicio/existencias",
        type: "post",
        dataType: "json",
        data: { "item": item, "documenType": documentType, 'sitio': sitio, 'almacen': almacen, 'onlylp': 'true' },
        beforeSend: function (xhr) {
            Materialize.toast("Obteniendo existencias", 3000);
        },
        success: function (data) {
            htmlPlatesBody = ' <table id="platesTable"><thead><tr><th width="65%">LP</th><th>Cantidad</th></tr></thead><tbody>';
            data.freePlates.forEach(element => {
                totalFreePlates += element.AvailPhysical;
                htmlPlatesBody += '<tr>';
                htmlPlatesBody += '<td>' + element.LicensePlateId + '</td>';
                htmlPlatesBody += '<td data-order="' + element.AvailPhysical + '">' + formatNumber(element.AvailPhysical) + ' ' + unidad + '</td>';
                htmlPlatesBody += '</tr>';
            });
            htmlPlatesBody += '</tbody></table >';
            htmlPlatesBody += ' <table><thead><tr><th width="65%"></th><th></th></tr></thead><tbody>';
            htmlPlatesBody += '<tr style="border-top: thick black;" bgcolor="#FFFFFF">';
            htmlPlatesBody += '<td style="font-size: small;font-weight: bold;">LP\'s Registradas</td>';
            htmlPlatesBody += '<td align="right" style="font-size: small;font-weight: bold;">' + formatNumber(totalFreePlates) + ' ' + unidad + '</td>';
            htmlPlatesBody += '</tr>';
            data.reservePlates.forEach(element => {
                totalReservedPlates += element.AvailPhysical;
                htmlPlatesBody += '<tr bgcolor="#FFFFFF">';
                htmlPlatesBody += '<td style="font-size: small;font-weight: bold;"> Reservada / En Proceso </td>';
                htmlPlatesBody += '<td align="right" style="font-size: small;font-weight: bold;" >(' + Math.abs(element.AvailPhysical) + ') ' + unidad + '</td>';
                htmlPlatesBody += '</tr>';
            });

            htmlPlatesBody += '<tr style="border-top: thick black;" bgcolor="#FFFFFF">';
            htmlPlatesBody += '<td></td>';
            htmlPlatesBody += '<td align="right" style="font-size: small;font-weight: bold;">Total: ' + formatNumber(totalFreePlates + totalReservedPlates) + ' ' + unidad + '</td>';
            htmlPlatesBody += '</tr>';
            htmlPlatesBody += '</tbody></table >';
            $('#plates-content').html(htmlPlatesBody);
            $("#modal-plates").openModal();
            $('#platesTable').DataTable({
                "language": {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "Mostrar _MENU_ registros",
                    "sZeroRecords": "No se encontraron resultados",
                    "sEmptyTable": "Ningún dato disponible en esta tabla =(",
                    "sInfo": "Mostrando registros del _START_ al _END_ de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando registros del 0 al 0 de 0 registros",
                    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix": "",
                    "sSearch": "Buscar:",
                    "sUrl": "",
                    "sInfoThousands": ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst": "Primero",
                        "sLast": "Último",
                        "sNext": "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    },
                    "buttons": {
                        "copy": "Copiar",
                        "colvis": "Visibilidad"
                    }
                },
                "searching": false,
                "paging": true,
                "pageLength": 9,
                "bLengthChange": false,
                "order": [[1, "desc"]]
            });
        },
        error: function (jqXHR, exep) {
            Materialize.toast('Error!' + catchError(jqXHR, exep), 3000);
        }
    });
    //$("#modal-plates").openModal();
});
