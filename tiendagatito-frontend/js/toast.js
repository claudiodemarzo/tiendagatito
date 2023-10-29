var toastAreaInitialized = false;

function initToasts() {
    if (!toastAreaInitialized) {
        var area = '<div id="toastarea" class="toast-container position-fixed top-0 end-0 mt-5 p-3 pt-4"></div>';
        $("body").append(area);
        toastAreaInitialized = true;
    }
}

function showToast(properties) {
    initToasts();
    props = {
        title: null,
        description: null,
        id: "toast" + Math.floor(Math.random() * 9999),
        icon: '/assets/imgs/logo.png',
        autohide: true,
        delay: 7000,
        animation: true,
        type: 'default'
    }
    err = false;
    for (const attr in props) {
        if (properties.hasOwnProperty(attr)) props[attr] = properties[attr]
        if (props[attr] == null) {
            console.log("Toast - Missing property " + attr);
            err = true;
        }
    }

    if (!err) {
        colorClass = ""
        textColorClass = ""
        switch (props.type) {
            case 'warning':
                colorClass = "bg-warning"
                textColorClass = "-white"
                break;

            case 'error':
                colorClass = "bg-danger"
                textColorClass = "-white"
                break;

            default:
                break;
        }
        var tempEl = `<div class="toast ` + colorClass + ' text' + textColorClass + `" id="` + props.id + `" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header ` + colorClass + ' text' + textColorClass + (textColorClass == "-white" ? " border-bottom-white" : "") + `">
      <img src="` + props.icon + `" class="rounded me-2" alt="..." height="35px">
      <strong class="me-auto">` + props.title + `</strong>
      <button type="button" class="btn-close btn-close` + textColorClass + ` me-1" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">
      ` + props.description + `
    </div>
  </div>`;

        $("#toastarea").append(tempEl);
        var createdEl = $("#" + props.id);
        var currentToast = new bootstrap.Toast(createdEl, props);
        currentToast.show();
        document.getElementById(props.id).addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    }
}