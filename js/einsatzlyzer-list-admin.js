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
