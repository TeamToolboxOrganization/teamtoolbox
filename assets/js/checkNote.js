document.addEventListener('DOMContentLoaded', function(){
    var els = document.querySelectorAll("input[type='checkbox']");
    for (var i = 0; i < els.length; i++) {
        els[i].onclick = function(e){
            let noteId = this.dataset["noteid"];
            var isChecked = this.checked;

            var xhr = new XMLHttpRequest();

            if (isChecked) {
                xhr.open('GET', 'checknote/checked?noteId=' + noteId);
            } else {
                xhr.open('GET', 'checknote/unchecked?noteId=' + noteId);
            }
            xhr.setRequestHeader('Content-Type', 'application/json');

            xhr.onload = function () {
                if (xhr.status !== 200) {
                    alert('Request failed.  Returned status of ' + xhr.status);
                }
            };
            xhr.send();
        };
    }

}, false);