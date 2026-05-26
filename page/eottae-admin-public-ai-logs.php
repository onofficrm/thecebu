<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
include_once G5_PATH.'/components/eottae/public-ai-admin-nav.php';

eottae_public_ai_ensure_schema();

$logs = eottae_public_ai_admin_list_logs(100);
$admin_token = eottae_public_ai_admin_token();

g5_page_start('공개톡 AI 발행 로그');
?>

<main class="promo-admin-page talk-admin-page public-ai-admin-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo eottae_public_ai_admin_settings_url(); ?>" class="promo-admin-page__back">← AI 기본 설정</a>
            <a href="<?php echo eottae_public_ai_admin_candidates_url('pending'); ?>" class="promo-admin-page__back">후보 메시지</a>
        </div>
        <h1 class="promo-admin-page__title">AI 발행 로그</h1>
        <p class="promo-admin-page__desc">테스트 발행·자동 발행 시도 기록입니다. 공개톡 메시지 ID는 3단계 연동 후 채워집니다.</p>
        <?php eottae_public_ai_render_admin_nav('logs'); ?>
    </header>

    <section class="promo-admin-panel talk-admin-panel">
        <?php if (empty($logs)) { ?>
        <p class="promo-admin-empty">표시할 로그가 없습니다.</p>
        <?php } else { ?>
        <div class="talk-admin-table-wrap">
            <table class="talk-admin-table public-ai-logs-table">
                <thead>
                    <tr>
                        <th scope="col">날짜</th>
                        <th scope="col">트리거</th>
                        <th scope="col">메시지</th>
                        <th scope="col">발행 상태</th>
                        <th scope="col">공개톡 ID</th>
                        <th scope="col">오류</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log) { ?>
                    <tr>
                        <td data-label="날짜"><?php echo $log['created_at'] !== '' ? substr($log['created_at'], 0, 16) : '-'; ?></td>
                        <td data-label="트리거"><?php echo $log['trigger_label']; ?></td>
                        <td data-label="메시지"><?php echo $log['message']; ?></td>
                        <td data-label="발행 상태"><span class="talk-apply-status talk-apply-status--<?php echo htmlspecialchars($log['publish_status'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo $log['publish_label']; ?></span></td>
                        <td data-label="공개톡 ID"><?php echo $log['chat_message_id'] > 0 ? (int) $log['chat_message_id'] : '-'; ?></td>
                        <td data-label="오류"><?php echo $log['error_message'] !== '' ? $log['error_message'] : '-'; ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </section>
</main>

<?php
g5_page_end();
