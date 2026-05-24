<?php
include_once(dirname(__FILE__).'/_init.php');

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.'/page/eottae-inquiries.php'));
}

$inquiries = eottae_get_member_inquiries($member['mb_id'], 30);

g5_page_start('문의 내역');
?>

<main class="mypage-subpage">
    <?php eottae_render_mypage_back(); ?>
    <h1 class="mypage-subpage__title">문의 내역</h1>

    <?php if (empty($inquiries)) { ?>
    <div class="empty-state">
        <p class="empty-state__title">문의 내역이 없습니다</p>
        <p>업소 상세의 <strong>문의하기</strong> 또는 사업자 영역에서 문의를 남기면 이곳에서 확인할 수 있습니다.</p>
    </div>
    <?php } else { ?>
    <ul class="inquiry-history-list">
        <?php foreach ($inquiries as $item) { ?>
        <li class="inquiry-history-list__item">
            <div class="inquiry-history-list__head">
                <strong><?php echo $item['subject']; ?></strong>
                <span class="inquiry-history-list__status"><?php echo $item['status']; ?></span>
            </div>
            <p class="inquiry-history-list__content"><?php echo $item['content']; ?></p>
            <time class="inquiry-history-list__date"><?php echo substr($item['datetime'], 0, 16); ?></time>
        </li>
        <?php } ?>
    </ul>
    <?php } ?>
</main>

<?php
g5_page_end();
