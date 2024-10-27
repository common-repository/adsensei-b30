<?php
/**
 * vi ad template
 */
?>

<!-- WP ADSENSEI v. <?php echo ADSENSEI_VERSION; ?>  automatic embeded vi ad -->
<div class="adsensei-location adsensei-vi-ad<?php echo $adId; ?>" id="adsensei-vi-ad<?php echo $adId; ?>" style="min-width:336px;<?php echo $style; ?>">
    <script>
    <?php echo do_shortcode($adCode); ?>
    </script>
</div>