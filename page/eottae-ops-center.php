<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-column.lib.php';
include_once G5_LIB_PATH.'/eottae-column-report.lib.php';
include_once G5_LIB_PATH.'/eottae-community-report.lib.php';
include_once G5_LIB_PATH.'/eottae-calendar-report.lib.php';
include_once G5_LIB_PATH.'/eottae-push.lib.php';
include_once G5_LIB_PATH.'/eottae-ops-center.lib.php';

$ops = eottae_ops_build_context();
$tasks = $ops['tasks'];
$today = $ops['today_activity'];
$week = $ops['week_activity'];
$members = $ops['members'];
$talk = $ops['talk'];
$push = $ops['push'];
$auto_comment = $ops['auto_comment'];
$latest_posts = $ops['latest_posts'];
$inbox = $ops['inbox'];
$kpi_7d = $ops['kpi_7d'];
$kpi_30d = $ops['kpi_30d'];
$ops_token = function_exists('eottae_talkroom_admin_token') ? eottae_talkroom_admin_token() : '';
$ops_proc_url = G5_URL.'/proc/eottae-ops-center.php';

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-ops-center.css">', 24);

g5_page_start('세부어때 운영센터');
?>
<main class="ops-center">
    <header class="ops-hero">
        <div>
            <p class="ops-hero__eyebrow">THECEBU CONTROL ROOM</p>
            <h1>세부어때 운영센터</h1>
            <p>회원, 커뮤니티, 세부톡, 알림, 자동댓글을 한 화면에서 점검하는 운영자 전용 UX입니다.</p>
        </div>
        <div class="ops-hero__actions">
            <a href="<?php echo G5_URL; ?>/" class="ops-btn ops-btn--ghost">사이트 보기</a>
            <a href="<?php echo G5_ADMIN_URL; ?>/" class="ops-btn">그누보드 관리자</a>
        </div>
    </header>

    <section class="ops-status-strip" aria-label="핵심 상태">
        <article class="ops-status-card">
            <span>오늘 새 글</span>
            <strong><?php echo number_format((int) $today['posts']); ?></strong>
            <em>댓글 <?php echo number_format((int) $today['comments']); ?>개</em>
        </article>
        <article class="ops-status-card">
            <span>오늘 방문 회원</span>
            <strong><?php echo number_format((int) $members['visited_today']); ?></strong>
            <em>신규 <?php echo number_format((int) $members['joined_today']); ?>명</em>
        </article>
        <article class="ops-status-card">
            <span>세부톡 활성 방</span>
            <strong><?php echo number_format((int) $talk['rooms_active']); ?></strong>
            <em>승인대기 <?php echo number_format((int) $talk['rooms_pending']); ?>건</em>
        </article>
        <article class="ops-status-card<?php echo empty($push['configured']) ? ' is-warn' : ''; ?>">
            <span>앱 푸시 기기</span>
            <strong><?php echo number_format((int) $push['active']); ?></strong>
            <em><?php echo !empty($push['configured']) ? '발송 가능' : 'VAPID 키 필요'; ?></em>
        </article>
    </section>

    <div class="ops-layout">
        <section class="ops-panel ops-panel--tasks">
            <div class="ops-panel__head">
                <div>
                    <p>Today Queue</p>
                    <h2>오늘 처리할 일</h2>
                </div>
                <span><?php echo get_text($ops['generated_at']); ?></span>
            </div>
            <div class="ops-task-list">
                <?php foreach ($tasks as $task) {
                    $count = (int) ($task['count'] ?? 0);
                    $tone = preg_replace('/[^a-z]/', '', (string) ($task['tone'] ?? 'normal'));
                    ?>
                <a href="<?php echo get_text($task['href'] ?? '#'); ?>" class="ops-task ops-task--<?php echo $tone; ?><?php echo $count > 0 ? ' has-count' : ''; ?>">
                    <span class="ops-task__badge"><?php echo number_format($count); ?></span>
                    <span class="ops-task__main">
                        <strong><?php echo get_text($task['label'] ?? '작업'); ?></strong>
                        <em><?php echo get_text($task['desc'] ?? ''); ?></em>
                    </span>
                    <span class="ops-task__go">관리</span>
                </a>
                <?php } ?>
            </div>
        </section>

        <section class="ops-panel">
            <div class="ops-panel__head">
                <div>
                    <p>Health</p>
                    <h2>시스템 상태</h2>
                </div>
            </div>
            <div class="ops-health-grid">
                <div>
                    <span>자동댓글</span>
                    <strong><?php echo !empty($auto_comment['installed']) ? '설치됨' : '미설치'; ?></strong>
                    <em>대기 <?php echo number_format((int) $auto_comment['pending']); ?> / 실패 <?php echo number_format((int) $auto_comment['failed']); ?></em>
                </div>
                <div>
                    <span>푸시 발송</span>
                    <strong><?php echo !empty($push['configured']) ? '정상 준비' : '키 필요'; ?></strong>
                    <em>오늘 발송 <?php echo number_format((int) $push['sent_today']); ?> / 오류기기 <?php echo number_format((int) $push['failed']); ?></em>
                </div>
                <div>
                    <span>온라인 세션</span>
                    <strong><?php echo number_format((int) $members['online']); ?></strong>
                    <em>최근 15분 기준</em>
                </div>
                <div>
                    <span>미읽음 알림</span>
                    <strong><?php echo number_format((int) $talk['notifications_unread']); ?></strong>
                    <em>세부톡 알림 테이블</em>
                </div>
            </div>
        </section>
    </div>

    <section class="ops-grid ops-grid--wide-left">
        <article class="ops-panel">
            <div class="ops-panel__head">
                <div>
                    <p>Inbox</p>
                    <h2>운영 알림 인박스</h2>
                </div>
                <span>처리 필요 항목 우선</span>
            </div>
            <div class="ops-inbox">
                <?php foreach ($inbox as $item) {
                    $count = (int) ($item['count'] ?? 0);
                    $tone = preg_replace('/[^a-z]/', '', (string) ($item['tone'] ?? 'done'));
                    ?>
                <a href="<?php echo get_text($item['href'] ?? '#'); ?>" class="ops-inbox-item ops-inbox-item--<?php echo $tone; ?>">
                    <span class="ops-inbox-item__state"><?php echo get_text($item['status'] ?? '정상'); ?></span>
                    <strong><?php echo get_text($item['label'] ?? ''); ?></strong>
                    <em><?php echo get_text($item['desc'] ?? ''); ?></em>
                    <b><?php echo number_format($count); ?></b>
                </a>
                <?php } ?>
            </div>
        </article>

        <article class="ops-panel">
            <div class="ops-panel__head">
                <div>
                    <p>Push Campaign</p>
                    <h2>푸시 알림 캠페인</h2>
                </div>
            </div>
            <form class="ops-campaign-form" data-ops-campaign-form data-proc-url="<?php echo get_text($ops_proc_url); ?>">
                <input type="hidden" name="action" value="send_push_campaign">
                <input type="hidden" name="eottae_ops_token" value="<?php echo get_text($ops_token); ?>">
                <label>
                    <span>제목</span>
                    <input type="text" name="title" maxlength="80" placeholder="예: 오늘 세부 생활정보 업데이트" required>
                </label>
                <label>
                    <span>내용</span>
                    <textarea name="body" rows="4" maxlength="220" placeholder="앱 사용자에게 보낼 짧은 알림 문구를 입력하세요." required></textarea>
                </label>
                <label>
                    <span>클릭 URL</span>
                    <input type="url" name="url" placeholder="<?php echo G5_URL; ?>/page/eottae-notifications.php">
                </label>
                <label>
                    <span>발송 기기 수 제한</span>
                    <input type="number" name="limit" min="1" max="1000" value="<?php echo min(500, max(1, (int) $push['active'])); ?>">
                </label>
                <button type="submit" class="ops-btn"<?php echo empty($push['configured']) || (int) $push['active'] < 1 ? ' disabled' : ''; ?>>전체 앱 푸시 발송</button>
                <p class="ops-campaign-form__status" data-ops-campaign-status><?php echo empty($push['configured']) ? '푸시 키 설정 후 발송할 수 있습니다.' : '활성 앱 기기 '.number_format((int) $push['active']).'대에 발송할 수 있습니다.'; ?></p>
            </form>
        </article>
    </section>

    <section class="ops-panel ops-panel--kpi">
        <div class="ops-panel__head">
            <div>
                <p>KPI Trend</p>
                <h2>운영 KPI 추이</h2>
            </div>
            <span>최근 7일 + 30일 요약</span>
        </div>
        <div class="ops-kpi-layout">
            <div class="ops-kpi-chart" aria-label="최근 7일 참여 추이">
                <?php
                $max_engagement = 1;
                foreach ($kpi_7d as $day) {
                    $max_engagement = max($max_engagement, (int) ($day['engagement'] ?? 0), (int) ($day['visited'] ?? 0));
                }
                foreach ($kpi_7d as $day) {
                    $engagement_height = max(6, min(100, round(((int) ($day['engagement'] ?? 0) / $max_engagement) * 100)));
                    $visit_height = max(6, min(100, round(((int) ($day['visited'] ?? 0) / $max_engagement) * 100)));
                    ?>
                <div class="ops-kpi-bar">
                    <div class="ops-kpi-bar__bars">
                        <span class="ops-kpi-bar__visit" style="height: <?php echo (int) $visit_height; ?>%"></span>
                        <span class="ops-kpi-bar__engagement" style="height: <?php echo (int) $engagement_height; ?>%"></span>
                    </div>
                    <strong><?php echo get_text($day['date']); ?></strong>
                    <em>참여 <?php echo number_format((int) $day['engagement']); ?></em>
                </div>
                <?php } ?>
            </div>
            <div class="ops-kpi-summary">
                <?php
                $sum_30 = array('posts' => 0, 'comments' => 0, 'joined' => 0, 'visited' => 0, 'engagement' => 0);
                foreach ($kpi_30d as $day) {
                    foreach ($sum_30 as $key => $value) {
                        $sum_30[$key] += (int) ($day[$key] ?? 0);
                    }
                }
                ?>
                <div><span>30일 방문회원</span><strong><?php echo number_format((int) $sum_30['visited']); ?></strong></div>
                <div><span>30일 신규가입</span><strong><?php echo number_format((int) $sum_30['joined']); ?></strong></div>
                <div><span>30일 글</span><strong><?php echo number_format((int) $sum_30['posts']); ?></strong></div>
                <div><span>30일 댓글</span><strong><?php echo number_format((int) $sum_30['comments']); ?></strong></div>
            </div>
        </div>
    </section>

    <section class="ops-grid">
        <article class="ops-panel">
            <div class="ops-panel__head">
                <div>
                    <p>Content</p>
                    <h2>최근 7일 활동</h2>
                </div>
            </div>
            <div class="ops-metric-row">
                <div><span>글</span><strong><?php echo number_format((int) $week['posts']); ?></strong></div>
                <div><span>댓글</span><strong><?php echo number_format((int) $week['comments']); ?></strong></div>
                <div><span>참여</span><strong><?php echo number_format((int) $week['posts'] + (int) $week['comments']); ?></strong></div>
            </div>
            <div class="ops-board-list">
                <?php if (empty($week['top_boards'])) { ?>
                <p class="ops-empty">최근 7일 활동이 아직 없습니다.</p>
                <?php } else { foreach ($week['top_boards'] as $board) { ?>
                <a href="<?php echo get_text($board['href']); ?>" class="ops-board-item">
                    <span><?php echo get_text($board['label']); ?></span>
                    <strong><?php echo number_format((int) $board['total']); ?></strong>
                    <em>글 <?php echo number_format((int) $board['posts']); ?> · 댓글 <?php echo number_format((int) $board['comments']); ?></em>
                </a>
                <?php }} ?>
            </div>
        </article>

        <article class="ops-panel">
            <div class="ops-panel__head">
                <div>
                    <p>Shortcut</p>
                    <h2>빠른 운영 링크</h2>
                </div>
            </div>
            <div class="ops-quick-links">
                <?php foreach ($ops['quick_links'] as $link) { ?>
                <a href="<?php echo get_text($link['href']); ?>"><?php echo get_text($link['label']); ?></a>
                <?php } ?>
            </div>
        </article>
    </section>

    <section class="ops-panel">
        <div class="ops-panel__head">
            <div>
                <p>Live Feed</p>
                <h2>최근 올라온 글</h2>
            </div>
        </div>
        <div class="ops-feed">
            <?php if (empty($latest_posts)) { ?>
            <p class="ops-empty">최근 글이 없습니다.</p>
            <?php } else { foreach ($latest_posts as $post) { ?>
            <a href="<?php echo get_text($post['href']); ?>" class="ops-feed-item">
                <span><?php echo get_text($post['board']); ?></span>
                <strong><?php echo get_text($post['subject']); ?></strong>
                <em><?php echo get_text($post['author']); ?> · <?php echo get_text(substr((string) $post['datetime'], 0, 16)); ?> · 조회 <?php echo number_format((int) $post['hit']); ?> · 댓글 <?php echo number_format((int) $post['comments']); ?></em>
            </a>
            <?php }} ?>
        </div>
    </section>
</main>
<script>
(function () {
    var form = document.querySelector('[data-ops-campaign-form]');
    if (!form) {
        return;
    }
    var status = form.querySelector('[data-ops-campaign-status]');
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        var button = form.querySelector('button[type="submit"]');
        if (button && button.disabled) {
            return;
        }
        if (!window.confirm('입력한 내용으로 앱 푸시를 발송할까요?')) {
            return;
        }
        if (button) {
            button.disabled = true;
        }
        if (status) {
            status.textContent = '푸시를 발송하고 있습니다...';
        }
        fetch(form.getAttribute('data-proc-url'), {
            method: 'POST',
            credentials: 'same-origin',
            body: new FormData(form)
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (status) {
                    status.textContent = data && data.message ? data.message + (data.sent ? ' (' + data.sent + '대)' : '') : '처리되었습니다.';
                }
                if (data && data.success) {
                    form.reset();
                }
            })
            .catch(function () {
                if (status) {
                    status.textContent = '발송 중 오류가 발생했습니다.';
                }
            })
            .finally(function () {
                if (button) {
                    button.disabled = false;
                }
            });
    });
})();
</script>
<?php
g5_page_end();
