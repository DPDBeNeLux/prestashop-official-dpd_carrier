var DPD = new function(){
  var selectXMLHttpObj = function(){
    if (window.XMLHttpRequest) {
      // code for IE7+, Firefox, Chrome, Opera, Safari
      return new XMLHttpRequest();
    } else {
      // code for IE6, IE5
      return new ActiveXObject("Microsoft.XMLHTTP");
    }
  }
  
  var pad = function(num, size) {
    var s = num+"";
    while (s.length < size) s = "0" + s;
    return s;
  }
  
  this.locator = function (objConfig){
    if(typeof objConfig.containerId == 'undefined') console.error("containerId is mandatory");
    if(typeof objConfig.controller == 'undefined') console.error("controller is mandatory");
    
    this.map;
    var markers = [];
    var infoLink;
    var selectLink;
    
    this.initialize = function() {
    
      if(typeof objConfig.mapCenter != 'undefined'){
          startCenter = new google.maps.LatLng(objConfig.mapCenter.lat, objConfig.mapCenter.lng);
      } else {
        startCenter = new google.maps.LatLng(51.0110348, 4.5061507);
      }
      
      initContainer(document.getElementById(objConfig.containerId));
      
      if(this.autoShowLocator) {
        this.showLocator();
      }
    };
    
    var getControllerData = function(action, params, callback) {
      var xhr = selectXMLHttpObj();
      xhr.open("POST", objConfig.controller, true);
      
      var paramsOut = 'action=' + action + "&" + params
      
      //Send the proper header information along with the request
      xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhr.setRequestHeader("Content-length", paramsOut.length);
      xhr.setRequestHeader("Connection", "close");

      xhr.onload = function (e) {
        if (xhr.readyState === 4) {
          if (xhr.status === 200) {
              try {
                var result = JSON.parse(xhr.responseText);
                callback(result);
              } catch (e) {
                alert(objConfig.notFoundMessage);
                console.error(e);
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
      
      
      xhr.send(paramsOut);
      
    };
    
    var getShops = function(location) {
      var params = "";
      
      if(typeof location != 'undefined') {
        params = 'lng=' + location.lng() + '&lat=' + location.lat();
      }
      
      var days = document.getElementById('dpd-locator-day');
      var times = document.getElementById('dpd-locator-time');
      var selDay = days.options[days.selectedIndex].value;
      var selTime = times.options[times.selectedIndex].value;
      
      if( selDay != 0) {
        params += '&day=' + (selDay - 1);
      }
      if( selTime != 0) {
        params += '&time=' + selTime;
      }
      
      showLoader();
      
      getControllerData('find', params, function(result){
        hideLoader();
        renderPlaces(result.data.shops);
      });
    };
    
    var getInfo = function(shopID, container) {
      params = 'dpdshopid=' + shopID;
      
      getControllerData('info', params, function(result){
        container.innerHTML = result.data;
        container.parentElement.setAttribute('data-loaded', 1);
      });
    };
    
    var saveShop = function(shopID, container) {
      params = 'dpdshopid=' + shopID;
      
      getControllerData('save', params, function(result){
        container.innerHTML = result.data;
        container.style.display = "block";
        objConfig.callBack(shopID);
      });
    }
    
    var renderPlaces = function(places) {
      var bounds = new google.maps.LatLngBounds();
      
      for (var i = 0, place; place = places[i]; i++) {
        var image;
        var location;
        var marker;
        
        if(typeof place.geometry != 'undefined') {
          image = {
            url: place.icon,
            size: new google.maps.Size(71, 71),
            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point(17, 34),
            scaledSize: new google.maps.Size(25, 25)
          };
          location = place.geometry.location
          marker = new google.maps.Marker({
            map: map,
            icon: image,
            title: place.name,
            position: location
          });
          
          google.maps.event.addListener(marker, 'click', function() {
            getShops(this.position);
          });
          
        } else {
          image = {
            url: place.logo.url,
            size: new google.maps.Size(place.logo.size.width, place.logo.size.height),
            origin: new google.maps.Point(place.logo.origin.x, place.logo.origin.y),
            anchor: new google.maps.Point(place.logo.anchor.x, place.logo.anchor.y),
            scaledSize: new google.maps.Size(place.logo.scaled.width, place.logo.scaled.height)
          };
          location = new google.maps.LatLng(place.lat, place.lng);
          
          marker = new google.maps.Marker({
            map: map,
            icon: image,
            title: place.name,
            position: location,
            info: new google.maps.InfoWindow({
              content: initMarkercontent(place)
            }),
            dpdshopid: place.id
          });
          
          document.getElementById('list-container').appendChild(initMarkercontent(place));
          
          google.maps.event.addListener(marker, 'click', function() {
            closeMarkersInfo();            
            this.info.open(map, this);
          });
        }
        markers.push(marker);
        bounds.extend(location);
      }
      map.fitBounds(bounds);
    };
    
    var initMarkercontent = function(place) {
      markerContent = document.createElement('div');
      markerContent.innerHTML = '<h1>' + place.name + '</h1>'
        + '<p>' + place.address + '</p>';
      
      markerAdditionalContent = document.createElement('div');
      markerContent.appendChild(markerAdditionalContent);
      
      infoLink = document.createElement('a');
      infoLink.onclick = function(event) {
        event.target.innerHTML = '<img src="/modules/dpdcarrier/lib/DIS/templates/img/dpd_loader.gif">';
        getInfo(place.id, event.target.parentElement);
      };
      infoLink.innerHTML = place.infoLink;
      markerAdditionalContent.appendChild(infoLink);
      
      selectLink = document.createElement('a');
      selectLink.innerHTML = place.selectLink;
      if(place.active) {
        selectLink.onclick = function(event) {
          saveShop(place.id, document.getElementById('dpd-selected-shop'));
        };
      }
      markerContent.appendChild(selectLink);
      
      return markerContent;
    }
    
    var closeMarkersInfo = function(){
      for (var i = 0; i < markers.length; i++) {
        if(markers[i].info) {
          markers[i].info.close();
        }
      }
      return true;
    };
    
    var clearMarkers = function(){
      var listContainer = document.getElementById('list-container');
      while (listContainer.firstChild) {
          listContainer.removeChild(listContainer.firstChild);
      }
      for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
      }
      markers = [];
    };
    
    var hideLoader = function() {
      document.getElementById('DPDloader').style.display = 'none';
    }
    var showLoader = function() {
      document.getElementById('DPDloader').style.display = '';
    }
    
    var initContainer = function(objContainer) {
      if(objContainer != null) {
      
        var objLoaderContainer = document.createElement("div");
        objLoaderContainer.id = "DPDloader";
        objContainer.appendChild(objLoaderContainer);
        
        var objLoader = document.createElement("div");
        objLoader.innerHTML = '<span class="dpd-loading"></span><br><span>'+ objConfig.loadingMessage +'</span>';
        objLoaderContainer.appendChild(objLoader);
        
        var objMainContainer = document.createElement("div");
        objMainContainer.id = "DPDlocator";
        objContainer.appendChild(objMainContainer);
        
        // Create the map canvas.
        var chosenShop = document.createElement("div");
        chosenShop.id = "dpd-selected-shop";
        objContainer.appendChild(chosenShop);
        
        // Create the start point search box
        var input = document.createElement("input");
        input.type = "text";
        input.name = "DPDsearchBar";
        input.id = "dpd-locator-input";
        input.className = "dpd-locator-controls";
        input.setAttribute("placeholder", "Search");
        input.onkeydown = function(event) { 
          if(event.keyCode == 13) {
            return false;
          }
        };
        objMainContainer.appendChild(input);
        
        var day = document.createElement("select");
        day.name = "DPDDay";
        day.id = "dpd-locator-day";
        day.className = "dpd-locator-controls";
        day.onchange = function(){clearMarkers(markers); getShops();};
        objMainContainer.appendChild(day);
        
        for (var i = 0; i < objConfig.daysOfTheWeek.length ; i++) {
          var option = document.createElement("option");
          option.value = i;
          option.text = objConfig.daysOfTheWeek[i];
          day.appendChild(option);
        }
        
        var time = document.createElement("select");
        time.name = "DPDTime";
        time.id = "dpd-locator-time";
        time.className = "dpd-locator-controls";
        time.onchange = function(){clearMarkers(markers); getShops();};
        objMainContainer.appendChild(time);
        
        var option = document.createElement("option");
        option.value = 0;
        option.text = objConfig.timeOfDay[0];
        time.appendChild(option);
          
        for (var i = 0; i < 24; i++) {
          var option = document.createElement("option");
          option.value = pad(i,2) + ":00";
          option.text = pad(i,2) + ":00";
          time.appendChild(option);
        }
        
        // Create the map canvas.
        var mapCanvas = document.createElement("div");
        mapCanvas.id = "map-canvas";
        objMainContainer.appendChild(mapCanvas);
        
        var mapOptions = {
          // User Controls
          panControl: true,
          zoomControl: true,
          mapTypeControl: true,
          scaleControl: true,
          streetViewControl: true,
          overviewMapControl: true,
          center: startCenter,
          zoom: 10,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        
        map = new google.maps.Map(mapCanvas, mapOptions);
        
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(day);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(time);
        
        var searchBox = new google.maps.places.SearchBox((input));
        
        var searchEvent = function() {
          var places = searchBox.getPlaces();
          clearMarkers(markers);
          renderPlaces(places);
          
          if(places.length == 1) {
            getShops(places[0].geometry.location);
          }
          
        }
        
        google.maps.event.addListener(map, 'bounds_changed', function() {
          var bounds = map.getBounds();
          searchBox.setBounds(bounds);
        });
        
        google.maps.event.addListener(searchBox, 'places_changed', searchEvent);
        
        // Create the map canvas.
        var listContainer = document.createElement("div");
        listContainer.id = 'list-container';
        objMainContainer.appendChild(listContainer);
        
        window.addEventListener("resize", function() {
          if(document.body.clientWidth < 700) {
            document.getElementById('map-canvas').style.display = 'none';
            document.getElementById('list-container').style.display = 'block';
          } else {
            document.getElementById('map-canvas').style.display = 'block';
            document.getElementById('list-container').style.display = 'none';
          }
        });
      }
    };
    
    this.showLocator = function(center){
      var objMapContainer = document.getElementById('DPDlocator');
      
      if(objMapContainer == null) {
        this.autoShowLocator = true;
      } else {
      
        if(typeof objConfig.fullscreen != 'undefined' && objConfig.fullscreen){
          objMapContainer.style.position =  "absolute";
          objMapContainer.style.width =  "100%";
          objMapContainer.style.height =  "100%";
          objMapContainer.style.top = "0";
          objMapContainer.style.left = "0";
          document.body.scrollTop = document.documentElement.scrollTop = 0;
          document.body.style.overflow = "hidden";
        } else {
          objMapContainer.style.position =  "relative";
          objMapContainer.style.width =  "100%";
          objMapContainer.style.height =  "600px";
          if(objConfig.width != 'undefined') objMapContainer.style.width = objConfig.width;
          if(objConfig.height != 'undefined') objMapContainer.style.height = objConfig.height;	
        }
        objMapContainer.style.display = 'block';
        objMapContainer.style.visibility = 'visible';
        
        google.maps.event.trigger(map, "resize");
        
        getShops();
      }
    };
    
    this.hideLocator = function(){
      var objMapContainer = document.getElementById('DPDlocator');
      objMapContainer.style.display = 'none';
      objMapContainer.style.visibility = 'hidden';
    };
    
    this.toggleLocator = function(){
      var objMapContainer = document.getElementById('DPDlocator');
      if(objMapContainer.style.display == 'none') {
        this.showLocator();
      } else {
        this.hideLocator();
      }
    };
    
    this.toggleFullscreen = function(){
      objConfig.fullscreen = !objConfig.fullscreen;
    };
  };
};
