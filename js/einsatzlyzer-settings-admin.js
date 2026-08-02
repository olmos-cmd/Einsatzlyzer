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
    });
}(jQuery));
