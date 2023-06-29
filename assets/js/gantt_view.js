import '../scss/gantt.scss';

import { gantt } from 'dhtmlx-gantt';

document.addEventListener('DOMContentLoaded', function(){

    gantt.plugins({
        marker: true,
        tooltip: true,
    });

    gantt.i18n.setLocale("fr");

    var ganttElement = document.getElementById("gantt_here");
    var viewType = ganttElement.dataset["viewtype"];
    var epicId = ganttElement.dataset["epicid"];

    var qxPreviousStart = new Date(2021, 1, 1);
    var q1Start = new Date(2023, 0, 2);
    var q2Start = new Date(2023, 3, 12);
    var q3Start = new Date(2023, 5, 21);
    var q4Start = new Date(2023, 8, 27);
    var qxNextStart = new Date(2024, 1, 1);

    // Custom quarter dates
    gantt.date.customQuarter_start = function(date){
        var next = new Date(date);

        if (next<q1Start){
            return qxPreviousStart;
        }
        if (next<q2Start){
            return q1Start;
        }
        if (next<q3Start){
            return q2Start;
        }
        if (next<q4Start){
            return q3Start;
        }
        if (next<qxNextStart){
            return q4Start;
        }
        return qxNextStart;
    };

    gantt.date.add_customQuarter = function(date, inc){
        if(date < q1Start){
            return q1Start;
        }
        if(date < q2Start){
            return q2Start;
        }
        if(date < q3Start){
            return q3Start;
        }
        if(date < q4Start){
            return q4Start;
        }

        return gantt.date.add(date, inc, "year");
    };

    function customQuarterLabel(date){
        var tmpDate = gantt.date.customQuarter_start(date);
        if(tmpDate == q1Start){
            return "Q1 2023";
        }
        if(tmpDate == q2Start){
            return "Q2 2023";
        }
        if(tmpDate == q3Start){
            return "Q3 2023";
        }
        if(tmpDate == q4Start){
            return "Q4 2023";
        }
        return "Qx";
    };


    var zoomConfig = {
        levels: [
            {
                name:"day",
                scale_height: 27,
                min_column_width:80,
                scales:[
                    {unit: "day", step: 1, format: "%d %M"}
                ]
            },
            {
                name:"week",
                scale_height: 50,
                min_column_width:50,
                scales:[
                    {unit: "week", step: 1, format: function (date) {
                            var weekNum = gantt.date.date_to_str("%W")(date);
                            return "S" + weekNum;
                        }},
                    {unit: "day", step: 1, format: "%d %M"}
                ]
            },
            {
                name:"quarter",
                height: 50,
                min_column_width:90,
                scales:[
                    {unit: "customQuarter", step: 1, format:customQuarterLabel},
                    {unit: "week", format: "S%W"},
                ]
            },
            {
                name:"year",
                scale_height: 50,
                min_column_width: 30,
                scales:[
                    {unit: "year", step: 1, format: "%Y"},
                    {unit: "week", format: "S%W"},
                    {
                        unit: "quarter", step: 1, format: function (date) {
                            var dateToStr = gantt.date.date_to_str("%M");
                            var endDate = gantt.date.add(gantt.date.add(date, 3, "month"), -1, "day");
                            return dateToStr(date) + " - " + dateToStr(endDate);
                        }
                    }
                ]
            }
        ]
    };

    function formatEndDate(date, template){
        // get 23:59:59 instead of 00:00:00 for the end date
        return template(new Date(date.valueOf() - 1));
    }

    var mainGridConfig = {
        columns: [
            {name: "text", tree: true, width: 200, template: function(task){
                if(task.key == null || task.key == ""){
                    return task.text;
                }
                return "<a target='_blank' href='https://xxxxxxxx.atlassian.net/browse/"+ task.key + "'>" + task.key + "</a>";
                }, resize: true},
            {name: "start_date", align: "center", width: 80, resize: true},
            {name: "deadline", label:"Deadline", align: "center", width: 100,
                template: function(task){
                    return formatEndDate(task.end_date, gantt.templates.date_grid);
                }, resize:true},
            {
                name: "squad", align: "center", width: 60, label: "Squad", template: function (task) {
                    if (task.squad) {
                        return task.squad;
                    } else {
                        return "";
                    }
                }
            },
            {name: "duration", width: 50, align: "center"},
        ]
    };

    gantt.templates.task_class = function (start, end, task) {
        return task.jiratype;
    };

    gantt.templates.grid_folder = function(item) {
        return "<div class='gantt_tree_icon gantt_"+ item.jiratype +"'></div>";
    };

    gantt.templates.grid_file = function(item) {
        return "<div class='gantt_tree_icon gantt_"+ item.jiratype +"'></div>";
    };

    gantt.templates.rightside_text = function (start, end, task) {
        if (task.deadline) {
            var deadlineDate = new Date(task.deadline);
            var deadlineDateNext = deadlineDate.setDate(deadlineDate.getDate() + 1);
            if (end > deadlineDateNext) {
                var overdue = Math.ceil(Math.abs((end.getTime() - deadlineDate.getTime()) / (24 * 60 * 60 * 1000)));
                var text = "<b>Retard: " + overdue + " jours</b>";
                return text;
            }
        }
    };

    gantt.config.layout = {
        css: "gantt_container",
        rows: [
            {
                cols: [
                    {view: "grid", group:"grids", config: mainGridConfig, scrollY: "scrollVer"},
                    {resizer: true, width: 1, group:"vertical"},
                    {view: "timeline", id: "timeline", scrollX: "scrollHor", scrollY: "scrollVer"},
                    {view: "scrollbar", id: "scrollVer", group:"vertical"}
                ]
            },
            {view: "scrollbar", id: "scrollHor"},
        ]
    };

    gantt.attachEvent("onTaskLoading", function (task) {
        if (task.deadline)
            task.deadline = gantt.date.parseDate(task.deadline, "xml_date");
        return true;
    });

    // Gestion des filtres
    var filter_inputs = document.getElementById("filters_wrapper").getElementsByTagName("input");
    for (var i = 0; i < filter_inputs.length; i++) {

        // attach event handler to update filters object and refresh data (so filters will be applied)
        filter_inputs[i].onchange = function () {
            if (this.parentElement.classList.contains('active')){
                this.parentElement.classList.remove('active')
            } else{
                this.parentElement.classList.add('active')
            }
            gantt.refreshData();
        }
    }

    function hasSquad(parent, squad) {
        if (gantt.getTask(parent).squad == squad)
            return true;

        var child = gantt.getChildren(parent);
        for (var i = 0; i < child.length; i++) {
            if (hasSquad(child[i], squad))
                return true;
        }
        return false;
    }

    gantt.attachEvent("onBeforeTaskDisplay", function (id, task) {
        for (var i = 0; i < filter_inputs.length; i++) {
            var filter_input = filter_inputs[i];

            if (filter_input.checked) {
                if (hasSquad(id, filter_input.name)) {
                    return true;
                }
            }
        }
        return false;
    });


    gantt.config.order_branch = "marker";
    gantt.config.duration_unit = "day";

    gantt.templates.scale_cell_class = function(date){
        var dateTmp = gantt.date.customQuarter_start(date);
        if(dateTmp == q1Start){
            return "quarterColor1";
        }
        if(dateTmp == q2Start){
            return "quarterColor2";
        }
        if(dateTmp == q3Start){
            return "quarterColor3";
        }
        if(dateTmp == q4Start){
            return "quarterColor4";
        }
    };

    gantt.templates.timeline_cell_class = function (task, date) {
        if (!gantt.isWorkTime(date, 'day')) {
            return ("no_work_hour");
        }
        return "";
    };

    gantt.setWorkTime({day: 6, hours: false});// make Saturday day-off
    gantt.setWorkTime({day: 7, hours: false});// make Sunday day-off

    // Get holiday dates
    var xhrHoliday = new XMLHttpRequest();
    xhrHoliday.open('GET', 'holidays');
    xhrHoliday.setRequestHeader('Content-Type', 'application/json');
    xhrHoliday.onload = function() {
        if (xhrHoliday.status === 200) {
            var holidaysList = JSON.parse(xhrHoliday.responseText);
            holidaysList.forEach(element => gantt.setWorkTime({hours:false, date: new Date(element.value)}));
        }
        else {
            alert('Request failed.  Returned status of ' + xhrHoliday.status);
        }
    };
    xhrHoliday.send();

    //gantt.config.autosize = "xy";
    gantt.config.resize_rows = true;
    gantt.config.work_time = true;  // removes non-working time from calculations
    gantt.config.skip_off_time = true;   // hides non-working time in the chart

    gantt.ext.zoom.init(zoomConfig);
    gantt.ext.zoom.setLevel("quarter");
    gantt.config.readonly = true;

    // Init
    gantt.init("gantt_here");

    gantt.i18n.setLocale("fr");

    if(epicId){
        gantt.load("/fr/gantt/data/epic/" + epicId);
    } else {
        gantt.load("/fr/gantt/data");
    }

    // Gestion du Zoom
    var radios = document.getElementsByName("scale");
    for (var i = 0; i < radios.length; i++) {
        radios[i].onclick = function (event) {

            for (var i = 0; i < radios.length; i++) {
                radios[i].parentElement.classList.remove("active");
            }
            this.parentElement.classList.add("active");
            gantt.ext.zoom.setLevel(event.target.value);
        };
    }

    gantt.ext.zoom.attachEvent("onAfterZoom", function(level, config){
        for (var i = 0; i < radios.length; i++) {
            radios[i].parentElement.classList.remove("active");
        }
        var selectedEl = document.querySelector(".gantt_radio[value='" +config.name+ "']")
        selectedEl.checked = true;
        selectedEl.parentElement.classList.add("active");
    });

    var els = document.querySelectorAll("button[name='btnZoomIn']");
    for (var i = 0; i < els.length; i++) {
        els[i].onclick = function(e){
            gantt.ext.zoom.zoomIn();
        };
    }

    var els = document.querySelectorAll("button[name='btnZoomOut']");
    for (var i = 0; i < els.length; i++) {
        els[i].onclick = function(e){
            gantt.ext.zoom.zoomOut();
        };
    }

    var els = document.querySelectorAll("button[name='btnExpandAll']");
    for (var i = 0; i < els.length; i++) {
        els[i].onclick = function(e){
            gantt.eachTask(function(task){
                task.$open = true;
            });
            gantt.render();
        };
    }

    var els = document.querySelectorAll("button[name='btnCollapseAll']");
    for (var i = 0; i < els.length; i++) {
        els[i].onclick = function(e){
            gantt.eachTask(function(task){
                task.$open = false;
            });
            gantt.render();
        };
    }

    // Gestion du marker
    var dateToStr = gantt.date.date_to_str(gantt.config.task_date);

    var id = gantt.addMarker({
        start_date: new Date(),
        css: "today",
        title:dateToStr(new Date())
    });
    setInterval(function(){
        var today = gantt.getMarker(id);
        today.start_date = new Date();
        today.title = dateToStr(today.start_date);
        gantt.updateMarker(id);
    }, 1000*60);

    // Shortcut configuration
    document.addEventListener("keydown", logKey);
    function logKey(event) {
        if (event.key === '-' || event.keyCode === 109) {
            gantt.ext.zoom.zoomOut();
            return;
        }
        if (event.key === '+' || event.keyCode === 107) {
            gantt.ext.zoom.zoomIn();
            return;
        }
    }

}, false);

