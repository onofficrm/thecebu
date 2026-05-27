(function () {
    'use strict';

    function initAdroomShopPicker() {
        var root = document.getElementById('adroom-shop-picker');
        if (!root) {
            return;
        }

        var boInput = document.getElementById('eottae_adroom_shop_bo_table');
        var wrInput = document.getElementById('eottae_adroom_shop_wr_id');
        var wr1 = root.querySelector('input[name="wr_1"]');
        var wr2 = root.querySelector('input[name="wr_2"]');
        var items = root.querySelectorAll('.adroom-shop-picker__item');

        function selectItem(item) {
            if (!item || !boInput || !wrInput) {
                return;
            }

            var bo = item.getAttribute('data-bo-table') || '';
            var wrId = item.getAttribute('data-wr-id') || '0';

            boInput.value = bo;
            wrInput.value = wrId;
            if (wr1) {
                wr1.value = bo;
            }
            if (wr2) {
                wr2.value = wrId;
            }

            items.forEach(function (el) {
                var on = el === item;
                el.classList.toggle('is-selected', on);
                el.setAttribute('aria-selected', on ? 'true' : 'false');
            });
        }

        items.forEach(function (item) {
            item.addEventListener('click', function () {
                selectItem(item);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAdroomShopPicker);
    } else {
        initAdroomShopPicker();
    }
})();
