<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$talk_invite_url = isset($talk_invite_url) ? trim((string) $talk_invite_url) : '';
$talk_invite_room_name = isset($talk_invite_room_name) ? get_text((string) $talk_invite_room_name) : '세부톡방';
$talk_invite_compact = !empty($talk_invite_compact);

if ($talk_invite_url === '') {
    return;
}

$talk_invite_input_id = 'talkInviteUrl'.substr(md5($talk_invite_url), 0, 8);
?>

<section class="talk-invite-share<?php echo $talk_invite_compact ? ' talk-invite-share--compact' : ''; ?>" aria-label="초대 링크">
    <div class="talk-invite-share__head">
        <h2 class="talk-invite-share__title">초대 링크</h2>
        <p class="talk-invite-share__desc">링크를 복사해 친구를 <strong><?php echo $talk_invite_room_name; ?></strong>에 초대하세요.</p>
    </div>
    <div class="talk-invite-share__row">
        <input
            type="text"
            id="<?php echo $talk_invite_input_id; ?>"
            class="talk-invite-share__input"
            value="<?php echo htmlspecialchars($talk_invite_url, ENT_QUOTES, 'UTF-8'); ?>"
            readonly
            aria-label="톡방 초대 링크"
        >
        <button
            type="button"
            class="talk-page__btn talk-invite-share__copy"
            data-talk-invite-copy
            data-copy-text="<?php echo htmlspecialchars($talk_invite_url, ENT_QUOTES, 'UTF-8'); ?>"
            data-copy-target="#<?php echo $talk_invite_input_id; ?>"
        >링크 복사</button>
    </div>
</section>
