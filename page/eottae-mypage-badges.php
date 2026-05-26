<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-member-growth.lib.php';
include_once G5_PATH.'/components/eottae/member-growth-display.php';

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', function_exists('eottae_login_url') ? eottae_login_url(eottae_member_growth_mypage_url()) : G5_BBS_URL.'/login.php');
}

eottae_member_growth_ensure_schema();
$profile = eottae_member_growth_get_profile($member['mb_id']);
$badges = eottae_member_growth_list_member_badges($member['mb_id']);
$logs = eottae_member_growth_recent_logs($member['mb_id'], 10);
$token = function_exists('eottae_member_growth_member_token') ? eottae_member_growth_member_token() : '';
$member_prefs = eottae_member_growth_get_member_prefs($member['mb_id']);
$public_bio = trim((string) ($member_prefs['public_bio'] ?? ''));

$total = (int) ($profile['total_score'] ?? 0);
$next = $profile['next_level'] ?? null;
$next_remain = 0;
$progress_pct = 100;
if (is_array($next) && !empty($next['min_score'])) {
    $next_remain = max(0, (int) $next['min_score'] - $total);
    $cur_min = (int) ($profile['level']['min_score'] ?? 0);
    $span = max(1, (int) $next['min_score'] - $cur_min);
    $progress_pct = min(100, max(0, (int) round((($total - $cur_min) / $span) * 100)));
}

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-member-growth.css">', 24);

g5_page_start('내 등급/뱃지');
?>

<main class="mypage-subpage member-growth-page">
    <?php if (function_exists('eottae_render_mypage_back')) {
        eottae_render_mypage_back();
    } ?>
    <h1 class="mypage-subpage__title">내 등급/뱃지</h1>
    <p class="mypage-subpage__links" style="margin:-8px 0 16px;font-size:0.88rem">
        <a href="<?php echo function_exists('eottae_member_growth_badge_book_url') ? eottae_member_growth_badge_book_url() : G5_URL.'/badges/'; ?>">뱃지 도감</a>
        · <a href="<?php echo function_exists('eottae_member_growth_ranking_url') ? eottae_member_growth_ranking_url('week') : G5_URL.'/ranking/'; ?>">활동 랭킹</a>
    </p>

    <section class="member-growth-hero" aria-labelledby="member-growth-level-title">
        <p class="member-growth-hero__level" id="member-growth-level-title">
            <?php if (!empty($profile['level'])) {
                echo eottae_member_growth_render_level_chip($profile['level']);
            } else {
                echo '새싹회원';
            } ?>
        </p>
        <p class="member-growth-hero__score">총 활동 점수 <strong><?php echo number_format($total); ?></strong>점</p>
        <?php if (is_array($next) && !empty($next['level_name'])) { ?>
        <p class="member-growth-hero__next">다음 등급 <strong><?php echo get_text($next['level_name']); ?></strong>까지 <?php echo number_format($next_remain); ?>점 남았습니다.</p>
        <div class="member-growth-progress" role="progressbar" aria-valuenow="<?php echo $progress_pct; ?>" aria-valuemin="0" aria-valuemax="100">
            <div class="member-growth-progress__bar" style="width:<?php echo $progress_pct; ?>%"></div>
        </div>
        <?php } else { ?>
        <p class="member-growth-hero__next">최고 등급에 도달했습니다!</p>
        <?php } ?>
    </section>

    <section class="member-growth-section">
        <h2 class="member-growth-section__title">프로필 소개</h2>
        <p class="member-growth-section__hint" style="font-size:0.84rem;color:#64748b;margin:0 0 10px">공개 프로필에 표시됩니다. 한 줄 소개를 적어 주세요.</p>
        <form id="publicBioForm" class="promo-admin-form" style="max-width:480px">
            <textarea name="public_bio" rows="3" maxlength="500" placeholder="예: 세부 생활·맛집 정보를 나누는 맘입니다." style="width:100%;padding:10px;border-radius:10px;border:1px solid #e2e8f0"><?php echo get_text($public_bio); ?></textarea>
            <button type="submit" class="member-growth-set-main-btn" style="margin-top:8px">소개 저장</button>
            <p data-public-bio-status style="font-size:0.82rem;margin-top:6px"></p>
        </form>
    </section>

    <section class="member-growth-section">
        <h2 class="member-growth-section__title">내 뱃지</h2>
        <?php if (empty($badges)) { ?>
        <p>아직 받은 뱃지가 없습니다. 글·댓글·챌린지 참여로 뱃지를 모아 보세요.</p>
        <?php } else { ?>
        <ul class="member-growth-badge-grid">
            <?php foreach ($badges as $badge) {
                $is_main = !empty($badge['is_main']);
                ?>
            <li class="member-growth-badge-item<?php echo $is_main ? ' is-main' : ''; ?>">
                <div class="member-growth-badge-item__info">
                    <?php echo eottae_member_growth_render_badge($badge, $is_main); ?>
                    <?php if (!empty($badge['badge_description'])) { ?>
                    <p class="member-growth-badge-item__desc"><?php echo get_text($badge['badge_description']); ?></p>
                    <?php } ?>
                </div>
                <button type="button" class="member-growth-set-main-btn<?php echo $is_main ? ' is-active' : ''; ?>" data-set-main-badge="<?php echo (int) $badge['badge_id']; ?>" data-token="<?php echo get_text($token); ?>">
                    <?php echo $is_main ? '대표 뱃지' : '대표로 설정'; ?>
                </button>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>
    </section>

    <?php if (!empty($logs)) { ?>
    <section class="member-growth-section">
        <h2 class="member-growth-section__title">최근 점수 획득</h2>
        <ul class="member-growth-log-list">
            <?php foreach ($logs as $log) { ?>
            <li class="member-growth-log-list__item">
                <span><?php echo get_text($log['memo'] !== '' ? $log['memo'] : ($log['action_type'] ?? '')); ?></span>
                <span class="member-growth-log-list__score">+<?php echo number_format((int) ($log['score'] ?? 0)); ?>P</span>
            </li>
            <?php } ?>
        </ul>
    </section>
    <?php } ?>
</main>

<script>
window.eottaeMemberGrowthProcUrl = <?php echo json_encode(G5_URL.'/proc/eottae-member-growth.php', JSON_UNESCAPED_UNICODE); ?>;
var bioForm = document.getElementById('publicBioForm');
if (bioForm) {
  bioForm.addEventListener('submit', function(e) {
    e.preventDefault();
    var fd = new FormData(bioForm);
    fd.append('action', 'save_public_bio');
    fd.append('eottae_member_growth_token', <?php echo json_encode($token, JSON_UNESCAPED_UNICODE); ?>);
    fetch(window.eottaeMemberGrowthProcUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        var st = document.querySelector('[data-public-bio-status]');
        if (st) st.textContent = data.message || '';
      });
  });
}

document.querySelectorAll('[data-set-main-badge]').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var fd = new FormData();
    fd.append('action', 'set_main_badge');
    fd.append('badge_id', btn.getAttribute('data-set-main-badge'));
    fd.append('eottae_member_growth_token', btn.getAttribute('data-token'));
    fetch(window.eottaeMemberGrowthProcUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.success) { location.reload(); }
        else { alert(data.message || '설정에 실패했습니다.'); }
      });
  });
});
</script>

<?php
g5_page_end();
