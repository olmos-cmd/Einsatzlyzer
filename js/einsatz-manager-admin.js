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

    const previewFocusMode = $('#ffl_preview_focus_mode');
    const previewFocusCustom = $('[data-preview-focus-custom]');
    const previewFocusX = $('#ffl_preview_focus_x');
    const previewFocusY = $('#ffl_preview_focus_y');
    const previewFocusXRange = $('#ffl_preview_focus_x_range');
    const previewFocusYRange = $('#ffl_preview_focus_y_range');
    const previewFocusStage = $('[data-preview-focus-stage]');
    const previewFocusPicker = $('[data-preview-focus-picker]');
    const previewFocusCard = $('[data-preview-focus-card]');
    const previewFocusMarker = $('[data-preview-focus-marker]');
    const galleryContainer = $('#ffl-gallery-preview-container');
    const galleryIdsField = $('#ffl_gallery_ids');

    const previewFocusPresets = {
        center: [50, 50],
        top: [50, 0],
        bottom: [50, 100],
        left: [0, 50],
        right: [100, 50]
    };

    const clampFocus = (value) => {
        const numeric = Number.parseFloat(value);
        if (!Number.isFinite(numeric)) return 50;
        return Math.max(0, Math.min(100, Math.round(numeric)));
    };

    const getPreviewSourceImage = () => previewFocusPicker.find('[data-preview-focus-source-image]');
    const getPreviewCardImage = () => previewFocusCard.find('[data-preview-focus-card-image]');
    const previewFocusEffect = $('[data-preview-focus-effect]');

    const getEffectiveFocus = () => {
        const mode = String(previewFocusMode.val() || 'center');
        if (mode === 'custom') {
            return [clampFocus(previewFocusX.val()), clampFocus(previewFocusY.val())];
        }
        return previewFocusPresets[mode] || previewFocusPresets.center;
    };

    const syncFocusInputs = (x, y) => {
        const nextX = clampFocus(x);
        const nextY = clampFocus(y);
        previewFocusX.val(nextX);
        previewFocusY.val(nextY);
        previewFocusXRange.val(nextX);
        previewFocusYRange.val(nextY);
    };

    const getContainedImageBounds = () => {
        const picker = previewFocusPicker.get(0);
        const image = getPreviewSourceImage().get(0);
        if (!picker) return null;

        const rect = picker.getBoundingClientRect();
        let width = Math.max(1, picker.clientWidth || rect.width);
        let height = Math.max(1, picker.clientHeight || rect.height);
        let offsetX = 0;
        let offsetY = 0;

        if (image && image.naturalWidth > 0 && image.naturalHeight > 0) {
            const scale = Math.min(width / image.naturalWidth, height / image.naturalHeight);
            const renderedWidth = image.naturalWidth * scale;
            const renderedHeight = image.naturalHeight * scale;
            offsetX = (width - renderedWidth) / 2;
            offsetY = (height - renderedHeight) / 2;
            width = renderedWidth;
            height = renderedHeight;
        }

        return {
            left: rect.left + offsetX,
            top: rect.top + offsetY,
            localLeft: offsetX,
            localTop: offsetY,
            width: Math.max(1, width),
            height: Math.max(1, height)
        };
    };

    const positionFocusMarker = (position) => {
        if (!previewFocusMarker.length || !getPreviewSourceImage().length) return;
        const bounds = getContainedImageBounds();
        if (!bounds) return;

        const x = clampFocus(position[0]);
        const y = clampFocus(position[1]);
        previewFocusMarker.css({
            left: `${bounds.localLeft + (bounds.width * x / 100)}px`,
            top: `${bounds.localTop + (bounds.height * y / 100)}px`
        });
    };

    const renderPreviewCard = (position) => {
        const card = previewFocusCard.get(0);
        const image = getPreviewCardImage().get(0);
        if (!card || !image) return;

        const x = clampFocus(position[0]);
        const y = clampFocus(position[1]);
        const cardRect = card.getBoundingClientRect();
        const cardWidth = Math.max(1, card.clientWidth || cardRect.width);
        const cardHeight = Math.max(1, card.clientHeight || cardRect.height);

        if (!(image.naturalWidth > 0 && image.naturalHeight > 0)) {
            image.style.setProperty('object-position', `${x}% ${y}%`, 'important');
            return;
        }

        /*
         * Render the crop explicitly instead of relying on object-position in the
         * WordPress admin. This mirrors object-fit: cover mathematically and makes
         * every available movement immediately visible in the live preview.
         */
        const scale = Math.max(cardWidth / image.naturalWidth, cardHeight / image.naturalHeight);
        const renderedWidth = image.naturalWidth * scale;
        const renderedHeight = image.naturalHeight * scale;
        const overflowX = Math.max(0, renderedWidth - cardWidth);
        const overflowY = Math.max(0, renderedHeight - cardHeight);
        const left = -(overflowX * x / 100);
        const top = -(overflowY * y / 100);

        image.style.removeProperty('object-position');
        image.style.width = `${renderedWidth}px`;
        image.style.height = `${renderedHeight}px`;
        image.style.left = `${left}px`;
        image.style.top = `${top}px`;

        if (previewFocusEffect.length) {
            let message = String(previewFocusEffect.attr('data-both') || '');
            if (overflowX < 0.75 && overflowY < 0.75) {
                message = String(previewFocusEffect.attr('data-none') || message);
            } else if (overflowX < 0.75) {
                message = String(previewFocusEffect.attr('data-vertical') || message);
            } else if (overflowY < 0.75) {
                message = String(previewFocusEffect.attr('data-horizontal') || message);
            }
            previewFocusEffect.text(message);
        }
    };

    const updatePreviewFocus = () => {
        if (!previewFocusMode.length) return;

        const mode = String(previewFocusMode.val() || 'center');
        const isCustom = mode === 'custom';
        previewFocusCustom.prop('hidden', !isCustom);

        const position = getEffectiveFocus();
        if (isCustom) syncFocusInputs(position[0], position[1]);

        renderPreviewCard(position);
        positionFocusMarker(position);
    };

    const setCustomFocus = (x, y) => {
        previewFocusMode.val('custom');
        previewFocusCustom.prop('hidden', false);
        syncFocusInputs(x, y);
        updatePreviewFocus();
    };

    const focusFromPointer = (event) => {
        const bounds = getContainedImageBounds();
        if (!bounds) return;
        const sourceEvent = event.originalEvent || event;
        const clientX = Number.isFinite(event.clientX) ? event.clientX : sourceEvent.clientX;
        const clientY = Number.isFinite(event.clientY) ? event.clientY : sourceEvent.clientY;
        if (!Number.isFinite(clientX) || !Number.isFinite(clientY)) return;
        const x = ((clientX - bounds.left) / bounds.width) * 100;
        const y = ((clientY - bounds.top) / bounds.height) * 100;
        setCustomFocus(x, y);
    };

    const bindSourceImageLoad = () => {
        const sourceImage = getPreviewSourceImage();
        sourceImage.off('load.fflPreviewFocus').on('load.fflPreviewFocus', function () {
            updatePreviewFocus();
        });
    };

    const setPreviewImage = (url) => {
        if (!previewFocusStage.length) return;
        const cleanUrl = String(url || '').trim();
        let sourceImage = getPreviewSourceImage();
        let cardImage = getPreviewCardImage();

        if (!cleanUrl) {
            sourceImage.remove();
            cardImage.remove();
            previewFocusPicker.removeClass('has-image is-dragging');
            previewFocusCard.removeClass('has-image');
            previewFocusStage.removeClass('has-image');
            if (!previewFocusPicker.find('[data-preview-focus-source-empty]').length) {
                $('<span>', {
                    'data-preview-focus-source-empty': '',
                    text: String(previewFocusStage.attr('data-empty-text') || 'No preview image available yet.')
                }).prependTo(previewFocusPicker);
            }
            if (!previewFocusCard.find('[data-preview-focus-card-empty]').length) {
                $('<span>', {
                    'data-preview-focus-card-empty': '',
                    text: String(previewFocusStage.attr('data-empty-text') || 'No preview image available yet.')
                }).prependTo(previewFocusCard);
            }
            return;
        }

        previewFocusPicker.find('[data-preview-focus-source-empty]').remove();
        previewFocusCard.find('[data-preview-focus-card-empty]').remove();

        if (!sourceImage.length) {
            sourceImage = $('<img>', {
                alt: '',
                draggable: false,
                'data-preview-focus-source-image': ''
            }).prependTo(previewFocusPicker);
        }
        if (!cardImage.length) {
            cardImage = $('<img>', {
                alt: '',
                draggable: false,
                'data-preview-focus-card-image': ''
            }).prependTo(previewFocusCard);
        }

        bindSourceImageLoad();
        cardImage.off('load.fflPreviewFocusCard').on('load.fflPreviewFocusCard', function () {
            updatePreviewFocus();
        });
        if (sourceImage.attr('src') !== cleanUrl) sourceImage.attr('src', cleanUrl);
        if (cardImage.attr('src') !== cleanUrl) cardImage.attr('src', cleanUrl);

        previewFocusPicker.addClass('has-image');
        previewFocusCard.addClass('has-image');
        previewFocusStage.addClass('has-image');
        updatePreviewFocus();
    };

    const attachmentPreviewUrl = (attachment) => {
        if (!attachment) return '';
        const sizes = attachment.sizes || {};
        return sizes.large?.url || sizes.medium_large?.url || sizes.medium?.url || attachment.url || '';
    };

    const getFeaturedImageId = () => {
        let featuredId = Number.parseInt(String($('#_thumbnail_id').val() || ''), 10) || 0;
        if (!featuredId && window.wp?.data?.select) {
            try {
                featuredId = Number.parseInt(window.wp.data.select('core/editor')?.getEditedPostAttribute('featured_media'), 10) || 0;
            } catch (error) {
                featuredId = 0;
            }
        }
        return featuredId;
    };

    let requestedFeaturedId = 0;
    const syncPreviewImageSource = () => {
        if (!previewFocusStage.length) return;

        const featuredId = getFeaturedImageId();
        if (featuredId && window.wp?.media) {
            requestedFeaturedId = featuredId;
            const model = window.wp.media.attachment(featuredId);
            const applyFeatured = () => {
                if (getFeaturedImageId() !== featuredId || requestedFeaturedId !== featuredId) return;
                const url = attachmentPreviewUrl(model.toJSON ? model.toJSON() : model.attributes);
                if (url) setPreviewImage(url);
            };

            const existingUrl = attachmentPreviewUrl(model.toJSON ? model.toJSON() : model.attributes);
            if (existingUrl) {
                setPreviewImage(existingUrl);
            } else if (typeof model.fetch === 'function') {
                const request = model.fetch();
                if (request && typeof request.done === 'function') request.done(applyFeatured);
            }
            return;
        }

        requestedFeaturedId = 0;
        const firstGallery = galleryContainer.find('.gallery-thumb-wrapper').first();
        const galleryUrl = String(firstGallery.attr('data-preview-url') || firstGallery.find('img').attr('src') || '');
        setPreviewImage(galleryUrl);
    };

    const updateGalleryIds = () => {
        const ids = galleryContainer.find('.gallery-thumb-wrapper').map(function () {
            return String($(this).data('attachment-id'));
        }).get();
        galleryIdsField.val(ids.join(','));
        syncPreviewImageSource();
    };

    const appendGalleryThumb = (attachment) => {
        const id = String(attachment.id || '');
        if (!id || galleryContainer.find(`.gallery-thumb-wrapper[data-attachment-id="${id}"]`).length) return;

        const thumb = attachment.sizes?.thumbnail?.url || attachment.url || '';
        const previewUrl = attachmentPreviewUrl(attachment) || thumb;
        const wrapper = $('<div>', {
            class: 'gallery-thumb-wrapper',
            'data-attachment-id': id,
            'data-preview-url': previewUrl
        });
        $('<img>', { src: thumb, alt: '' }).appendTo(wrapper);
        $('<button>', {
            type: 'button',
            class: 'remove-gallery-image',
            'aria-label': 'Bild entfernen',
            html: '&times;'
        }).appendTo(wrapper);
        $('<span>', { class: 'dashicons dashicons-move' }).appendTo(wrapper);
        galleryContainer.append(wrapper);
    };

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
            mediaUploader.state().get('selection').each(function (attachmentModel) {
                appendGalleryThumb(attachmentModel.toJSON());
            });
            updateGalleryIds();
        });

        mediaUploader.open();
    });

    if (galleryContainer.length && typeof galleryContainer.sortable === 'function') {
        galleryContainer.sortable({
            items: '.gallery-thumb-wrapper',
            update: updateGalleryIds
        });
    }

    galleryContainer.on('click', '.remove-gallery-image', function () {
        $(this).closest('.gallery-thumb-wrapper').remove();
        updateGalleryIds();
    });

    previewFocusMode.on('change', function () {
        const position = getEffectiveFocus();
        if (String(previewFocusMode.val()) === 'custom') syncFocusInputs(position[0], position[1]);
        updatePreviewFocus();
    });

    previewFocusXRange.on('input change', function () {
        setCustomFocus($(this).val(), previewFocusY.val());
    });
    previewFocusYRange.on('input change', function () {
        setCustomFocus(previewFocusX.val(), $(this).val());
    });
    previewFocusX.on('input change', function () {
        setCustomFocus($(this).val(), previewFocusY.val());
    });
    previewFocusY.on('input change', function () {
        setCustomFocus(previewFocusX.val(), $(this).val());
    });

    let focusPointerId = null;
    previewFocusPicker.on('pointerdown', function (event) {
        if (!getPreviewSourceImage().length || event.button > 0) return;
        focusPointerId = event.pointerId;
        previewFocusPicker.addClass('is-dragging');
        focusFromPointer(event);
        if (this.setPointerCapture) this.setPointerCapture(event.pointerId);
        event.preventDefault();
    });

    previewFocusPicker.on('pointermove', function (event) {
        if (focusPointerId === null || focusPointerId !== event.pointerId) return;
        focusFromPointer(event);
        event.preventDefault();
    });

    const finishFocusPointer = function (event) {
        if (focusPointerId === null || (event.pointerId !== undefined && focusPointerId !== event.pointerId)) return;
        if (this.releasePointerCapture && event.pointerId !== undefined) {
            try { this.releasePointerCapture(event.pointerId); } catch (error) { /* already released */ }
        }
        focusPointerId = null;
        previewFocusPicker.removeClass('is-dragging');
    };
    previewFocusPicker.on('pointerup pointercancel lostpointercapture', finishFocusPointer);

    previewFocusPicker.on('keydown', function (event) {
        if (!getPreviewSourceImage().length) return;
        const key = event.key;
        if (!['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(key)) return;
        const step = event.shiftKey ? 5 : 1;
        const position = getEffectiveFocus();
        let x = position[0];
        let y = position[1];
        if (key === 'ArrowLeft') x -= step;
        if (key === 'ArrowRight') x += step;
        if (key === 'ArrowUp') y -= step;
        if (key === 'ArrowDown') y += step;
        setCustomFocus(x, y);
        event.preventDefault();
    });

    const featuredImageBox = document.getElementById('postimagediv');
    if (featuredImageBox && window.MutationObserver) {
        let featuredSyncTimer = null;
        const observer = new MutationObserver(() => {
            window.clearTimeout(featuredSyncTimer);
            featuredSyncTimer = window.setTimeout(syncPreviewImageSource, 60);
        });
        observer.observe(featuredImageBox, { childList: true, subtree: true, attributes: true });
    }
    $(document).on('change', '#_thumbnail_id', syncPreviewImageSource);

    if (window.wp?.data?.subscribe && window.wp?.data?.select) {
        let lastFeaturedId = getFeaturedImageId();
        window.wp.data.subscribe(() => {
            const nextFeaturedId = getFeaturedImageId();
            if (nextFeaturedId !== lastFeaturedId) {
                lastFeaturedId = nextFeaturedId;
                syncPreviewImageSource();
            }
        });
    }

    $(window).on('resize.fflPreviewFocus', function () {
        window.requestAnimationFrame(updatePreviewFocus);
    });

    syncFocusInputs(previewFocusX.val(), previewFocusY.val());
    bindSourceImageLoad();
    updatePreviewFocus();
    syncPreviewImageSource();

});
