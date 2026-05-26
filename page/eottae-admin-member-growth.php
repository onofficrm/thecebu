<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-member-growth.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_PATH.'/components/eottae/member-growth-display.php';

eottae_member_growth_ensure_schema();

$levels_table = eottae_member_growth_levels_table();
$badges_table = eottae_member_growth_badges_table();
$levels = array();
$badges = array();
$lr = sql_query(" SELECT * FROM `{$levels_table}` ORDER BY sort_order ASC, min_score ASC ", false);
while ($row = sql_fetch_array($lr)) {
    if (is_array($row)) {
        $levels[] = $row;
    }
}
$br = sql_query(" SELECT * FROM `{$badges_table}` ORDER BY sort_order ASC, badge_id ASC ", false);
while ($row = sql_fetch_array($br)) {
    if (is_array($row)) {
        $badges[] = $row;
    }
}

$admin_token = eottae_talkroom_admin_token();
$proc_url = G5_URL.'/proc/eottae-member-growth-admin.php';
$current_week = eottae_member_growth_week_key();
$featured_list = eottae_member_growth_list_featured($current_week, false, 20);

$log_filter_mb_id = isset($_GET['log_mb_id']) ? preg_replace('/[^a-z0-9_@.-]/i', '', (string) $_GET['log_mb_id']) : '';
$log_filter_action = isset($_GET['log_action']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['log_action']) : '';
$log_page = max(1, (int) ($_GET['log_page'] ?? 1));
$log_limit = 30;
$log_offset = ($log_page - 1) * $log_limit;
$score_log_result = eottae_member_growth_list_score_logs(array(
    'mb_id'       => $log_filter_mb_id,
    'action_type' => $log_filter_action,
    'limit'       => $log_limit,
    'offset'      => $log_offset,
));
$score_logs = $score_log_result['rows'] ?? array();
$score_log_total = (int) ($score_log_result['total'] ?? 0);
$score_log_pages = max(1, (int) ceil($score_log_total / $log_limit));
$score_rule_keys = array_keys(eottae_member_growth_score_rules());

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-member-growth.css">', 24);

g5_page_start('회원 등급/뱃지 관리');
?>

