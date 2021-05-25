/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$('#tabla').DataTable();
$(document).ajaxStart(function () {
    $("#loaderBootStrap").modal({ keyboard: false, backdrop: 'static' });
}).ajaxStop(function () {
    $("#loaderBootStrap").modal('hide');
}).ajaxError(function () {
    $("#loaderBootStrap").modal('hide');
});
var temporalSelectHtml = '';
var canti = 0;
var cantiDollar = 0;
function checkMonto(monto) {
    if (monto > (canti + 100)) {
        //$('#diarioMontoFactura').val(canti);
    }
}

function filter() {
    var f1 = $("#f1").val();
    var f2 = $("#f2").val();
    $.ajax({
        url: "diarios/get-Info", type: "POST", cache: false, dataType: 'json', data: { fechai: f1, fechaf: f2 },
        beforeSend: function (xhr) {
            $('#tabla').empty();
            $('#tabla').DataTable().destroy();
        },
        success: function (data) {
            var lista = [];
            $.each(data, function (i, v) {
                var fecha = v[3];
                var editar = '';
                if (fecha.indexOf('1900') != -1) {
                    fecha = 'no contabilizado';
                    editar = '<a onclick="getDataForEdit(\'' + v[0] + '\')"><i  style="color:green" class="fa fa-edit"></i>&nbsp;Editar</a> <a onclick="cerrarDiario(\'' + v[0] + '\')"><i  style="color:green" class="fa fa-lock"></i>&nbsp;Cerrar diario</a>';
                }
                lista.push(['<a onclick="getDetalle(\'' + v[0] + '\')">' + v[0] + '</a> &nbsp;&nbsp;' + editar, v[1], v[2], fecha]);
            });
            $('#tabla').DataTable({
                destroy: true,
                "order": [[0, "desc"]],
                data: lista,
                columns: [
                    { title: "Diario" },
                    { title: "Descripción" },
                    { title: "Nombre" },
                    { title: "Fecha" }
                ]
            });
        },
        error: function (x) {
            console.log(x);
            $('#tabla').DataTable();
        }
    });
}

var descrip = $('#descripcion').val();
function getSaldo(ov) {
    // onblur="myFunction()"
    var diario = "OV-" + ov;
    $('#descripcion').val(descrip + " , " + diario);
    $.ajax({
        url: "diarios/getsaldo", type: "POST", cache: false, dataType: 'json', data: { diario: diario },
        beforeSend: function (xhr) {
        },
        success: function (data) {
            console.log(data.diario);
            body = '';
            if (data.diario != 'no') {
                if ( data.diario.indexOf('Error') < 0 ){
                    body += '<div class="row">';
                    body += '    <div class="col-md-12">';
                    body += '       <table class="table" style="font-size:15px;">';
                    body += '           <tr>';
                    body += '               <td style="text-align:left">El diario(s):</td>';
                    body += '               <td>' + data.diario + '</td>';
                    body += '           </tr>';
                    body += '           <tr>';
                    body += '               <td style="text-align:left">Total aplicado:</td>';
                    body += '               <td>' + data.totaldiarios + '</td>';
                    body += '           </tr>';
                    if (data.hasDollars) {
                        body += '           <tr>';
                        body += '               <td style="text-align:left">Dolares aplicados:</td>';
                        body += '               <td>' + data.inDollars + '</td>';
                        body += '           </tr>';
                        body += '           <tr>';
                        body += '               <td style="text-align:left">Pesos aplicados:</td>';
                        body += '               <td>' + data.inPesos + '</td>';
                        body += '           </tr>';
                        let missingDollars = data.cantidad / data.todayExchange;
                        cantiDollar = missingDollars;
                        $('#missingDollars').html('Faltante en dolares: ' + missingDollars);
                        $('#dolarsBody').show();
                    }
                    body += '           <tr>';
                    body += '               <td style="text-align:left">Total de la OV:</td>';
                    body += '               <td>' + data.totalOV + '</td>';
                    body += '           </tr>';
                    body += '       </table>';
                    body += '    </div>';
                    body += '</div>';
                    swal({
                        type: 'warning',
                        title: 'Ya existe un diario!',
                        html: body,
                        allowOutsideClick: false
                    }).then(function (result) {
                        valor = $('#status').val();
                        if (result && (valor == 2)) {
                            location.reload();
                        }
                    });
                }else{
                    swal({type              : 'warning',
                        title               : 'Atencion',
                        html                : 'Ocurrio una incidencia en la creacion del diario!',
                        allowOutsideClick   : false
                    }).then(function (result){
                        if (result){
                            location.reload();
                        }
                    });
                }
            }
            // var totalAmount=0;
            //     $.each(data.monto.value,function (i,v){
            //         totalAmount+=v.LineAmount;
            //     });
            // var tax = 1.16;
            // console.log(data);
            // if(data.monto.value[0].ShippingSiteId.indexOf('TJNA')>=0 ||data.monto.value[0].ShippingSiteId.indexOf('MEXL')>=0 ||data.monto.value[0].ShippingSiteId.indexOf('JURZ')>=0){//si la sucursal es mexicali, tijuana o juarez el iva se calcula al 8%
            //            tax = 1.08;
            // }
            // totalAmount=totalAmount*tax;//se hace la operacion para calcular el iva    
            // totalAmount=totalAmount*1.16;
            // totalAmount=totalAmount.toFixed(2);
            canti = Number(data.cantidad);
            $('#diarioMontoFactura').val(Number(canti));
            if (canti == 0) {
                swal("No existe la orden de venta", "Favor de revisar", "error");
            }
            $('#status').val(data.status);
        },
        error: function (x) {
            console.log(x);
        }
    });
}

