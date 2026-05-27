<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-weather.lib.php';
include_once G5_PATH.'/components/eottae/public-ai-admin-nav.php';

eottae_public_ai_ensure_schema();
eottae_public_ai_weather_ensure_schema();

$list = eottae_public_ai_weather_list_recent(14);
$admin_token = eottae_public_ai_admin_token();
$saved = !empty($_GET['saved']);

g5_page_start('공개톡 AI 날씨 데이터');
?>

<main class="promo-admin-page talk-admin-page public-ai-admin-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <?php eottae_public_ai_render_admin_page_mypage_back(); ?>
            <a href="<?php echo eottae_public_ai_admin_settings_url(); ?>" class="promo-admin-page__back">← AI 기본 설정</a>
        </div>
        <h1 class="promo-admin-page__title">날씨 데이터</h1>
        <p class="promo-admin-page__desc">API 키가 없을 때는 수동 입력으로 후보 메시지를 생성합니다. 날씨는 변동될 수 있으니 부드러운 표현으로 안내합니다.</p>
        <?php eottae_public_ai_render_admin_nav('weather'); ?>
    </header>

    <?php if ($saved) { ?><p class="talk-ai-settings__saved" role="status">저장했습니다.</p><?php } ?>

    <section class="promo-admin-panel talk-admin-panel">
        <h2 class="promo-admin-panel__title">날씨 수동 입력</h2>
        <form id="publicAiWeatherForm" class="talk-apply-form">
            <div class="talk-apply-form__field">
                <label for="weather_date">예보 날짜</label>
                <input type="date" id="weather_date" name="forecast_date" class="talk-apply-form__input" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="talk-apply-form__field">
                <label for="weather_summary">날씨 요약</label>
                <input type="text" id="weather_summary" name="weather_summary" class="talk-apply-form__input" maxlength="120" required placeholder="예: 오후 소나기, 맑음, 무더위">
            </div>
            <div class="talk-apply-form__field talk-apply-form__field--inline">
                <label for="weather_rain">강수확률 (%)</label>
                <input type="number" id="weather_rain" name="rain_chance" class="talk-apply-form__input" min="0" max="100" value="0">
            </div>
            <div class="talk-apply-form__field talk-apply-form__field--inline">
                <label for="weather_tmin">최저 (℃)</label>
                <input type="number" id="weather_tmin" name="temperature_min" class="talk-apply-form__input" step="1">
            </div>
            <div class="talk-apply-form__field talk-apply-form__field--inline">
                <label for="weather_tmax">최고 (℃)</label>
                <input type="number" id="weather_tmax" name="temperature_max" class="talk-apply-form__input" step="1">
            </div>
            <div class="talk-apply-form__field">
                <label for="weather_source">출처</label>
                <input type="text" id="weather_source" name="source" class="talk-apply-form__input" value="manual" placeholder="manual / PAGASA / OpenWeather">
            </div>
            <div class="talk-apply-form__field">
                <label for="weather_note">출처 메모</label>
                <input type="text" id="weather_note" name="source_note" class="talk-apply-form__input" maxlength="255" placeholder="예: PAGASA 3월 예보 참고">
            </div>
            <button type="submit" class="promo-admin-btn promo-admin-btn--primary">저장</button>
        </form>
        <p class="talk-ai-settings__hint">정기 슬롯 크론 실행 시 Open-Meteo로 오늘·내일 날씨를 자동 수집합니다. 수동 입력은 API 데이터보다 우선합니다.</p>
    </section>

    <section class="promo-admin-panel talk-admin-panel">
        <h2 class="promo-admin-panel__title">최근 예보</h2>
        <?php if (empty($list)) { ?>
        <p class="promo-admin-empty">등록된 날씨 데이터가 없습니다.</p>
        <?php } else { ?>
        <table class="talk-admin-table">
            <thead><tr><th>날짜</th><th>요약</th><th>강수</th><th>기온</th><th>출처</th></tr></thead>
            <tbody>
            <?php foreach ($list as $w) { ?>
                <tr>
                    <td><?php echo $w['forecast_date']; ?></td>
                    <td><?php echo $w['weather_summary']; ?></td>
                    <td><?php echo (int) $w['rain_chance']; ?>%</td>
                    <td><?php echo $w['temperature_min'] !== null ? (int) $w['temperature_min'].'℃' : '-'; ?> ~ <?php echo $w['temperature_max'] !== null ? (int) $w['temperature_max'].'℃' : '-'; ?></td>
                    <td><?php echo $w['source']; ?><?php if ($w['source_note'] !== '') { ?><br><span class="talk-admin-table__sub"><?php echo $w['source_note']; ?></span><?php } ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php } ?>
    </section>
</main>

<?php eottae_public_ai_render_admin_actions_script($admin_token); ?>
<script>
(function () {
  var form = document.getElementById('publicAiWeatherForm');
  if (!form || typeof window.eottaePublicAiAdminPost !== 'function') return;
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var fd = new FormData(form);
    var fields = {};
    fd.forEach(function (v, k) { fields[k] = v; });
    window.eottaePublicAiAdminPost('save_weather', fields).then(function (data) {
      alert(data.message || '');
      if (data.success) location.href = location.pathname + '?saved=1';
    });
  });
}());
</script>
<?php g5_page_end(); ?>
