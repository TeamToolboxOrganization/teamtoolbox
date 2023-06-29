document.addEventListener('DOMContentLoaded', function(){
    var els = document.querySelectorAll("div[name='mep']");
    for (var i = 0; i < els.length; i++) {
        els[i].onclick = function(e){
            var xhr = new XMLHttpRequest();
            var value = this.dataset["value"];
            xhr.open('GET', 'mep/confirm/' + value);

            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    var div = document.querySelector('div[data-value="'+ value +'"]');
                    var divText = document.querySelector('div[mepid="'+ value +'"]');
                    if(this.response == "Validé"){
                        div.classList.remove("mepColorToValidate");
                        div.classList.add("mepColor");
                    } else {
                        div.classList.remove("mepColor");
                        div.classList.add("mepColorToValidate");
                    }
                    divText.innerText = this.response;
                } else {
                    alert('La mise à jour a échoué ' + xhr.status);
                }
            };
            xhr.send();
        };
    }
}, false);