<main class="promo-admin-page member-growth-admin">
    <header class="promo-admin-page__header">
        <h1 class="promo-admin-page__title">회원 등급/뱃지 관리</h1>
        <p class="promo-admin-page__desc">활동 점수·등급·뱃지를 관리합니다. 수동 뱃지/점수 지급은 아래 폼을 사용하세요.</p>
    </header>

    <section class="promo-admin-panel">
        <h2 class="promo-admin-panel__title">운영 도구</h2>
        <div class="promo-admin-form__row">
            <button type="button" class="promo-admin-btn" id="btnAutoFeatured" data-token="<?php echo get_text($admin_token); ?>">주간 랭킹 기준 우수회원 자동 선정</button>
            <button type="button" class="promo-admin-btn" id="btnSnapshotRank" data-token="<?php echo get_text($admin_token); ?>" data-week="<?php echo get_text($current_week); ?>">이번 주 랭킹 스냅샷 저장</button>
            <button type="button" class="promo-admin-btn" id="btnWeeklyCron" data-token="<?php echo get_text($admin_token); ?>">주간 크론 실행 (스냅샷+우수회원)</button>
            <button type="button" class="promo-admin-btn" id="btnRecalcLevels" data-token="<?php echo get_text($admin_token); ?>">전체 등급 재계산</button>
        </div>
        <p class="promo-admin-page__desc">크론: <code>php cron/sebu_member_growth_weekly.php</code> (월요일 00:10 권장)</p>
        <p class="promo-admin-page__desc"><a href="<?php echo G5_URL.'/page/eottae-admin-community-reports.php'; ?>">커뮤니티 신고 관리</a></p>
        <p class="promo-admin-form__status" data-ops-status></p>
    </section>

    <section class="promo-admin-panel">
        <h2 class="promo-admin-panel__title">활동 점수 로그</h2>
        <form class="promo-admin-form" method="get" action="">
            <div class="promo-admin-form__row">
                <label>회원 ID <input type="text" name="log_mb_id" value="<?php echo get_text($log_filter_mb_id); ?>" placeholder="mb_id"></label>
                <label>활동 유형
                    <select name="log_action">
                        <option value="">전체</option>
                        <?php foreach ($score_rule_keys as $action_key) { ?>
                        <option value="<?php echo get_text($action_key); ?>"<?php echo $log_filter_action === $action_key ? ' selected' : ''; ?>><?php echo get_text(eottae_member_growth_action_type_label($action_key)); ?></option>
                        <?php } ?>
                    </select>
                </label>
                <button type="submit" class="promo-admin-btn">검색</button>
            </div>
        </form>
        <p class="promo-admin-page__desc">총 <?php echo number_format($score_log_total); ?>건 · <?php echo $log_page; ?> / <?php echo $score_log_pages; ?> 페이지</p>
        <?php if (!empty($score_logs)) { ?>
        <table class="eottae-guide-table" style="margin-top:12px">
            <thead>
                <tr>
                    <th>일시</th>
                    <th>회원</th>
                    <th>활동</th>
                    <th>점수</th>
                    <th>대상</th>
                    <th>메모</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($score_logs as $log) { ?>
                <tr>
                    <td><?php echo get_text(substr((string) ($log['created_at'] ?? ''), 0, 16)); ?></td>
                    <td>
                        <?php echo get_text($log['mb_nick'] ?? $log['mb_id']); ?>
                        <small>(<?php echo get_text($log['mb_id']); ?>)</small>
                    </td>
                    <td><?php echo get_text(eottae_member_growth_action_type_label($log['action_type'] ?? '')); ?></td>
                    <td><?php echo ((int) ($log['score'] ?? 0) >= 0 ? '+' : '').number_format((int) ($log['score'] ?? 0)); ?></td>
                    <td><?php echo get_text(trim(($log['target_type'] ?? '').' #'.(int) ($log['target_id'] ?? 0), ' #0')); ?></td>
                    <td><?php echo get_text($log['memo'] ?? ''); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php if ($score_log_pages > 1) { ?>
        <nav class="promo-admin-form__row" style="margin-top:12px" aria-label="점수 로그 페이지">
            <?php
            $log_query_base = array();
            if ($log_filter_mb_id !== '') {
                $log_query_base['log_mb_id'] = $log_filter_mb_id;
            }
            if ($log_filter_action !== '') {
                $log_query_base['log_action'] = $log_filter_action;
            }
            for ($p = 1; $p <= $score_log_pages; $p++) {
                if ($p > 1 && $p < $score_log_pages - 2 && abs($p - $log_page) > 2) {
                    if ($p === 2 || $p === $score_log_pages - 1) {
                        echo '<span>…</span>';
                    }
                    continue;
                }
                $log_query_base['log_page'] = $p;
                $href = '?'.http_build_query($log_query_base);
                if ($p === $log_page) {
                    echo '<strong>['.$p.']</strong> ';
                } else {
                    echo '<a href="'.htmlspecialchars($href, ENT_QUOTES, 'UTF-8').'">['.$p.']</a> ';
                }
            }
            ?>
        </nav>
        <?php } ?>
        <?php } else { ?>
        <p class="promo-admin-page__desc">조건에 맞는 점수 로그가 없습니다.</p>
        <?php } ?>
    </section>

    <section class="promo-admin-panel">
        <h2 class="promo-admin-panel__title">회원 등급</h2>
        <table class="eottae-guide-table">
            <thead><tr><th>등급</th><th>최소 점수</th><th>미리보기</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($levels as $level) { ?>
                <tr>
                    <td><?php echo get_text($level['level_name']); ?></td>
                    <td><?php echo number_format((int) $level['min_score']); ?></td>
                    <td><?php echo eottae_member_growth_render_level_chip($level); ?></td>
                    <td><button type="button" class="member-growth-set-main-btn" data-edit-level="<?php echo (int) $level['level_id']; ?>">수정</button></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <form class="promo-admin-form" id="saveLevelForm" style="margin-top:16px">
            <input type="hidden" name="action" value="save_level">
            <input type="hidden" name="eottae_talkroom_admin_token" value="<?php echo get_text($admin_token); ?>">
            <input type="hidden" name="level_id" id="level_id" value="0">
            <div class="promo-admin-form__row">
                <label>등급명 <input type="text" name="level_name" id="level_name" required></label>
                <label>최소 점수 <input type="number" name="min_score" id="min_score" value="0"></label>
                <label>아이콘 <input type="text" name="icon" id="level_icon" placeholder="🌱"></label>
            </div>
            <label>설명 <input type="text" name="level_description" id="level_description"></label>
            <div class="promo-admin-form__row">
                <label>색상
                    <select name="color" id="level_color">
                        <option value="default">default</option>
                        <option value="life">life</option>
                        <option value="food">food</option>
                        <option value="meetup">meetup</option>
                        <option value="vip">vip</option>
                    </select>
                </label>
                <label>정렬 <input type="number" name="sort_order" id="level_sort" value="0"></label>
                <label><input type="checkbox" name="is_active" value="1" checked id="level_active"> 활성</label>
            </div>
            <button type="submit" class="promo-admin-btn promo-admin-btn--primary">등급 저장</button>
            <p class="promo-admin-form__status" data-save-level-status></p>
        </form>
    </section>

    <section class="promo-admin-panel">
        <h2 class="promo-admin-panel__title">뱃지 목록</h2>
        <ul class="member-growth-badge-grid">
            <?php foreach ($badges as $badge) { ?>
            <li class="member-growth-badge-item">
                <div class="member-growth-badge-item__info">
                    <?php echo eottae_member_growth_render_badge($badge); ?>
                    <p class="member-growth-badge-item__desc"><?php echo get_text($badge['badge_description']); ?> · <?php echo eottae_member_growth_badge_condition_label($badge); ?></p>
                </div>
                <button type="button" class="member-growth-set-main-btn" data-edit-badge="<?php echo (int) $badge['badge_id']; ?>">수정</button>
            </li>
            <?php } ?>
        </ul>
        <form class="promo-admin-form" id="saveBadgeDefForm" style="margin-top:16px">
            <input type="hidden" name="action" value="save_badge_def">
            <input type="hidden" name="eottae_talkroom_admin_token" value="<?php echo get_text($admin_token); ?>">
            <input type="hidden" name="badge_id" id="badge_def_id" value="0">
            <div class="promo-admin-form__row">
                <label>뱃지명 <input type="text" name="badge_name" id="badge_def_name" required></label>
                <label>아이콘 <input type="text" name="badge_icon" id="badge_def_icon" placeholder="🍽"></label>
            </div>
            <label>설명 <input type="text" name="badge_description" id="badge_def_desc"></label>
            <div class="promo-admin-form__row">
                <label>조건 유형 <input type="text" name="condition_type" id="badge_def_cond" placeholder="manual, post_count…"></label>
                <label>조건 값 <input type="number" name="condition_value" id="badge_def_val" value="0"></label>
            </div>
            <div class="promo-admin-form__row">
                <label><input type="checkbox" name="is_auto" value="1" id="badge_def_auto"> 자동 지급</label>
                <label><input type="checkbox" name="show_on_main" value="1" checked id="badge_def_main"> 메인 노출</label>
                <label><input type="checkbox" name="is_active" value="1" checked id="badge_def_active"> 활성</label>
            </div>
            <button type="submit" class="promo-admin-btn promo-admin-btn--primary">뱃지 저장</button>
            <p class="promo-admin-form__status" data-save-badge-def-status></p>
        </form>
    </section>

    <section class="promo-admin-panel">
        <h2 class="promo-admin-panel__title">이번 주 우수회원 선정 (<?php echo get_text($current_week); ?>)</h2>
        <form class="promo-admin-form" id="saveFeaturedForm">
            <input type="hidden" name="action" value="save_featured">
            <input type="hidden" name="eottae_talkroom_admin_token" value="<?php echo get_text($admin_token); ?>">
            <input type="hidden" name="week_key" value="<?php echo get_text($current_week); ?>">
            <div class="promo-admin-form__row">
                <label>회원 ID <input type="text" name="mb_id" required></label>
                <label>정렬 <input type="number" name="sort_order" value="0" style="width:80px"></label>
            </div>
            <label>소개 문구 <input type="text" name="intro_text" placeholder="예: 아이 병원 정보와 키즈카페 후기를 공유해주셨어요."></label>
            <label>선정 사유 <input type="text" name="reason" placeholder="예: 생활정보 공유 우수"></label>
            <label>활동 요약 <input type="text" name="activity_summary" placeholder="예: 글 5 · 댓글 20 · 챌린지 참여"></label>
            <label><input type="checkbox" name="show_on_main" value="1" checked> 메인 노출</label>
            <button type="submit" class="promo-admin-btn promo-admin-btn--primary">우수회원 등록</button>
            <p class="promo-admin-form__status" data-save-featured-status></p>
        </form>
        <?php if (!empty($featured_list)) { ?>
        <ul class="member-growth-log-list" style="margin-top:16px">
            <?php foreach ($featured_list as $f) { ?>
            <li class="member-growth-log-list__item">
                <span>
                    <strong><?php echo get_text($f['display_nick'] ?? $f['mb_id']); ?></strong>
                    <?php if (!empty($f['intro_text'])) { ?> — <?php echo get_text($f['intro_text']); ?><?php } ?>
                    <?php if (!empty($f['show_on_main'])) { ?> <em>(메인)</em><?php } ?>
                </span>
                <button type="button" class="member-growth-set-main-btn" data-delete-featured="<?php echo (int) $f['featured_id']; ?>" data-token="<?php echo get_text($admin_token); ?>">삭제</button>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>
    </section>

    <section class="promo-admin-panel">
        <h2 class="promo-admin-panel__title">랭킹 제외 / 닉네임 마스킹</h2>
        <form class="promo-admin-form" id="memberPrefsForm">
            <input type="hidden" name="action" value="save_member_prefs">
            <input type="hidden" name="eottae_talkroom_admin_token" value="<?php echo get_text($admin_token); ?>">
            <label>회원 ID <input type="text" name="target_mb_id" required></label>
            <label><input type="checkbox" name="exclude_ranking" value="1"> 랭킹에서 제외</label>
            <label><input type="checkbox" name="mask_nickname" value="1"> 닉네임 마스킹</label>
            <button type="submit" class="promo-admin-btn promo-admin-btn--primary">설정 저장</button>
            <p class="promo-admin-form__status" data-member-prefs-status></p>
        </form>
        <p class="promo-admin-page__desc" style="margin-top:8px">레벨 10 이상·최고관리자 계정은 기본적으로 랭킹에서 제외됩니다.</p>
    </section>

    <section class="promo-admin-panel">
        <h2 class="promo-admin-panel__title">뱃지 메인 노출 / 회원 뱃지 숨김</h2>
        <form class="promo-admin-form" id="badgeSettingsForm">
            <input type="hidden" name="action" value="badge_settings">
            <input type="hidden" name="eottae_talkroom_admin_token" value="<?php echo get_text($admin_token); ?>">
            <div class="promo-admin-form__row">
                <label>뱃지
                    <select name="badge_id">
                        <?php foreach ($badges as $badge) { ?>
                        <option value="<?php echo (int) $badge['badge_id']; ?>"><?php echo get_text($badge['badge_name']); ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label><input type="checkbox" name="show_on_main" value="1" checked> 메인 최근 획득에 노출</label>
            </div>
            <div class="promo-admin-form__row">
                <label>회원 ID <input type="text" name="target_mb_id" placeholder="숨김 처리 시"></label>
                <label><input type="checkbox" name="hide_member_badge" value="1"> 해당 회원 뱃지 숨김</label>
            </div>
            <button type="submit" class="promo-admin-btn promo-admin-btn--primary">뱃지 설정 저장</button>
            <p class="promo-admin-form__status" data-badge-settings-status></p>
        </form>
    </section>

    <section class="promo-admin-panel">
        <h2 class="promo-admin-panel__title">회원별 뱃지 지급</h2>
        <form class="promo-admin-form" id="grantBadgeForm">
            <input type="hidden" name="action" value="grant_badge">
            <input type="hidden" name="eottae_talkroom_admin_token" value="<?php echo get_text($admin_token); ?>">
            <div class="promo-admin-form__row">
                <label>회원 ID <input type="text" name="target_mb_id" required></label>
                <label>뱃지
                    <select name="badge_id" required>
                        <?php foreach ($badges as $badge) { ?>
                        <option value="<?php echo (int) $badge['badge_id']; ?>"><?php echo get_text($badge['badge_name']); ?></option>
                        <?php } ?>
                    </select>
                </label>
            </div>
            <label>지급 사유 <input type="text" name="reason" placeholder="예: 교민 인증"></label>
            <button type="submit" class="promo-admin-btn promo-admin-btn--primary">뱃지 지급</button>
            <p class="promo-admin-form__status" data-grant-badge-status></p>
        </form>
    </section>

    <section class="promo-admin-panel">
        <h2 class="promo-admin-panel__title">수동 점수 지급/차감</h2>
        <form class="promo-admin-form" id="grantScoreForm">
            <input type="hidden" name="action" value="grant_score">
            <input type="hidden" name="eottae_talkroom_admin_token" value="<?php echo get_text($admin_token); ?>">
            <div class="promo-admin-form__row">
                <label>회원 ID <input type="text" name="target_mb_id" required></label>
                <label>점수 (+/-) <input type="number" name="score" required placeholder="100 또는 -50"></label>
            </div>
            <label>메모 <input type="text" name="memo" value="관리자 지급"></label>
            <button type="submit" class="promo-admin-btn promo-admin-btn--primary">점수 반영</button>
            <p class="promo-admin-form__status" data-grant-score-status></p>
        </form>
    </section>
</main>

<script>
window.eottaeMemberGrowthAdminProc = <?php echo json_encode($proc_url, JSON_UNESCAPED_UNICODE); ?>;
function bindAdminForm(id, statusSel) {
  var form = document.getElementById(id);
  if (!form) return;
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    var status = document.querySelector(statusSel);
    fetch(window.eottaeMemberGrowthAdminProc, { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (status) status.textContent = data.message || '';
        if (data.success) form.reset();
      });
  });
}
var levelsData = <?php echo json_encode($levels, JSON_UNESCAPED_UNICODE); ?>;
var badgesData = <?php echo json_encode($badges, JSON_UNESCAPED_UNICODE); ?>;

document.querySelectorAll('[data-edit-level]').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var id = parseInt(btn.getAttribute('data-edit-level'), 10);
    var row = levelsData.find(function(l) { return parseInt(l.level_id, 10) === id; });
    if (!row) return;
    document.getElementById('level_id').value = row.level_id;
    document.getElementById('level_name').value = row.level_name || '';
    document.getElementById('min_score').value = row.min_score || 0;
    document.getElementById('level_icon').value = row.icon || '';
    document.getElementById('level_description').value = row.level_description || '';
    document.getElementById('level_color').value = row.color || 'default';
    document.getElementById('level_sort').value = row.sort_order || 0;
    document.getElementById('level_active').checked = row.is_active !== '0';
  });
});

