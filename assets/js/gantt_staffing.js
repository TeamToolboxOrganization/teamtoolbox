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

    var qxPreviousStart = new Date(2023, 1, 1);
    var q1Start = new Date(2023, 0, 2);
    var q2Start = new Date(2023, 4, 10);
    var q3Start = new Date(2023, 6, 19);
    var q4Start = new Date(2023, 9, 25);
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

    function calculateResourceLoad(tasks, scale) {
        var step = scale.unit;
        var timegrid = {};

        for (var i = 0; i < tasks.length; i++) {
            var task = tasks[i];

            var currDate = gantt.date[step + "_start"](new Date(task.start_date));

            while (currDate < task.end_date) {

                var date = currDate;
                currDate = gantt.date.add(currDate, 1, step);

                if (!gantt.isWorkTime({date: date, task: task})) {
                    continue;
                }

                var timestamp = date.valueOf();
                if (!timegrid[timestamp])
                    timegrid[timestamp] = 0;

                timegrid[timestamp] += 1;
            }
        }

        var timetable = [];
        var start, end;
        for (var i in timegrid) {
            start = new Date(i * 1);
            end = gantt.date.add(start, 1, step);
            timetable.push({
                start_date: start,
                end_date: end,
                value: timegrid[i]
            });
        }

        return timetable;
    }


    var renderResourceLine = function (resource, timeline) {
        var tasks = gantt.getTaskBy("owner", resource.id);
        var timetable = calculateResourceLoad(tasks, timeline.getScale());

        var row = document.createElement("div");

        for (var i = 0; i < timetable.length; i++) {

            var day = timetable[i];

            var css = "";
            if (day.value <= 1) {
                css = "gantt_resource_marker gantt_resource_marker_ok";
            } else {
                css = "gantt_resource_marker gantt_resource_marker_overtime";
            }

            var sizes = timeline.getItemPosition(resource, day.start_date, day.end_date);
            var el = document.createElement('div');
            el.className = css;

            el.style.cssText = [
                'left:' + sizes.left + 'px',
                'width:' + sizes.width + 'px',
                'position:absolute',
                'height:' + (gantt.config.row_height - 1) + 'px',
                'line-height:' + sizes.height + 'px',
                'top:' + sizes.top + 'px'
            ].join(";");

            el.innerHTML = day.value;
            row.appendChild(el);
        }
        return row;
    };

    var resourceLayers = [
        renderResourceLine,
        "taskBg"
    ];

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
            /*{name: "start_date", align: "center", width: 80, resize: true},
            {name: "end_date", label:"End", align: "center", width: 100,
                template: function(task){
                    return formatEndDate(task.end_date, gantt.templates.date_grid);
                }, resize:true},*/
            {
                name: "owner", align: "center", width: 60, label: "Dev", template: function (task) {
                    var store = gantt.getDatastore("resources");
                    var owner = store.getItem(task.owner);
                    if (owner) {
                        return owner.label;
                    } else {
                        return "N/A";
                    }
                }
            },
            {name: "duration", width: 50, align: "center"},
            {name: "focus", align: "center", width: 60, label: "Focus", template: function (task) {
                    if(task.jiratype !== "epic"){
                        return "";
                    }
                    return "<a href='/fr/gantt/?view=staffing&epicId="+ task.id + "'>Focus</a>";
                }
            },
            {name: "add", width: 44}
        ]
    };

    var resourcePanelConfig = {
        columns: [
            {
                name: "name", label: "Name", template: function (resource) {
                    return resource.label;
                }
            },
            {
                name: "workload", label: "Workload", template: function (resource) {
                    var tasks = gantt.getTaskBy("owner", resource.id);

                    var totalDuration = 0;
                    for (var i = 0; i < tasks.length; i++) {
                        totalDuration += tasks[i].duration;
                    }
                    return (totalDuration || 0) * 8 + "";
                }
            }
        ]
    };

    // recalculate progress of summary tasks when the progress of subtasks changes
    (function dynamicProgress() {

        function calculateSummaryProgress(task) {
            if (task.type != gantt.config.types.project)
                return task.progress;
            var totalToDo = 0;
            var totalDone = 0;
            gantt.eachTask(function (child) {
                if (child.type != gantt.config.types.project) {
                    totalToDo += child.duration;
                    totalDone += (child.progress || 0) * child.duration;
                }
            }, task.id);
            if (!totalToDo) return 0;
            else return totalDone / totalToDo;
        }

        function refreshSummaryProgress(id, submit) {
            if (!gantt.isTaskExists(id))
                return;

            var task = gantt.getTask(id);
            var newProgress = calculateSummaryProgress(task);

            if (newProgress !== task.progress) {
                task.progress = newProgress;

                if (!submit) {
                    gantt.refreshTask(id);
                } else {
                    gantt.updateTask(id);
                }
            }

            if (!submit && gantt.getParent(id) !== gantt.config.root_id) {
                refreshSummaryProgress(gantt.getParent(id), submit);
            }
        }


        gantt.attachEvent("onParse", function () {
            gantt.eachTask(function (task) {
                task.progress = calculateSummaryProgress(task);
            });
        });

        gantt.attachEvent("onAfterTaskUpdate", function (id) {
            refreshSummaryProgress(gantt.getParent(id), true);
        });

        gantt.attachEvent("onTaskDrag", function (id) {
            refreshSummaryProgress(gantt.getParent(id), false);
        });
        gantt.attachEvent("onAfterTaskAdd", function (id) {
            refreshSummaryProgress(gantt.getParent(id), true);
        });


        (function () {
            var idParentBeforeDeleteTask = 0;
            gantt.attachEvent("onBeforeTaskDelete", function (id) {
                idParentBeforeDeleteTask = gantt.getParent(id);
            });
            gantt.attachEvent("onAfterTaskDelete", function () {
                refreshSummaryProgress(idParentBeforeDeleteTask, true);
            });
        })();
    })();

    gantt.templates.task_class = function (start, end, task) {
        return task.jiratype;
    };

    gantt.templates.progress_text = function (start, end, task) {
        return "<span style='text-align:left !important; color: #FFFFFF'>" + Math.round(task.progress * 100) + "% </span>";
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
            {resizer: true, width: 1},
            {
                config: resourcePanelConfig,
                cols: [
                    {
                        view: "grid",
                        id: "resourceGrid",
                        group:"grids",
                        bind: "resources",
                        scrollY: "resourceVScroll"
                    },
                    {resizer: true, width: 1, group:"vertical"},
                    {
                        view: "timeline",
                        id: "resourceTimeline",
                        bind: "resources",
                        bindLinks: null,
                        layers: resourceLayers,
                        scrollX: "scrollHor",
                        scrollY: "resourceVScroll"
                    },
                    {view: "scrollbar", id: "resourceVScroll", group:"vertical"}
                ]
            },
        ]
    };

    var resourcesStore = gantt.createDatastore({
        name: "resources",
        initItem: function (item) {
            item.id = item.key || gantt.uid();
            return item;
        }
    });

    var tasksStore = gantt.getDatastore("task");
    tasksStore.attachEvent("onStoreUpdated", function (id, item, mode) {
        resourcesStore.refresh();
    });

    // Gestion de la popup d'update
    gantt.locale.labels.section_squad = "Squad";
    gantt.locale.labels.section_owner = "Développeur";
    gantt.locale.labels.section_deadline = "Deadline";
    gantt.locale.labels.deadline_enable_button = 'Ajouter';
    gantt.locale.labels.deadline_disable_button = 'Enlever';

    gantt.config.lightbox.sections = [
        {name: "squad", height:22, map_to:"squad", type:"select", options: [
                {key:'N/A', label: "N/A"},
                {key:'Squad1', label: "Squad1"},
                {key:'Squad2', label: "Squad2"}
            ]},
        {name: "owner", type: "select", map_to: "owner", options:gantt.serverList("users")},
        {name: "time", type: "duration", map_to: "auto"},
        {name: "description", height: 70, map_to: "text", type: "textarea"},
    ];

    gantt.config.lightbox.project_sections = [
        {name: "type", type: "typeselect", map_to: "type"},
        {name: "time", map_to: "auto", type: "duration"},
        {
            name: "deadline", map_to: {start_date: "deadline"},
            type: "duration_optional",
            button: true,
            single_date: true
        },
        {name: "description", height: 70, map_to: "text", type: "textarea"},
    ];

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

            if (filter_input.parentElement.classList.contains('active')) {
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
    gantt.ext.zoom.setLevel("week");

    // Init
    gantt.init("gantt_here");

    // Get user list
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'users');
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onload = function() {
        if (xhr.status === 200) {
            var usersList = JSON.parse(xhr.responseText);
            resourcesStore.parse(usersList);
        }
        else {
            alert('Request failed.  Returned status of ' + xhr.status);
        }
    };
    xhr.send();


    //gantt.parse(taskData);
    if(epicId){
        gantt.load("/fr/gantt/data?open=true&epicId=" + epicId);
    } else {
        gantt.load("/fr/gantt/data?open=true");
    }

    //gantt.load("/fr/gantt/loader/jira");

    // Add data
    //gantt.parse(data);

    function updateTempIdTask(result) {
        if(result.type == 'task'){
            gantt.changeTaskId(result.tmpid, result.tid);
        } else {
            gantt.changeLinkId(result.tmpid, result.tid);
        }
    }

    // entity - "task"|"link"
    // action - "create"|"update"|"delete"
    // data - an object with task or link data
    // id – the id of a processed object (task or link)
    var dp = gantt.createDataProcessor(function(entity, action, data, id) {
        switch(action) {
            case "create":
                return gantt.ajax.post(
                    "control/" + entity,
                    data
                ).then(function(response){
                    var res = JSON.parse(response.responseText);
                    updateTempIdTask(res);
                });
                break;
            case "update":
                return gantt.ajax.post(
                    "control/" + entity + "/" + id,
                    data
                );
                break;
            case "delete":
                return gantt.ajax.post(
                    "control/" + entity + "/delete/" + id
                );
                break;
        }
    });

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

    var els = document.querySelectorAll("button[name='reload']");
    for (var i = 0; i < els.length; i++) {
        els[i].onclick = function(e){
            if(e.target.disabled == false){
                e.target.disabled = true;
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'loader/jira/');
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.onload = function() {
                    e.target.disabled = false;
                    if (xhr.status === 200) {
                        alert('Chargement terminé : Veuillez recharger la page');
                    }
                    else {
                        alert('Request failed.  Returned status of ' + xhr.status);
                    }
                };
                xhr.send();
            }
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

