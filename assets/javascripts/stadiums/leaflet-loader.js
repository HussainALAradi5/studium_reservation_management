/** Loads Leaflet 1.9.4 (free, OpenStreetMap tiles, no API key) once, only on pages with a map. */
const LEAFLET_JS = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
const LEAFLET_SRI = 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=';
const ATTRIBUTION = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

let loading = null;

export function loadLeaflet() {
    if (window.L) return Promise.resolve(window.L);
    loading ??= new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = LEAFLET_JS;
        script.integrity = LEAFLET_SRI;
        script.crossOrigin = '';
        script.onload = () => resolve(window.L);
        script.onerror = () => reject(new Error('Map library failed to load.'));
        document.head.append(script);
    });
    return loading;
}

export function createMap(L, element, { lat, lng, zoom, tileUrl, interactive = true }) {
    const map = L.map(element, { scrollWheelZoom: false, zoomControl: interactive, dragging: interactive || !L.Browser.mobile });
    L.tileLayer(tileUrl, { maxZoom: 19, attribution: ATTRIBUTION }).addTo(map);
    map.setView([lat, lng], zoom);
    return map;
}

/** Brand-colored pin built from CSS, so no marker image files are needed. */
export function pinIcon(L) {
    return L.divIcon({
        className: '',
        html: '<div style="width:28px;height:28px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);background:linear-gradient(135deg,#10b981,#0ea5e9);border:3px solid #fff;box-shadow:0 6px 14px rgba(0,0,0,.3)"></div>',
        iconSize: [28, 28],
        iconAnchor: [14, 28],
    });
}