document.querySelectorAll('[data-edit-badge]').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var id = parseInt(btn.getAttribute('data-edit-badge'), 10);
    var row = badgesData.find(function(b) { return parseInt(b.badge_id, 10) === id; });
    if (!row) return;
    document.getElementById('badge_def_id').value = row.badge_id;
    document.getElementById('badge_def_name').value = row.badge_name || '';
    document.getElementById('badge_def_icon').value = row.badge_icon || '';
    document.getElementById('badge_def_desc').value = row.badge_description || '';
    document.getElementById('badge_def_cond').value = row.condition_type || 'manual';
    document.getElementById('badge_def_val').value = row.condition_value || 0;
    document.getElementById('badge_def_auto').checked = row.is_auto === '1';
    document.getElementById('badge_def_main').checked = row.show_on_main !== '0';
    document.getElementById('badge_def_active').checked = row.is_active !== '0';
  });
});

function adminPost(action, extra, statusSel) {
  var fd = new FormData();
  fd.append('action', action);
  fd.append('eottae_talkroom_admin_token', extra.token || '');
  Object.keys(extra).forEach(function(k) {
    if (k !== 'token') fd.append(k, extra[k]);
  });
  return fetch(window.eottaeMemberGrowthAdminProc, { method: 'POST', body: fd, credentials: 'same-origin' })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      var st = document.querySelector(statusSel);
      if (st) st.textContent = data.message || '';
      return data;
    });
}

