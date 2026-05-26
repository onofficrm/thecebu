<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-news.lib.php';
include_once G5_PATH.'/components/eottae/public-ai-admin-nav.php';

eottae_public_ai_ensure_schema();
eottae_public_ai_external_news_ensure_schema();

$admin_token = eottae_public_ai_admin_token();
$saved = !empty($_GET['saved']);
$categories = eottae_public_ai_external_news_categories();
$table = eottae_public_ai_external_news_table();
$list = array();
$result = sql_query(" SELECT * FROM `{$table}` ORDER BY news_id DESC LIMIT 50 ", false);
if ($result) {
    while ($row = sql_fetch_array($result)) {
        $list[] = eottae_public_ai_external_news_format_row($row);
    }
}

g5_page_start('공개톡 AI 외부뉴스');
?>

<main class="promo-admin-page talk-admin-page public-ai-admin-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <?php eottae_public_ai_render_admin_page_mypage_back(); ?>
            <a href="<?php echo eottae_public_ai_admin_settings_url(); ?>" class="promo-admin-page__back">← AI 기본 설정</a>
        </div>
        <h1 class="promo-admin-page__title">외부뉴스 소스</h1>
        <p class="promo-admin-page__desc">외부뉴스는 <strong>후보만 자동 생성</strong>되며, 관리자 승인 후에만 공개톡에 발행됩니다. 민감 주제는 자동 제외됩니다.</p>
        <?php eottae_public_ai_render_admin_nav('news'); ?>
    </header>

    <?php if ($saved) { ?><p class="talk-ai-settings__saved" role="status">저장했습니다.</p><?php } ?>

    <section class="promo-admin-panel talk-admin-panel">
        <h2 class="promo-admin-panel__title">뉴스 등록 (수동)</h2>
        <form id="publicAiNewsForm" class="talk-apply-form">
            <div class="talk-apply-form__field">
                <label for="news_title">제목 (내부용, 짧게)</label>
                <input type="text" id="news_title" name="title" class="talk-apply-form__input" maxlength="200" required>
            </div>
            <div class="talk-apply-form__field">
                <label for="news_summary">요약 (후보 생성 참고, 본문 복사 금지)</label>
                <textarea id="news_summary" name="summary" class="talk-apply-form__textarea" rows="3" maxlength="500" required></textarea>
            </div>
            <div class="talk-apply-form__field">
                <label for="news_category">분류</label>
                <select id="news_category" name="category" class="talk-apply-form__select">
                    <?php foreach ($categories as $key => $label) { ?>
                    <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="talk-apply-form__field">
                <label for="news_source_name">출처명</label>
                <input type="text" id="news_source_name" name="source_name" class="talk-apply-form__input" maxlength="80">
            </div>
            <div class="talk-apply-form__field">
                <label for="news_source_url">출처 URL</label>
                <input type="url" id="news_source_url" name="source_url" class="talk-apply-form__input" maxlength="255">
            </div>
            <button type="submit" class="promo-admin-btn promo-admin-btn--primary">등록</button>
        </form>
    </section>

    <section class="promo-admin-panel talk-admin-panel">
        <h2 class="promo-admin-panel__title">등록 목록</h2>
        <?php if (empty($list)) { ?>
        <p class="promo-admin-empty">등록된 외부뉴스가 없습니다.</p>
        <?php } else { ?>
        <table class="talk-admin-table">
            <thead><tr><th>ID</th><th>분류</th><th>제목</th><th>민감</th><th>상태</th><th>등록일</th></tr></thead>
            <tbody>
            <?php foreach ($list as $n) { ?>
                <tr>
                    <td><?php echo (int) $n['news_id']; ?></td>
                    <td><?php echo $n['category_label']; ?></td>
                    <td><?php echo $n['title']; ?><br><span class="talk-admin-table__sub"><?php echo cut_str($n['summary'], 60, '…'); ?></span></td>
                    <td><?php echo $n['is_sensitive'] ? '예' : '-'; ?></td>
                    <td><?php echo $n['status']; ?></td>
                    <td><?php echo substr($n['created_at'], 0, 16); ?></td>
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
  var form = document.getElementById('publicAiNewsForm');
  if (!form || typeof window.eottaePublicAiAdminPost !== 'function') return;
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var fd = new FormData(form);
    var fields = {};
    fd.forEach(function (v, k) { fields[k] = v; });
    window.eottaePublicAiAdminPost('save_external_news', fields).then(function (data) {
      alert(data.message || '');
      if (data.success) location.href = location.pathname + '?saved=1';
    });
  });
}());
</script>
<?php g5_page_end(); ?>
