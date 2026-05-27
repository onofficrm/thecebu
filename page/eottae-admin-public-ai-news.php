<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-news.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-news-feed.lib.php';
include_once G5_PATH.'/components/eottae/public-ai-admin-nav.php';

eottae_public_ai_ensure_schema();
eottae_public_ai_external_news_ensure_schema();
eottae_public_ai_news_feed_ensure_schema();

$admin_token = eottae_public_ai_admin_token();
$saved = !empty($_GET['saved']);
$categories = eottae_public_ai_external_news_categories();
$feeds = eottae_public_ai_news_feed_list(false);

$web_cron_url = '';
if (function_exists('eottae_public_ai_web_cron_urls')) {
    $cron_urls = eottae_public_ai_web_cron_urls();
    $web_cron_url = (string) ($cron_urls['fetch_news_feeds'] ?? '');
}

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
        <h1 class="promo-admin-page__title">외부뉴스 · RSS 수집</h1>
        <p class="promo-admin-page__desc">
            RSS/뉴스 피드를 자동 수집해 <strong>외부뉴스</strong>로 등록합니다.
            AI 공개톡에는 <strong>후보만 생성</strong>되며, 관리자 승인 후 발행됩니다. 민감·정치·범죄 키워드는 자동 제외됩니다.
        </p>
        <?php eottae_public_ai_render_admin_nav('news'); ?>
    </header>

    <?php if ($saved) { ?><p class="talk-ai-settings__saved" role="status">저장했습니다.</p><?php } ?>

    <section class="promo-admin-panel talk-admin-panel">
        <h2 class="promo-admin-panel__title">RSS 피드 등록</h2>
        <p class="promo-admin-panel__desc">Google News, 언론사 RSS, 관광청 피드 등을 등록하세요. 크론이 주기적으로 수집합니다.</p>
        <?php if ($web_cron_url !== '') { ?>
        <p class="talk-admin-form__hint">웹 크론 URL (1시간마다 권장): <code class="public-ai-cron-url"><?php echo htmlspecialchars($web_cron_url, ENT_QUOTES, 'UTF-8'); ?></code></p>
        <?php } else { ?>
        <p class="talk-admin-form__hint">서버 crontab: <code>php <?php echo G5_PATH; ?>/cron/sebu_public_ai_fetch_news_feeds.php</code></p>
        <?php } ?>

        <form id="publicAiNewsFeedForm" class="talk-apply-form">
            <input type="hidden" name="feed_id" id="news_feed_id" value="0">
            <div class="talk-apply-form__field">
                <label for="news_feed_name">피드 이름</label>
                <input type="text" id="news_feed_name" name="name" class="talk-apply-form__input" maxlength="120" required placeholder="예) Cebu Daily News">
            </div>
            <div class="talk-apply-form__field">
                <label for="news_feed_url">RSS/Atom URL</label>
                <input type="url" id="news_feed_url" name="feed_url" class="talk-apply-form__input" maxlength="500" required placeholder="https://example.com/feed.xml">
            </div>
            <div class="talk-apply-form__field">
                <label for="news_feed_site">사이트 URL (선택)</label>
                <input type="url" id="news_feed_site" name="site_url" class="talk-apply-form__input" maxlength="255">
            </div>
            <div class="talk-apply-form__field">
                <label for="news_feed_category">기본 분류</label>
                <select id="news_feed_category" name="category" class="talk-apply-form__select">
                    <?php foreach ($categories as $key => $label) { ?>
                    <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="talk-apply-form__field talk-apply-form__field--inline">
                <label for="news_feed_interval">수집 주기(분)</label>
                <input type="number" id="news_feed_interval" name="fetch_interval_min" class="talk-apply-form__input talk-apply-form__input--sm" min="15" max="1440" value="60">
                <label for="news_feed_max">회당 최대 건수</label>
                <input type="number" id="news_feed_max" name="max_items_per_run" class="talk-apply-form__input talk-apply-form__input--sm" min="1" max="30" value="8">
            </div>
            <label class="talk-admin-form__check"><input type="checkbox" name="is_enabled" value="1" checked> 수집 사용</label>
            <div class="talk-apply-form__actions">
                <button type="button" class="promo-admin-btn" id="btnTestNewsFeed">피드 미리보기</button>
                <button type="submit" class="promo-admin-btn promo-admin-btn--primary">피드 저장</button>
            </div>
        </form>
        <div id="newsFeedPreview" class="public-ai-feed-preview" hidden></div>
    </section>

    <section class="promo-admin-panel talk-admin-panel">
        <div class="promo-admin-panel__head-row">
            <h2 class="promo-admin-panel__title">등록된 RSS 피드</h2>
            <button type="button" class="promo-admin-btn promo-admin-btn--primary" id="btnFetchAllFeeds">전체 수집 실행</button>
        </div>
        <?php if (empty($feeds)) { ?>
        <p class="promo-admin-empty">등록된 RSS 피드가 없습니다.</p>
        <?php } else { ?>
        <table class="talk-admin-table">
            <thead>
                <tr>
                    <th>이름</th>
                    <th>분류</th>
                    <th>주기</th>
                    <th>마지막 수집</th>
                    <th>신규</th>
                    <th>상태</th>
                    <th>관리</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($feeds as $feed) { ?>
                <tr>
                    <td>
                        <?php echo $feed['name']; ?>
                        <br><span class="talk-admin-table__sub"><?php echo cut_str($feed['feed_url'], 48, '…'); ?></span>
                    </td>
                    <td><?php echo $feed['category_label']; ?></td>
                    <td><?php echo (int) $feed['fetch_interval_min']; ?>분</td>
                    <td><?php echo $feed['last_fetched_at'] !== '0000-00-00 00:00:00' ? substr($feed['last_fetched_at'], 0, 16) : '—'; ?></td>
                    <td><?php echo (int) $feed['last_new_count']; ?></td>
                    <td>
                        <?php echo $feed['is_enabled'] ? 'ON' : 'OFF'; ?>
                        <?php if ($feed['last_error'] !== '') { ?>
                        <br><span class="talk-admin-table__sub talk-admin-table__sub--error"><?php echo cut_str($feed['last_error'], 40, '…'); ?></span>
                        <?php } ?>
                    </td>
                    <td>
                        <button type="button" class="promo-admin-btn promo-admin-btn--sm js-fetch-feed" data-feed-id="<?php echo (int) $feed['feed_id']; ?>">수집</button>
                        <button type="button" class="promo-admin-btn promo-admin-btn--sm js-edit-feed" data-feed='<?php echo htmlspecialchars(json_encode($feed, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>'>수정</button>
                        <button type="button" class="promo-admin-btn promo-admin-btn--sm js-delete-feed" data-feed-id="<?php echo (int) $feed['feed_id']; ?>">삭제</button>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php } ?>
    </section>

    <section class="promo-admin-panel talk-admin-panel">
        <h2 class="promo-admin-panel__title">뉴스 수동 등록</h2>
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
        <h2 class="promo-admin-panel__title">수집·등록 목록 (최근 50건)</h2>
        <?php if (empty($list)) { ?>
        <p class="promo-admin-empty">등록된 외부뉴스가 없습니다.</p>
        <?php } else { ?>
        <table class="talk-admin-table">
            <thead><tr><th>ID</th><th>분류</th><th>제목</th><th>출처</th><th>민감</th><th>등록</th></tr></thead>
            <tbody>
            <?php foreach ($list as $n) { ?>
                <tr>
                    <td><?php echo (int) $n['news_id']; ?></td>
                    <td><?php echo $n['category_label']; ?></td>
                    <td><?php echo $n['title']; ?><br><span class="talk-admin-table__sub"><?php echo cut_str($n['summary'], 60, '…'); ?></span></td>
                    <td><?php echo $n['source_name']; ?><?php if (!empty($n['feed_id'])) { ?><br><span class="talk-admin-table__sub">RSS #<?php echo (int) $n['feed_id']; ?></span><?php } ?></td>
                    <td><?php echo $n['is_sensitive'] ? '예' : '-'; ?></td>
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
  if (typeof window.eottaePublicAiAdminPost !== 'function') return;

  var feedForm = document.getElementById('publicAiNewsFeedForm');
  var newsForm = document.getElementById('publicAiNewsForm');
  var preview = document.getElementById('newsFeedPreview');

  function showPreview(data) {
    if (!preview || !data || !data.preview) return;
    var html = '<p><strong>미리보기</strong> (총 ' + (data.total || 0) + '건)</p><ul>';
    data.preview.forEach(function (row) {
      html += '<li><strong>' + row.title + '</strong>';
      if (row.summary) html += '<br>' + row.summary;
      if (row.link) html += '<br><a href="' + row.link + '" target="_blank" rel="noopener">' + row.link + '</a>';
      html += '</li>';
    });
    html += '</ul>';
    preview.innerHTML = html;
    preview.hidden = false;
  }

  if (feedForm) {
    feedForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(feedForm);
      var fields = {};
      fd.forEach(function (v, k) { fields[k] = v; });
      fields.is_enabled = feedForm.querySelector('[name="is_enabled"]').checked ? '1' : '';
      window.eottaePublicAiAdminPost('save_news_feed', fields).then(function (data) {
        alert(data.message || '');
        if (data.success) location.href = location.pathname + '?saved=1';
      });
    });
  }

  var btnTest = document.getElementById('btnTestNewsFeed');
  if (btnTest && feedForm) {
    btnTest.addEventListener('click', function () {
      var url = feedForm.querySelector('[name="feed_url"]').value;
      window.eottaePublicAiAdminPost('test_news_feed', { feed_url: url }).then(function (data) {
        if (!data.success) {
          alert(data.message || '미리보기 실패');
          return;
        }
        showPreview(data);
      });
    });
  }

  document.querySelectorAll('.js-fetch-feed').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = btn.getAttribute('data-feed-id');
      window.eottaePublicAiAdminPost('fetch_news_feeds', { feed_id: id, force: '1' }).then(function (data) {
        alert(data.message || '');
        if (data.success) location.reload();
      });
    });
  });

  var btnAll = document.getElementById('btnFetchAllFeeds');
  if (btnAll) {
    btnAll.addEventListener('click', function () {
      if (!confirm('등록된 모든 RSS 피드를 지금 수집할까요?')) return;
      window.eottaePublicAiAdminPost('fetch_news_feeds', { force: '1' }).then(function (data) {
        alert(data.message || '');
        if (data.success) location.reload();
      });
    });
  }

  document.querySelectorAll('.js-edit-feed').forEach(function (btn) {
    btn.addEventListener('click', function () {
      try {
        var feed = JSON.parse(btn.getAttribute('data-feed') || '{}');
        feedForm.querySelector('[name="feed_id"]').value = feed.feed_id || 0;
        feedForm.querySelector('[name="name"]').value = feed.name || '';
        feedForm.querySelector('[name="feed_url"]').value = feed.feed_url || '';
        feedForm.querySelector('[name="site_url"]').value = feed.site_url || '';
        feedForm.querySelector('[name="category"]').value = feed.category || 'local';
        feedForm.querySelector('[name="fetch_interval_min"]').value = feed.fetch_interval_min || 60;
        feedForm.querySelector('[name="max_items_per_run"]').value = feed.max_items_per_run || 8;
        feedForm.querySelector('[name="is_enabled"]').checked = !!feed.is_enabled;
        feedForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } catch (e) {}
    });
  });

  document.querySelectorAll('.js-delete-feed').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!confirm('이 RSS 피드를 삭제할까요?')) return;
      window.eottaePublicAiAdminPost('delete_news_feed', { feed_id: btn.getAttribute('data-feed-id') }).then(function (data) {
        alert(data.message || '');
        if (data.success) location.reload();
      });
    });
  });

  if (newsForm) {
    newsForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(newsForm);
      var fields = {};
      fd.forEach(function (v, k) { fields[k] = v; });
      window.eottaePublicAiAdminPost('save_external_news', fields).then(function (data) {
        alert(data.message || '');
        if (data.success) location.href = location.pathname + '?saved=1';
      });
    });
  }
}());
</script>
<?php g5_page_end(); ?>
