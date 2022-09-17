require('../css/map.css');

// const ol = require('ol');

import 'ol/ol.css';
import {Map, View} from 'ol';
import TileLayer from 'ol/layer/Tile';
import OSM from 'ol/source/OSM';
import {fromLonLat} from 'ol/proj';
import VectorLayer from 'ol/layer/Vector';
import VectorSource from 'ol/source/Vector';
import Point from 'ol/geom/Point';
import LineString from 'ol/geom/LineString';
import Feature from 'ol/Feature';
import * as Style from 'ol/style';
import {FullScreen, defaults as defaultControls} from 'ol/control';

const view = new View({
    center: fromLonLat([22.5, 55.2]),
    zoom: 5,
});

var map = new Map({
    target: 'map',
    layers: [
      new TileLayer({
        source: new OSM()
      })
    ],
    view: view,
    controls: defaultControls().extend([new FullScreen()])
  });

var vs = new VectorSource({});
var vl = new VectorLayer({ source: vs });

function getStyleCircle(radius) {
    return new Style.Style({
      image: new Style.Circle({
          radius: radius * 3,
          fill: new Style.Fill({color: 'yellow'}),
          stroke: new Style.Stroke({
            color: [0,0,255], width: 2
        })
      }),
    })
  }

function getStyleLabel(callsign) {
    return new Style.Style({
      text: new Style.Text({
          font: '12px Calibri,sans-serif',
          overflow: true,
          fill: new Style.Fill({
              color: '#000'
          }),
          stroke: new Style.Stroke({
              color: '#fff',
              width: 3
          }),
          text: callsign,
          offsetY: 15
      })
    })
  }

function addMapEntries(items,op) {
    for (var i=0; i<items.length; i++) {
        if (items[i].location &&
            ( items[i].location.lon != op.location.lon &&
            items[i].location.lat != op.location.lat ) ) {
            var f = new Feature({
                name: items[i].callsign,
                geometry: new Point(fromLonLat(
                    [ items[i].location.lon, items[i].location.lat ]
                ))
            });
            f.setStyle( [
                getStyleCircle(2),
                getStyleLabel(items[i].callsign)
            ] )
            vs.addFeature(f);
        }
        else {
            console.log(items[i]);
        }
    }
    var p = new Feature({
        name: op.callsign,
        geometry: new Point(fromLonLat( [ op.location.lon, op.location.lat ] ))
    });
    p.setStyle( [ getStyleCircle(3), getStyleLabel(op.callsign) ] )
    vs.addFeature(p);

    map.addLayer(vl);
    view.centerOn(p.getGeometry().getCoordinates(), map.getSize(), [400, 300]);
}

document.addEventListener('DOMContentLoaded', function() {
    var userLocation = document.querySelector('.js-user-location');
    var json = userLocation.dataset.points;
    var data = JSON.parse(json);
    addMapEntries(data.points,data.operator);
});
