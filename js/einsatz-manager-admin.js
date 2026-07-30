jQuery(function ($) {
    'use strict';

    const wrapper = $('#ffl-admin-map-popup-wrapper');
    const status = $('#ffl-geocode-status');
    const latField = $('#ffl_lat');
    const lonField = $('#ffl_lon');
    const addressField = $('#ffl_einsatzort');
    let map = null;
    let marker = null;

    const openMap = () => {
        wrapper.addClass('is-open').attr('aria-hidden', 'false');
        $('body').css('overflow', 'hidden');
        if (!map) initMap();
        window.setTimeout(() => map && map.invalidateSize(), 120);
    };

    const closeMap = () => {
        wrapper.removeClass('is-open').attr('aria-hidden', 'true');
        $('body').css('overflow', '');
    };

    const initMap = () => {
        const fallbackLat = Number.parseFloat(ffl_einsatz_admin_data.fw_lat) || 53.269114;
        const fallbackLon = Number.parseFloat(ffl_einsatz_admin_data.fw_lon) || 7.668382;
        const currentLat = Number.parseFloat(latField.val());
        const currentLon = Number.parseFloat(lonField.val());
        const initial = [Number.isFinite(currentLat) ? currentLat : fallbackLat, Number.isFinite(currentLon) ? currentLon : fallbackLon];

        map = L.map('ffl-admin-map').setView(initial, Number.isFinite(currentLat) ? 16 : 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap',
            maxZoom: 19
        }).addTo(map);

        marker = L.marker(initial, { draggable: true }).addTo(map);
        if (!Number.isFinite(currentLat)) marker.setOpacity(.45);

        const update = (latlng) => {
            marker.setLatLng(latlng).setOpacity(1);
            latField.val(Number(latlng.lat).toFixed(8));
            lonField.val(Number(latlng.lng).toFixed(8));
        };

        map.on('click', (event) => update(event.latlng));
        marker.on('dragend', () => update(marker.getLatLng()));
    };

    const moveMap = (lat, lon) => {
        if (!map) initMap();
        const latlng = L.latLng(lat, lon);
        marker.setLatLng(latlng).setOpacity(1);
        map.setView(latlng, 16);
        latField.val(Number(lat).toFixed(8));
        lonField.val(Number(lon).toFixed(8));
    };

    $('#ffl-geocode-and-show-map-button').on('click', function () {
        openMap();
        const address = String(addressField.val() || '').trim();
        if (!address) {
            status.text('Karte geöffnet – Position direkt anklicken.');
            return;
        }

        status.text('Adresse wird gesucht …');
        $.post(ffl_einsatz_admin_data.ajax_url, {
            action: 'ffl_geocode',
            nonce: ffl_einsatz_admin_data.geocode_nonce,
            address
        }).done((response) => {
            if (response && response.success && response.data) {
                moveMap(response.data.lat, response.data.lon);
                status.text('Adresse gefunden. Position kann auf der Karte angepasst werden.');
            } else {
                status.text(response?.data?.message || 'Adresse konnte nicht gefunden werden.');
            }
        }).fail((xhr) => {
            status.text(xhr.responseJSON?.data?.message || 'Adresssuche momentan nicht erreichbar.');
        });
    });

    $('.ffl-admin-map-close, #ffl-admin-map-overlay').on('click', closeMap);
    $(document).on('keydown', (event) => {
        if (event.key === 'Escape' && wrapper.hasClass('is-open')) closeMap();
    });

    let mediaUploader = null;
    $('#ffl_upload_gallery_button').on('click', function (event) {
        event.preventDefault();
        if (!window.wp || !wp.media) {
            window.alert('Die WordPress-Mediathek konnte nicht geladen werden.');
            return;
        }

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: 'Bilder für die Einsatzgalerie auswählen',
            button: { text: 'Bilder übernehmen' },
            library: { type: 'image' },
            multiple: true
        });

        mediaUploader.on('select', function () {
            const currentIds = String($('#ffl_gallery_ids').val() || '').split(',').filter(Boolean);
            mediaUploader.state().get('selection').each(function (attachmentModel) {
                const attachment = attachmentModel.toJSON();
                const id = String(attachment.id);
                if (currentIds.includes(id)) return;
                currentIds.push(id);
                const thumb = attachment.sizes?.thumbnail?.url || attachment.url;
                $('#ffl-gallery-preview-container').append(`
                    <div class="gallery-thumb-wrapper" data-attachment-id="${id}">
                        <img src="${thumb}" alt="">
                        <button type="button" class="remove-gallery-image" aria-label="Bild entfernen">&times;</button>
                        <span class="dashicons dashicons-move"></span>
                    </div>
                `);
            });
            $('#ffl_gallery_ids').val(currentIds.join(','));
        });

        mediaUploader.open();
    });

    const updateGalleryIds = () => {
        const ids = $('#ffl-gallery-preview-container .gallery-thumb-wrapper').map(function () {
            return String($(this).data('attachment-id'));
        }).get();
        $('#ffl_gallery_ids').val(ids.join(','));
    };

    $('#ffl-gallery-preview-container').sortable({
        items: '.gallery-thumb-wrapper',
        update: updateGalleryIds
    });

    $('#ffl-gallery-preview-container').on('click', '.remove-gallery-image', function () {
        $(this).closest('.gallery-thumb-wrapper').remove();
        updateGalleryIds();
    });
});
