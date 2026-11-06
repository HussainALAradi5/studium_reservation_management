/**
 * Location picker on the stadium form ([data-map-picker]): click or drag the pin, use the browser's
 * location, or clear. Writes 7-decimal coordinates into the hidden latitude/longitude inputs.
 */
import { qs } from '../core/dom.js';
import { toast } from '../core/toast.js';
import { createMap, loadLeaflet, pinIcon } from './leaflet-loader.js';

const DECIMALS = 7;
const PINNED_ZOOM = 16;

export async function initMapPicker(element) {
    const form = element.closest('form');
    const latInput = qs('[data-map-lat]', form);
    const lngInput = qs('[data-map-lng]', form);
    const status = qs('[data-map-status]', form);
    const hasPin = latInput.value !== '' && lngInput.value !== '';

    let L;
    try {
        L = await loadLeaflet();
    } catch (error) {
        toast.warning(error.message);
        return;
    }
    const map = createMap(L, element, {
        lat: hasPin ? Number(latInput.value) : Number(element.dataset.defaultLat),
        lng: hasPin ? Number(lngInput.value) : Number(element.dataset.defaultLng),
        zoom: hasPin ? PINNED_ZOOM : Number(element.dataset.defaultZoom),
        tileUrl: element.dataset.tileUrl,
    });
    let marker = null;

    const setPin = (lat, lng, pan = false) => {
        const fixedLat = Number(lat).toFixed(DECIMALS);
        const fixedLng = Number(lng).toFixed(DECIMALS);
        latInput.value = fixedLat;
        lngInput.value = fixedLng;
        status.textContent = `Pinned at ${fixedLat}, ${fixedLng}`;
        form.querySelector('[data-error-for="latitude"]')?.closest('.field')?.classList.remove('has-error');
        if (!marker) {
            marker = L.marker([lat, lng], { draggable: true, icon: pinIcon(L) }).addTo(map);
            marker.on('dragend', () => {
                const point = marker.getLatLng();
                setPin(point.lat, point.lng);
            });
        } else {
            marker.setLatLng([lat, lng]);
        }
        if (pan) map.setView([lat, lng], PINNED_ZOOM);
    };

    if (hasPin) setPin(latInput.value, lngInput.value);
    map.on('click', (event) => setPin(event.latlng.lat, event.latlng.lng));

    qs('[data-map-clear]', form)?.addEventListener('click', () => {
        marker?.remove();
        marker = null;
        latInput.value = '';
        lngInput.value = '';
        status.textContent = 'Click the map to drop a pin.';
    });

    qs('[data-map-locate]', form)?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            toast.warning('Your browser cannot share its location.');
            return;
        }
        navigator.geolocation.getCurrentPosition(
            (position) => setPin(position.coords.latitude, position.coords.longitude, true),
            () => toast.warning('Location permission was denied.'),
            { enableHighAccuracy: true, timeout: 10000 },
        );
    });
}
