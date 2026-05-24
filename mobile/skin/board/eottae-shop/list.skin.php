<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_LIB_PATH.'/thumbnail.lib.php');
include_once(G5_LIB_PATH.'/eottae.lib.php');
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

$quick_categories = eottae_shop_quick_categories($board);
$region_options = eottae_shop_region_options();
$sort_links = eottae_shop_sort_links(isset($sst) ? $sst : '');
$list_base = get_pretty_url($bo_table);
$current_region = (isset($sfl) && $sfl === 'wr_2' && !empty($stx)) ? get_text($stx) : '';
$eottae_user_coords = eottae_shop_user_coords_from_request();
if (isset($sst) && $sst === 'near' && $eottae_user_coords && is_array($list)) {
    eottae_shop_sort_list_by_distance($list, $eottae_user_coords['lat'], $eottae_user_coords['lng']);
}
$shop_map_markers = eottae_shop_map_markers($list, $bo_table);
$eottae_maps_enabled = eottae_enqueue_google_maps();

function eottae_shop_build_list_url($bo_table, $params = array())
{
    $base = G5_BBS_URL.'/board.php?bo_table='.$bo_table;
    if (empty($params)) {
        return $base;
    }

    return $base.'&'.http_build_query($params);
}
?>

<div class="shop-near-page board-wrap board-wrap--eottae-shop" id="bo_list" style="width:<?php echo $width; ?>">

<div class="shop-near-page__layout">
<aside class="shop-near-page__panel">

    <section class="shop-near-search">
        <form class="shop-near-search__row" method="get" action="<?php echo G5_BBS_URL; ?>/board.php">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
            <label class="sound_only" for="shop_search_category">카테고리</label>
            <select id="shop_search_category" name="sca" class="shop-near-search__select" onchange="this.form.submit();">
                <option value="">카테고리</option>
                <?php foreach ($quick_categories as $cat) {
                    if ($cat['slug'] === '') {
                        continue;
                    } ?>
                <option value="<?php echo get_text($cat['slug']); ?>"<?php echo ($sca === $cat['slug']) ? ' selected' : ''; ?>><?php echo get_text($cat['label']); ?></option>
                <?php } ?>
            </select>
            <label class="sound_only" for="shop_search_region">지역</label>
            <select id="shop_search_region" name="stx" class="shop-near-search__select" onchange="this.form.sfl.value='wr_2'; this.form.submit();">
                <option value="">지역</option>
                <?php foreach ($region_options as $region) { ?>
                <option value="<?php echo get_text($region); ?>"<?php echo ($current_region === $region) ? ' selected' : ''; ?>><?php echo get_text($region); ?></option>
                <?php } ?>
            </select>
            <input type="hidden" name="sfl" value="<?php echo $current_region ? 'wr_2' : (isset($sfl) ? $sfl : ''); ?>">
        </form>

        <form class="shop-near-search__bar" method="get" action="<?php echo G5_BBS_URL; ?>/board.php">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
            <?php if ($sca) { ?><input type="hidden" name="sca" value="<?php echo get_text($sca); ?>"><?php } ?>
            <input type="hidden" name="sfl" value="wr_subject||wr_content">
            <label class="sound_only" for="shop_search_keyword">검색어</label>
            <input type="search" id="shop_search_keyword" name="stx" value="<?php echo ($sfl === 'wr_subject||wr_content' && !empty($stx)) ? get_text(stripslashes($stx)) : ''; ?>" class="shop-near-search__input" placeholder="업체명, 키워드 검색">
            <button type="submit" class="shop-near-search__submit">검색</button>
        </form>

        <button type="button" class="shop-near-search__geo" id="shopNearGeoBtn">📍 현재 위치 기준으로 내 주변 찾기</button>
    </section>

    <section class="shop-near-filters">
        <div class="shop-near-filters__group">
            <p class="shop-near-filters__label">빠른 카테고리</p>
            <div class="shop-near-pills">
                <?php foreach ($quick_categories as $cat) {
                    $href = $cat['slug'] === '' ? $list_base : get_pretty_url($bo_table, '', 'sca='.urlencode($cat['slug']));
                    $active = ($cat['slug'] === '' && $sca === '') || ($cat['slug'] !== '' && $sca === $cat['slug']);
                    ?>
                <a href="<?php echo $href; ?>" class="shop-near-pill<?php echo $active ? ' is-active' : ''; ?>"><?php echo get_text($cat['label']); ?></a>
                <?php } ?>
            </div>
        </div>

        <div class="shop-near-filters__group">
            <p class="shop-near-filters__label">지역 선택</p>
            <div class="shop-near-pills">
                <a href="<?php echo $list_base; ?>" class="shop-near-pill<?php echo $current_region === '' ? ' is-active' : ''; ?>">전체</a>
                <?php foreach ($region_options as $region) {
                    $href = eottae_shop_build_list_url($bo_table, array('sfl' => 'wr_2', 'stx' => $region));
                    ?>
                <a href="<?php echo $href; ?>" class="shop-near-pill<?php echo ($current_region === $region) ? ' is-active' : ''; ?>"><?php echo get_text($region); ?></a>
                <?php } ?>
            </div>
        </div>

        <nav class="shop-near-sort" aria-label="정렬">
            <?php foreach ($sort_links as $link) {
                if (!empty($link['disabled'])) {
                    ?>
            <span class="shop-near-sort__item is-disabled" title="Google Maps API 키 설정 후 이용 가능"><?php echo $link['label']; ?></span>
                    <?php
                    continue;
                }
                $params = array('sst' => $link['sst'], 'sod' => $link['sod']);
                if ($sca) {
                    $params['sca'] = $sca;
                }
                if ($current_region) {
                    $params['sfl'] = 'wr_2';
                    $params['stx'] = $current_region;
                }
                $params = eottae_shop_append_coords_query($params);
                $href = eottae_shop_build_list_url($bo_table, $params);
                $active = eottae_shop_is_sort_active($link, isset($sst) ? $sst : '', isset($sod) ? $sod : '');
                $near_attrs = ($link['sst'] === 'near' && !$eottae_user_coords) ? ' data-shop-near-sort' : '';
                ?>
            <a href="<?php echo $href; ?>" class="shop-near-sort__item<?php echo $active ? ' is-active' : ''; ?>"<?php echo $near_attrs; ?>><?php echo $link['label']; ?></a>
            <?php } ?>
        </nav>
    </section>

    <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">

    <div class="shop-near-results">
        <header class="shop-near-results__head">
            <h2 class="shop-near-results__title">총 <strong><?php echo number_format($total_count); ?></strong>개의 결과</h2>
            <?php if ($write_href) { ?>
            <a href="<?php echo $write_href; ?>" class="shop-near-results__write">+ 업체등록</a>
            <?php } ?>
        </header>

        <div class="shop-near-results__list">
            <?php
            for ($i = 0; $i < count($list); $i++) {
                $list[$i]['bo_table'] = $bo_table;
                eottae_render_shop_card($list[$i], $bo_table, 'list');
            }
            if (count($list) === 0) {
            ?>
            <div class="empty-state">
                <p class="empty-state__title">등록된 업체가 없습니다</p>
                <p>조건을 변경하거나 첫 업체를 등록해 보세요.</p>
                <?php if ($write_href) { ?><a href="<?php echo $write_href; ?>" class="shop-near-results__write" style="margin-top:12px;display:inline-flex">업체 등록</a><?php } ?>
            </div>
            <?php } ?>
        </div>

        <nav class="board-paging shop-near-paging" aria-label="페이지"><?php echo $write_pages; ?></nav>
    </div>
    </form>
