import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', function(){

    var width, height, gradient;

    function getGradient(ctx, chartArea) {
        const chartWidth = chartArea.right - chartArea.left;
        const chartHeight = chartArea.bottom - chartArea.top;
        if (gradient === null || width !== chartWidth || height !== chartHeight) {
            // Create the gradient because this is either the first render
            // or the size of the chart has changed
            width = chartWidth;
            height = chartHeight;
            gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
            gradient.addColorStop(0, "#904ee2");
            gradient.addColorStop(0.5, "#63ba3c");
            gradient.addColorStop(1, "#4bade8");
        }

        return gradient;
    }

    const data = {
        labels: [],
        datasets: [{
            label: 'Mindset',
            backgroundColor: '#064d9d',
            tension: 0.5,
            pointBorderWidth: 5,
            data: [],
            borderColor: function (context) {
                const chart = context.chart;
                const {ctx, chartArea} = chart;

                if (!chartArea) {
                    // This case happens on initial chart load
                    return null;
                }
                return getGradient(ctx, chartArea);
            },
        }]
    };

    const config = {
        type: 'line',
        data,
        options: {}
    };

    var chartElement = document.getElementById("div_chart");
    var itemId = chartElement.dataset["itemid"];

    var myChart = new Chart(
        chartElement,
        config
    );

    // Get user list
    var xhr = new XMLHttpRequest();

    if (itemId == 0) {
        xhr.open('GET', 'mindset/history/');
    } else {
        xhr.open('GET', 'mindset/history/' + itemId);
    }

    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onload = function () {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            myChart.data.labels = response.labels;
            myChart.data.datasets[0].data = response.data; // or you can iterate for multiple datasets
            myChart.update(); // finally update our chart
        } else {
            alert('Request failed.  Returned status of ' + xhr.status);
        }
    };
    xhr.send();

}, false);