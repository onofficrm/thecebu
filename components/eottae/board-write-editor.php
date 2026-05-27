<?php
/**
 * 게시판 글쓰기·수정 — SmartEditor2 / fallback textarea
 * write.php 스코프: $editor_html, $is_dhtml_editor, $content, $config
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

$eottae_editor_label = isset($eottae_editor_label) ? (string) $eottae_editor_label : '내용';
$eottae_editor_placeholder = isset($eottae_editor_placeholder)
    ? (string) $eottae_editor_placeholder
    : '내용을 입력하세요';
$eottae_editor_field_class = isset($eottae_editor_field_class)
    ? (string) $eottae_editor_field_class
    : 'community-write-page__field community-write-page__field--content';
$eottae_editor_use_editor = !empty($is_dhtml_editor) && !empty($editor_html);
?>
<div class="<?php echo htmlspecialchars($eottae_editor_field_class, ENT_QUOTES, 'UTF-8'); ?>">
    <label for="wr_content"><?php echo get_text($eottae_editor_label); ?></label>
    <?php if ($eottae_editor_use_editor) { ?>
    <div class="eottae-board-editor wr_content <?php echo !empty($config['cf_editor']) ? get_text($config['cf_editor']) : 'smarteditor2'; ?>">
        <?php echo $editor_html; ?>
    </div>
    <?php } else { ?>
    <textarea name="wr_content" id="wr_content" required class="community-write-page__textarea" placeholder="<?php echo htmlspecialchars($eottae_editor_placeholder, ENT_QUOTES, 'UTF-8'); ?>"><?php echo $content; ?></textarea>
    <?php } ?>
</div>
