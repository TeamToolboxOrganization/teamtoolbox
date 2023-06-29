document.addEventListener('DOMContentLoaded', function(){
    var els = document.querySelectorAll("button[id='sidebarCollapse']");
    for (var i = 0; i < els.length; i++) {
        els[i].onclick = function (e) {
            if (document.querySelector("#sidebar").classList.contains("active")) {
                document.querySelector("#sidebar").classList.remove("active");
            } else {
                document.querySelector("#sidebar").classList.add("active");
            }
        };
    }
}, false);