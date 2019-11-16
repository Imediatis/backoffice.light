/// <reference path="alertify.js" />

var Title = "EE-Dashboard";
var Options = {
    "closeButton": true,
    "debug": false,
    "progressBar": true,
    "preventDuplicates": true,
    "positionClass": "toast-top-right",
    "onclick": null,
    "showDuration": "100",
    "hideDuration": "100",
    "timeOut": "3000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

var timeOut = 5;

function showMessage(status, message) {
    switch (status) {
        case 1:
            alertify.success(message, timeOut);
            //toastr.success( message,Title);
            break;
        case 2:
            alertify.warning(message, timeOut);
            //toastr.warning(message, Title);
            break;
        case -1:
            alertify.error(message, timeOut);
            //toastr.error(message, Title);
            break;
        default:
            alertify.message(message, timeOut);
            //toastr.info(message,Title);
    }
}

function validateEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

String.format = function() {
    var s = arguments[0];
    for (var i = 0; i < arguments.length - 1; i++) {
        var reg = new RegExp("\\{\\s*" + i + "\\s*\\}", "gm");
        s = s.replace(reg, arguments[i + 1]);
    }
    return s;
};

String.prototype.stripSlashes = function() {
    return this.replace(/\\(.)/mg, "$1");
};

function LocationChange(url, selectedItem, target, branch) {
    vbranche = (typeof branch === 'undefined') ? 1 : 2;
    $.ajax({
        type: "POST",
        url: url,
        data: {
            id: selectedItem,
            branch: vbranche
        },
        dataType: "json",
        success: function(response) {
            $('#' + target).html(response.data);
            if (!response.isSuccess) {
                alertify.error(response.message);
            }
        }
    });
}