var btnAuto = document.getElementById('btnAutoFeatured');
if (btnAuto) {
  btnAuto.addEventListener('click', function() {
    if (!confirm('이번 주 주간 랭킹 1위 회원을 우수회원으로 등록할까요?')) return;
    adminPost('auto_featured', { token: btnAuto.getAttribute('data-token'), limit: 1 }, '[data-ops-status]').then(function(d) {
      if (d.success) location.reload();
    });
  });
}

var btnSnap = document.getElementById('btnSnapshotRank');
if (btnSnap) {
  btnSnap.addEventListener('click', function() {
    adminPost('snapshot_rankings', { token: btnSnap.getAttribute('data-token'), week_key: btnSnap.getAttribute('data-week') }, '[data-ops-status]');
  });
}

var btnWeeklyCron = document.getElementById('btnWeeklyCron');
if (btnWeeklyCron) {
  btnWeeklyCron.addEventListener('click', function() {
    if (!confirm('지난 주 랭킹 저장 + 이번 주 우수회원 자동 선정을 실행할까요?')) return;
    adminPost('run_weekly_cron', { token: btnWeeklyCron.getAttribute('data-token'), featured_limit: 3 }, '[data-ops-status]').then(function(d) {
      if (d.success) location.reload();
    });
  });
}

