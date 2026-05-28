<?php
if (!defined('_GNUBOARD_')) {
    exit;
}
?>
<aside class="report-list-notice" role="note">
    <p>제보해주신 내용은 관리자 확인 후 공개될 수 있습니다.</p>
    <p>허위 정보, 비방, 개인정보가 포함된 내용은 공개되지 않을 수 있습니다.</p>
    <p>익명 제보도 가능하지만, 정확한 확인이 필요한 경우 연락처를 남겨주세요.</p>
    <?php if (empty($GLOBALS['eottae_report_list_is_admin'])) { ?>
    <p class="report-list-notice__policy">일반 회원에게는 <strong>공개됨</strong> 상태 제보와 본인이 작성한 제보만 목록에 표시됩니다.</p>
    <?php } ?>
</aside>
