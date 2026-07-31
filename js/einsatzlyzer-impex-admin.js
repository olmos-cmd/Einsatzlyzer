(function () {
    'use strict';

    function initAutoBatch() {
        var form = document.getElementById('ffl-impex-auto-batch');
        if (!form || form.dataset.autoSubmit !== '1') {
            return;
        }
        window.setTimeout(function () {
            form.submit();
        }, 450);
    }

    function initScopeForms() {
        document.querySelectorAll('[data-ffl-scope]').forEach(function (field) {
            var radios = field.querySelectorAll('input[type="radio"][name="scope"]');
            var year = field.querySelector('input[name="year"]');
            var dateInputs = field.querySelectorAll('input[type="date"]');

            function update() {
                var selected = field.querySelector('input[name="scope"]:checked');
                var value = selected ? selected.value : 'all';
                if (year) {
                    year.disabled = value !== 'year';
                }
                dateInputs.forEach(function (input) {
                    input.disabled = value !== 'range';
                });
            }

            radios.forEach(function (radio) {
                radio.addEventListener('change', update);
            });
            update();
        });
    }

    function initFileInput() {
        document.querySelectorAll('.ffl-impex-file input[type="file"]').forEach(function (input) {
            input.addEventListener('change', function () {
                var root = input.closest('.ffl-impex-file');
                if (!root) {
                    return;
                }
                var strong = root.querySelector('strong');
                if (strong && input.files && input.files[0]) {
                    strong.textContent = input.files[0].name;
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initScopeForms();
        initFileInput();
        initAutoBatch();
    });
}());