</aside>

<?php include G5_PATH.'/components/eottae/shop-map-placeholder.php'; ?>

</div>
</div>

<script>
function fboardlist_submit(f) {
    return true;
}

(function () {
    var geoBtn = document.getElementById('shopNearGeoBtn');
    var mapsEnabled = <?php echo $eottae_maps_enabled ? 'true' : 'false'; ?>;

    function redirectWithCoords(lat, lng, withNear) {
        var u = new URL(window.location.href);
        u.searchParams.set('eottae_lat', String(lat));
        u.searchParams.set('eottae_lng', String(lng));
        if (withNear) {
            u.searchParams.set('sst', 'near');
            u.searchParams.set('sod', 'asc');
        }
        window.location.href = u.toString();
    }

    function requestLocation(withNear) {
        if (!mapsEnabled) {
            alert('Google Maps API 키 설정 후 현재 위치 기반 검색이 가능합니다.');
            return;
        }
        if (!navigator.geolocation) {
            alert('현재 위치를 사용할 수 없습니다.');
            return;
        }
        navigator.geolocation.getCurrentPosition(
            function (pos) {
                redirectWithCoords(pos.coords.latitude, pos.coords.longitude, !!withNear);
            },
            function () {
                alert('위치 권한이 필요합니다.');
            },
            { enableHighAccuracy: false, timeout: 10000, maximumAge: 60000 }
        );
    }

    if (geoBtn) {
        geoBtn.addEventListener('click', function () {
            requestLocation(true);
        });
    }

    document.querySelectorAll('[data-shop-near-sort]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            requestLocation(true);
        });
    });
})();
</script>
