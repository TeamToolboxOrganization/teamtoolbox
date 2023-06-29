document.addEventListener('DOMContentLoaded', function(){

    var btnTabs = document.querySelectorAll("li[data-btn-tab='true']");
    for (var i = 0; i < btnTabs.length; i++) {
        btnTabs[i].onclick = function(e){
            if(this.className != "active"){
                // Action on Tab title
                var btnTabs = document.querySelectorAll("li[data-btn-tab='true']");
                for (var i = 0; i < btnTabs.length; i++) {
                    btnTabs[i].className = "btn btn-info";
                }
                this.className = "btn btn-info active";

                // Desactive tabs content
                var tabs = document.getElementsByClassName("tab-pane")
                for (var i = 0; i < tabs.length; i++) {
                    tabs[i].classList.remove("active", "in")
                }

                // Activate Tab content
                var targetid = this.dataset.target;
                var targetTab = document.getElementById(targetid);
                targetTab.classList.add("active", "in");
            }
        };
    }

    var els = document.querySelectorAll("a[data-type='office']");
    for (var i = 0; i < els.length; i++) {
        els[i].onclick = function(e){

            var xhr = new XMLHttpRequest();
            var date = this.dataset["date"];
            xhr.open('POST', '/fr/collab/office', true);

            xhr.onload = function () {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    var userid = response.userid;
                    var action = response.action;
                    var ampm = response.ampm;
                    var ul = document.querySelector('ul[data-date="'+ date +'"]');

                    if(action == "add"){
                        var content = document.querySelector('#current_user_li').content;
                        ul.appendChild(document.importNode(content, true));
                    } else {
                        var lis = ul.getElementsByTagName('li');

                        if(action == "update"){
                            for (var i = 0; i < lis.length; i++){
                                if(lis[i].dataset["userid"] == userid){
                                    var spanAmPm = lis[i].getElementsByClassName("users-list-date");
                                    for (var j = 0; j < spanAmPm.length; j++){
                                        spanAmPm[j].innerText = ampm;
                                    }
                                }
                            }
                        }
                        if(action == "delete"){
                            for (var i = 0; i < lis.length; i++){
                                if(lis[i].dataset["userid"] == userid){
                                    lis[i].remove();
                                }
                            }
                        }
                    }

                } else {
                    alert('La mise à jour a échoué ' + xhr.status);
                }
            };

            var data = new FormData();
            data.append('date', date);
            xhr.send(data);
        };
    }
}, false);