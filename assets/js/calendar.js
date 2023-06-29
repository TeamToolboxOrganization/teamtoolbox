import '../scss/calendar.scss';

import { Calendar } from '@fullcalendar/core';
import interactionPlugin from '@fullcalendar/interaction';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';

document.addEventListener('DOMContentLoaded', function(){

    var calendarEl = document.getElementById('calendar');
    var itemId = calendarEl.dataset["itemid"];
    var itemType = calendarEl.dataset["itemtype"];

    var currentUserId = calendarEl.dataset["currentuserid"];
    var lang = calendarEl.dataset["lang"];
    var allDates = calendarEl.dataset["alldates"];

    var vacationsInput = {
        id: 'vacations',
        url: '/fr/calendar/data/' + itemType + '/' + itemId,
        method: 'POST',
        extraParams: {
            type: 'vacations',
            allDates: allDates,
        },
        failure: function(errorObj) {
            console.log('there was an error while fetching events vacations : ' + errorObj.message );
        },
        color: '#CC492A',   // a non-ajax option
        textColor: '#FFFFFF' // a non-ajax option
    };

    var officeInput = {
        id: 'office',
        url: '/fr/calendar/data/' + itemType + '/' + itemId,
        method: 'POST',
        extraParams: {
            type: 'Bureau',
            allDates: allDates,
        },
        failure: function(errorObj) {
            console.log('there was an error while fetching events vacations : ' + errorObj.message );
        },
        color: '#003FC0',   // a non-ajax option
        textColor: '#FFFFFF' // a non-ajax option
    };

    var homeInput = {
        id: 'home',
        url: '/fr/calendar/data/' + itemType + '/' + itemId,
        method: 'POST',
        extraParams: {
            type: 'Télétravail',
            allDates: allDates,
        },
        failure: function(errorObj) {
            console.log('there was an error while fetching events vacations : ' + errorObj.message );
        },
        color: '#8B5000',   // a non-ajax option
        textColor: '#FFFFFF' // a non-ajax option
    };

    var mepInput = {
        id: 'mep',
        url: '/fr/calendar/data/' + itemType + '/' + itemId,
        method: 'POST',
        extraParams: {
            type: 'MEP',
            allDates: allDates,
        },
        failure: function(errorObj) {
            console.log('there was an error while fetching events vacations : ' + errorObj.message );
        },
        color: '#376A1F',   // a non-ajax option
        textColor: '#FFFFFF' // a non-ajax option
    };

    var birthdaysInput = {
        id: 'birthdays',
        url: '/fr/calendar/data/' + itemType + '/' + itemId,
        method: 'POST',
        extraParams: {
            type: 'birthdays',
            allDates: allDates,
        },
        failure: function(errorObj) {
            console.log('there was an error while fetching events vacations : ' + errorObj.message );
        },
        color: '#EB144C',   // a non-ajax option
        textColor: '#FFFFFF' // a non-ajax option
    };

    var calendar = new Calendar(calendarEl, {
        plugins: [ interactionPlugin, dayGridPlugin, timeGridPlugin, listPlugin ],
        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: 'dayGridMonth,listMonth'
        },
        height: 'auto',
        weekends: false,
        weekNumbers: true,
        eventOrder: "start,-duration,allDay,icon,title",
        firstDay: 1,
        weekText: 'S',
        navLinks: false, // can click day/week names to navigate views
        editable: true,
        locale: lang,
        dayMaxEvents: true, // allow "more" link when too many events
        dateClick: function(info) {
            if((itemType == 'user' && currentUserId == itemId) || (itemType == 'squad')){
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/fr/collab/office', true);
                xhr.onload = function () {
                    if (xhr.status !== 200) {
                        alert('Request failed.  Returned status of ' + xhr.status);
                    } else {
                        var officeSource = calendar.getEventSourceById('office');
                        if(officeSource != null){
                            officeSource.refetch()
                        } else {
                            calendar.addEventSource(officeInput);
                        }
                        /*calendar.addEvent({
                            title: 'Bureau',
                            start: info.dateStr,
                            color: '#efbaff',   // a non-ajax option
                            textColor: 'black' // a non-ajax option
                        })*/
                    }
                };

                var data = new FormData();
                data.append('date', info.dateStr);
                xhr.send(data);
            }
        },
        loading: function (isLoading) {
            if (isLoading) {
                document.getElementById('loading').hidden = false;
                calendarEl.classList.add("loading");
            } else {
                document.getElementById('loading').hidden = true;
                calendarEl.classList.remove("loading");
            }
        },
        eventContent: function(arg) {
            let arrayOfDomNodes = [];

            // title event
            let titleEvent = document.createElement('div');
            if(arg.event._def.title) {
                titleEvent.innerHTML = " " + arg.event._def.title
                titleEvent.classList = "fc-event-title fc-sticky"
            }

            // image event
            let iconEventWrap = document.createElement('i')
            if(arg.event.extendedProps.icon) {
                iconEventWrap.className = 'fa fas';
                iconEventWrap.classList.add(arg.event.extendedProps.icon);
            }

            arrayOfDomNodes = [ iconEventWrap, titleEvent ]
            return { domNodes: arrayOfDomNodes }
        }
    });

    calendar.render();

    window.addEventListener("load", function(){
        calendar.addEventSource(officeInput);
        calendar.addEventSource(homeInput);
        calendar.addEventSource(vacationsInput);
        calendar.addEventSource(mepInput);
        calendar.addEventSource(birthdaysInput);
    });

    // Gestion des sources
    var checkBtn = document.getElementsByName("calendarFilter");
    for (var i = 0; i < checkBtn.length; i++) {
        checkBtn[i].onclick = function (event) {
            if(event.target.checked){
                this.parentElement.classList.add("active");
                if(this.id === 'office'){
                    calendar.addEventSource(officeInput);
                }
                if(this.id === 'home'){
                    calendar.addEventSource(homeInput);
                }
                if(this.id === 'vacations'){
                    calendar.addEventSource(vacationsInput);
                }
                if(this.id === 'birthdays'){
                    calendar.addEventSource(birthdaysInput);
                }
                if(this.id === 'mep'){
                    calendar.addEventSource(mepInput);
                }
            } else {
                this.parentElement.classList.remove("active");
                if(this.id === 'office'){
                    calendar.getEventSourceById('office').remove();
                }
                if(this.id === 'home'){
                    calendar.getEventSourceById('home').remove();
                }
                if(this.id === 'vacations'){
                    calendar.getEventSourceById('vacations').remove();
                }
                if(this.id === 'birthdays'){
                    calendar.getEventSourceById('birthdays').remove();
                }
                if(this.id === 'mep'){
                    calendar.getEventSourceById('mep').remove();
                }
            }
        };
    }

}, false);

