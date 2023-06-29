document.addEventListener('DOMContentLoaded', function(){

    const md = require('markdown-it')({
        html: true,
        linkify: true,
        typographer: true
    });

    let markdowntextarearender = document.getElementById("markdowntextarearender");
    let markdowntextarea = document.getElementById("note_content");
    markdowntextarea.onkeyup = function(event){
        markdowntextarearender.innerHTML = md.render(event.target.value);
    };

    let noteDomTemp = document.getElementById('newNoteCreation');
    if (noteDomTemp){
        if (localStorage) {
            // LocalStorage is supported!
            localStorage.setItem(noteDomTemp.dataset["noteid"], noteDomTemp.dataset["notecontent"]);

            let returnlink = document.getElementById("endlink1");
            if(returnlink == null){
                returnlink = document.getElementById("endlink2");
            }
            if(returnlink != null){
                returnlink.click();
            }
        } else {
            // No support. Use a fallback such as browser cookies or store on the server.
        }
    }
}, false);