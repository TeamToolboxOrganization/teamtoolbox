document.addEventListener('DOMContentLoaded', function(){
    var els = document.querySelectorAll("button[name='remove']");
    for (var i = 0; i < els.length; i++) {
        els[i].onclick = function(e){
            var xhr = new XMLHttpRequest();

            xhr.open('GET', 'delete/' + this.value);

            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.querySelector("tr[mepid='" + this.response + "']").remove();
                } else {
                    alert('La suppression a échoué ' + xhr.status);
                }
            };
            xhr.send();
        };
    }

    var previousMepLink = document.getElementById('previousMep')

    previousMepLink.onclick = function(e){
        var els = document.querySelectorAll("tr[data-ispassed='true']");
        for (var i = 0; i < els.length; i++) {
            if (els[i].classList.contains('hide')){
                els[i].classList.remove('hide')
            } else{
                els[i].classList.add('hide')
            }
        }
    }

}, false);