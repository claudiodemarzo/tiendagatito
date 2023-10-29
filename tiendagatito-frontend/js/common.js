window.onload = function() {
    initCountdown()
}

function initCountdown() {
    var endDate = new Date($(".countDownSeason").data("timeend").replace(/\s/, 'T')).getTime()
    var nowDate = new Date($(".countDownSeason").data("now").replace(/\s/, 'T')).getTime()
    $(".countDownSeason").removeData("now")
    var seasonNum = $(".countDownSeason").data('num')
    var tl = endDate - nowDate

    var intv = setInterval(function() {
        var days = Math.floor(tl / (1000 * 60 * 60 * 24));
        var hours = Math.floor((tl % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((tl % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((tl % (1000 * 60)) / 1000);

        $(".countDownSeason").html("Temporada " + seasonNum + " termina en: <wbr>" + (!days == 0 ? days + "d " : "") + (hours == 0 ? "" : hours + "h ") + (minutes == 0 ? "" : minutes + "m ") + (seconds == 0 ? "" : seconds + "s"))
        
        tl -= 1000
        if (tl < 0) {
            clearInterval(intv)
            $(".countDownSeason").text("Temporada terminada")
        }
    }, 1000)
}

function pop(type, message, priority = false) {
    toastr.options = {
        "closeButton": priority,
        "debug": false,
        "newestOnTop": false,
        "progressBar": !priority,
        "positionClass": "toast-top-right",
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": (priority ? "0" : "5000"),
        "extendedTimeOut": (priority ? "0" : "1000"),
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }
    switch (type) {
        case 1:
            toastr["error"](message);
            break;
        case 2:
            toastr["warning"](message);
            break;
        case 3:
            toastr["success"](message);
            break;
    }
}


function execPOST(endpoint, data, parseJSON, successfallback) {
    $.ajax({
        url: endpoint,
        data: data,
        type: 'POST',
        success: function(response) {
            if (parseJSON) {
                var parsed = JSON.parse(response);
                if (parsed.codice < 0) {
                    pop(1, parsed.response);
                } else if (parsed.codice == 0) {
                    pop(2, parsed.response);
                } else {
                    successfallback(parsed);
                }
            } else {
                successfallback(response);
            }
        },
        error: function() {
            pop(1, "Si è verificato un problema, riprova più tardi");
        }
    });
}

function execFormDataPOST(endpoint, formData, successfallback) {
    $.ajax({
        url: endpoint,
        data: formData,
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        success: function(response) { successfallback(response) },
        error: function(xhr, status, error) {
            console.log(error);
            pop(1, "Si è verificato un problema, riprova più tardi");
        }
    });
}

function setCookie(name, value, days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function eraseCookie(name) {
    document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

function isEllipsisActive($e) {
    return ($e.parent().innerWidth() < $e.width());
}
