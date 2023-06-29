document.addEventListener('DOMContentLoaded', function(title, body) {
    if(window.Notification){
        let noteDomTempList = document.getElementsByName('notification');

        for (var i = 0; i < noteDomTempList.length; i++) {
            let title = noteDomTempList[i].dataset["title"];
            let body = noteDomTempList[i].dataset["body"];
            Notification.requestPermission().then(function(result) {
                let notification = new Notification(title, { body: body });
            });
        }


    }
},false);