document.addEventListener('DOMContentLoaded', function(){

    const md = require('markdown-it')({
        html: true,
        linkify: true,
        typographer: true
    });

    if (localStorage) {
        let noteDomTempList = document.getElementsByName('readNoteNode');
        for (var i = 0; i < noteDomTempList.length; i++) {
            var data = localStorage.getItem(noteDomTempList[i].id);
            if(data != null){
                noteDomTempList[i].innerHTML = md.render(data);
            }
        }
    } else {
        // No support. Use a fallback such as browser cookies or store on the server.
    }
}, false);