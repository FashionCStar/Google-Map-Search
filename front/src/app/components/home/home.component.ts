import {Component, OnInit, ViewChild} from '@angular/core';
import {HttpClient, HttpHeaders} from '@angular/common/http';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import {} from 'googlemaps';
import {ProductService} from '../../services/product.service';
declare var $: any;
@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.scss'],
})

export class HomeComponent implements OnInit {
  lat = 47.9240641967;
  lng = -118.2736372948;
  datas: any[] = [];
  @ViewChild('map') mapElement: any;
  map: google.maps.Map;
  drawingManager: any;
  drawpol = false;
  markers: any[] = [];
  poly: google.maps.Polyline;
  polygon: google.maps.Polygon;
  loading = true;
  constructor(private http: HttpClient, private _formBuilder: FormBuilder, private _productService: ProductService) {
  }

  ngOnInit() {
    const mapProperties = {
      center: new google.maps.LatLng(this.lat,  this.lng),
      zoom: 10,
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: false,
      draggable: true,
      zoomControlOptions: {
        position: google.maps.ControlPosition.RIGHT_TOP
      },
    };
    this.map = new google.maps.Map(this.mapElement.nativeElement,    mapProperties);
    this.poly = new google.maps.Polyline({
      strokeColor: '#000000',
      strokeOpacity: 1.0,
      strokeWeight: 3
    });

    this.polygon = new google.maps.Polygon({
      paths: this.poly.getPath(),
      // strokeColor: "#FF0000",
      strokeOpacity: 0.8,
      strokeWeight: 1,
      fillColor: 'rgba(73,78,188,0.21)',
      fillOpacity: 0.35
    });

    const centerControlDiv = document.createElement("div");
    this.CenterControl(centerControlDiv, this.map);

    // @ts-ignore TODO(jpoehnelt)
    centerControlDiv.index = 1;
    this.map.controls[google.maps.ControlPosition.TOP_RIGHT].push(centerControlDiv);

    google.maps.event.addListener(this.map, 'zoom_changed', (e) => {
      this.getpropertyByBounds();
    });
    google.maps.event.addListener(this.map, 'dragend', (e) => {
      this.getpropertyByBounds();
    });
    google.maps.event.addListener(this.map, 'click', (e) => {
        this.addLatLng(e);
    });
    this.getPropertyList();
  }

  getpropertyByBounds() {
    if (this.polygon && this.polygon.getPaths().getLength() > 0) {
      const polyArray = [];
      this.polygon.getPath().forEach(p => {
        polyArray.push([p.lat(), p.lng()]);
      });
      this.getPropertyList(polyArray);
    } else {
      const bounds = this.map.getBounds();
      const ne = bounds.getNorthEast(); // LatLng of the north-east corner
      const sw = bounds.getSouthWest(); // LatLng of the south-west corder
      const polygonData = [];
      polygonData.push([ne.lat(), ne.lng()]);
      polygonData.push([sw.lat(), ne.lng()]);
      polygonData.push([sw.lat(), sw.lng()]);
      polygonData.push([ne.lat(), sw.lng()]);
      this.getPropertyList(polygonData);
    }

  }

