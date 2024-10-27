<?php
/*
 * Vi Notices
 */
?>



<div class="adsensei-banner-wrapper notice <?php echo $type; ?>">
  <section class="adsensei-banner-content">
    <div class="adsensei-banner-columns">
      <main class="adsensei-banner-main"><?php echo $message; ?></main>
      <aside class="adsensei-banner-sidebar-second" style="margin-right:30px;"><p style="text-align:center;"><img src="<?php echo ADSENSEI_PLUGIN_URL; ?>assets/images/vi_adsensei_logo.png" width="152" height="70"></p></aside>
    </div>
    <?php if(isset($transient)){ ?>
    <aside class="adsensei-banner-close"><div style="margin-top:5px;"><a href="<?php echo admin_url(); ?>admin.php?page=adsensei-settings&adsensei-action=close_<?php echo $transient; ?>" class="adsensei-notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a></div></aside>
<?php } ?>
  </section>
</div>

