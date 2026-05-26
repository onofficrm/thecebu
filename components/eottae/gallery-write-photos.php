<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (empty($file_count) || (int) $file_count < 1) {
    return;
}
?>

<div class="board-write-form__row board-write-form__photos gallery-write-photos">
    <p class="gallery-write-photos__label">
        사진 첨부 <span class="gallery-write-photos__hint">(최대 <?php echo (int) $file_count; ?>장 · JPG, PNG, HEIC 등)</span>
    </p>
    <div class="gallery-write-photos__grid" id="gallery_write_photos">
        <?php for ($i = 0; $i < $file_count; $i++) {
            $has_file = ($w === 'u' && !empty($file[$i]['file']));
            $preview_src = '';
            if ($has_file && function_exists('eottae_gallery_file_is_image') && eottae_gallery_file_is_image($file[$i]['source'])) {
                $preview_src = G5_DATA_URL.'/file/'.$bo_table.'/'.urlencode($file[$i]['file']);
            }
        ?>
        <div class="gallery-write-photos__slot<?php echo $has_file ? ' is-filled' : ''; ?>" data-slot="<?php echo $i; ?>">
            <label class="gallery-write-photos__picker" for="bf_file_<?php echo $i + 1; ?>">
                <input type="file" name="bf_file[]" id="bf_file_<?php echo $i + 1; ?>" accept="image/*,.heic,.heif" class="gallery-write-photos__input" title="사진 <?php echo $i + 1; ?>">
                <span class="gallery-write-photos__placeholder" aria-hidden="true">
                    <i class="fa fa-camera" aria-hidden="true"></i>
                    <span>사진 추가</span>
                </span>
                <img src="<?php echo $preview_src; ?>" alt="" class="gallery-write-photos__preview"<?php echo $preview_src ? '' : ' hidden'; ?>>
            </label>
            <?php if ($has_file) { ?>
            <p class="gallery-write-photos__current"><?php echo get_text($file[$i]['source']); ?></p>
            <label class="gallery-write-photos__delete">
                <input type="checkbox" name="bf_file_del[<?php echo $i; ?>]" value="1"> 삭제
            </label>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
</div>

<script>
(function($) {
    $(function() {
        $('#gallery_write_photos').on('change', '.gallery-write-photos__input', function() {
            var $slot = $(this).closest('.gallery-write-photos__slot');
            var $preview = $slot.find('.gallery-write-photos__preview');
            var $placeholder = $slot.find('.gallery-write-photos__placeholder');
            var file = this.files && this.files[0];

            if (!file) {
                $slot.removeClass('is-filled');
                $preview.attr('src', '').prop('hidden', true);
                $placeholder.show();
                return;
            }

            if ((!file.type || file.type.indexOf('image/') !== 0) && !/\.(jpe?g|png|gif|webp|heic|heif|bmp|avif)$/i.test(file.name || '')) {
                alert('이미지 파일만 업로드할 수 있습니다.');
                this.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                $preview.attr('src', e.target.result).prop('hidden', false);
                $placeholder.hide();
                $slot.addClass('is-filled');
            };
            reader.readAsDataURL(file);
        });
    });
})(jQuery);
</script>