  getPropertyList(polyogon?: any[]): void {
    this.markers.forEach(m => {
      m.setMap(null);
    });
    this.markers = [];
    this.datas = [];
    this.loading = true;
    this._productService.getPropertyList(polyogon).subscribe (res => {
      this.loading = false;
      if (res['success']) {
        this.datas = res['data'];
        for (let i = 0; i <= this.datas.length; i++) {
          if (this.datas[i]) {
            this.markers[i] = new google.maps.Marker({
              position: new google.maps.LatLng(Number(parseFloat(this.datas[i].latitude).toFixed(10)), Number(parseFloat(this.datas[i].longitude).toFixed(10))),
              map: this.map,
              icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 0
              }
            });

            // google.maps.event.addListener(this.markers[i], 'click', () => {
            //   this.map.setZoom(12);
            //   this.map.setCenter(this.markers[i].getPosition());
            // });
            const contentString = '<div class="marker-content">' +
              '</div>' +
              '<span id="firstHeading' + i + '"><strong class="marker-text">$' + Math.round(this.datas[i].listprice / 1000) + 'K</strong></span>' +
              '</div>';
            const infowindow = new google.maps.InfoWindow({
              content: contentString
            });
            infowindow.open(this.map, this.markers[i]);
            google.maps.event.addDomListener(infowindow, 'domready', () => {
              $('#firstHeading' + i).click(() => {
                console.log("Hello World" + this.datas[i].id);
              });
            });
          }
        }
      }
    }, err => {
      console.log(err);
      this.loading = false;
    });
  }

  addLatLng(event: google.maps.MouseEvent) {
    if (this.drawpol) {
      this.poly.setMap(this.map);
      const path = this.poly.getPath();
      path.push(event.latLng);
      this.polygon.setPaths(path);
      this.polygon.setMap(this.map);
      const polyArray = [];
      if (this.polygon.getPath().getLength() > 2) {
        this.polygon.getPath().forEach(p => {
          polyArray.push([p.lat(), p.lng()]);
        });
        this.getPropertyList(polyArray);
      }
    }
  }

  CenterControl(controlDiv: Element, map: google.maps.Map) {
    // Set CSS for the control border.
    const controlUI = document.createElement("div");
    controlUI.style.backgroundColor = "#fff";
    controlUI.style.border = "2px solid #fff";
    controlUI.style.borderRadius = "3px";
    controlUI.style.boxShadow = "0 2px 6px rgba(0,0,0,.3)";
    controlUI.style.cursor = "pointer";
    controlUI.style.marginTop = "10px";
    controlUI.style.marginRight = "10px";
    controlUI.style.marginBottom = "10px";
    controlUI.style.textAlign = "center";
    controlUI.title = "Click to recenter the map";
    controlDiv.appendChild(controlUI);

    // Set CSS for the control interior.
    let controlText = document.createElement("div");
    controlText.style.color = "rgb(25,25,25)";
    controlText.style.fontFamily = "Roboto,Arial,sans-serif";
    controlText.style.fontSize = "16px";
    controlText.style.lineHeight = "38px";
    controlText.style.paddingLeft = "5px";
    controlText.style.paddingRight = "5px";
    controlText.style.width = "35px";
    controlText.style.height = "35px";
    controlText.innerHTML = "<svg viewBox=\"0 0 24 24\" class=\"cy-map-button-polygon-open sc-bdVaJa bssMCl\">" +
      "<path d=\"M20,17.6l2-11c1.1-0.1,2-1,2-2.1c0-1.2-1-2.1-2.2-2.1c-1.1,0-1.9,0.7-2.1,1.7L4.2,5.7C3.8,5,3.1,4.5,2.2,4.5 C1,4.5,0,5.5,0,6.7c0,1.2,1,2.1,2.2,2.1c0.1,0,0.2,0,0.3,0l2.6,5.9c-0.5,0.4-0.8,1-0.8,1.6c0,1.2,1,2.1,2.2,2.1c0.8,0,1.5-0.4,1.9-1 l8.4,2.2c0.1,1.2,1,2.1,2.2,2.1c1.2,0,2.2-1,2.2-2.2C21.2,18.7,20.7,17.9,20,17.6z M3.6,8.3C4,7.9,4.3,7.5,4.3,6.9l15.5-1.7 c0.2,0.5,0.5,0.9,1,1.1l-2,11c-0.7,0.1-1.4,0.5-1.7,1.1l-8.4-2.2c0-1.2-1-2.1-2.2-2.1c-0.1,0-0.2,0-0.3,0L3.6,8.3z\">" +
      "</path></svg>";
    controlUI.appendChild(controlText);

    // Setup the click event listeners:
    controlUI.addEventListener("click", () => {
      this.drawpol = !this.drawpol;
      if (!this.drawpol) {
        controlText.innerHTML = "<svg viewBox=\"0 0 24 24\" class=\"cy-map-button-polygon-open sc-bdVaJa bssMCl\">" +
          "<path d=\"M20,17.6l2-11c1.1-0.1,2-1,2-2.1c0-1.2-1-2.1-2.2-2.1c-1.1,0-1.9,0.7-2.1,1.7L4.2,5.7C3.8,5,3.1,4.5,2.2,4.5 C1,4.5,0,5.5,0,6.7c0,1.2,1,2.1,2.2,2.1c0.1,0,0.2,0,0.3,0l2.6,5.9c-0.5,0.4-0.8,1-0.8,1.6c0,1.2,1,2.1,2.2,2.1c0.8,0,1.5-0.4,1.9-1 l8.4,2.2c0.1,1.2,1,2.1,2.2,2.1c1.2,0,2.2-1,2.2-2.2C21.2,18.7,20.7,17.9,20,17.6z M3.6,8.3C4,7.9,4.3,7.5,4.3,6.9l15.5-1.7 c0.2,0.5,0.5,0.9,1,1.1l-2,11c-0.7,0.1-1.4,0.5-1.7,1.1l-8.4-2.2c0-1.2-1-2.1-2.2-2.1c-0.1,0-0.2,0-0.3,0L3.6,8.3z\">" +
          "</path></svg>";
        this.poly.setPath([]);
        this.poly.setMap(null);
        this.polygon.setPaths([]);
        this.polygon.setMap(null);
        this.map.setOptions({draggableCursor: null});
        this.getpropertyByBounds();
      } else {
        controlText.innerHTML = "<svg viewBox=\"0 0 24 24\" class=\"cy-map-button-polygon-close sc-bdVaJa bssMCl\"><path d=\"M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z\">" +
          "</path></svg>";
        this.map.setOptions({draggableCursor: 'crosshair'});
        // this.getpropertyByBounds();
      }
    });
  }

}
