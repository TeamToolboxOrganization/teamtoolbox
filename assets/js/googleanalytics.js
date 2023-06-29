//await import("https://www.googletagmanager.com/gtag/js?id=G-W7N78D5JPD")

document.addEventListener('DOMContentLoaded', function(){
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    let noteDomTemp = document.getElementById('googleid');

    if(noteDomTemp){
        let userId = noteDomTemp.dataset['userid'];
        let squadName = noteDomTemp.dataset['squadname'];

        if(userId){
            gtag('config', 'G-XXXXXXXXXX', {
                'user_id': userId
            });
        } else {
            gtag('config', 'G-XXXXXXXXXX');
        }

        if(userId && squadName){
            gtag('set', 'user_properties', {
                'squad': squadName,
            });
        }
    }
}, false);
