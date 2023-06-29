import '../scss/tuto_dashboard.scss';
import Shepherd from 'shepherd.js';

document.addEventListener('DOMContentLoaded', function(title, body) {


    const tour = new Shepherd.Tour({
        useModalOverlay: true,
        defaultStepOptions: {
            classes: 'shadow-md bg-purple-dark',
            scrollTo: true
        }
    });

    let noteDomTuto = document.getElementById('tuto');

    tour.addStep({
        id: 'step1',
        title: noteDomTuto.dataset["title"],
        text: noteDomTuto.dataset["text"],
        classes: 'example-step-extra-class',
        buttons: [
            {
                text: noteDomTuto.dataset["stop"],
                action: tour.complete,
                secondary: true,
            },
            {
                text: noteDomTuto.dataset["start"],
                action: tour.next
            }
        ]
    });

    let noteDomTemp = document.getElementById('OfficeCard');

    tour.addStep({
        id: 'step4',
        title: noteDomTemp.dataset["title"],
        text: noteDomTemp.dataset["text"],
        attachTo: {
            element: '#OfficeCard',
            on: 'bottom'
        },
        classes: 'example-step-extra-class',
        buttons: [
            {
                text: noteDomTuto.dataset["previous"],
                action: tour.back,
                secondary: true,
            },
            {
                text: noteDomTuto.dataset["next"],
                action: tour.next
            }
        ]
    });

    //Attempt to get the element using document.getElementById
    var elementMood = document.getElementById("MoodCard");

    //If it isn't "undefined" and it isn't "null", then it exists.
    if(typeof(elementMood) != 'undefined' && elementMood != null){

        noteDomTemp = document.getElementById('MoodCard');
        tour.addStep({
            id: 'step2',
            title: noteDomTemp.dataset["title"],
            text: noteDomTemp.dataset["text"],
            attachTo: {
                element: '#MoodCard',
                on: 'bottom'
            },
            classes: 'example-step-extra-class',
            buttons: [
                {
                    text: noteDomTuto.dataset["previous"],
                    action: tour.back,
                    secondary: true,
                },
                {
                    text: noteDomTuto.dataset["next"],
                    action: tour.next
                }
            ]
        });
    }

    noteDomTemp = document.getElementById('BirthdayCard');
    tour.addStep({
        id: 'step3',
        title: noteDomTemp.dataset["title"],
        text: noteDomTemp.dataset["text"],
        attachTo: {
            element: '#BirthdayCard',
            on: 'bottom'
        },
        classes: 'example-step-extra-class',
        buttons: [
            {
                text: noteDomTuto.dataset["previous"],
                action: tour.back,
                secondary: true,
            },
            {
                text: noteDomTuto.dataset["next"],
                action: tour.next
            }
        ]
    });

    noteDomTemp = document.getElementById('MepCard');
    tour.addStep({
        id: 'step5',
        title: noteDomTemp.dataset["title"],
        text: noteDomTemp.dataset["text"],
        attachTo: {
            element: '#MepCard',
            on: 'top'
        },
        classes: 'example-step-extra-class',
        buttons: [
            {
                text: noteDomTuto.dataset["previous"],
                action: tour.back,
                secondary: true,
            },
            {
                text: noteDomTuto.dataset["stop"],
                action: tour.complete
            }
        ]
    });

    tour.start();

},false);