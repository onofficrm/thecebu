<?php
include_once(dirname(__FILE__).'/_init.php');

$events = eottae_get_events(20);
$active = eottae_get_events(10, '진행중');

g5_page_start('이벤트·프로모션');
?>

<main class="mypage-subpage">
    <?php eottae_render_mypage_back(); ?>
    <h1 class="mypage-subpage__title">이벤트·프로모션</h1>

    <?php if (empty($events)) { ?>
    <div class="empty-state">
        <p class="empty-state__title">진행 중인 이벤트가 없습니다</p>
        <p>세부어때 업체·커뮤니티 이벤트가 등록되면 이곳에 표시됩니다.</p>
    </div>
    <?php } else { ?>
        <?php if (!empty($active)) { ?>
        <section class="event-section">
            <h2 class="event-section__title">진행 중</h2>
            <div class="event-card-grid">
                <?php foreach ($active as $event) { ?>
                <a href="<?php echo $event['href']; ?>" class="event-card event-card--active">
                    <span class="event-card__badge"><?php echo $event['category'] ?: '진행중'; ?></span>
                    <h3 class="event-card__title"><?php echo $event['subject']; ?></h3>
                    <p class="event-card__desc"><?php echo $event['content']; ?></p>
                    <time class="event-card__date"><?php echo substr($event['datetime'], 0, 10); ?></time>
                </a>
                <?php } ?>
            </div>
        </section>
        <?php } ?>

        <section class="event-section">
            <h2 class="event-section__title">전체 이벤트</h2>
            <div class="event-card-grid">
                <?php foreach ($events as $event) { ?>
                <a href="<?php echo $event['href']; ?>" class="event-card">
                    <?php if ($event['category']) { ?>
                    <span class="event-card__badge"><?php echo $event['category']; ?></span>
                    <?php } ?>
                    <h3 class="event-card__title"><?php echo $event['subject']; ?></h3>
                    <p class="event-card__desc"><?php echo $event['content']; ?></p>
                    <time class="event-card__date"><?php echo substr($event['datetime'], 0, 10); ?></time>
                </a>
                <?php } ?>
            </div>
        </section>
    <?php } ?>
</main>

<?php
g5_page_end();
