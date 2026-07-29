(() => {
    'use strict';

    const ready = (callback) => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
        } else {
            callback();
        }
    };

    const requestedMapProvider = String(window.fflEinsatzlyzer?.mapProvider || 'osm').toLowerCase();
    const mapProvider = ['google', 'here'].includes(requestedMapProvider) ? requestedMapProvider : 'osm';
    let leafletPromise = null;
    let googleMapsPromise = null;
    let googleClustererPromise = null;
    let hereMapsPromise = null;

    const ensureLeaflet = () => {
        if (window.L) return Promise.resolve(window.L);
        if (leafletPromise) return leafletPromise;

        leafletPromise = new Promise((resolve, reject) => {
            if (!document.querySelector('link[data-ffl-leaflet]')) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = fflEinsatzlyzer.leafletCssUrl;
                link.dataset.fflLeaflet = '1';
                document.head.appendChild(link);
            }

            const existing = document.querySelector('script[data-ffl-leaflet]');
            if (existing) {
                existing.addEventListener('load', () => resolve(window.L), { once: true });
                existing.addEventListener('error', reject, { once: true });
                return;
            }

            const script = document.createElement('script');
            script.src = fflEinsatzlyzer.leafletJsUrl;
            script.async = true;
            script.dataset.fflLeaflet = '1';
            script.addEventListener('load', () => resolve(window.L), { once: true });
            script.addEventListener('error', reject, { once: true });
            document.head.appendChild(script);
        });

        return leafletPromise;
    };

    const ensureGoogleMaps = () => {
        if (window.google?.maps) return Promise.resolve(window.google.maps);
        if (googleMapsPromise) return googleMapsPromise;

        const apiKey = String(fflEinsatzlyzer.googleApiKey || '').trim();
        if (!apiKey) return Promise.reject(new Error('Google-Maps-API-Schlüssel fehlt.'));

        googleMapsPromise = new Promise((resolve, reject) => {
            const existing = document.querySelector('script[data-ffl-google-maps], script[src*="maps.googleapis.com/maps/api/js"]');
            if (existing) {
                let attempts = 0;
                const timer = window.setInterval(() => {
                    attempts += 1;
                    if (window.google?.maps) {
                        window.clearInterval(timer);
                        resolve(window.google.maps);
                    } else if (attempts > 120) {
                        window.clearInterval(timer);
                        reject(new Error('Google Maps konnte nicht geladen werden.'));
                    }
                }, 100);
                return;
            }

            const callbackName = `fflGoogleMapsReady${Date.now()}`;
            const params = new URLSearchParams({
                key: apiKey,
                loading: 'async',
                callback: callbackName,
                v: 'weekly',
                language: 'de',
                region: 'DE',
                auth_referrer_policy: 'origin'
            });

            window[callbackName] = () => {
                delete window[callbackName];
                resolve(window.google.maps);
            };

            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?${params.toString()}`;
            script.async = true;
            script.dataset.fflGoogleMaps = '1';
            script.onerror = () => {
                delete window[callbackName];
                reject(new Error('Google Maps konnte nicht geladen werden.'));
            };
            document.head.appendChild(script);
        });

        return googleMapsPromise;
    };

    const ensureGoogleClusterer = () => {
        if (window.markerClusterer?.MarkerClusterer) return Promise.resolve(window.markerClusterer);
        if (googleClustererPromise) return googleClustererPromise;

        googleClustererPromise = new Promise((resolve, reject) => {
            const existing = document.querySelector('script[data-ffl-google-clusterer]');
            if (existing) {
                existing.addEventListener('load', () => resolve(window.markerClusterer), { once: true });
                existing.addEventListener('error', reject, { once: true });
                return;
            }

            const script = document.createElement('script');
            script.src = fflEinsatzlyzer.googleClustererUrl;
            script.async = true;
            script.dataset.fflGoogleClusterer = '1';
            script.addEventListener('load', () => resolve(window.markerClusterer), { once: true });
            script.addEventListener('error', reject, { once: true });
            document.head.appendChild(script);
        });

        return googleClustererPromise;
    };


    const ensureHereMaps = () => {
        if (window.H?.Map && window.H?.service?.Platform) return Promise.resolve(window.H);
        if (hereMapsPromise) return hereMapsPromise;

        const apiKey = String(fflEinsatzlyzer.hereApiKey || '').trim();
        if (!apiKey) return Promise.reject(new Error('HERE-API-Schlüssel fehlt.'));

        const base = String(fflEinsatzlyzer.hereJsBase || 'https://js.api.here.com/v3/3.2/');
        const modules = [
            'mapsjs-core.js',
            'mapsjs-service.js',
            'mapsjs-mapevents.js',
            'mapsjs-clustering.js',
            'mapsjs-ui.js'
        ];

        if (!document.querySelector('link[data-ffl-here-ui]')) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = `${base}mapsjs-ui.css`;
            link.dataset.fflHereUi = '1';
            document.head.appendChild(link);
        }

        const loadModule = (file) => new Promise((resolve, reject) => {
            const existing = document.querySelector(`script[data-ffl-here-module="${file}"]`);
            if (existing) {
                if (existing.dataset.loaded === '1') {
                    resolve();
                    return;
                }
                existing.addEventListener('load', resolve, { once: true });
                existing.addEventListener('error', reject, { once: true });
                return;
            }

            const script = document.createElement('script');
            script.src = `${base}${file}`;
            script.async = false;
            script.dataset.fflHereModule = file;
            script.addEventListener('load', () => {
                script.dataset.loaded = '1';
                resolve();
            }, { once: true });
            script.addEventListener('error', () => reject(new Error(`HERE-Modul ${file} konnte nicht geladen werden.`)), { once: true });
            document.head.appendChild(script);
        });

        hereMapsPromise = modules.reduce(
            (promise, module) => promise.then(() => loadModule(module)),
            Promise.resolve()
        ).then(() => {
            if (!window.H?.Map || !window.H?.service?.Platform) {
                throw new Error('HERE Maps konnte nicht initialisiert werden.');
            }
            return window.H;
        });

        return hereMapsPromise;
    };

    const hereBaseLayer = (layers) => {
        const style = String(fflEinsatzlyzer.hereMapStyle || 'normal');
        const normal = layers?.vector?.normal || {};
        if (style === 'night') return normal.mapnight || normal.map;
        if (style === 'lite') return normal.lite || normal.map;
        if (style === 'litenight') return normal.litenight || normal.mapnight || normal.map;
        return normal.map;
    };

    const markerSymbol = (icon) => ({
        fire: '✦',
        tools: '+',
        warning: '!',
        exercise: '★',
        weather: '≈',
        hazard: '◆',
        signal: '•'
    }[icon] || '•');

    const safeClass = (value) => String(value || 'signal').replace(/[^a-z0-9_-]/gi, '');

    const markerIcon = (color, icon = 'signal') => L.divIcon({
        className: 'ffl-map-marker-wrap',
        html: `<span class="ffl-map-marker ffl-map-marker--${safeClass(icon)}" style="--marker-color:${String(color || '#d62828').replace(/["'<>]/g, '')}"><b>${markerSymbol(icon)}</b></span>`,
        iconSize: [38, 42],
        iconAnchor: [19, 40],
        popupAnchor: [0, -37]
    });

    const clusterIcon = (count) => L.divIcon({
        className: 'ffl-map-cluster-wrap',
        html: `<span class="ffl-map-cluster"><b>${Number(count)}</b><small>Einsätze</small></span>`,
        iconSize: [54, 54],
        iconAnchor: [27, 27]
    });

    const addTiles = (map, showControl = true) => {
        const street = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: fflEinsatzlyzer.mapAttribution,
            maxZoom: 19,
            className: 'ffl-map-tiles ffl-map-tiles--street'
        });
        const topo = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            attribution: 'Kartendaten © OpenStreetMap-Mitwirkende · Darstellung © OpenTopoMap',
            maxZoom: 17,
            className: 'ffl-map-tiles ffl-map-tiles--topo'
        });
        const contrast = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: fflEinsatzlyzer.mapAttribution,
            maxZoom: 19,
            className: 'ffl-map-tiles ffl-map-tiles--contrast'
        });

        street.addTo(map);
        let control = null;
        if (showControl) {
            control = L.control.layers({
                'Straßenkarte': street,
                'Topografisch': topo,
                'Kontrastreich': contrast
            }, {}, {
                position: 'topright',
                collapsed: window.matchMedia('(max-width: 780px)').matches
            }).addTo(map);
        }

        return { street, topo, contrast, control };
    };

    const pointPopup = (point) => `<div class="ffl-map-popup"><strong>${escapeHtml(point.title || '')}</strong><span>${escapeHtml(point.date || '')} · ${escapeHtml(point.type || '')}</span><span>${escapeHtml(point.location || '')}</span><a href="${escapeAttribute(point.url || '#')}">Einsatzbericht ansehen →</a></div>`;

    const createClusteredMarkers = (map, points) => {
        const layer = L.layerGroup().addTo(map);
        const spiderLayer = L.layerGroup().addTo(map);
        const threshold = 48;

        const clearSpider = () => spiderLayer.clearLayers();

        const spiderfy = (cluster) => {
            clearSpider();
            const center = L.latLng(cluster.lat, cluster.lon);
            const centerPoint = map.latLngToLayerPoint(center);
            const radius = Math.max(42, Math.min(92, 34 + cluster.points.length * 3));

            cluster.points.forEach((point, index) => {
                const angle = ((Math.PI * 2) / cluster.points.length) * index - Math.PI / 2;
                const pixel = L.point(centerPoint.x + Math.cos(angle) * radius, centerPoint.y + Math.sin(angle) * radius);
                const latLng = map.layerPointToLatLng(pixel);
                L.polyline([center, latLng], { color: point.color || '#64748b', weight: 1.5, opacity: 0.55 }).addTo(spiderLayer);
                L.marker(latLng, { icon: markerIcon(point.color, point.icon) }).addTo(spiderLayer).bindPopup(pointPopup(point));
            });
            map.panTo(center, { animate: true });
        };

        const render = () => {
            layer.clearLayers();
            clearSpider();
            const clusters = [];

            points.forEach((point) => {
                const lat = Number.parseFloat(point.lat);
                const lon = Number.parseFloat(point.lon);
                if (!Number.isFinite(lat) || !Number.isFinite(lon)) return;
                const pixel = map.latLngToLayerPoint([lat, lon]);
                let cluster = clusters.find((item) => item.pixel.distanceTo(pixel) <= threshold);
                if (!cluster) {
                    cluster = { pixel, points: [], lat: 0, lon: 0 };
                    clusters.push(cluster);
                }
                cluster.points.push({ ...point, lat, lon });
            });

            clusters.forEach((cluster) => {
                cluster.lat = cluster.points.reduce((sum, point) => sum + point.lat, 0) / cluster.points.length;
                cluster.lon = cluster.points.reduce((sum, point) => sum + point.lon, 0) / cluster.points.length;

                if (cluster.points.length === 1) {
                    const point = cluster.points[0];
                    L.marker([point.lat, point.lon], { icon: markerIcon(point.color, point.icon) }).addTo(layer).bindPopup(pointPopup(point));
                    return;
                }

                const marker = L.marker([cluster.lat, cluster.lon], { icon: clusterIcon(cluster.points.length), keyboard: true }).addTo(layer);
                marker.on('click', () => {
                    const bounds = L.latLngBounds(cluster.points.map((point) => [point.lat, point.lon]));
                    const spread = bounds.getNorthEast().distanceTo(bounds.getSouthWest());
                    if (map.getZoom() < 16 && spread > 15) {
                        map.fitBounds(bounds.pad(0.45), { maxZoom: 16, animate: true });
                    } else if (map.getZoom() < 17) {
                        map.setView([cluster.lat, cluster.lon], map.getZoom() + 2, { animate: true });
                    } else {
                        spiderfy(cluster);
                    }
                });
            });
        };

        map.on('zoomend moveend', render);
        map.on('click', clearSpider);
        render();
        return { render, clearSpider };
    };


    const hereMarkerIcon = (color = '#d62828', icon = 'signal', size = 40) => {
        const safeColor = String(color || '#d62828').replace(/[^#a-z0-9(),.%\s-]/gi, '');
        const symbol = escapeHtml(markerSymbol(icon));
        const width = size;
        const height = Math.round(size * 1.12);
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="0 0 40 45">
            <path d="M20 1C9.5 1 1 9.5 1 20c0 13.5 19 24 19 24s19-10.5 19-24C39 9.5 30.5 1 20 1z" fill="${safeColor}" stroke="#fff" stroke-width="2.5"/>
            <circle cx="20" cy="19" r="9.2" fill="rgba(10,20,35,.28)" stroke="rgba(255,255,255,.42)"/>
            <text x="20" y="23.5" text-anchor="middle" font-family="Arial,sans-serif" font-size="13" font-weight="800" fill="#fff">${symbol}</text>
        </svg>`;
        return new H.map.Icon(svg, { size: { w: width, h: height }, anchor: { x: width / 2, y: height - 2 } });
    };

    const hereClusterIcon = (count) => {
        const safeCount = Number(count) || 1;
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="58" height="58" viewBox="0 0 58 58">
            <circle cx="29" cy="29" r="27" fill="#0f2740" stroke="#fff" stroke-width="3"/>
            <circle cx="29" cy="29" r="20" fill="#d62828" opacity=".92"/>
            <text x="29" y="34" text-anchor="middle" font-family="Arial,sans-serif" font-size="16" font-weight="800" fill="#fff">${safeCount}</text>
        </svg>`;
        return new H.map.Icon(svg, { size: { w: 58, h: 58 }, anchor: { x: 29, y: 29 } });
    };

    const addHereInteraction = (map, layers) => {
        const events = new H.mapevents.MapEvents(map);
        const behavior = new H.mapevents.Behavior(events);
        const ui = H.ui.UI.createDefault(map, layers, 'de-DE');
        const resize = () => map.getViewPort().resize();
        window.addEventListener('resize', resize, { passive: true });
        return { behavior, ui, resize };
    };

    const showHereBubble = (ui, position, html) => {
        ui.getBubbles().forEach((bubble) => ui.removeBubble(bubble));
        ui.addBubble(new H.ui.InfoBubble(position, { content: html }));
    };

    const initHereSingleMap = async (element) => {
        await ensureHereMaps();
        const lat = Number.parseFloat(element.dataset.lat);
        const lon = Number.parseFloat(element.dataset.lon);
        if (!Number.isFinite(lat) || !Number.isFinite(lon)) throw new Error('Ungültige Koordinaten.');

        const platform = new H.service.Platform({ apikey: String(fflEinsatzlyzer.hereApiKey || '') });
        const layers = platform.createDefaultLayers();
        const map = new H.Map(element, hereBaseLayer(layers), {
            center: { lat, lng: lon },
            zoom: 15,
            pixelRatio: window.devicePixelRatio || 1
        });
        const interaction = addHereInteraction(map, layers);
        const point = {
            lat,
            lon,
            title: element.dataset.title || 'Einsatzort',
            location: element.dataset.location || '',
            color: element.dataset.color || '#d62828',
            icon: element.dataset.icon || 'signal'
        };
        const marker = new H.map.Marker({ lat, lng: lon }, { icon: hereMarkerIcon(point.color, point.icon) });
        marker.setData(point);
        marker.addEventListener('tap', () => {
            showHereBubble(interaction.ui, marker.getGeometry(), `<div class="ffl-map-popup"><strong>${escapeHtml(point.title)}</strong><span>${escapeHtml(point.location)}</span></div>`);
        });
        map.addObject(marker);
        element.dataset.mapReady = '1';
        element.fflMapInstance = map;
        element.fflMapProvider = 'here';
        return map;
    };

    const initHereOverviewMap = async (element, mapData) => {
        await ensureHereMaps();
        const station = fflEinsatzlyzer.station || {};
        const centerLat = Number.parseFloat(station.lat) || 53.269114;
        const centerLon = Number.parseFloat(station.lon) || 7.668382;
        const platform = new H.service.Platform({ apikey: String(fflEinsatzlyzer.hereApiKey || '') });
        const layers = platform.createDefaultLayers();
        const map = new H.Map(element, hereBaseLayer(layers), {
            center: { lat: centerLat, lng: centerLon },
            zoom: 11,
            pixelRatio: window.devicePixelRatio || 1
        });
        const interaction = addHereInteraction(map, layers);
        const points = (Array.isArray(mapData.points) ? mapData.points : []).filter((point) => Number.isFinite(Number.parseFloat(point.lat)) && Number.isFinite(Number.parseFloat(point.lon)));
        const boundsStrip = new H.geo.LineString();

        const displayPoint = (point, marker) => {
            showHereBubble(interaction.ui, marker.getGeometry(), pointPopup(point));
        };

        if (window.H?.clustering?.Provider && points.length > 12) {
            const dataPoints = points.map((point) => {
                const lat = Number.parseFloat(point.lat);
                const lon = Number.parseFloat(point.lon);
                boundsStrip.pushPoint({ lat, lng: lon });
                return new H.clustering.DataPoint(lat, lon, 1, point);
            });
            const provider = new H.clustering.Provider(dataPoints, {
                clusteringOptions: { eps: 40, minWeight: 2 },
                theme: {
                    getClusterPresentation: (cluster) => {
                        const marker = new H.map.Marker(cluster.getPosition(), {
                            icon: hereClusterIcon(cluster.getWeight()),
                            min: cluster.getMinZoom(),
                            max: cluster.getMaxZoom()
                        });
                        marker.setData(cluster);
                        return marker;
                    },
                    getNoisePresentation: (noisePoint) => {
                        const point = noisePoint.getData();
                        const marker = new H.map.Marker(noisePoint.getPosition(), {
                            icon: hereMarkerIcon(point.color, point.icon),
                            min: noisePoint.getMinZoom()
                        });
                        marker.setData(point);
                        return marker;
                    }
                }
            });
            provider.addEventListener('tap', (event) => {
                const marker = event.target;
                const data = marker?.getData?.();
                if (data?.isCluster?.()) {
                    map.setCenter(data.getPosition(), true);
                    map.setZoom(Math.min(17, map.getZoom() + 2), true);
                    return;
                }
                if (data?.title) displayPoint(data, marker);
            });
            map.addLayer(new H.map.layer.ObjectLayer(provider));
        } else {
            points.forEach((point) => {
                const lat = Number.parseFloat(point.lat);
                const lon = Number.parseFloat(point.lon);
                boundsStrip.pushPoint({ lat, lng: lon });
                const marker = new H.map.Marker({ lat, lng: lon }, { icon: hereMarkerIcon(point.color, point.icon) });
                marker.setData(point);
                marker.addEventListener('tap', () => displayPoint(point, marker));
                map.addObject(marker);
            });
        }

        if (points.length === 1) {
            map.setCenter({ lat: Number.parseFloat(points[0].lat), lng: Number.parseFloat(points[0].lon) });
            map.setZoom(14);
        } else if (boundsStrip.getPointCount() > 1) {
            const bounds = boundsStrip.getBoundingBox();
            if (bounds) map.getViewModel().setLookAtData({ bounds }, true);
        }

        element.dataset.mapReady = '1';
        element.fflMapInstance = map;
        element.fflMapProvider = 'here';
        return map;
    };

    const googleMarkerOptions = (point, map = null) => ({
        map,
        position: { lat: Number.parseFloat(point.lat), lng: Number.parseFloat(point.lon) },
        title: String(point.title || 'Einsatzort'),
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            fillColor: String(point.color || '#d62828'),
            fillOpacity: 1,
            strokeColor: '#ffffff',
            strokeOpacity: 1,
            strokeWeight: 3,
            scale: 12
        },
        label: {
            text: markerSymbol(point.icon),
            color: '#ffffff',
            fontSize: '13px',
            fontWeight: '800'
        }
    });

    const createGoogleMarkers = async (map, points) => {
        const infoWindow = new google.maps.InfoWindow();
        const markers = points.map((point) => {
            const marker = new google.maps.Marker(googleMarkerOptions(point));
            marker.addListener('click', () => {
                infoWindow.setContent(pointPopup(point));
                infoWindow.open({ map, anchor: marker });
            });
            return marker;
        });

        try {
            const clusterLibrary = await ensureGoogleClusterer();
            if (clusterLibrary?.MarkerClusterer) {
                new clusterLibrary.MarkerClusterer({ map, markers });
                return markers;
            }
        } catch (error) {
            // Ohne Cluster-Bibliothek bleiben die Marker einzeln nutzbar.
        }

        markers.forEach((marker) => marker.setMap(map));
        return markers;
    };

    const initGoogleSingleMap = async (element) => {
        await ensureGoogleMaps();
        const lat = Number.parseFloat(element.dataset.lat);
        const lon = Number.parseFloat(element.dataset.lon);
        if (!Number.isFinite(lat) || !Number.isFinite(lon)) throw new Error('Ungültige Koordinaten.');

        const map = new google.maps.Map(element, {
            center: { lat, lng: lon },
            zoom: 15,
            mapTypeId: fflEinsatzlyzer.googleMapType || 'roadmap',
            gestureHandling: 'cooperative',
            streetViewControl: false,
            fullscreenControl: true,
            mapTypeControl: true
        });
        const point = {
            lat,
            lon,
            title: element.dataset.title || 'Einsatzort',
            location: element.dataset.location || '',
            color: element.dataset.color || '#d62828',
            icon: element.dataset.icon || 'signal'
        };
        const marker = new google.maps.Marker(googleMarkerOptions(point, map));
        const infoWindow = new google.maps.InfoWindow({
            content: `<div class="ffl-map-popup"><strong>${escapeHtml(point.title)}</strong><span>${escapeHtml(point.location)}</span></div>`
        });
        marker.addListener('click', () => infoWindow.open({ map, anchor: marker }));
        element.dataset.mapReady = '1';
        element.fflMapInstance = map;
        return map;
    };

    const initGoogleOverviewMap = async (element, mapData) => {
        await ensureGoogleMaps();
        const map = new google.maps.Map(element, {
            center: { lat: 53.269114, lng: 7.668382 },
            zoom: 11,
            mapTypeId: fflEinsatzlyzer.googleMapType || 'roadmap',
            gestureHandling: 'cooperative',
            streetViewControl: false,
            fullscreenControl: true,
            mapTypeControl: true
        });
        const bounds = new google.maps.LatLngBounds();
        const points = (Array.isArray(mapData.points) ? mapData.points : []).filter((point) => Number.isFinite(Number.parseFloat(point.lat)) && Number.isFinite(Number.parseFloat(point.lon)));

        points.forEach((point) => bounds.extend({ lat: Number.parseFloat(point.lat), lng: Number.parseFloat(point.lon) }));
        await createGoogleMarkers(map, points);

        if (points.length === 1) {
            map.setCenter({ lat: Number.parseFloat(points[0].lat), lng: Number.parseFloat(points[0].lon) });
            map.setZoom(14);
        } else if (!bounds.isEmpty()) {
            map.fitBounds(bounds, 42);
        }

        element.dataset.mapReady = '1';
        element.fflMapInstance = map;
        return map;
    };

    const showMapError = (element, error) => {
        element.classList.remove('is-awaiting-consent');
        element.classList.add('ffl-map-error');
        element.innerHTML = `<div><strong>${escapeHtml(fflEinsatzlyzer.mapErrorText || 'Die Karte konnte nicht geladen werden.')}</strong><span>${escapeHtml(error?.message || '')}</span></div>`;
        element.dataset.mapReady = 'error';
    };

    const mountGoogleConsent = (element, onAccept) => {
        if (element.dataset.googleConsentMounted) return;
        element.dataset.googleConsentMounted = '1';
        element.dataset.mapReady = 'consent';
        element.classList.add('is-awaiting-consent');
        element.innerHTML = `
            <div class="ffl-google-consent">
                <span class="ffl-google-consent__icon" aria-hidden="true">G</span>
                <div><strong>${escapeHtml(fflEinsatzlyzer.googleLoadTitle || 'Google Maps laden')}</strong><p>${escapeHtml(fflEinsatzlyzer.googleLoadText || '')}</p></div>
                <button type="button" class="ffl-button ffl-button--primary">${escapeHtml(fflEinsatzlyzer.googleLoadButton || 'Karte anzeigen')}</button>
            </div>`;
        const button = element.querySelector('button');
        button?.addEventListener('click', async () => {
            button.disabled = true;
            button.textContent = 'Karte wird geladen …';
            element.classList.remove('is-awaiting-consent');
            element.innerHTML = '';
            try {
                await onAccept();
            } catch (error) {
                showMapError(element, error);
            }
        }, { once: true });
    };


    const mountHereConsent = (element, onAccept) => {
        if (element.dataset.hereConsentMounted) return;
        element.dataset.hereConsentMounted = '1';
        element.dataset.mapReady = 'consent';
        element.classList.add('is-awaiting-consent');
        element.innerHTML = `
            <div class="ffl-google-consent ffl-here-consent">
                <span class="ffl-google-consent__icon" aria-hidden="true">H</span>
                <div><strong>${escapeHtml(fflEinsatzlyzer.hereLoadTitle || 'HERE Karte laden')}</strong><p>${escapeHtml(fflEinsatzlyzer.hereLoadText || '')}</p></div>
                <button type="button" class="ffl-button ffl-button--primary">${escapeHtml(fflEinsatzlyzer.hereLoadButton || 'Karte anzeigen')}</button>
            </div>`;
        const button = element.querySelector('button');
        button?.addEventListener('click', async () => {
            button.disabled = true;
            button.textContent = 'Karte wird geladen …';
            element.classList.remove('is-awaiting-consent');
            element.innerHTML = '';
            try {
                await onAccept();
            } catch (error) {
                showMapError(element, error);
            }
        }, { once: true });
    };

    const initSingleMaps = (scope = document) => {
        scope.querySelectorAll('[data-single-map]:not([data-map-ready])').forEach((element) => {
            if (mapProvider === 'google') {
                mountGoogleConsent(element, () => initGoogleSingleMap(element));
                return;
            }
            if (mapProvider === 'here') {
                mountHereConsent(element, () => initHereSingleMap(element));
                return;
            }

            element.dataset.mapReady = 'pending';
            const start = async () => {
                try {
                    await ensureLeaflet();
                    const lat = Number.parseFloat(element.dataset.lat);
                    const lon = Number.parseFloat(element.dataset.lon);
                    if (!Number.isFinite(lat) || !Number.isFinite(lon)) return;
                    const map = L.map(element, { scrollWheelZoom: false, zoomControl: true }).setView([lat, lon], 15);
                    addTiles(map, true);
                    const marker = L.marker([lat, lon], { icon: markerIcon(element.dataset.color, element.dataset.icon) }).addTo(map);
                    marker.bindPopup(`<div class="ffl-map-popup"><strong>${escapeHtml(element.dataset.title || 'Einsatzort')}</strong><span>${escapeHtml(element.dataset.location || '')}</span></div>`);
                    element.addEventListener('click', () => map.scrollWheelZoom.enable(), { once: true });
                    element.dataset.mapReady = '1';
                    element.fflMapInstance = map;
                } catch (error) {
                    showMapError(element, error);
                }
            };

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    if (entries.some((entry) => entry.isIntersecting)) {
                        observer.disconnect();
                        start();
                    }
                }, { rootMargin: '280px' });
                observer.observe(element);
            } else {
                start();
            }
        });
    };

    const parseOverviewMapData = (dataNode) => {
        try {
            const parsed = JSON.parse(dataNode.textContent || '{}');
            return Array.isArray(parsed) ? { points: parsed } : parsed;
        } catch (error) {
            return { points: [] };
        }
    };

    const initOverviewMaps = (scope = document) => {
        scope.querySelectorAll('[data-map-toggle]:not([data-map-bound])').forEach((toggle) => {
            const results = toggle.closest('[data-archive-results]') || scope;
            const wrap = results.querySelector('[data-overview-wrap]');
            const element = results.querySelector('[data-overview-map]');
            const dataNode = results.querySelector('.ffl-overview-map-data');
            if (!wrap || !element || !dataNode) return;
            toggle.dataset.mapBound = '1';
            let map = null;

            const loadOpenStreetMap = async () => {
                await ensureLeaflet();
                const mapData = parseOverviewMapData(dataNode);
                map = L.map(element, { scrollWheelZoom: false }).setView([53.269114, 7.668382], 11);
                const bounds = [];
                const points = Array.isArray(mapData.points) ? mapData.points : [];
                addTiles(map, true);

                points.forEach((point) => {
                    const lat = Number.parseFloat(point.lat);
                    const lon = Number.parseFloat(point.lon);
                    if (Number.isFinite(lat) && Number.isFinite(lon)) bounds.push([lat, lon]);
                });
                createClusteredMarkers(map, points);

                if (bounds.length === 1) map.setView(bounds[0], 14);
                else if (bounds.length > 1) map.fitBounds(bounds, { padding: [35, 35], maxZoom: 14 });
                element.addEventListener('click', () => map.scrollWheelZoom.enable(), { once: true });
                element.fflMapInstance = map;
            };

            const loadMap = async () => {
                toggle.classList.add('is-loading');
                try {
                    if (mapProvider === 'google') map = await initGoogleOverviewMap(element, parseOverviewMapData(dataNode));
                    else if (mapProvider === 'here') map = await initHereOverviewMap(element, parseOverviewMapData(dataNode));
                    else await loadOpenStreetMap();
                } catch (error) {
                    showMapError(element, error);
                } finally {
                    toggle.classList.remove('is-loading');
                }
            };

            toggle.addEventListener('click', async () => {
                const open = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', String(!open));
                wrap.hidden = open;
                const label = toggle.querySelector('[data-map-toggle-label]');
                const subtitle = toggle.querySelector('[data-map-toggle-subtitle]');
                if (label) label.textContent = open ? 'Einsatzorte auf Karte anzeigen' : 'Karte wieder schließen';
                if (subtitle) subtitle.textContent = open ? (toggle.dataset.mapClosedSubtitle || 'Einsatzorte auf der Karte entdecken') : 'Zurück zu den Einsatzberichten';
                toggle.setAttribute('aria-label', open ? 'Einsatzorte auf Karte anzeigen' : 'Karte wieder schließen');
                if (open) return;

                if (!map && mapProvider === 'google') {
                    mountGoogleConsent(element, loadMap);
                } else if (!map && mapProvider === 'here') {
                    mountHereConsent(element, loadMap);
                } else if (!map) {
                    await loadMap();
                }

                window.setTimeout(() => {
                    if (!map) return;
                    if (mapProvider === 'google' && window.google?.maps) google.maps.event.trigger(map, 'resize');
                    else if (mapProvider === 'here') map.getViewPort?.().resize();
                    else map.invalidateSize?.();
                }, 120);
            });
        });
    };

    const initLiveArchives = () => {
        document.querySelectorAll('[data-live-archive]').forEach((archive) => {
            const form = archive.querySelector('[data-live-filter]');
            if (!form || form.dataset.liveBound) return;
            form.dataset.liveBound = '1';
            let controller = null;
            let searchTimer = 0;

            const resultNode = () => archive.querySelector('[data-archive-results]');
            const readState = () => ({
                einsatz_suche: form.elements.einsatz_suche?.value.trim() || '',
                einsatz_jahr: form.elements.einsatz_jahr?.value || '',
                einsatz_art: form.elements.einsatz_art?.value || ''
            });

            const setStateFromUrl = () => {
                const params = new URLSearchParams(window.location.search);
                if (form.elements.einsatz_suche) form.elements.einsatz_suche.value = params.get('einsatz_suche') || '';
                if (form.elements.einsatz_jahr) form.elements.einsatz_jahr.value = params.get('einsatz_jahr') || '';
                if (form.elements.einsatz_art) form.elements.einsatz_art.value = params.get('einsatz_art') || '';
            };

            const updateUrl = (state, page, replace = false) => {
                const target = new URL(archive.dataset.archiveUrl || window.location.href, window.location.href);
                Object.entries(state).forEach(([key, value]) => value ? target.searchParams.set(key, value) : target.searchParams.delete(key));
                if (page > 1) target.searchParams.set('einsatzseite', String(page));
                else target.searchParams.delete('einsatzseite');
                const method = replace ? 'replaceState' : 'pushState';
                history[method]({ fflArchive: true }, '', target.toString());
            };

            const bindDynamic = () => {
                initOverviewMaps(archive);
                archive.querySelectorAll('[data-clear-filter]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const field = form.elements[button.dataset.clearFilter];
                        if (field) field.value = '';
                        loadResults(1, true);
                    });
                });
                archive.querySelectorAll('[data-clear-all]').forEach((button) => {
                    button.addEventListener('click', () => {
                        form.reset();
                        loadResults(1, true);
                    });
                });
                archive.querySelectorAll('.ffl-pagination a').forEach((link) => {
                    link.addEventListener('click', (event) => {
                        event.preventDefault();
                        const url = new URL(link.href, window.location.href);
                        const pathPage = url.pathname.match(/\/page\/(\d+)\/?$/);
                        const page = Number.parseInt(url.searchParams.get('einsatzseite') || (pathPage ? pathPage[1] : '1'), 10) || 1;
                        loadResults(page, true, true);
                    });
                });
            };

            const loadResults = async (page = 1, push = true, scroll = false) => {
                const current = resultNode();
                if (!current) return;
                controller?.abort();
                controller = new AbortController();
                current.setAttribute('aria-busy', 'true');
                archive.classList.add('is-filtering');

                const state = readState();
                const target = new URL(archive.dataset.archiveUrl || window.location.href, window.location.href);
                Object.entries(state).forEach(([key, value]) => value ? target.searchParams.set(key, value) : target.searchParams.delete(key));
                if (page > 1) target.searchParams.set('einsatzseite', String(page));
                else target.searchParams.delete('einsatzseite');

                try {
                    /*
                     * Bewusst die öffentliche Einsatzseite abrufen, nicht wp-admin/admin-ajax.php.
                     * Einige Hoster schützen /wp-admin zusätzlich per HTTP-Basic-Auth. Ein Frontend-
                     * AJAX-Aufruf dorthin würde dann beim Blättern einen Zugangsdaten-Dialog öffnen.
                     */
                    const response = await fetch(target.toString(), {
                        method: 'GET',
                        headers: { 'Accept': 'text/html' },
                        signal: controller.signal,
                        credentials: 'same-origin'
                    });
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    const html = await response.text();
                    const parsed = new DOMParser().parseFromString(html, 'text/html');
                    const replacement = parsed.querySelector('[data-live-archive] [data-archive-results]') || parsed.querySelector('[data-archive-results]');
                    if (!replacement) throw new Error('Archive results missing');
                    current.replaceWith(replacement.cloneNode(true));
                    bindDynamic();
                    if (push) updateUrl(state, page);
                    if (scroll) archive.querySelector('[data-archive-results]')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } catch (error) {
                    if (error.name !== 'AbortError') window.location.assign(target.toString());
                } finally {
                    archive.classList.remove('is-filtering');
                    resultNode()?.setAttribute('aria-busy', 'false');
                }
            };

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                loadResults(1, true);
            });
            form.querySelectorAll('select').forEach((select) => select.addEventListener('change', () => loadResults(1, true)));
            form.elements.einsatz_suche?.addEventListener('input', () => {
                window.clearTimeout(searchTimer);
                searchTimer = window.setTimeout(() => loadResults(1, true), 420);
            });
            window.addEventListener('popstate', () => {
                setStateFromUrl();
                loadResults(Number.parseInt(new URLSearchParams(window.location.search).get('einsatzseite') || '1', 10), false);
            });
            bindDynamic();
        });
    };

    const initGallery = () => {
        const galleryItems = Array.from(document.querySelectorAll('[data-gallery-item]'));
        const lightbox = document.querySelector('[data-lightbox]');
        if (!galleryItems.length || !lightbox || lightbox.dataset.bound) return;
        lightbox.dataset.bound = '1';

        const image = lightbox.querySelector('img');
        const caption = lightbox.querySelector('figcaption');
        const counter = lightbox.querySelector('[data-lightbox-count]');
        const closeButton = lightbox.querySelector('[data-lightbox-close]');
        const prevButton = lightbox.querySelector('[data-lightbox-prev]');
        const nextButton = lightbox.querySelector('[data-lightbox-next]');
        let current = 0;
        let touchStartX = 0;
        let lastFocus = null;

        const show = (index) => {
            current = (index + galleryItems.length) % galleryItems.length;
            const item = galleryItems[current];
            image.src = item.dataset.full || '';
            image.alt = item.querySelector('img')?.alt || `Einsatzbild ${current + 1}`;
            caption.textContent = item.dataset.caption || '';
            counter.textContent = `${current + 1} / ${galleryItems.length}`;
        };

        const open = (index) => {
            lastFocus = document.activeElement;
            show(index);
            lightbox.hidden = false;
            lightbox.setAttribute('aria-hidden', 'false');
            document.body.classList.add('ffl-lightbox-open');
            closeButton?.focus();
        };

        const close = () => {
            lightbox.hidden = true;
            lightbox.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('ffl-lightbox-open');
            image.src = '';
            lastFocus?.focus?.();
        };

        galleryItems.forEach((item, index) => item.addEventListener('click', () => open(index)));
        closeButton?.addEventListener('click', close);
        prevButton?.addEventListener('click', () => show(current - 1));
        nextButton?.addEventListener('click', () => show(current + 1));
        lightbox.addEventListener('click', (event) => { if (event.target === lightbox) close(); });
        lightbox.addEventListener('touchstart', (event) => { touchStartX = event.changedTouches[0]?.screenX || 0; }, { passive: true });
        lightbox.addEventListener('touchend', (event) => {
            const endX = event.changedTouches[0]?.screenX || 0;
            if (Math.abs(endX - touchStartX) >= 45) show(endX < touchStartX ? current + 1 : current - 1);
        }, { passive: true });
        document.addEventListener('keydown', (event) => {
            if (lightbox.hidden) return;
            if (event.key === 'Escape') close();
            if (event.key === 'ArrowLeft') show(current - 1);
            if (event.key === 'ArrowRight') show(current + 1);
            if (event.key === 'Tab') {
                const focusable = [closeButton, prevButton, nextButton].filter(Boolean);
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
                if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
            }
        });
    };

    const initShare = () => {
        document.querySelectorAll('[data-share-url]:not([data-share-bound])').forEach((button) => {
            button.dataset.shareBound = '1';
            button.addEventListener('click', async () => {
                const url = button.dataset.shareUrl || window.location.href;
                const title = button.dataset.shareTitle || document.title;
                if (navigator.share) {
                    try { await navigator.share({ title, url }); } catch (error) { /* Benutzerabbruch */ }
                    return;
                }
                await copyText(url);
                showTemporaryLabel(button, 'Link kopiert');
            });
        });

        document.querySelectorAll('[data-copy-url]:not([data-copy-bound])').forEach((button) => {
            button.dataset.copyBound = '1';
            button.addEventListener('click', async () => {
                await copyText(button.dataset.copyUrl || window.location.href);
                const card = button.closest('.ffl-share-card') || button.parentElement;
                const status = card?.querySelector('.ffl-copy-status');
                if (status) {
                    status.textContent = fflEinsatzlyzer.copyText || 'Link kopiert';
                    window.setTimeout(() => { status.textContent = ''; }, 2200);
                }
            });
        });
    };



    const normalizeManualElementorHeader = () => {
        const wrapper = document.querySelector('.ffl-manual-elementor-header');
        if (!wrapper) return;

        const root = wrapper.firstElementChild;
        if (!root) return;

        const collapse = () => {
            wrapper.style.minHeight = '0';
            wrapper.style.marginBottom = '0';
            wrapper.style.paddingBottom = '0';

            wrapper.querySelectorAll('.elementor-sticky__spacer').forEach((spacer) => {
                spacer.style.setProperty('display', 'none', 'important');
                spacer.style.setProperty('height', '0', 'important');
                spacer.style.setProperty('min-height', '0', 'important');
                spacer.style.setProperty('margin', '0', 'important');
                spacer.style.setProperty('padding', '0', 'important');
            });

            /* Nur die obersten Elementor-Hüllen neutralisieren. Verschachtelte
               Menücontainer behalten ihre eigenen Abstände und Höhen. */
            const shells = [root];
            if (root.matches('.elementor')) {
                shells.push(...Array.from(root.children));
            }
            shells.forEach((shell) => {
                if (!(shell instanceof HTMLElement)) return;
                shell.style.setProperty('min-height', '0', 'important');
                shell.style.setProperty('--min-height', '0px');
                shell.style.setProperty('margin-bottom', '0', 'important');
            });

            const meaningful = Array.from(wrapper.querySelectorAll(
                '.elementor-widget:not(.elementor-widget-spacer), nav, img, svg, button, [role="navigation"], .elementor-nav-menu'
            )).filter((element) => {
                const rect = element.getBoundingClientRect();
                const style = window.getComputedStyle(element);
                return rect.width > 0 && rect.height > 0 && style.display !== 'none' && style.visibility !== 'hidden';
            });

            if (!meaningful.length) return;

            const wrapperRect = wrapper.getBoundingClientRect();
            let visibleBottom = wrapperRect.top;
            meaningful.forEach((element) => {
                const rect = element.getBoundingClientRect();
                if (rect.bottom > visibleBottom) visibleBottom = rect.bottom;
            });

            const contentHeight = Math.ceil(Math.max(1, visibleBottom - wrapperRect.top + 12));
            const currentHeight = wrapper.getBoundingClientRect().height;

            /* Nur deutlich übergroße Leerbereiche korrigieren. Normale
               Elementor-Abstände innerhalb des Menüs bleiben erhalten. */
            if (currentHeight > contentHeight + 70) {
                wrapper.style.setProperty('height', `${contentHeight}px`, 'important');
                wrapper.style.setProperty('min-height', '0', 'important');
                wrapper.style.setProperty('overflow', 'visible', 'important');
                root.style.setProperty('height', `${contentHeight}px`, 'important');
                root.style.setProperty('min-height', '0', 'important');
                root.style.setProperty('overflow', 'visible', 'important');
            }
        };

        window.requestAnimationFrame(collapse);
        window.setTimeout(collapse, 250);
        window.setTimeout(collapse, 900);
        window.addEventListener('resize', collapse, { passive: true });
    };

    const initJumpNavigation = () => {
        const nav = document.querySelector('.ffl-mobile-jumpnav');
        if (!nav || !('IntersectionObserver' in window)) return;
        const links = Array.from(nav.querySelectorAll('a[href^="#"]'));
        const sections = links.map((link) => document.querySelector(link.getAttribute('href'))).filter(Boolean);
        const observer = new IntersectionObserver((entries) => {
            const visible = entries.filter((entry) => entry.isIntersecting).sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
            if (!visible) return;
            links.forEach((link) => link.classList.toggle('is-active', link.getAttribute('href') === `#${visible.target.id}`));
        }, { rootMargin: '-20% 0px -65% 0px', threshold: [0.01, 0.25, 0.6] });
        sections.forEach((section) => observer.observe(section));
    };

    const copyText = async (text) => {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);
            return;
        }
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        textarea.remove();
    };

    const showTemporaryLabel = (button, text) => {
        const original = button.innerHTML;
        button.textContent = text;
        window.setTimeout(() => { button.innerHTML = original; }, 1800);
    };

    const escapeHtml = (value) => String(value)
        .replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;').replaceAll("'", '&#039;');
    const escapeAttribute = (value) => escapeHtml(value).replaceAll('`', '&#096;');

    ready(() => {
        initLiveArchives();
        initSingleMaps();
        initOverviewMaps();
        initGallery();
        initShare();
        initJumpNavigation();
        normalizeManualElementorHeader();
    });
})();
