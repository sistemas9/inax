var linedisc = '';
var conjuntoprecios = '';
var articulo = '';
function cargarClientes(data){
    clients = [];
    $.map(data, function (item){
        var info = {label: "No hay Resultados",value: " "};
        info['label'] = $.trim(item.ClaveCliente) + " - " + $.trim(item.Nombre);
        info['value'] = $.trim(item.ClaveCliente);
        info['nombre'] = $.trim(item.Nombre);
        info['decuentoLinea'] = $.trim(item.DescuentoLinea);
        info['conjuntoPrecios'] = $.trim(item.ConjuntoPrecios);
        clients.push(info);
    });
    seleccion = [{label: "Seleccione un cliente",value:""}];
    clienteTotal = seleccion.concat(clients);
    $('#cliente').selectize({
        persist     : false,
        options     : clients,
        labelField  : "label",
        valueField  : "value",
        searchField : ['label','value'],
        placeholder : "Selecione un cliente...",
        render      : {
            item    : function(item, escape) {
                    return '<div style="font-size:11px;" data-lineDisc="'+item.decuentoLinea+'" data-conjuntoPrecios="'+item.conjuntoPrecios+'">' +
                        (item.label ? '<span>' + escape(item.label) + '</span>' : '') +
                    '</div>';
                },
            option  : function(item, escape) {
                var label   = item.value || item.value;
                var caption = item.label ? item.label : null;
                return '<div>' +
                    '<i class="fa fa-address-book" aria-hidden="true" style="color:#337AB7;padding: 5px;"></i>' +
                    (caption ? '&nbsp<span class="caption" style="font-size:11px">' + escape(caption) + '</span>' : '') +
                '</div>';
            }
        },
        onChange    : function(item,escape){
            option          = $('#cliente').siblings('.selectize-control').children('.selectize-input').children('div');
            linedisc        = $(option).data('linedisc');
            conjuntoprecios = $(option).data('conjuntoprecios');
            $('#listaPrecios').val(conjuntoprecios);
            $('#descuentoLinea').val(linedisc);
        }
    });
}

/**
 * junta las 2 listas de articulos en una sola esto para detectar nombre comunes y los asocia al codigo del articulo,
 * se hace en 2 consultas para no dar tanta carga a la base de datos
 * */
function cargarItems(art,comunes){
    var itemsMap = [],
    itemsMap2 = [];
    items = art;
    $.each(items,function(i, val){
        itemsMap[items[i].value] = items[i].value+' - '+items[i].label;
    });
    artArray=itemsMap;
    var a=items.length;
    var items2=JSON.parse(comunes);//este es el arreglo para los articulos relacionados
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
    $('#codigoArticulo').selectize({
        persist     : false,
        options     : items,
        labelField  : "label",
        valueField  : "value",
        searchField : ['label','value'],
        placeholder : "Selecione un articulo...",
        render      : {
            item    : function(item, escape) {
                        return '<div style="font-size:11px;">' +
                            (item.label ? '<span>' + escape(item.label) + '</span>' : '') +
                        '</div>';
                },
            option  : function(item, escape) {
                        var label = item.value || item.value;
                        var caption = item.label ? item.label : null;
                        return '<div>' +
                            '<i class="fa fa-cubes" aria-hidden="true" style="color:#337AB7;padding: 5px;"></i>' +
                            (caption ? '&nbsp<span class="caption" style="font-size:11px">' + escape(caption) + '</span>' : '') +
                        '</div>';
            }
        },
        onChange    : function(item){
                        articulo = item;
                        $.ajax({
                            url         : 'BuscarPrecios/minimoVenta',
                            method      : "POST",
                            dataType    : "JSON",
                            data        : {item:item},
                            beforeSend  : function (xhr) {
                                //$('.toast').toast();
                            },
                            success     : function(res){
                                var minimoVenta = eval(res.MULTIPLEQTY);
                                $('#cantidad').val(minimoVenta.toFixed(2));
                            }
                        });
        }
    });
}
/** valida los campos de consulta 
*** que no esten vacios
**/
function validarCamposConsulta(){
    var valido = true;
    $('.required').each(function(){
        if ($(this).is('input')){
            if (this.value === ''){
                $(this).addClass('invalid');
                valido = false;
            }
        }
        if ($(this).is('select')){
            if (this.value === ''){
                if ($(this).hasClass('selectized')){
                    $(this).siblings('.selectize-control').children('.selectize-input').addClass('invalid');
                }
                valido = false;
            }
        }
    });
    return valido;
}

/** consulta los datos de 
*** precio del item
**/
function consultarDatosPrecio(){
    cantidad = $('#cantidad').val();
    data = {linedisc:linedisc,conjuntoprecios:conjuntoprecios,articulo:articulo,cantidad:cantidad};
    $.ajax({
        url         : 'BuscarPrecios/datosPrecio',
        method      : "POST",
        dataType    : "JSON",
        data        : data,
        beforeSend  : function (xhr) {
        },
        success     : function(res){
            DescuentoAcuerdo     = eval(res.DescuentoAcuerdo);
            LineAmount           = eval(res.LineAmount);
            Moneda               = res.Moneda;
            MontoNeto            = eval(res.MontoNeto);
            MontoNetoIVA         = eval( (res.MontoNeto)*1.16 );
            MontoNetoIVAFrontera = eval( (res.MontoNeto)*1.08 );
            PorcentajeDescuento1 = eval(res.PorcentajeDescuento1);
            PrecioAcuerdo        = eval(res.PrecioAcuerdo);
            PrecioUnitario       = eval(res.PrecioUnitario);
            TipoCambio           = eval(res.TipoCambio);
            UnidadAcuerdo        = res.UnidadAcuerdo;
            $('#montoneto').val( numberWithCommas(MontoNeto.toFixed(2)) );
            $('#montonetoIVA').val( numberWithCommas(MontoNetoIVA.toFixed(2)) );
            $('#montonetoIVAFrontera').val( numberWithCommas(MontoNetoIVAFrontera.toFixed(2)) );
            $('#moneda').val(Moneda);
            $('#precioAcuerdo').val( numberWithCommas(PrecioAcuerdo.toFixed(2)) );
            $('#descuentoAcuerdo').val(DescuentoAcuerdo.toFixed(2));
            $('#punitarioAcuerdo').val(PrecioUnitario);
            $('#porcDesc1Acuerdo').val(PorcentajeDescuento1);
            $('#unidadAcuerdo').val(UnidadAcuerdo);
            $('#unidadPrecio').val(UnidadAcuerdo);
            $('#porcDesc2Acuerdo').val('0.00');
            $('#preciovtaPrecio').val('0.00');
            $('#preciocompraCoste').val('0.00');
        }
    });
}

/** devuelve el formato de numero con comas
*** del precio de un articulo
**/
function numberWithCommas(x) {
    var parts = x.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return '$'+parts.join(".");
}