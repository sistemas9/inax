/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ajaxStart(function () {
    $("#loaderBootStrap").modal({keyboard: false,backdrop: 'static'});
}).ajaxStop(function () {
    $("#loaderBootStrap").modal('hide');
});

function getJsonFromUrl(url, dataSet) {
    return $.ajax({
        type: "POST", url: url, data: dataSet,
        dataType: 'JSON',
        async: false,
        success: function (data) {
            return data;
        },
        error: function (jqXHR, textStatus, errorThrown) {
            swal("Error!", jqXHR.responseText, "error");
        }
    });
}

function getDetalle(ov){
    try{
        var detalle=getJsonFromUrl('consultaguia/get-Detail-From-Ov',{ov:ov}).responseJSON;
        var tableCont='';
        $('#folioOv').html(ov);
        $('#detalleOV > tbody').html(tableCont);
        $.each(detalle.value,function (i,v){
            tableCont+='<tr><td>'+Number(v.LineCreationSequenceNumber)+'</td><td>'+v.ItemNumber+'</td><td>'+v.LineDescription+'</td><td>'+Number(v.OrderedSalesQuantity)+'</td><td>'+v.SalesUnitSymbol+'</td></tr>';
        });
        $('#detalleOV > tbody').html(tableCont);
        $("#detalleDoc").modal();
    }
    catch (e){
        $("#loaderBootStrap").modal('hide');
        swal("Error!", e, "error");
    }
}

function getList(){
    try{
        var ovEstatus={'Open order':'Orden Abierta','Delivered':'Entregado','Invoiced':'Facturado','Canceled':'Cancelado','Backorder':'Creada'};
        var serialize = $('#inputsFilterTbl').serialize();
        var columnasDef = [{title: "Orden de venta"}, {title: "Cliente"}, {title: "Modo de entrega"}, {title: "Secretario de ventas"}, {title: "Paqueteria"}, {title: "Fecha"}, {title: "Guía"}, {title: "Estatus"}];
        if(serialize!=='cliente='){
            $.ajax({
                type: "POST", url: 'consultaguia/get-list', data: serialize,
                dataType: 'JSON',
                success: function (data) {
                    var dataL=[];
                    $.each(data.value,function (i,v){
                        dataL.push(
                            [
                                '<a onclick="getDetalle(\''+v.SalesOrderNumber+'\')"><span class="fa fa-plus-circle sr-icons "></span> '+v.SalesOrderNumber+'</a>',
                                v.DeliveryAddressDescription,
                                v.DeliveryModeCode,
                                v.OrderTakerPersonnelNumber+'&nbsp;',
                                v.SalesOrderProcessingStatus,
                                v.RequestedReceiptDate,
                                'N/D',//v.TransportationDocumentLineId,
                                ovEstatus[v.SalesOrderStatus]+'&nbsp;'
                            ]
                        );
                    });
                    $("#reporte-tbl").DataTable().destroy();
                    $("#reporte-tbl>thead").remove();
                    $("#reporte-tbl").DataTable({
                        destroy: true,
                        order: [[3, "desc"]],
                        data: dataL,
                        columns: columnasDef,
                        columnDefs: [
                            {targets: [0], "width": "10%"},
                            {targets: '_all', "searchable": true}
                        ]
                    });
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $("#loaderBootStrap").modal('hide');
                    swal("Error!", jqXHR.responseText, "error");
                }
            });
        }
        else{
            swal("¡Alto!","Favor de seleccionar algun cliente", "info");
        }
    }
    catch(e){
        $("#loaderBootStrap").modal('hide');
        swal("Error!", e, "error");
    }    
}