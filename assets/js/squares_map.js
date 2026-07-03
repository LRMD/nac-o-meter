require('../css/squares_map.css');

import 'ol/ol.css';
import {
    Map,
    View
} from 'ol';
import TileLayer from 'ol/layer/Tile';
import OSM from 'ol/source/OSM';
import VectorLayer from 'ol/layer/Vector';
import VectorSource from 'ol/source/Vector';
import Feature from 'ol/Feature';
import { fromExtent } from 'ol/geom/Polygon';
import { transformExtent } from 'ol/proj';
import { createEmpty, extend } from 'ol/extent';
import * as Style from 'ol/style';
import { Attribution } from 'ol/control';

// Big square (e.g. KO24) -> [minLon, minLat, maxLon, maxLat] in EPSG:3857
function squareExtent(square) {
    var lon = (square.charCodeAt(0) - 65) * 20 - 180 + parseInt(square[2], 10) * 2;
    var lat = (square.charCodeAt(1) - 65) * 10 - 90 + parseInt(square[3], 10);
    return transformExtent([lon, lat, lon + 2, lat + 1], 'EPSG:4326', 'EPSG:3857');
}

function getSquareStyle(square, count, maxCount) {
    return new Style.Style({
        fill: new Style.Fill({
            color: [54, 162, 235, 0.15 + 0.45 * (count / maxCount)]
        }),
        stroke: new Style.Stroke({
            color: [54, 162, 235, 0.9],
            width: 1
        }),
        text: new Style.Text({
            font: 'bold 11px Calibri,sans-serif',
            fill: new Style.Fill({
                color: '#000'
            }),
            stroke: new Style.Stroke({
                color: '#fff',
                width: 3
            }),
            text: square + '\n' + count
        })
    });
}

document.addEventListener('DOMContentLoaded', function () {
    var target = document.getElementById('squares-map');
    if (!target) return;

    var squares = JSON.parse(target.dataset.squares);
    if (!squares.length) return;

    var maxCount = squares.reduce(function (max, item) {
        return Math.max(max, parseInt(item.count, 10));
    }, 1);

    var vs = new VectorSource({});
    var fullExtent = createEmpty();

    squares.forEach(function (item) {
        var extent = squareExtent(item.square);
        var f = new Feature({
            geometry: fromExtent(extent)
        });
        f.setStyle(getSquareStyle(item.square, parseInt(item.count, 10), maxCount));
        vs.addFeature(f);
        extend(fullExtent, extent);
    });

    var map = new Map({
        target: target,
        layers: [
            new TileLayer({
                source: new OSM()
            }),
            new VectorLayer({
                source: vs
            })
        ],
        // static map: no zoom/pan/scroll, only the OSM attribution
        controls: [new Attribution({ collapsible: false })],
        interactions: [],
        view: new View()
    });
    map.getView().fit(fullExtent, { padding: [10, 10, 10, 10] });
});
