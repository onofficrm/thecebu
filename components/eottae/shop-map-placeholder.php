<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$markers = isset($shop_map_markers) && is_array($shop_map_markers) ? $shop_map_markers : array();
$marker_positions = array(
    array('top' => '18%', 'left' => '22%', 'tone' => 'blue', 'icon' => '🍽'),
    array('top' => '34%', 'left' => '58%', 'tone' => 'pink', 'icon' => '💆'),
    array('top' => '52%', 'left' => '36%', 'tone' => 'cyan', 'icon' => '☕'),
    array('top' => '68%', 'left' => '64%', 'tone' => 'blue', 'icon' => '🏪'),
    array('top' => '42%', 'left' => '78%', 'tone' => 'mint', 'icon' => '📍'),
);
?>

<section class="shop-map-panel" aria-label="지도 영역">
    <div class="shop-map-panel__canvas" id="shopMapPlaceholder">
        <p class="shop-map-panel__placeholder">Google Maps API (연동 예정)</p>
        <?php
        $i = 0;
        foreach ($markers as $marker) {
            if ($i >= count($marker_positions)) {
                break;
            }
            $pos = $marker_positions[$i];
            $i++;
            ?>
        <button type="button" class="shop-map-panel__pin shop-map-panel__pin--<?php echo $pos['tone']; ?>" style="top:<?php echo $pos['top']; ?>;left:<?php echo $pos['left']; ?>;" data-map-pin data-url="<?php echo htmlspecialchars($marker['url'], ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars($marker['name'], ENT_QUOTES, 'UTF-8'); ?>">
            <span aria-hidden="true"><?php echo $pos['icon']; ?></span>
        </button>
            <?php
        }
        if (empty($markers)) {
            foreach ($marker_positions as $pos) {
                ?>
        <span class="shop-map-panel__pin shop-map-panel__pin--<?php echo $pos['tone']; ?> shop-map-panel__pin--demo" style="top:<?php echo $pos['top']; ?>;left:<?php echo $pos['left']; ?>;" aria-hidden="true">
            <?php echo $pos['icon']; ?>
        </span>
                <?php
            }
        }
        ?>
    </div>
    <button type="button" class="shop-map-panel__locate" id="shopMapLocateBtn" title="내 위치">
        <span aria-hidden="true">⌖</span>
    </button>
</section>
