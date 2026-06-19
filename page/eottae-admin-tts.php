<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-tts.lib.php';

eottae_tts_ensure_schema();

$saved = false;
$error = '';
$admin_token = eottae_talkroom_admin_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['admin_token']) ? trim((string) $_POST['admin_token']) : '';
    if (!eottae_talkroom_verify_admin_token($token)) {
        $error = '관리자 토큰이 만료되었습니다. 새로고침 후 다시 저장해 주세요.';
    } else {
        $result = eottae_tts_save_settings(array(
            'enabled' => isset($_POST['enabled']) ? 1 : 0,
            'model' => $_POST['model'] ?? 'tts-1',
            'voice' => $_POST['voice'] ?? 'nova',
            'speed' => $_POST['speed'] ?? '1.0',
            'max_chars' => $_POST['max_chars'] ?? '4000',
            'boards' => isset($_POST['boards']) && is_array($_POST['boards']) ? $_POST['boards'] : array(),
        ));
        $saved = !empty($result['ok']);
        $admin_token = eottae_talkroom_admin_token(true);
    }
}

$settings = eottae_tts_get_settings();
$voices = eottae_tts_voice_options();
$boards = eottae_tts_board_options();
$selected_boards = array_fill_keys($settings['board_list'], true);
$api_key_ready = eottae_tts_api_key() !== '';

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-tts-admin.css">', 24);

g5_page_start('AI 음성읽기 관리');
?>
<main class="tts-admin-page">
    <header class="tts-admin-hero">
        <div>
            <p>AI TEXT TO SPEECH</p>
            <h1>AI 음성읽기 관리</h1>
            <span>게시판 본문을 OpenAI TTS로 MP3 파일로 생성하고, 생성된 음성은 캐시해 재사용합니다.</span>
        </div>
        <div class="tts-admin-hero__actions">
            <a href="<?php echo G5_URL; ?>/page/eottae-ops-center.php">운영센터</a>
            <a href="<?php echo G5_ADMIN_URL; ?>/">그누보드 관리자</a>
        </div>
    </header>

    <?php if ($saved) { ?>
    <p class="tts-admin-alert tts-admin-alert--ok">설정을 저장했습니다.</p>
    <?php } ?>
    <?php if ($error !== '') { ?>
    <p class="tts-admin-alert tts-admin-alert--error"><?php echo get_text($error); ?></p>
    <?php } ?>
    <?php if (!$api_key_ready) { ?>
    <p class="tts-admin-alert tts-admin-alert--error">AI API 키가 설정되어 있지 않습니다. <code>data/eottae-secrets.local.php</code>의 <code>ai_generate_api_key</code> 또는 <code>openai_api_key</code>를 확인해 주세요.</p>
    <?php } ?>

    <form method="post" class="tts-admin-form">
        <input type="hidden" name="admin_token" value="<?php echo get_text($admin_token); ?>">

        <section class="tts-admin-panel">
            <h2>기본 설정</h2>
            <label class="tts-admin-check">
                <input type="checkbox" name="enabled" value="1"<?php echo !empty($settings['enabled']) ? ' checked' : ''; ?>>
                게시글 AI 음성읽기 사용
            </label>

            <div class="tts-admin-grid">
                <label>
                    <span>OpenAI TTS 모델</span>
                    <input type="text" name="model" value="<?php echo get_text($settings['model']); ?>" placeholder="tts-1">
                    <em>호환성을 위해 기본값은 <code>tts-1</code>입니다.</em>
                </label>
                <label>
                    <span>기본 음성</span>
                    <select name="voice">
                        <?php foreach ($voices as $voice => $label) { ?>
                        <option value="<?php echo get_text($voice); ?>"<?php echo $settings['voice'] === $voice ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label>
                    <span>기본 재생 속도</span>
                    <select name="speed">
                        <?php foreach (array('0.75', '1.00', '1.25', '1.50', '1.75', '2.00') as $speed) { ?>
                        <option value="<?php echo $speed; ?>"<?php echo abs((float) $settings['speed'] - (float) $speed) < 0.01 ? ' selected' : ''; ?>><?php echo rtrim(rtrim($speed, '0'), '.'); ?>x</option>
                        <?php } ?>
                    </select>
                </label>
                <label>
                    <span>생성 글자 수 제한</span>
                    <input type="number" name="max_chars" min="500" max="4000" value="<?php echo (int) $settings['max_chars']; ?>">
                    <em>OpenAI TTS 입력 한도와 비용 관리를 위해 최대 4000자까지 사용합니다.</em>
                </label>
            </div>
        </section>

        <section class="tts-admin-panel">
            <div class="tts-admin-panel__head">
                <h2>적용 게시판</h2>
                <span>체크된 게시판의 글 보기 화면에 음성읽기 바가 표시됩니다.</span>
            </div>
            <div class="tts-admin-board-grid">
                <?php foreach ($boards as $board) { ?>
                <label class="tts-admin-board">
                    <input type="checkbox" name="boards[]" value="<?php echo get_text($board['bo_table']); ?>"<?php echo isset($selected_boards[$board['bo_table']]) ? ' checked' : ''; ?>>
                    <strong><?php echo get_text($board['label']); ?></strong>
                    <em><?php echo get_text($board['bo_table']); ?></em>
                </label>
                <?php } ?>
            </div>
        </section>

        <div class="tts-admin-submit">
            <button type="submit">설정 저장</button>
        </div>
    </form>
</main>
<?php
g5_page_end();
