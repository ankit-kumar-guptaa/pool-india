/**
 * Pool India — Google Places Autocomplete Helper
 * Usage: PI_Places.init(config)
 */
const PI_Places = (() => {
    const GMAPS_KEY = 'AIzaSyDZJ7k0nMuZPRNFxAtaIe0HHLNg5okTUVI';

    // Inject Google Maps script once
    let _loaded = false;
    let _callbacks = [];

    function load(cb) {
        if (_loaded) { cb(); return; }
        _callbacks.push(cb);
        if (document.getElementById('gm-script')) return; // already injecting
        const s = document.createElement('script');
        s.id = 'gm-script';
        s.async = true;
        s.defer = true;
        s.src = `https://maps.googleapis.com/maps/api/js?key=${GMAPS_KEY}&libraries=places,geometry&callback=__PI_MapsReady`;
        window.__PI_MapsReady = () => {
            _loaded = true;
            _callbacks.forEach(f => f());
            _callbacks = [];
        };
        document.head.appendChild(s);
    }

    /**
     * init({
     *   inputId:    string,          // id of <input>
     *   latId?:     string,          // hidden input for lat
     *   lngId?:     string,          // hidden input for lng
     *   onSelect?:  fn({place, lat, lng, formatted_address}),
     *   onClear?:   fn(),
     *   bias?:      'IN'             // country bias (default India)
     * })
     */
    function init(cfg) {
        load(() => _attach(cfg));
    }

    function initAll(cfgArray) {
        load(() => cfgArray.forEach(c => _attach(c)));
    }

    function _attach(cfg) {
        const input = document.getElementById(cfg.inputId);
        if (!input) return;

        const options = {
            componentRestrictions: { country: cfg.bias || 'in' },
            fields: ['formatted_address', 'geometry', 'name', 'place_id'],
            types: ['geocode', 'establishment'],
        };

        const ac = new google.maps.places.Autocomplete(input, options);

        // Style the pac-container to match Pool India design
        _injectAutocompleteStyles();

        ac.addListener('place_changed', () => {
            const place = ac.getPlace();
            if (!place.geometry) {
                // User typed something without selecting — clear coords
                _setHidden(cfg, '', '');
                cfg.onClear && cfg.onClear();
                return;
            }
            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();
            _setHidden(cfg, lat, lng);
            // Prefer formatted_address but fallback to name
            const label = place.formatted_address || place.name || input.value;
            input.value = label;
            cfg.onSelect && cfg.onSelect({ place, lat, lng, formatted_address: label });
        });

        // Clear coords on manual edit
        input.addEventListener('input', () => {
            _setHidden(cfg, '', '');
            cfg.onClear && cfg.onClear();
        });
    }

    function _setHidden(cfg, lat, lng) {
        if (cfg.latId) { const el = document.getElementById(cfg.latId); if (el) el.value = lat; }
        if (cfg.lngId) { const el = document.getElementById(cfg.lngId); if (el) el.value = lng; }
    }

    let _stylesInjected = false;
    function _injectAutocompleteStyles() {
        if (_stylesInjected) return;
        _stylesInjected = true;
        const css = `
        .pac-container {
            border-radius: 16px !important;
            border: 1.5px solid #e2e8f0 !important;
            box-shadow: 0 20px 60px rgba(29,58,112,.15) !important;
            margin-top: 6px !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            overflow: hidden !important;
            z-index: 99999 !important;
        }
        .pac-item {
            padding: 10px 16px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            color: #1d3a70 !important;
            border-top: 1px solid #f1f5f9 !important;
            cursor: pointer !important;
            transition: background .15s !important;
        }
        .pac-item:first-child { border-top: none !important; }
        .pac-item:hover, .pac-item-selected {
            background: #f0fdf4 !important;
        }
        .pac-item-query {
            font-size: 14px !important;
            font-weight: 800 !important;
            color: #1b8036 !important;
        }
        .pac-icon {
            background-image: none !important;
            width: 20px !important; height: 20px !important;
            margin-top: 2px !important;
        }
        .pac-icon::before {
            content: '\\f3c5';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 12px;
            color: #f3821a;
        }
        .pac-logo { display: none !important; }
        `;
        const style = document.createElement('style');
        style.textContent = css;
        document.head.appendChild(style);
    }

    /**
     * Calculate distance & duration between two latlng pairs.
     * Returns Promise<{distance_km, duration_text, duration_secs}>
     */
    function getDistance(fromLatLng, toLatLng) {
        return new Promise((resolve, reject) => {
            load(() => {
                const svc = new google.maps.DistanceMatrixService();
                svc.getDistanceMatrix({
                    origins: [new google.maps.LatLng(fromLatLng.lat, fromLatLng.lng)],
                    destinations: [new google.maps.LatLng(toLatLng.lat, toLatLng.lng)],
                    travelMode: google.maps.TravelMode.DRIVING,
                    unitSystem: google.maps.UnitSystem.METRIC,
                }, (res, status) => {
                    if (status !== 'OK') { reject(status); return; }
                    const el = res.rows[0]?.elements[0];
                    if (!el || el.status !== 'OK') { reject('NO_RESULT'); return; }
                    resolve({
                        distance_km: el.distance.value / 1000,
                        distance_text: el.distance.text,
                        duration_text: el.duration.text,
                        duration_secs: el.duration.value,
                    });
                });
            });
        });
    }

    return { init, initAll, load, getDistance };
})();
