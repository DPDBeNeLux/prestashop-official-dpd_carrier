/**
 * using a namespace.
 */

var dpdAdminConfig = new function() {
    this.currentstep = 0;
    
    var serialize = function (form) {
        if (!form || form.nodeName !== "FORM") {
            return;
        }
        var i, j,
            obj = {};
        for (i = form.elements.length - 1; i >= 0; i = i - 1) {
            if (form.elements[i].name === "") {
                continue;
            }
            switch (form.elements[i].nodeName) {
            case 'INPUT':
                switch (form.elements[i].type) {
                case 'text':
                case 'hidden':
                case 'password':
                case 'button':
                case 'reset':
                case 'submit':
                    obj[form.elements[i].name] = encodeURIComponent(form.elements[i].value);
                    break;
                case 'checkbox':
                case 'radio':
                    if (form.elements[i].checked) {
                        obj[form.elements[i].name] = encodeURIComponent(form.elements[i].value);
                    }
                    break;
                case 'file':
                    break;
                }
                break;
            case 'TEXTAREA':
                obj[form.elements[i].name] = encodeURIComponent(form.elements[i].value);
                break;
            case 'SELECT':
                switch (form.elements[i].type) {
                case 'select-one':
                    obj[form.elements[i].name] = encodeURIComponent(form.elements[i].value);
                    break;
                case 'select-multiple':
                    for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1) {
                        if (form.elements[i].options[j].selected) {
                            obj[form.elements[i].name] = encodeURIComponent(form.elements[i].options[j].value);
                        }
                    }
                    break;
                }
                break;
            case 'BUTTON':
                switch (form.elements[i].type) {
                case 'reset':
                case 'submit':
                case 'button':
                    obj[form.elements[i].name] = encodeURIComponent(form.elements[i].value);
                    break;
                }
                break;
            }
        }
        return obj;
    };
    
    var scrollTo = function (element, to, duration) {
        if (duration <= 0) return;
        var difference = to - element.scrollTop;
        var perTick = difference / duration * 10;

        setTimeout(function () {
            element.scrollTop = element.scrollTop + perTick;
            if (element.scrollTop === to) return;
            scrollTo(element, to, duration - 10);
        }, 10);
    };

    this.scrollToId = function (id) {
        var element = document.getElementById(id);
        scrollTo( dpdAdminConfigValues.scrollContainer, element.offsetTop + dpdAdminConfigValues.scrollOffset, 600);
        return;
    };
    
    /**
     * @Todo: clean this up...
     */
    this.dpdAccountToggleStep = function (event){
        var target = event.target;
        var stepOneBtn = document.getElementById('dpd-account');
        var stepTwoBtn = document.getElementById('dpd-dis-account');
        var stepOne = document.getElementById('dpd-account-step-one');
        var stepTwo = document.getElementById('dpd-account-step-two');
        var stepThree = document.getElementById('dpd-account-step-three');
        var saveBtn = document.getElementById('dpd-save');
        var formTitle = document.getElementById('dpd-account-form-title');
        switch(target.id){
            case 'dpd-account':
                if(target.checked) {
                    this.currentstep = 1;
                    stepOne.style.display = "none";
                    stepTwo.style.display = "block";
                    stepThree.style.display = "none";
                } else {
                    stepOne.style.display = "block";
                    stepTwo.style.display = "none";
                    stepTwoBtn.checked = false;
                    formTitle.innerHTML = "Request form";
                    saveBtn.innerHTML = "Send";
                    saveBtn.className = "active";
                    stepThree.style.display = "none";
                }
                break;
            case 'dpd-dis-account':
            case 'dpd-menu-configuration':
                if(target.tagName == "A" || target.checked) {
                    this.currentstep = 2;
                    stepOneBtn.checked = true;
                    stepTwoBtn.checked = true;
                    stepOne.style.display = "none";
                    stepTwo.style.display = "block";
                    formTitle.innerHTML = "DIS configuration";
                    saveBtn.innerHTML = "Save";
                    saveBtn.className = "active";
                    stepThree.style.display = "block";
                } else {
                    formTitle.innerHTML = "Request form";
                    saveBtn.innerHTML = "Send";
                    saveBtn.className = "active";
                    stepThree.style.display = "none";
                }
                break;
        }
        this.scrollToId('dpd-starting');
    };
    
    this.loadStates = function() {
        var xhr = new XMLHttpRequest();
        
        var params = "states=true";
        
        xhr.open("POST", dpdAdminConfigValues.configControllerUrl, true);
        
        //Send the proper header information along with the request
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.setRequestHeader("Content-length", params.length);
        xhr.setRequestHeader("Connection", "close");

        xhr.onload = function (e) {
          if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                  result = JSON.parse(xhr.responseText);
                  parseStates(result);
                } catch (e) {
                  displayError(xhr.responseText);
                  return;
                }
                loadSettings();
            } else {
              console.error(xhr.statusText);
            }
          }
        };
        xhr.onerror = function (e) {
          console.error(xhr.statusText);
        };
        xhr.send(params);
    };
    
    var parseStates = function(data) {
        
        var select = document.getElementById('dpd-select-state');
        
        for (var key in data['success']) {
            var newOption = document.createElement('option');
            newOption.value = key;
            newOption.innerHTML = data['success'][key];
            select.appendChild(newOption);
        }
    }
    
    this.submitFormToController = function(formId, controllerUrl) {
        var form = document.getElementById(formId);
        var xhr = new XMLHttpRequest();
        
        var params = "step=" + encodeURIComponent(this.currentstep);//JSON.stringify();
        var sForm = serialize(form);
        for(var key in sForm) {
          params += "&" + key + "=" + sForm[key];
        }
        xhr.open("POST", controllerUrl, true);
        
        //Send the proper header information along with the request
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        //xhr.setRequestHeader("Content-length", params.length);
        //xhr.setRequestHeader("Connection", "close");

        xhr.onload = function (e) {
          if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                  result = JSON.parse(xhr.responseText);
                  parseResult(result);
                } catch (e) {
                  displayError(xhr.responseText);
                  return;
                }
            } else {
              console.error(xhr.statusText);
            }
          }
        };
        xhr.onerror = function (e) {
          console.error(xhr.statusText);
        };
        xhr.send(params);
    };
    
    var displayError = function(data) {
        var container = document.getElementById("dpd-error");
        parseMessages(container, {"error" : data});
    };
    
    var parseResult = function(data) {
      for(var containerKey in data) {
        var container = document.getElementById("dpd-" + containerKey);
        parseMessages(container, data[containerKey]);
      }
    }
    
    var parseMessages = function(container, messages) {
      
      if(Object.keys(messages).length > 0) {
        var output = "<ul>";
        for(var key in messages) {
          output += "<li>" + messages[key] + "</li>";
        }
        output += "</ul>";
        container.innerHTML = output;
        container.style.display = "block";
      } else {
        container.innerHTML = "";
        container.style.display = "none";
      }
      
    };
    
    this.loadStats = function() {
        var xhr = new XMLHttpRequest();
        
        xhr.open("POST", dpdAdminConfigValues.statsControllerUrl, true);
        
        xhr.onload = function (e) {
          if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                  result = JSON.parse(xhr.responseText);
                  parseStats(result);
                } catch (e) {
                  console.error(xhr.responseText);
                  return;
                }
            } else {
              console.error(xhr.statusText);
            }
          }
        };
        xhr.onerror = function(e) {
          console.error(xhr.statusText);
        };
        xhr.send(null);
    };
    
    var parseStats = function (data) {
      var totalCheckout = 0;
      var totalLabels = 0;
      for(var service in data) {
        document.getElementById("dpd-" + service + "-checkout").innerHTML = data[service]["checkout"];
        document.getElementById("dpd-" + service + "-labels").innerHTML = data[service]["labels"];
        
        totalCheckout += data[service]["checkout"];
        totalLabels += data[service]["labels"];
      }
      
      document.getElementById("dpd-status-total-checkout").innerHTML = totalCheckout;
      document.getElementById("dpd-status-total-labels").innerHTML = totalLabels;
    };

    var loadSettings = function() {
        var xhr = new XMLHttpRequest();
        
        var params = "settings=true";
        
        xhr.open("POST", dpdAdminConfigValues.configControllerUrl, true);
        
        //Send the proper header information along with the request
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.setRequestHeader("Content-length", params.length);
        xhr.setRequestHeader("Connection", "close");

        xhr.onload = function (e) {
          if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                  result = JSON.parse(xhr.responseText);
                  parseSettings(result);
                } catch (e) {
                  displayError(xhr.responseText);
                  return;
                }
            } else {
              console.error(xhr.statusText);
            }
          }
        };
        xhr.onerror = function (e) {
          console.error(xhr.statusText);
        };
        xhr.send(params);
    };
    
    var parseSettings = function(data) {
        for (var key in data['success']) {
            var elements = document.getElementsByName(key);
            for (var elementKey in elements) {
                switch (elements[elementKey].type) {
                    case 'checkbox':
                        elements[elementKey].checked = data['success'][key];
                        break;
                    default:
                        elements[elementKey].value = data['success'][key];
                }
            }
        }
    }
}

 
window.addEventListener("load", dpdAdminConfig.loadStats, false);
window.addEventListener("load", dpdAdminConfig.loadStates, false);