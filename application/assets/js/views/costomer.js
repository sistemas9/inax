$(document).ready(function() {
    gaTrigger('Customers','Ready',userBranchOffice);
});

$("#barraProgreso").remove();

function deleteFila2(id) {
        $("#row2" + id).remove();
    }
function habilitaExtencion(id){
    if ($('#formaContacto'+id+' option:selected').val() == 1){
            $('#extension'+id).attr('disabled', false);
            $('#extension'+id).attr('type', 'number');
            $('#telefono'+id).attr('type', 'number');
        } else if ($('#formaContacto'+id+' option:selected').val() != 2){
            $('#extension'+id).attr('disabled', 'disabled');
            $('#telefono'+id).attr('type', 'text');
        }
        else {
            $('#extension'+id).attr('disabled', 'disabled');
            $('#telefono'+id).attr('type', 'email');
            
        }
}
function filterRFC(e){
    $('#'+e.id).val(e.value.toUpperCase().replace(" ", ""));
}
/* funciones para agregar multiples contactos */
function addContacto(){
        var tds = $("#contacto tr:first td").length;
        var trs = $("#contacto tr").length + 1;
        var nuevaFila = "<tr id='row2" + trs + "'>";
        for (var i = 0; i < tds; i++) {
            nuevaFila += "<td>"
                    +"<div class='list-group-item list-group-item-info clearfix'><a onclick='deleteFila2(" + trs + ")' class='bar-subtitle collection-item'><span class='pull-rigth'><i class='Small material-icons' onclick='deleteFila2(" + trs + ")' style='color:#8c1616;'>delete</i></span>Contacto " + trs + "</a></div>"
                    +"<br><div class='col-sm-6'>"
                        +"<div class=''>"
                            +"<label>Nombre De Contacto</label>"
                            +"<input type='text' placeholder='Descripción de contacto' id='descripcion"+trs+"' name='descripcion["+trs+"]' class='form-control validarInput' required>"
                        +"</div><br>"
                        +"<div class=''>"
                            +"<label >Dirección ó Número De Contacto</label>"
                            +"<input type='number' placeholder='Dirección ó telefono de contacto' id='telefono" + trs + "' name='telefono[" + trs + "]' class='form-control validarInput' required>"
                        +"</div>"
                    +"</div>"
                    +"<div class='col-sm-6'>"
                        +"<div class=''>"
                            +"<label>Tipo De Contacto</label><br>"
                            +"<select id='formaContacto" + trs + "' class='form-control validarOption' onchange='habilitaExtencion(" + trs + ")' name='formaContacto[" + trs + "]' required>"
                                +"<option value='' disabled selected>Seleccione tipo de contacto</option>"
                                +"<option value='1'>Telefono</option>"
                                +"<option value='2'>Email</option>"
                                +"<option value='3'>URL</option>"
                                +"<option value='4'>Télex</option>"
                                +"<option value='5'>Fax</option>"
                            +"</select>"
                        +"</div><br>"
                        +"<div class=''>"
                        +"<label>Extensión</label>"
                        +"<input class='form-control' type='number' placeholder='Ingrese la extensión' id='extension" + trs + "' name='extension[" + trs + "]' disabled='true'>"
                    +"</div>"
                    +"</div>"
                    +"</td>";
        }
        nuevaFila += "</tr>";
        $('#contacto tbody').append(nuevaFila);
}
/* funcion para obtener la colonia segun el id*/
function getColonia(id) {
    gaTrigger('AddressCounties','entity',userBranchOffice);
    var estado =  $('#estado' + id).val();
    $.ajax({
        url: "customer",
        type: "get",
        dataType: "json",
        async: true,
        cache: true,
        data: {'token': 'getColonia', 'key': estado},
        success: function (data) {
            var option;
            // console.log("si entre" +" "+ id );
            // console.log(data);
            option = '<option value="" selected>Selecciona...</option>';
            for (var i = 0; i < data.length; i++) {

                option += '<option value="' + data[i].CountyId + '">' + data[i].Description + '</option>';

            }
            $('#colonia' + id).html(option);
            $('#colonia'+id).select2();
        }
    });
}

