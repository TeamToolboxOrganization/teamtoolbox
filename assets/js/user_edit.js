document.addEventListener('DOMContentLoaded', function(){

    var els = document.querySelectorAll("button[name='remove']");
    for (var i = 0; i < els.length; i++) {
        els[i].onclick = function(e){
            var xhr = new XMLHttpRequest();

            xhr.open('GET', 'delete/' + this.value);

            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.querySelector("tr[userid='" + this.response + "']").remove();
                } else {
                    alert('La suppression a échoué ' + xhr.status);
                }
            };
            xhr.send();
        };
    }

}, false);