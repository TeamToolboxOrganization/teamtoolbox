document.addEventListener('DOMContentLoaded', function(){
    var customColors = document.querySelectorAll("input[name='colors']");
    for (var i = 0; i < customColors.length; i++) {
        customColors[i].addEventListener("change", function(e,i){
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'editCustomColor');
            xhr.setRequestHeader('Content-Type', 'application/json');
            var colorFields = document.querySelectorAll("input[name='colors']")
            let data;
            var loader = document.getElementsByClassName("loading");
            for (var i = 0; i < colorFields.length; i++) {
                colorFields[i].style.display="none";
                loader[i].style.display="block";
            }
            xhr.onload = function () {
                if (xhr.status !== 200) {
                    e.target.value = e.target.defaultValue;
                    alert('Request failed.  Color not modified.');
                }
                for (var i = 0; i < colorFields.length; i++) {
                    colorFields[i].style.display="inline-block";
                    loader[i].style.display="none";
                }
            };
            data = '{ "id": "' + e.target.id + '", "value": "' + e.target.value + '"}';
            xhr.send(data);
        })

    }
}, false);