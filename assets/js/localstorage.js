window.exportNotes = function() {
    if (localStorage) {
        // LocalStorage is supported!
        var data = JSON.stringify(localStorage);
        var blob = new Blob([data], {type: 'text/json'}),
            e    = document.createEvent('MouseEvents'),
            a    = document.createElement('a')

        a.download = 'notes.json';
        a.href = window.URL.createObjectURL(blob)
        a.dataset.downloadurl =  ['text/json', a.download, a.href].join(':')
        e.initMouseEvent('click', true, false, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null)
        a.dispatchEvent(e)
    } else {
        // No support. Use a fallback such as browser cookies or store on the server.
    }
};

document.addEventListener('DOMContentLoaded', function(){
    let importBtn = document.getElementById('importNoteInput');
    if (importBtn){
        importBtn.onchange = () => {
            const file = document.getElementById('importNoteInput').files[0];
            if (file) {
                const reader = new FileReader();
                reader.readAsText(file, 'UTF-8');
                reader.onload = (evt) => {
                    console.log(evt.target.result);
                    let parsedJSON = JSON.parse(reader.result);
                    Object.keys(parsedJSON).forEach(function (k) {
                        localStorage.setItem(k, parsedJSON[k]);
                    });
                    location.reload();
                };
                reader.onerror = (evt) => {
                    console.error('Failed to read this file');
                };
            }
        };
    }
}, false);