var btnRecalc = document.getElementById('btnRecalcLevels');
if (btnRecalc) {
  btnRecalc.addEventListener('click', function() {
    if (!confirm('모든 회원의 등급을 점수 기준으로 다시 계산할까요?')) return;
    adminPost('recalc_all_levels', { token: btnRecalc.getAttribute('data-token') }, '[data-ops-status]');
  });
}

bindAdminForm('grantBadgeForm', '[data-grant-badge-status]');
bindAdminForm('grantScoreForm', '[data-grant-score-status]');
bindAdminForm('saveFeaturedForm', '[data-save-featured-status]');
bindAdminForm('memberPrefsForm', '[data-member-prefs-status]');
bindAdminForm('badgeSettingsForm', '[data-badge-settings-status]');
bindAdminForm('saveLevelForm', '[data-save-level-status]');
bindAdminForm('saveBadgeDefForm', '[data-save-badge-def-status]');

document.querySelectorAll('[data-delete-featured]').forEach(function(btn) {
  btn.addEventListener('click', function() {
    if (!confirm('우수회원 선정을 삭제할까요?')) return;
    var fd = new FormData();
    fd.append('action', 'delete_featured');
    fd.append('featured_id', btn.getAttribute('data-delete-featured'));
    fd.append('eottae_talkroom_admin_token', btn.getAttribute('data-token'));
    fetch(window.eottaeMemberGrowthAdminProc, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.success) location.reload();
        else alert(data.message || '삭제 실패');
      });
  });
});
</script>

<?php
g5_page_end();
