/**
 * Pool India — Google Places Autocomplete Helper
 * ─────────────────────────────────────────────────
 * Modeled after the Angular AutocompleteComponent:
 *   - componentRestrictions: { country: 'IN' }
 *   - types: ['geocode']
 *   - place_changed → lat, lng, formatted_address
 *
 * Usage:
 *   PI_Places.init({ inputId, latId, lngId, onSelect, onClear })
 *   PI_Places.initAll([...configs])
 */
const PI_Places = (() => {
    const GMAPS_KEY = 'AIzaSyDZJ7k0nMuZPRNFxAtaIe0HHLNg5okTUVI';

    // ── Google Maps script loader (singleton, handles duplicate protection) ──
    let _loaded = false;
    let _loading = false;
    let _callbacks = [];

    function load(cb) {
        // If already loaded, fire immediately
        if (_loaded && typeof google !== 'undefined' && google.maps && google.maps.places) {
            cb();
            return;
        }
        _callbacks.push(cb);

        // If another load is in progress, just queue
        if (_loading) return;

        // Check if Google Maps was already loaded by another script tag
        if (typeof google !== 'undefined' && google.maps && google.maps.places) {
            _loaded = true;
            _loading = false;
            _callbacks.forEach(f => f());
            _callbacks = [];
            return;
        }

        // Check if another script tag is already injecting Google Maps
        const existingScripts = document.querySelectorAll('script[src*="maps.googleapis.com/maps/api/js"]');
        if (existingScripts.length > 0) {
            // Script tag exists but API not ready yet — poll until ready
            _loading = true;
            const poll = setInterval(() => {
                if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                    clearInterval(poll);
                    _loaded = true;
                    _loading = false;
                    _callbacks.forEach(f => f());
                    _callbacks = [];
                }
            }, 100);
            return;
        }

        // No existing script — inject our own
        _loading = true;
        const s = document.createElement('script');
        s.id = 'pi-gmaps-script';
        s.async = true;
        s.defer = true;
        s.src = `https://maps.googleapis.com/maps/api/js?key=${GMAPS_KEY}&libraries=places,geometry&callback=__PI_MapsReady`;

        window.__PI_MapsReady = () => {
            _loaded = true;
            _loading = false;
            _callbacks.forEach(f => f());
            _callbacks = [];
        };

        s.onerror = () => {
            console.error('[PI_Places] Failed to load Google Maps API');
            _loading = false;
        };

        document.head.appendChild(s);
    }

    /**
     * init(config) — attach Places Autocomplete to a single input
     *
     * config = {
     *   inputId:    string,          // id of <input>
     *   latId?:     string,          // hidden input for lat
     *   lngId?:     string,          // hidden input for lng
     *   onSelect?:  fn({ place, lat, lng, formatted_address }),
     *   onClear?:   fn(),
     *   bias?:      'IN'             // country bias (default 'IN')
     *   types?:     ['geocode']      // default 'geocode' — same as Angular component
     * }
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

        // Prevent double-attaching
        if (input.dataset.piAcAttached === '1') return;
        input.dataset.piAcAttached = '1';

        // Match Angular autocomplete config:
        //   componentRestrictions: { country: 'IN' }, types: [this.adressType]
        const options = {
            componentRestrictions: { country: cfg.bias || 'IN' },
            fields: ['formatted_address', 'geometry', 'name', 'place_id'],
            types: cfg.types || ['geocode'],  // Angular uses 'geocode' by default
        };

        const ac = new google.maps.places.Autocomplete(input, options);

        // Style the pac-container to match Pool India design
        _injectAutocompleteStyles();

        // ── place_changed — same as Angular's google.maps.event.addListener ──
        google.maps.event.addListener(ac, 'place_changed', () => {
            const place = ac.getPlace();
            if (!place || !place.geometry) {
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

        // Clear coords on manual edit (ensures stale coords are wiped)
        input.addEventListener('input', () => {
            _setHidden(cfg, '', '');
            cfg.onClear && cfg.onClear();
        });

        // Prevent form submit on Enter while autocomplete dropdown is open
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                // If pac-container is visible, prevent form submit
                const pacContainers = document.querySelectorAll('.pac-container');
                for (const pac of pacContainers) {
                    if (pac.style.display !== 'none' && pac.querySelectorAll('.pac-item').length > 0) {
                        e.preventDefault();
                        return;
                    }
                }
            }
        });
    }

    function _setHidden(cfg, lat, lng) {
        if (cfg.latId) { const el = document.getElementById(cfg.latId); if (el) el.value = lat; }
        if (cfg.lngId) { const el = document.getElementById(cfg.lngId); if (el) el.value = lng; }
    }

    // ── Beautiful dropdown styles matching Pool India design ──
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
            background: #fff !important;
        }
        .pac-item {
            padding: 10px 16px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            color: #1d3a70 !important;
            border-top: 1px solid #f1f5f9 !important;
            cursor: pointer !important;
            transition: background .15s !important;
            line-height: 1.5 !important;
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
            margin-right: 8px !important;
        }
        .pac-icon::after {
            content: '📍';
            font-size: 14px;
        }
        .pac-icon-marker { display: inline-block !important; }
        .pac-logo::after {
            display: none !important;
        }
        .hdpi .pac-icon {
            background-image: none !important;
        }
        `;
        const style = document.createElement('style');
        style.textContent = css;
        document.head.appendChild(style);
    }

    /**
     * Calculate distance & duration between two latlng pairs.
     * Returns Promise<{distance_km, distance_text, duration_text, duration_secs}>
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