function changeCurrency(currency) {
    if (currency == 'USD') {
        $('#diarioMontoFactura').val(Number(cantiDollar));
        $('#currencyInput').val('USD');
        $('#diarioCuentaContra').html('<option value="BANMER6065" selected>BANMER6065</option>');
    } else {
        $('#diarioMontoFactura').val(Number(canti));
        $('#currencyInput').val('MXN');
        $('#diarioFPago').change();
    }
}

function getDetalle(diario) {
    var accountType = [];
    accountType[0] = 'Libro mayor';
    accountType[1] = 'Cliente';
    accountType[2] = 'Vendedor';
    accountType[3] = 'Proyecto';
    accountType[5] = 'Activos fijos';
    accountType[6] = 'Banco';
    accountType[12] = 'Activo fijo';
    accountType[13] = 'Avance titular';
    accountType[14] = 'Aplazamientos';
    accountType[15] = 'Dinero para gastos menores';
    $("#diarioFolio").html(diario);
    $.ajax({
        url: "diarios/get-Detail", type: "POST", cache: false, dataType: 'json', data: { diario: diario },
        beforeSend: function (xhr) {

        },
        success: function (data) {
            var lista = [];
            //console.log(data.value[0]);
            $.each(data.value, function (i, v) {
                //console.log(v);
                lista.push([v.TransactionDate,
                v.AccountType,
                v.AccountDisplayValue,
                v.MarkedInvoice,
                v.TransactionText,
                Number(v.CreditAmount),
                v.OffsetAccountType,
                v.OffsetAccountDisplayValue]);
            });
            $('#detalleDiario').modal('show');
            $('#diarioDetalle').DataTable({
                destroy: true,
                order: [[3, "desc"]],
                data: lista,
                columns: [
                    { title: "Fecha" },
                    { title: "Tipo De Cuenta" },
                    { title: "Cuenta" },
                    { title: "Factura" },
                    { title: "Descripción" },
                    { title: "Crédito" },
                    { title: "Tipo De Cuenta De Contrapartida" },
                    { title: "Cuenta De Contrapartida" }
                ]
            });
        },
        error: function (x) {
            console.log(x);
            $('#diarioDetalle').DataTable({ destroy: true });
        }
    });
}

function getDataForEdit(diario) {
    $("#diarioHidden").val(diario);
    var accountType = [];
    accountType[0] = 'Libro mayor';
    accountType[1] = 'Cliente';
    accountType[2] = 'Vendedor';
    accountType[3] = 'Proyecto';
    accountType[5] = 'Activos fijos';
    accountType[6] = 'Banco';
    accountType[12] = 'Activo fijo';
    accountType[13] = 'Avance titular';
    accountType[14] = 'Aplazamientos';
    accountType[15] = 'Dinero para gastos menores';
    $("#diarioFolioEditar").html(diario);
    $.ajax({
        url: "diarios/get-Detail", type: "POST", cache: false, dataType: 'json', data: { diario: diario },
        beforeSend: function (xhr) {

        },
        success: function (data) {
            var lista = '';
            $.each(data, function (i, v) {
                lista += '<tr>'
                    //+'<td><input type="checkbox" name="elimina[]"></td>'
                    + '<td><input type="hidden" name="LineNum[]" value="' + v[9] + '"><input type="hidden" name="LedgerDimension[]" value="' + v[2] + '">' + v[2] + '</td>'
                    + '<td><input type="hidden" name="MarkedInvoice[]" value="' + v[3] + '">' + v[3] + '</td>'
                    + '<td><input class="form-control" type="text" name="Txt[]" value="' + v[4] + '"></td>'
                    + '<td><input class="form-control" type="number" name="AmountCurCredit[]" value="' + Number(v[5]).toFixed(3) + '"></td>'
                    + '<td><input type="hidden" name="PaymMode[]" value="' + v[8] + '">' + v[8] + '</td>'
                    + '<td><input type="hidden" name="OffsetLedgerDimension[]" value="' + v[7] + '">' + v[7] + '</td>'
                    + '</tr>';
            });
            $('#editarDiario').modal('show');
            $('#diarioDetalleEditar>tbody').html(lista);
        },
        error: function (x) {
            console.log(x);
            $('#diarioDetalle').DataTable({ destroy: true });
        }
    });
}

