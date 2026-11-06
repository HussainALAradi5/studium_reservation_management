/** Read-only stadium location map ([data-map-view]). */
import { createMap, loadLeaflet, pinIcon } from './leaflet-loader.js';
import { toast } from '../core/toast.js';

const ZOOM = 15;

export async function initMapView(element) {
    try {
        const L = await loadLeaflet();
        const lat = Number(element.dataset.lat);
        const lng = Number(element.dataset.lng);
        const map = createMap(L, element, { lat, lng, zoom: ZOOM, tileUrl: element.dataset.tileUrl });
        L.marker([lat, lng], { icon: pinIcon(L), title: element.dataset.label }).addTo(map);
    } catch (error) {
        toast.warning(error.message);
    }
}
