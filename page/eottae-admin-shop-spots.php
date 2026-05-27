<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-shop-spot.lib.php';
eottae_shop_spot_ensure_schema();

$configs = eottae_shop_spot_get_all_config();
$bookings = eottae_shop_spot_admin_bookings(40);
$proc_url = G5_URL.'/proc/eottae-shop-spot-admin.php';

g5_page_start('최우수 업체 노출 관리');
?>

<main class="promo-admin-page shop-spot-admin">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo G5_URL; ?>/page/eottae-events.php" class="promo-admin-page__back">← 이벤트·프로모션</a>
        </div>
        <h1 class="promo-admin-page__title">최우수 업체 노출 관리</h1>
        <p class="promo-admin-page__desc">목록 최상단 <strong>3개 자리</strong>의 필요 포인트·노출 기간을 설정합니다. 사업자는 업체 수정 화면에서 포인트로 신청하며, 노출 업체에는 「최우수업체」 배지가 표시됩니다.</p>
    </header>

    <section class="promo-admin-panel" aria-labelledby="shop-spot-config-title">
        <h2 id="shop-spot-config-title" class="promo-admin-panel__title">노출 자리 설정</h2>
        <form class="promo-admin-form" id="shopSpotConfigForm" method="post" action="<?php echo $proc_url; ?>">
            <input type="hidden" name="action" value="save_all">
            <?php for ($slot = 1; $slot <= eottae_shop_spot_slot_count(); $slot++) {
                $cfg = isset($configs[$slot]) ? $configs[$slot] : array();
                $is_slot1 = ($slot === 1);
                ?>
            <fieldset class="shop-spot-admin__slot<?php echo $is_slot1 ? ' shop-spot-admin__slot--primary' : ''; ?>">
                <legend><?php echo (int) $slot; ?>번 자리<?php echo $is_slot1 ? ' (대표 · 관리자 핵심 설정)' : ''; ?></legend>
                <div class="promo-admin-form__row">
                    <div class="promo-admin-form__field">
                        <label for="points_<?php echo $slot; ?>">필요 포인트</label>
                        <input type="number" name="points_<?php echo $slot; ?>" id="points_<?php echo $slot; ?>" min="0" step="100" value="<?php echo (int) ($cfg['points_required'] ?? 0); ?>" required>
                    </div>
                    <div class="promo-admin-form__field">
                        <label for="days_<?php echo $slot; ?>">노출 기간(일)</label>
                        <input type="number" name="days_<?php echo $slot; ?>" id="days_<?php echo $slot; ?>" min="1" max="365" value="<?php echo (int) ($cfg['days_duration'] ?? 30); ?>" required>
                    </div>
                    <div class="promo-admin-form__field promo-admin-form__field--check">
                        <label>
                            <input type="checkbox" name="enabled_<?php echo $slot; ?>" value="1"<?php echo !empty($cfg['is_enabled']) ? ' checked' : ''; ?>>
                            신청 허용
                        </label>
                    </div>
                </div>
            </fieldset>
            <?php } ?>
            <p class="promo-admin-form__status" data-shop-spot-admin-status role="status"></p>
            <button type="submit" class="promo-reward-btn promo-reward-btn--primary">설정 저장</button>
        </form>
    </section>

    <section class="promo-admin-panel" aria-labelledby="shop-spot-bookings-title">
        <h2 id="shop-spot-bookings-title" class="promo-admin-panel__title">최근 신청 내역</h2>
        <?php if (empty($bookings)) { ?>
        <p class="promo-admin-form__hint">신청 내역이 없습니다.</p>
        <?php } else { ?>
        <div class="shop-spot-admin__table-wrap">
            <table class="shop-spot-admin__table">
                <thead>
                    <tr>
                        <th>자리</th>
                        <th>게시판</th>
                        <th>업체</th>
                        <th>회원</th>
                        <th>포인트</th>
                        <th>기간</th>
                        <th>상태</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b) {
                        $subject = '';
                        if (!empty($b['shop_wr_id']) && !empty($b['shop_bo_table'])) {
                            global $g5;
                            $wt = $g5['write_prefix'].preg_replace('/[^a-z0-9_]/', '', (string) $b['shop_bo_table']);
                            $sr = sql_fetch(" select wr_subject from `{$wt}` where wr_id = '".(int) $b['shop_wr_id']."' limit 1 ");
                            $subject = !empty($sr['wr_subject']) ? get_text($sr['wr_subject']) : '#'.(int) $b['shop_wr_id'];
                        }
                        ?>
                    <tr>
                        <td><?php echo (int) $b['spot_slot']; ?>번</td>
                        <td><?php echo get_text($b['list_bo_table']); ?></td>
                        <td><?php echo $subject; ?></td>
                        <td><?php echo get_text($b['mb_id']); ?></td>
                        <td><?php echo number_format((int) $b['points_paid']); ?>P</td>
                        <td><?php echo get_text(substr((string) $b['starts_at'], 0, 10)); ?> ~ <?php echo get_text(substr((string) $b['ends_at'], 0, 10)); ?></td>
                        <td><?php echo get_text($b['status']); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </section>
</main>

<script>
(function () {
  var form = document.getElementById('shopSpotConfigForm');
  if (!form) return;
  var statusEl = form.querySelector('[data-shop-spot-admin-status]');
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (statusEl) statusEl.textContent = '저장 중…';
    var fd = new FormData(form);
    fetch(form.action, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (statusEl) statusEl.textContent = data.message || (data.ok ? '저장되었습니다.' : '저장 실패');
        if (data.ok) setTimeout(function () { location.reload(); }, 600);
      })
      .catch(function () {
        if (statusEl) statusEl.textContent = '저장 중 오류가 발생했습니다.';
      });
  });
})();
</script>

<?php
g5_page_end();
