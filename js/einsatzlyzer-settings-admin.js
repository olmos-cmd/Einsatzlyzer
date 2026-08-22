(function ($) {
    'use strict';

    $(function () {
        var frame;
        var $input = $('#ffl_default_image_id');
        var $preview = $('#ffl_default_image_preview');
        var $remove = $('#ffl_remove_default_image');

        $('#ffl_select_default_image').on('click', function (event) {
            event.preventDefault();

            if (frame) {
                frame.open();
                return;
            }

            frame = wp.media({
                title: 'Standardbild für Einsätze ohne Bild auswählen',
                button: { text: 'Dieses Bild verwenden' },
                library: { type: 'image' },
                multiple: false
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                var previewUrl = attachment.sizes && attachment.sizes.medium
                    ? attachment.sizes.medium.url
                    : attachment.url;

                $input.val(attachment.id);
                $preview.removeClass('is-empty').html(
                    $('<img>', { src: previewUrl, alt: attachment.alt || attachment.title || 'Standardbild' })
                );
                $remove.show();
            });

            frame.open();
        });

        $remove.on('click', function (event) {
            event.preventDefault();
            $input.val('');
            $preview.addClass('is-empty').html('<span>Noch kein eigenes Standardbild ausgewählt</span>');
            $remove.hide();
        });

        var $areaModal = $('#ffl-response-area-modal');
        var $areaOpen = $('#ffl-response-area-open');
        var $areaTextarea = $('#ffl_response_area_coordinates');
        var $areaStatus = $('[data-ffl-response-area-status]');

        function parseResponseArea(raw) {
            var parts = String(raw || '').split(/[|\r\n]+/);
            var points = [];
            var invalid = [];
            parts.forEach(function (part) {
                part = $.trim(part);
                if (!part) return;
                var pair = part.split(',').map(function (value) { return $.trim(value); });
                if (pair.length !== 2) { invalid.push(part); return; }
                var lat = Number.parseFloat(pair[0]);
                var lon = Number.parseFloat(pair[1]);
                if (!Number.isFinite(lat) || !Number.isFinite(lon) || lat < -90 || lat > 90 || lon < -180 || lon > 180) {
                    invalid.push(part);
                    return;
                }
                points.push([lat, lon]);
            });
            return { points: points, invalid: invalid };
        }

        var areaI18n = window.fflResponseAreaAdmin || {};

        function validateResponseArea() {
            if (!$areaTextarea.length || !$areaStatus.length) return;
            var parsed = parseResponseArea($areaTextarea.val());
            if (!$.trim($areaTextarea.val())) {
                $areaStatus.removeClass('is-valid is-invalid').text(areaI18n.empty || 'Noch keine Koordinaten eingetragen.');
                return;
            }
            if (parsed.invalid.length || parsed.points.length < 3) {
                var message = parsed.points.length + ' ' + (areaI18n.validPoints || 'gültige Punkte');
                if (parsed.invalid.length) message += ' · ' + parsed.invalid.length + ' ' + (areaI18n.invalidRows || 'ungültige Zeile(n)');
                if (parsed.points.length < 3) message += ' · ' + (areaI18n.minimum || 'mindestens 3 Punkte erforderlich');
                $areaStatus.removeClass('is-valid').addClass('is-invalid').text(message);
                return;
            }
            var closed = parsed.points.length > 1 && parsed.points[0][0] === parsed.points[parsed.points.length - 1][0] && parsed.points[0][1] === parsed.points[parsed.points.length - 1][1];
            var uniqueCount = closed ? parsed.points.length - 1 : parsed.points.length;
            $areaStatus.removeClass('is-invalid').addClass('is-valid').text(uniqueCount + ' ' + (areaI18n.polygonPoints || 'gültige Polygonpunkte') + (closed ? ' · ' + (areaI18n.closed || 'Polygon ist geschlossen') : ' · ' + (areaI18n.autoClose || 'wird beim Speichern automatisch geschlossen')));
        }

        function closeResponseAreaModal() {
            if (!$areaModal.length) return;
            $areaModal.prop('hidden', true);
            $('body').css('overflow', '');
        }

        $areaOpen.on('click', function () {
            $areaModal.prop('hidden', false);
            $('body').css('overflow', 'hidden');
            validateResponseArea();
            window.setTimeout(function () { $areaTextarea.trigger('focus'); }, 50);
        });
        $areaModal.on('click', '[data-ffl-response-area-close]', closeResponseAreaModal);
        $('#ffl-response-area-check').on('click', validateResponseArea);
        $areaTextarea.on('input', function () {
            $areaStatus.removeClass('is-valid is-invalid').text(areaI18n.changed || 'Koordinaten geändert – bitte prüfen oder speichern.');
        });
        $(document).on('keydown', function (event) {
            if (event.key === 'Escape' && $areaModal.length && !$areaModal.prop('hidden')) closeResponseAreaModal();
        });
    });
}(jQuery));
