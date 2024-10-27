<?php

function adsenseiB30_home_text_settings_page() {

	global $home_text_settings;
	?>
	<div id="main_wrapper" class="items-container">

	  <div class="ninja-container borderRed">
	      <img class="ninja" style="width:55px!important;margin-top:40px;" src="<?php echo esc_attr(plugins_url('assets/ninja2.png',__FILE__)); ?>" alt="">
	      <div>
	        <h2 style=""><?php _e('--HINAKA--','adsensei-b30'); ?></h2>
	        <h4 style="margin-bottom:0"><?php _e('Una letal ninja invisible en tu Home','adsensei-b30'); ?></h4>
	      </div>
	  </div>

	  <div>
	      <form id="adsenseib30_form3" method="post" action="options.php">
	        <?php settings_fields('home_text_settings_group'); ?>
	        <?php adsenseib30_get_tab3(); ?>
	        <p class="submit">
	          <input type="submit" class="button -red"  value="<?php _e('Guardar opciones', 'adsensei-b30'); ?>" />
	        </p>
	      </form>
	  </div>

		<div class="saveResult"></div>
	</div>
<?php
    adsenseiB30_postJqueryScript();
}

function adsenseib30_get_tab3(){

    $home_text_settings = get_option('home_text_settings');
    $home_text_id = "home_text_settings[homeText]";
    $home_text = $home_text_settings['homeText'];
?>

    <div class="rm_title red" style="margin-top:10px">
      <h3>
        <span style="color:#eee"><?php _e('Texto a añadir en home','adsensei-b30'); ?></span>
      </h3>
    </div>

    <?php wp_editor( $home_text, 'hometext', array( 'textarea_id' => $home_text_id, 'textarea_name' => $home_text_id) ); ?>

<?php
}



function adsenseiB30_postJqueryScript(){
?>
  <script type="text/javascript">

      jQuery(document).ready(function() {
         jQuery('#adsenseib30_form3').submit(function() {

           jQuery(this).find("textarea").html(adsenseiB30_get_tinymce_content())

            jQuery('#adsenseib30_form3').ajaxSubmit({
               success: function(){
                  jQuery('.saveResult').html("<div class='saveMessage successModal'></div>");
                  jQuery('.saveMessage').append("<p><?php _e('Ajustes guardados','adsensei-b30') ?></p>").slideDown(250);
               },
               error: function(xhr, status, error) {
                  var err = eval("(" + xhr.responseText + ")");
                  alert(err.Message);
                },
               timeout: 3200
            });
            setTimeout("jQuery('.saveMessage').fadeOut(400);", 3200);
            return false;
         });
      });

      function adsenseiB30_get_tinymce_content(){
          if (jQuery("#wp-hometext-wrap").hasClass("tmce-active")){
              return tinyMCE.activeEditor.getContent();
          }else{
              return jQuery('#hometext').val();
          }
      }
    </script>
<?php

}