function guardarDiario() {
    var form = $("#diarioForm").serialize();
    cantiDollar = 0;
    $.ajax({
        url: "diarios/save-Diario", type: 'POST', data: form,
        beforeSend: function (xhr) {
            console.log('se manda');
        },
        success: function (data, textStatus, jqXHR) {
            console.log(data);
            if (data.resultado == 'ok') {
                swal("Diario guardado!", data.respuesta, "success");

            }
            else {
                swal("No se guardo!", data.respuesta, "info");
            }
            // filter();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('no jaleichon');
            catchError(jqXHR, textStatus);
        }
    });
}
function nuevoDiarioModalOpen() {
    $.ajax({
        url: 'inicio/cuenta-Contrapartida', type: 'POST', contentType: 'json',
        beforeSend: function (xhr) {
            $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
        },
        success: function (data, textStatus, jqXHR) {
            $('#process').html('');
            $('#contraPartida').html('');
            // $.each(data.JournalName.value,function (i,v){
            //     var selected='';
            //     if(mostrador===v[0]){
            //         selected='selected="" ';
            //     }
            //     $("#contraPartida").append('<option value="'+v.Name+'" '+selected+'>'+v.Name+"</option>");
            // });
            $.each(data.JournalName.value, function (i, v) {
                var selected = '';
                //console.log(mostrador);
                $.each(data.GroupUser.value, function (e, x) {
                    if (x.groupId == v.DocumentNumber) {
                        //selected='selected="" ';
                        $("#contraPartida").append('<option value="' + v.Name + '" ' + selected + '>' + v.Name + "</option>");
                    }
                });
            });
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
            $('#diarioResult').html(jqXHR.status);
        }
    });
    var html = '';
    $.each(payModeList.value, function (i, v) {
        html += '<option value="' + v.Name + '">' + v.Description + '</option>';
    });
    $('#diarioFPago').html(html);
    var selec = $('#diarioFPago option:selected').text();
    if (mostrador.indexOf('CH') !== -1) {
        $.ajax({
            url: 'inicio/cuenta-Contrapartida-Linea', type: 'POST', data: { selec: selec },
            beforeSend: function (xhr) {
                $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
            },
            success: function (data, textStatus, jqXHR) {
                var html = '';
                $('#diarioCuentaContra').html(html);
                $.each(data.value, function (i, v) {
                    html += '<option value="' + v.BankAccountId + '">' + v.BankAccountId + '</option>';
                });
                $('#diarioCuentaContra').html(html);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
                $('#diarioResult').html(jqXHR.status);
            }
        });
    }
    else {
        diarioFormaPago("01");
    }
}
function mapeoClte(valor, arreglo, request) {
    return $.map(arreglo, function (clte) {
        var posNombre = clte.nombre.indexOf(valor.toUpperCase());
        var posArticu = clte.value;//.indexOf(valor.toUpperCase());
        if (request.term.indexOf('*') < 0) {
            if ((posNombre >= 0) || (posArticu >= 0)) {
                return clte;
            }
        } else {
            if ((posNombre >= 0)) {
                return clte;
            }
        }
    });
}
function mapArray(clients) {
    var clientList = [];
    $.map(clients, function (item) {
        var info = { label: "No hay Resultados", value: " " };
        info["label"] = item.InvoiceNumber;//$.trim(item[0]);
        info["value"] = item.TotalInvoiceAmount;//$.trim(item[0]);
        info["nombre"] = item.InvoiceCustomerAccountNumber;//$.trim(Number(item[1]));
        clientList.push(info);
    });
    return clientList;
}
function autocompleteSetLabel(list, id) {
    $(id).autocomplete({
        autoFocus: true, minLength: 9,
        source: function (request, response) {
            var buscar = request.term.split(' ');
            var itemClte = [];
            $(buscar).each(function (index, valor) {
                if (index == 0) {
                    itemClte = mapeoClte(valor, list, request);
                } else {
                    itemClte = mapeoClte(valor, itemClte, request);
                }
            });
            response(itemClte);
        },
        response: function (event, ui) {
            if (ui.content.length === 0) {
                ui.content.push({ label: "No hay Resultados", value: "1" });
            }
        },
        select: function (event, ui) {
            event.preventDefault();
            $(id).val(ui.item.label);
            //console.log(ui.item.value);
            $("#diarioMontoFactura").val(ui.item.value);
        }
    });
}
function crearDiario() {
    try {
        cantiDollar = 0;
        $('#dolarsBody').hide();
        var formData = $('#diarioPagoForm').serializeArray();
        var status = formData[7].value;
        if (status != '2') {
            if (($('#diarioFPago').val() == 04 || $('#diarioFPago').val() == 28) && !/^(?!(0000))[0-9]{4}$/.test($('#digitosTarjeta').val())) {
                swal("Atencion.","Ingresar Digitos Tarjeta", 'warning');
                return;
            }
            $.ajax({
                type: 'POST', url: 'diarios/save-Diario', data: formData,
                beforeSend: function (xhr) {
                    $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
                },
                success: function (data, textStatus, jqXHR) {
                    $('#process').html('');
                    if (data.resultado == 'ok') {
                        $('#folioDiario').val(data.resultado);
                        $('#diarioGuardarBtn').hide();
                        swal("Guardado", "Diario creado con exito con folio: " + data.respuesta, "success");
                        $('#currencyInput').val('MXN');
                        $('#diarioFacturaFolio').val('');
                        $('#diarioMontoFactura').val('');
                    } else if (data.resultado == 0) {
                        var str = data.resultado;
                        swal("El cliente no tiene crédito!", '', "error");
                        $('#diarioMontoFactura').val(data.saldo);
                    }
                    else {
                        var str = data.resultado;
                        swal("Alto!", str, "error");
                        $('#diarioMontoFactura').val(data.saldo);
                    }
                    // filter();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
                    $('#diarioResult').html(jqXHR.status);
                }
            });
        } else {
            swal('Atencion!', 'La cantidad registrada es igual o excede el monto de la OV', 'error');
        }
    }
    catch (e) {
        console.log(e);
    }
}
function cerrarDiario(diario) {
    $.ajax({
        url: "diarios/cerrar-Diario", data: { diario: diario }, type: 'POST',
        beforeSend: function (xhr) {

        },
        success: function (data, textStatus, jqXHR) {
            if (data.resultado === 'ok') {
                swal("Diario Registrado!", data.respuesta, "success");
            }
            else {
                swal("No se guardo!", data.respuesta, "info");
            }
            filter();
        },
        error: function (jqXHR, textStatus, errorThrown) {

        }
    });
}
function diarioFormaPago(fp) {
    var selec = $('#diarioFPago option:selected').text();
    console.log(selec);
    if ($('#currencyInput').val() == 'USD') {
        return $('#diarioCuentaContra').html('<option value="BANMER6065" selected>BANMER6065</option>');
    }
    $.ajax({
        url: 'inicio/cuenta-Contrapartida-Linea', type: 'POST', data: { selec: selec },
        beforeSend: function (xhr) {
            $('#process').html('<img src="../application/assets/img/cargando.gif" style="width:1em;">');
        },
        success: function (data, textStatus, jqXHR) {
            var html = '';
            $('#diarioCuentaContra').html(html);
            console.log(data.value);
            $.each(data.value, function (i, v) {
                html += '<option value="' + v.BankAccountId + '">' + v.BankAccountId + '</option>';
            });
            $('#diarioCuentaContra').html(html);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $('#process').html('<img src="../application/assets/img/error.png" style="width:1em;">');
            $('#diarioResult').html(jqXHR.status);
        }
    });
}

$('#diarioFPago').on('change',function(){
    let fpago = $(this).val();
    if(fpago == 04 || fpago == 28){
        $('#digitosTarjetaDiv').show();
    }else{
        $('#digitosTarjeta').val('');
        $('#digitosTarjetaDiv').hide();
    }
});