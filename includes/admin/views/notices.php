<?php
/*
 * WP ADSENSEI Notices
 */
?>



<div class="adsensei-banner-wrapper notice <?php echo $type; ?>">
  <section class="adsensei-banner-content">
    <div class="adsensei-banner-columns">
        <main class="adsensei-banner-main"><p><?php echo $message; ?></p></main>
      <aside class="adsensei-banner-sidebar-second" style="margin-right:30px;"></p></aside>
    </div>
    <aside class="adsensei-banner-close"><div style="margin-top:5px;"><a href="<?php echo admin_url();?>admin.php?page=adsensei-settings&adsensei-action=<?php echo $action; ?>" class="adsensei-notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a></div></aside>
  </section>
</div>

