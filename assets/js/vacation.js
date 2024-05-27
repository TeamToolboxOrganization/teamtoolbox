import '../scss/vacation.scss';

document.addEventListener('DOMContentLoaded', function(){
    // Enregistrement AJAX
    let changeVacationStatusBtn = document.querySelectorAll('.changeVacationStatusBtn');
    const n = changeVacationStatusBtn.length;
    let locale = window.location.href.includes('en') ? 'en' : 'fr';
    for(let i = 0; i < n; i++) {
        changeVacationStatusBtn[i].addEventListener("click", function () {
            let statusValue = this.dataset['status'];
            let vacationId = this.dataset['id'];
            let currentStatus = this.closest('td').previousElementSibling

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/' + locale + '/vacation/changeVacationStatus/' + vacationId);
            xhr.onload = function () {
                var response = JSON.parse(xhr.responseText);
                if (xhr.status !== 200) {
                    if(xhr.status === 403){
                        alert(response.error);
                    }
                    else{
                        alert('Vacation status change failed.  Returned status of ' + xhr.status);
                    }
                }
                if(xhr.status === 200){
                    currentStatus.classList.add(response.stateClass)
                    currentStatus.textContent = response.currentStateName;
                }
            };
            var data = new FormData();
            data.append('newStatus', statusValue);
            xhr.send(data);
        }, false);
    }
});