function getCiudad(estado,id){
    gaTrigger('AddressCities','entity',userBranchOffice);
        $.ajax({
            url: "customer",
            type: "get",
            dataType: "json",
            async: true,
            cache: true,
            data: {'token': 'getCiudad', 'key': estado},
            success: function (data) {
                var option='';

                //console.log(data);
                option = '<option value="" disabled selected>Selecciona una ciudad</option>';
                for (var i = 0; i < data.length; i++) {
                    option += '<option value="' + data[i].Name + '"  >' + data[i].Name + '</option>';

                }
                $('#ciudad'+id).html(option);
                $('#ciudad'+id).select2();
            }
        });
    }
    function setDireccion(id) {
        gaTrigger('AddressPostalCodes','entity',userBranchOffice);
        var zipCode = $('#codigoPostal' + id).val();
        $.ajax({
            url: "customer",type: "get",dataType: "json",async: true,
            data: {'token': 'getZipcode', 'key': zipCode},
            beforeSend: function (xhr) {
                $('#cpimagen'+id).html('<img src="../application/assets/img/cargando.gif" style="width: 14px;">');
            },
            success: function (data) {
                if(data.noresult=="No Results"){

                    $('#cpimagen'+id).html('no existe');
                }
                else{

                    $('#cpimagen'+id).html('');
                    $('#pais' + id).val(data[0].CountryRegionId);
                    $('#paisAbr' + id).val(data[0].CountryRegionId);
                    $('#estado' + id).val(data[0].StateId);
                    $('#estadoAbr' + id).val(data[0].StateId);
                    getCiudad(data[0].StateId,id);
                    getColonia(id);
                }
            }
        });
    }
    function deleteFila(id) {
        $("#row" + id).remove();
    }
    /** funciones para multiples direcciones**/
    function addDireccion() {
        var tds = $("#direccion tr:first td").length;
        var trs = $("#direccion tr").length + 1;
        var nuevaFila = "<tr id='row" + trs + "'>";
        var propositoOptions = "<option value='Delivery'>Delivery</option><option value='Business'>Business</option><option value='Invoice'>Invoice</option>";        
        for (var i = 0; i < tds; i++) {
            nuevaFila += "<td><br><div class='col s12'>"
                   +"<div class='list-group'><a onclick='deleteFila(" + trs + ")'  class='list-group-item list-group-item-info clearfix'><span class='badge' style='background-color: rgba(119, 119, 119, 0) !important;'><i class='Small material-icons' onclick='deleteFila(" + trs + ")' style='color:#8c1616;'>delete</i></span>Dirección " + trs + "</a></div>"
                    + "<br><div class='col-sm-12'>"
                    + "<div class='col-sm-2'>"
                    + "<label>Código Postal <span id='cpimagen"+trs+"'></span></label>"
                    + "<input type='text' placeholder='...' id='codigoPostal" + trs + "' onchange='setDireccion(" + trs + ")' name='cp[" + trs + "]' class='form-control validarInput' required>"
                    + "</div>"
                    + "<div class='col-sm-2'>"
                    + "<label>Estado</label>"
                    + "<input class='form-control' type='text' id='estado" + trs + "' name='estado[" + trs + "]' readonly='' required"
                    + "<input id='estadoAbr"+ trs +"' type='hidden'/>"
                    + "</div>"
                    + "<div class='col-sm-2'>"
                    + "<label >País</label>"
                    + "<input type='text' id='pais" + trs + "' name='pais[" + trs + "]' readonly='' required class='form-control'>"
                    + "<input id='paisAbr"+ trs +"' type='hidden'/>"
                    + "</div>"
                    + "<div class='col-sm-4'>"
                    + "<label>Calle</label>"
                    + "<input type='text' placeholder='Ingrese una calle' id='calle" + trs + "' name='calle[" + trs + "]' class='form-control validarInput' required>"
                    + "</div>"
                    + "<div class='col-sm-2'>"
                    + "<label>Número</label>"
                    + "<input type='text' placeholder='Ingrese número' id='numero" + trs + "' name='numero[" + trs + "]' class=' form-control validarInput' required>"
                    + "</div>"
                    + "</div>"
                    + "<div class='col-sm-12'>"
                    + "<div class='col-sm-3 espacioDerecho'>"
                    + "<label>Ciudad</label>"
                    + "<select id='ciudad" + trs + "' on name='ciudad[" + trs + "]' class='form-control validarOption' required>"
                    + "<option  value='' disabled selected>Seleccione...</option>"
                    + "</select>"
                    + "</div>"
                    + "<div class='col-sm-4 espacioDerecho'>"
                    + "<label>Colonia</label>"
                    + "<select id='colonia" + trs + "' name='colonia[" + trs + "]' class='form-control validarOption' required>"
                    + "<option value='' disabled selected>Seleccione una colonia</option>"
                    + "</select>"
                    + "</div>"
                    + "<div class='col-sm-3'>"
                    + "<label>Proposito</label>"
                    + "<select id='proposito" + trs + "' class='form-control proposi' name='proposito[" + trs + "]' class='form-control validarOption' multiple required>" + propositoOptions + "</select>"
                    + "<input type='text' id='propositoVal"+trs+"' name='propositoVal["+trs+"]' class='Valpro' hidden> "
                    + "</div>"
                    + "</div>"
                    + "</div></td>";
        }
        nuevaFila += "</tr>";
        $('#direccion tbody').append(nuevaFila);
        //setTimeout(function(){$('.proposi').multiselect()},1000);
        $('.proposi').multiselect();
    }
    
    $(document).ready(function () {
        $("#formCustomer").keypress(function (e) {
            if (e.which == 13) {
                return false;
            }
        });
        /*
         * load segmentos de clientes
         */
        var option;
        option = '<option value="" selected>Selecciona un segmento de clientes</option>';
        for (var i = 0; i < segmentos.length; i++) {

            option += '<option value="' + segmentos[i].SegmentId + '">' + segmentos[i].Description + '</option>';

        }
        $('#segmento').html(option);        
        $('#segmento').attr('disabled', false);
        /*
         * load conjunto de clientes
         */
        var option;
        option = '<option value="" disabled selected>Selecciona un conjunto de clientes</option>';
        for (var i = 0; i < clientes.length; i++) {

            option += '<option value="' + clientes[i].CustomerGroupId + '">' + clientes[i].CustomerGroupId + '</option>';

        }
        $('#conjuntoCliente').html(option);
        /*
         * moneda
         */
        // moneda

        option = '<option value="" disabled>Selecciona una moneda</option><option value="MXN" selected>MXN</option>';
        for (var i = 0; i < moneda.length; i++) {
            option += '<option value="' + moneda[i].ISOCurrencyCode + '">' + moneda[i].ISOCurrencyCode + '</option>';

        }
        monedaList = option;
        $('#moneda').html(option);
        //sitio de venta        
        var option;

        //console.log(sitios);
        option = '<option value="" disabled selected>Selecciona sitio de ventas</option>';
        for (var i = 0; i < sitios.length; i++) {
            option += '<option value="' + sitios[i].SiteId + '">' + sitios[i].SiteId + ' - ' + sitios[i].SiteName + '</option>';

        }
        $('#sitioVenta').html(option);
        var inicial = '<option value="" disabled selected></option>';
        $('#almacen').html(inicial);
        console.log(zonaVenta);
        var zVenta = zonaVenta;
        var option;
        option = '<option value="" selected>Selecciona sucursal</option>';
        for (var i = 0; i < zVenta.length; i++) {
            option += '<option value="' + zVenta[i].DistrictId + '">' + zVenta[i].DistrictId + ' - ' + zVenta[i].DistrictDescription + '</option>';
        }
        $('#zonaVenta').html(option);
        
        //var proposito = "{}"
        //proposito = JSON.parse(proposito);
        // var option;
        // option = '';
        // for (var i = 0; i < proposito.length; i++) {
        //     option += '<option value="' + proposito[i].type + '">' + proposito[i].DESCRIPTION + '</option>';
        // }
        // propositoOptions = option;
        //$('#proposito1').html(option);
        $('.proposi').multiselect();
        //$('#proposito1').select2();       


        $("#sitioVenta").on('change', function () {
            gaTrigger('STF_InventWarehouseEntity','entity',userBranchOffice);
            var sitio = $("#sitioVenta").val();
            //almacen
            $.ajax({
                url: "customer",
                method: "get",
                dataType: "json",
                async: true,
                data: {'token': 'getAlmacen', 'sitio': sitio},
                success: function (data) {
                    // var option;
                    // if (data.length > 0) {
                    //     option = '<option value="" disabled selected>Selecciona un almacén</option>';
                    //     for (var i = 0; i < data.length; i++) {
                    //         option += '<option value="' + data[i].Warehouse + '">' + data[i].Warehouse + ' - ' + data[i].Name + '</option>';
                    //     }
                    // } else {
                    //     option = '<option value="" disabled selected>Sin almacenes definidos</option>';
                    // }
                    $('#almacen').html(data[0].Warehouse);
                    $('#almacen').attr('disabled', false);

                }
            });
            //descuento
            gaTrigger('STF_PriceDiscGroupEntity','entity',userBranchOffice);
            $.ajax({
                url: "customer",
                type: "get",
                dataType: "json",
                async: true,
                data: {'token': 'getDescuento', 'sitio': sitio},
                success: function (data) {
                    var option;
                    //console.log(data);
                    option = '<option value="" selected>Selecciona un descuento</option>';
                    for (var i = 0; i < data.length; i++) {
                        option += '<option value="' + data[i].GroupId + '">' + data[i].GroupId + ' - ' + data[i].Name + '</option>';
                    }
                    $('#tipoDescuento').html(option);
                    $('#tipoDescuento').attr('disabled', false);
                }
            });
        });
        $('#formaContacto').on('change', function (e) {
            e.preventDefault();
            if ($('#formaContacto option:selected').val() == 1) {
                $('#extension').attr('disabled', false);
                $('#extension').attr('type', 'number');
                $('#telefono').attr('type', 'number');
            } else if ($('#formaContacto option:selected').val() != 2){
                $('#extension').attr('disabled', 'disabled');
                $('#telefono').attr('type', 'text');
            }else{
                $('#extension').attr('disabled', 'disabled');
                $('#telefono').attr('type', 'email'); 
            }
        });

        $('#formCustomer').on('submit', function (e) {
            e.preventDefault();
            var bandera = false;
            var validarOption = $(".validarOption option:selected").val();
            var validarInput = $(".validarInput").val();
            //validación de RFC valido.
            var rfc = $('#rfc').val();
            if (rfc.length == 12) {
                var valid = '^(([A-Z]|[a-z]){3})([0-9]{6})((([A-Z]|[a-z]|[0-9]){3}))';
            } else {
                var valid = '^(([A-Z]|[a-z]|\s){1})(([A-Z]|[a-z]){3})([0-9]{6})((([A-Z]|[a-z]|[0-9]){3}))';
            }
            var validRfc = new RegExp(valid);
            var matchArray = rfc.match(validRfc);
            if (matchArray == null) {
                $('#rfcModal').modal();
                bandera = false;
            }
            //Validación de campos vacios.
            if (validarOption !== "" || validarOption !== null || validarInput !== "" || validarInput !== null) {
                bandera = true;
            }
            $(".Valpro").each(function(e){
               // console.log(e);
                index=e+1;
            var purpose = $("#proposito"+index).val();
             //purpose.toString();            
            var purpose2 = purpose.toString().replace(",",";");
            var purpose2 = purpose2.toString().replace(",",";");
            purpose2 = purpose2 + ";"; 
            $("#propositoVal"+index).val(purpose2);
            });
            
            
            var datos = $("#formCustomer").serialize();
            datos += '&token=saveInfo';
            //datos += $("#proposito1").val();

            if (bandera==true) {
                $.ajax({url: "customer", type: "POST",dataType:'json',data: datos,
                    beforeSend: function (xhr) {
                        $('#msjprocess').html('<p><center><img src="../application/assets/img/cargando.gif" style="width:50px;"></center></p>');
                        $('#process').modal();
                    },
                    success: function (data) {

                        $('#process').modal('hide');
                        if (data.status != 'Fallo' && typeof(data.respuesta.error) === 'undefined') {
                            $('#msjexito').html('<p><span style="font-weight: bold;">El cliente se ha dado de alta con la clave: <span style="color: rgb(206, 0, 0);">'+data.msg+'</span></span></p>');
                            $('#exito').modal(); 
                            $("#formCustomer").trigger('reset');                        
                        } else {
                            $('#msjerror').html('<p><span style="color: rgb(206, 0, 0);">Favor de verificar las siguientes opciones:</span></p><ol><li>Cliente es entidad jurídica cuando es (<span style="font-weight: bold;">Persona Moral</span>).</li><li>Cliente es Persona jurídica cuando es (<span style="font-weight: bold;">Persona Física</span>).</li><li><span style="font-weight: bold;">El sistema valida el RFC</span>, favor de verificar que el RFC es valido.</li><li>Verifique que tiene conexión a la red.</li></ol><p>'+data.respuesta.error.innererror.message+'</p>');
                            $('#error').modal();
                        }
                    },
                    error: function (data) {
                        $("#detalleError").html(data);
                        $('#error').modal();
                    }
                });
            } else {
                $('#process').modal('hide');
                $("#detalleError").html('Hay campos vacios, favor de completarlos');
                $('#error').modal();                
            }
        });
        
        
    });