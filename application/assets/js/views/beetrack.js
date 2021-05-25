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

