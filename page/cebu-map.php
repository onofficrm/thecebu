<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/cebu-map-data.php';

$page_title = '세부생활지도 | 세부어때';
$page_description = '세부어때 세부생활지도에서 부동산, 구인구직, 중고장터 정보를 지도 기반으로 확인해보세요. 세부시티, 막탄, 만다우에, 라푸라푸 등 지역별 생활정보를 쉽게 찾을 수 있습니다.';
$page_canonical = G5_URL.'/cebu-map.php';

if (is_file(G5_PATH.'/components/maps/map-config.php')) {
    include_once G5_PATH.'/components/maps/map-config.php';
}

$cebu_map_markers = cebu_map_markers(250);
$cebu_map_cfg = function_exists('onoff_map_get_config') ? onoff_map_get_config() : array();
$cebu_map_has_key = function_exists('onoff_map_has_api_key') ? onoff_map_has_api_key() : false;
$cebu_map_json = json_encode($cebu_map_markers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($cebu_map_json === false) {
    $cebu_map_json = '[]';
}

$cebu_map_css = G5_PATH.'/css/cebu-map.css';
$cebu_map_js = G5_PATH.'/js/cebu-map.js';
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/cebu-map.css?ver='.(is_file($cebu_map_css) ? (int) filemtime($cebu_map_css) : 0).'">', 35);
add_javascript('<script src="'.G5_JS_URL.'/cebu-map.js?ver='.(is_file($cebu_map_js) ? (int) filemtime($cebu_map_js) : 0).'" defer></script>', 25);

if ($cebu_map_has_key && !empty($cebu_map_cfg['api_key'])) {
    $map_script_key = htmlspecialchars($cebu_map_cfg['api_key'], ENT_QUOTES, 'UTF-8');
    add_javascript(
        '<script src="https://maps.googleapis.com/maps/api/js?key='.$map_script_key.'&amp;callback=initCebuLifeMap" defer></script>',
        5
    );
}

$initial_type = isset($_GET['type']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['type']) : 'all';
if (!in_array($initial_type, array('all', 'market', 'job', 'estate'), true)) {
    $initial_type = 'all';
}
$request_near = !empty($_GET['near']) && (string) $_GET['near'] === '1';
$market_write_url = G5_BBS_URL.'/write.php?bo_table='.(function_exists('eottae_market_board_table') ? eottae_market_board_table() : 'market');
$job_write_url = G5_BBS_URL.'/write.php?bo_table='.(function_exists('eottae_job_board_table') ? eottae_job_board_table() : 'job');
$estate_write_url = G5_BBS_URL.'/write.php?bo_table='.(function_exists('eottae_estate_board_table') ? eottae_estate_board_table() : 'estate');

g5_page_start('세부생활지도');
?>
<main class="cebu-map-page" data-cebu-map-page>
    <script type="application/json" id="cebuMapData"><?php echo $cebu_map_json; ?></script>
    <script>
    window.__CEBU_LIFE_MAP_CONFIG__ = {
        hasApiKey: <?php echo $cebu_map_has_key ? 'true' : 'false'; ?>,
        defaultLat: <?php echo json_encode((float) ($cebu_map_cfg['default_lat'] ?? 10.313)); ?>,
        defaultLng: <?php echo json_encode((float) ($cebu_map_cfg['default_lng'] ?? 123.9174)); ?>,
        defaultZoom: <?php echo json_encode((int) ($cebu_map_cfg['default_zoom'] ?? 12)); ?>
    };
    </script>

    <header class="cebu-map-hero">
        <p class="cebu-map-hero__eyebrow">Cebu Life Map</p>
        <h1 class="cebu-map-hero__title">세부생활지도</h1>
        <p class="cebu-map-hero__lead">세부의 부동산, 구인구직, 중고장터 정보를 지도에서 한눈에 확인하세요. 위치를 기준으로 내 주변 생활정보를 쉽고 빠르게 찾을 수 있습니다.</p>
        <div class="cebu-map-hero__actions">
            <a href="<?php echo get_text($market_write_url); ?>">중고물품 등록</a>
            <a href="<?php echo get_text($job_write_url); ?>">구인공고 등록</a>
            <a href="<?php echo get_text($estate_write_url); ?>">부동산 매물 등록</a>
        </div>
    </header>

    <?php if (!$cebu_map_has_key) { ?>
    <p class="cebu-map-notice" role="status">Google Maps API 키가 없어 지도 대신 리스트 중심으로 표시됩니다. <code>google_maps_api_key</code> 설정 후 지도가 활성화됩니다.</p>
    <?php } ?>

    <section class="cebu-map-filters" aria-label="세부생활지도 필터">
        <div class="cebu-map-filter cebu-map-filter--search">
            <label for="cebuMapKeyword">검색</label>
            <input type="search" id="cebuMapKeyword" data-map-filter="keyword" placeholder="냉장고, IT Park, 홀직원, 콘도">
        </div>
        <div class="cebu-map-filter">
            <label for="cebuMapType">카테고리</label>
            <select id="cebuMapType" data-map-filter="type">
                <option value="all"<?php echo $initial_type === 'all' ? ' selected' : ''; ?>>전체</option>
                <option value="market"<?php echo $initial_type === 'market' ? ' selected' : ''; ?>>중고장터</option>
                <option value="job"<?php echo $initial_type === 'job' ? ' selected' : ''; ?>>구인구직</option>
                <option value="estate"<?php echo $initial_type === 'estate' ? ' selected' : ''; ?>>부동산</option>
            </select>
        </div>
        <div class="cebu-map-filter">
            <label for="cebuMapArea">지역</label>
            <select id="cebuMapArea" data-map-filter="area">
                <option value="all">전체</option>
                <?php foreach (eottae_location_area_options() as $area_key => $area_label) { ?>
                <option value="<?php echo get_text($area_key); ?>"><?php echo get_text($area_label); ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="cebu-map-filter">
            <label for="cebuMapStatus">상태</label>
            <select id="cebuMapStatus" data-map-filter="status">
                <option value="all">전체</option>
                <optgroup label="중고장터">
                    <option value="market:selling">판매중</option>
                    <option value="market:reserved">예약중</option>
                    <option value="market:sold">판매완료</option>
                    <option value="market:not-sold">판매완료 제외</option>
                </optgroup>
                <optgroup label="구인구직">
                    <option value="job:recruiting">모집중</option>
                    <option value="job:completed">마감</option>
                </optgroup>
                <optgroup label="부동산">
                    <option value="estate:trading">거래가능</option>
                    <option value="estate:completed">계약완료</option>
                </optgroup>
            </select>
        </div>
        <div class="cebu-map-filter">
            <label for="cebuMapRadius">반경</label>
            <select id="cebuMapRadius" data-map-filter="radius">
                <option value="all">전체</option>
                <option value="1">1km</option>
                <option value="3">3km</option>
                <option value="5">5km</option>
                <option value="10">10km</option>
            </select>
        </div>
        <div class="cebu-map-filter">
            <label for="cebuMapSort">정렬</label>
            <select id="cebuMapSort" data-map-filter="sort">
                <option value="latest">최신순</option>
                <option value="near">가까운순</option>
                <option value="price_asc">가격낮은순</option>
            </select>
        </div>
        <div class="cebu-map-filter cebu-map-filter--near">
            <label>내 주변</label>
            <button type="button" class="cebu-map-near-btn" data-map-near<?php echo $request_near ? ' data-auto-near="1"' : ''; ?>>내 주변 보기</button>
        </div>
    </section>
    <p class="cebu-map-status" data-map-status role="status" aria-live="polite"></p>

    <section class="cebu-map-layout" aria-label="세부생활지도">
        <aside class="cebu-map-list-panel">
            <div class="cebu-map-list-panel__head">
                <h2>지도 생활정보</h2>
                <p><span data-map-count><?php echo number_format(count($cebu_map_markers)); ?></span>개 표시 중</p>
            </div>
            <div class="cebu-map-list" data-map-list>
                <p class="cebu-map-empty">위치가 등록된 생활정보가 없습니다.</p>
            </div>
        </aside>

        <div class="cebu-map-canvas-wrap">
            <div class="cebu-map-canvas<?php echo $cebu_map_has_key ? '' : ' is-fallback'; ?>" data-map-canvas>
                <?php if (!$cebu_map_has_key) { ?>
                <div class="cebu-map-fallback">
                    <strong>지도 API 키가 필요합니다.</strong>
                    <span>현재는 리스트로 위치 정보를 확인할 수 있습니다.</span>
                </div>
                <?php } ?>
            </div>
            <div class="cebu-map-bottom-card" data-map-active-card hidden></div>
        </div>
    </section>
</main>
<?php
g5_page_end();
