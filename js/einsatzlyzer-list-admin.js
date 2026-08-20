(function () {
    'use strict';

    function cleanTitleCells() {
        document.querySelectorAll('td.column-title').forEach(function (cell) {
            cell.querySelectorAll('.post-excerpt, .post_excerpt, .excerpt, p:not(.row-actions)').forEach(function (node) {
                node.remove();
            });

            var rowActions = cell.querySelector('.row-actions');
            Array.prototype.slice.call(cell.childNodes).forEach(function (node) {
                if (node.nodeType === Node.TEXT_NODE && node.textContent.trim()) {
                    node.remove();
                }
                if (node.nodeType === Node.ELEMENT_NODE && node.tagName === 'BR') {
                    node.remove();
                }
            });

            if (rowActions) {
                cell.appendChild(rowActions);
            }
        });
    }

    function labelFilters() {
        document.querySelectorAll('.tablenav.top select.ffl-admin-filter').forEach(function (select) {
            select.setAttribute('aria-label', select.options.length ? select.options[0].text : 'Einsatzfilter');
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        cleanTitleCells();
        labelFilters();
    });
}());

/* Einsatzlyzer 9.7.5: Überschrift und Logo zuverlässig in einer Zeile anordnen. */
(function () {
    'use strict';

    function positionOverviewBrand() {
        var logo = document.querySelector('.ffl-admin-title-logo');
        var heading = document.querySelector('body.post-type-ffl_einsatz.edit-php .wrap > h1.wp-heading-inline');

        if (!logo || !heading) {
            return;
        }

        heading.textContent = 'Aktuelle Einsatzberichte';

        var bar = heading.closest('.ffl-admin-heading-bar');
        if (!bar) {
            bar = document.createElement('div');
            bar.className = 'ffl-admin-heading-bar';
            heading.parentNode.insertBefore(bar, heading);
            bar.appendChild(heading);
        }

        bar.appendChild(logo);
        logo.classList.add('is-positioned');
    }

    document.addEventListener('DOMContentLoaded', positionOverviewBrand);
    window.addEventListener('load', positionOverviewBrand);
    window.setTimeout(positionOverviewBrand, 100);
}());

/* Einsatzlyzer 10.1.1: mobile Aktionszeile bereinigen. */
(function () {
    'use strict';

    function cleanMobileRowActions() {
        if (!window.matchMedia || !window.matchMedia('(max-width: 782px)').matches) {
            return;
        }

        document.querySelectorAll('body.post-type-ffl_einsatz.edit-php td.column-title .row-actions span').forEach(function (item) {
            var link = item.querySelector('a');
            var label = (link ? link.textContent : item.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();

            if (!label || label.indexOf('purge from cache') !== -1 || label.indexOf('cache leeren') !== -1) {
                item.remove();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', cleanMobileRowActions);
    window.addEventListener('load', cleanMobileRowActions);
    window.setTimeout(cleanMobileRowActions, 150);
}());
