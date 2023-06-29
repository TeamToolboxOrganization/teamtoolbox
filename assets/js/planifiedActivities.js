import Chart from 'chart.js/auto';
document.addEventListener('DOMContentLoaded', function(){

    const options = {
        //cutoutPercentage: 40,
        responsive: false,
        plugins:{
            legend:{
                display: false,
            }
        }
    }

    const dataWeek = {
        labels: [],
        datasets: [{
            label: "Nombre d'heure planifiées",
            data: [],
            backgroundColor: [],
            borderColor: [],
            borderWidth: 1
        }]
    };

    const dataMonth = {
        labels: [],
        datasets: [{
            label: "Nombre d'heure planifiées",
            data: [],
            backgroundColor: [],
            borderColor: [],
            borderWidth: 1
        }]
    };

    const configWeek = {
        type: 'doughnut',
        data: dataWeek,
        options: options,
    }

    const configMonth = {
        type: 'doughnut',
        data: dataMonth,
        options: options,
    }

    var ctxWeek = document.getElementById("div_chart_current_week");
    var ctxMonth = document.getElementById("div_chart_current_month");

    var loader = document.getElementsByClassName("loader");

    var weekChart = new Chart(ctxWeek,configWeek);
    var monthChart = new Chart(ctxMonth,configMonth)

    var weekButton = document.getElementById("weekButton");
    var monthButton = document.getElementById("monthButton");


    weekButton.addEventListener("click", function(e) {
        e.preventDefault();
        getCSV("week");

    })

    monthButton.addEventListener("click", function(e) {
        e.preventDefault();
        getCSV("month");
    })

    var xhr = new XMLHttpRequest();

    //requete pour le donut du mois courrant
    xhr.open('GET', '../calendar/planification/week');
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onload = function () {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            weekChart.data.labels = response.labels;
            weekChart.data.datasets[0].data = response.data; // or you can iterate for multiple datasets
            // console.log(response.size);
            weekChart.data.datasets[0].borderColor = hexToRGBA(response.color,true);
            weekChart.data.datasets[0].backgroundColor = hexToRGBA(response.color,false);
            weekChart.update();
            weekButton.style.display = null;
            if( response.error != ""){
                alert(response.error);
            }
        } else {
            console.log('Request failed.  Returned status of ' + xhr.status);
        }
        loader[0].style.display="none";
        ctxWeek.style.display = "block";
        var xhrMonth = new XMLHttpRequest();
        //requete pour le donut du mois courrant
        xhrMonth.open('GET', '../calendar/planification/month');
        xhrMonth.setRequestHeader('Content-Type', 'application/json');
        xhrMonth.onload = function () {
            if (xhrMonth.status === 200) {
                var response = JSON.parse(xhrMonth.responseText);
                monthChart.data.labels = response.labels;
                monthChart.data.datasets[0].data = response.data; // or you can iterate for multiple datasets
                monthChart.data.datasets[0].borderColor = hexToRGBA(response.color,true);
                monthChart.data.datasets[0].backgroundColor = hexToRGBA(response.color,false);
                monthChart.update();
                monthButton.style.display = null;
                if( response.error != ""){
                    console.log(response.error);
                }
            } else {
                console.log('Request failed.  Returned status of ' + xhrMonth.status);
            }
            weekButton.disabled = null;
            monthButton.disabled = null;
            loader[1].style.display="none";
            ctxMonth.style.display = "block";
        };
        xhrMonth.send();
    };
    xhr.send();

}, false);

function hexToRGBA(tab,opacity) {
    var result = [];
    for(var i=0;i<tab.length;i++){
        var hex = tab[i];
        var r = parseInt(hex.slice(1,3),16);
        var g = parseInt(hex.slice(3,5),16);
        var b = parseInt(hex.slice(5,7),16);
        if (opacity){
            result.push("rgba("+r+","+g+","+b+",1)");
        }
        else{
            result.push("rgba("+r+","+g+","+b+",0.5)");
        }
    }
    return result;
}

function arrayToCsv(data){
    return data.map(row =>
        row
            .map(String)  // convert every value to String
            .map(v => v.replaceAll('"', '""'))  // escape double colons
            .map(v => `"${v}"`)  // quote it
            .join(',')  // comma-separated
    ).join('\r\n');  // rows starting on new lines
}

function getCSV(duration) {
    var xhr = new XMLHttpRequest();
    var buttonWeek = document.getElementById("weekButton");
    var buttonMonth = document.getElementById("monthButton");
    var loading = document.getElementsByClassName("loading");
    if(duration == "week"){
        buttonWeek.style.display="none";
        buttonMonth.disabled="true";
        loading[0].style.display="block";
    }else{
        buttonMonth.style.display="none";
        buttonWeek.disabled="true";
        loading[1].style.display="block";
    }

    xhr.open('GET', '../calendar/planification/'+duration+'/csv');
    xhr.responseType = 'blob';
    xhr.onload = function () {
        if(duration == "week"){
            loading[0].style.display="none";
            buttonWeek.style.display=null;
            buttonMonth.disabled=null;
        }else{
            loading[1].style.display="none";
            buttonWeek.disabled=null;
            buttonMonth.style.display=null;
        }

        if (xhr.status !== 200) {
            alert('Request failed.  Returned status of ' + xhr.status);
        }else{
            var blob = this.response;
            var url = URL.createObjectURL(blob);
            var filename = "";
            var disposition = xhr.getResponseHeader('Content-Disposition');
            if (disposition && disposition.indexOf('attachment') !== -1) {
                var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                var matches = filenameRegex.exec(disposition);
                if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
            }
            var link = document.createElement('a');
            link.style.display = 'none';
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }
    };
    xhr.send();
}