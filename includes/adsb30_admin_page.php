<?php

function adsenseib30_settings_page() {
	global $adsenseib30_settings;
?>
	<script>
		jQuery(document).ready(function() {
			jQuery("div#fButton").click(function(){
				jQuery("form#adsenseib30_form").submit();
			});
		});
	</script>
	<div id="main_wrapper" class="items-container">
		<div class="floatingHelpIcon"><a href="#shortcodes"><div class="fButton"></div></a></div>
		<div class="floatingIcon"><div id="fButton" class="fButton"></div></div>
		<div class="ninja-container borderBlue">
			<img class="ninja" style="width:50px!important" src="<?php echo esc_attr(plugins_url('assets/ninja1_.png',__FILE__)); ?>" alt=""/>
			<div>
				<h2><?php _e('KABUZA', 'adsensei-b30'); ?></h2>
				<h4><?php _e('Anuncios ninja a golpe de katana allá dónde quieras', 'adsensei-b30'); ?></h4>
			</div>
		</div>

		<div>
				<form id="adsenseib30_form" method="post" action="options.php">
					<?php settings_fields('adsenseib30_settings_group'); ?>
					<p class="submit" style="display:none">
						<input type="submit" class="button-primary" value="<?php _e('Guardar opciones', 'adsensei-b30'); ?>" />
						<a style="margin-left:30px" href="#shortcodes"> <?php _e('VER SHORTCODES','adsensei-b30') ?></a>
						<div class="saveResult"></div>
					</p>
					<?php
						$categories = get_categories(array('hide_empty' => false));
						//$catIds = array_map(create_function('$o', 'return $o->cat_ID;'), $categories);
						$catIds = array_map(function($o){return $o->cat_ID;}, $categories);
						//$catNames = array_map(create_function('$o', 'return $o->name;'), $categories);
						$catNames = array_map(function($o){return $o->name;}, $categories);
						array_unshift($catIds, -1);
						array_unshift($catNames, __('Aplicar a todas','adsensei-b30'));

						adsenseib30_get_tab(1, $catIds, $catNames);
						adsenseib30_get_tab(2, $catIds, $catNames);
						adsenseib30_get_tab(3, $catIds, $catNames);
						adsenseib30_get_tab(4, $catIds, $catNames);
						adsenseib30_get_tab(5, $catIds, $catNames);
						adsenseib30_get_tab(6, $catIds, $catNames);
						adsenseib30_get_tab(7, $catIds, $catNames);
						adsenseib30_get_tab(8, $catIds, $catNames);
						adsenseib30_get_tab(9, $catIds, $catNames);
						adsenseib30_get_tab(10, $catIds, $catNames);
					?>
					<p class="submit">
						<input id="saveButton" type="submit" class="button-primary" value="<?php _e('Guardar Opciones', 'adsensei-b30'); ?>" />
					</p>
				</form>
				<div class="saveResult"></div>
				<script type="text/javascript">
					jQuery(document).ready(function() {
						jQuery('#adsenseib30_form').submit(function() {
							jQuery(this).ajaxSubmit({
								success: function(){
									jQuery('.saveResult').html("<div class='saveMessage successModal'></div>");
									jQuery('.saveMessage').append("<p><?php _e('Ajustes guardados','adsensei-b30'); ?></p>").slideDown(250);
								},
								timeout: 3200
							});
							setTimeout("jQuery('.saveMessage').fadeOut(400);", 3200);
							return false;
						});
					});
				</script>
		</div>
		<a name="shortcodes"></a>
		<div class="rm_opts">
			<div class="rm_section shortcodes">
				<h2><?php _e('SHORTCODES','adsensei-b30'); ?></h2>
				<p><?php _e('Puedes insertar cualquier anuncio, en la posición que quieras, mediante su shortcode:','adsensei-b30'); ?></p>
				<ul>
					<li><b>[sin_anuncios_b30]</b>: <?php _e('Desactiva el uso de AdSensei en un post/página en concreto','adsensei-b30'); ?></li>
					<li><b>[anuncio_b30 id=x]</b>:  <?php _e('Inserta el anuncio x en el lugar dónde pongas el shortcode','adsensei-b30'); ?><br/>
						<p> <?php _e('Ejemplo:','adsensei-b30'); ?> </p>
						<p><b>[anuncio_b30 id=2]</b>:  <?php _e('Inserta el anuncio 2 dónde hayas escrito el shortcode.','adsensei-b30'); ?></p>
					</li>
				</ul>
			</div>
		</div>
	</div>
<?php
}

function adsenseib30_get_tab($numAd, $catIds, $catNames){
	$adsenseib30_settings = get_option('adsenseib30_settings');

  $html_idCode = "adsenseib30_settings[adCode".$numAd."]";
  $html_idPosition = "adsenseib30_settings[adPosition".$numAd."]";
  $html_idDevice = "adsenseib30_settings[adDevice".$numAd."]";
  $html_idMargin = "adsenseib30_settings[adMargin".$numAd."]";
  $html_idAdName = "adsenseib30_settings[adName".$numAd."]";
  $html_idAdEnabled = "adsenseib30_settings[adEnabled".$numAd."]";
  $html_idAlign = "adsenseib30_settings[adAlign".$numAd."]";
  $html_idShowOn = "adsenseib30_settings[showOn".$numAd."]";
  $html_idCategory = "adsenseib30_settings[adCategory".$numAd."]";

  $adCode = isset($adsenseib30_settings['adCode'.$numAd])?$adsenseib30_settings['adCode'.$numAd]:'';
  $adPossition = isset($adsenseib30_settings['adPosition'.$numAd])?$adsenseib30_settings['adPosition'.$numAd]:'';
  $adMargin = isset($adsenseib30_settings['adMargin'.$numAd])?$adsenseib30_settings['adMargin'.$numAd]:'';
  $adAlign = isset($adsenseib30_settings['adAlign'.$numAd])?$adsenseib30_settings['adAlign'.$numAd]:'';
  $adEnabled = isset($adsenseib30_settings['adEnabled'.$numAd])?$adsenseib30_settings['adEnabled'.$numAd]:'';
  $adName = isset($adsenseib30_settings['adName'.$numAd])?$adsenseib30_settings['adName'.$numAd]:'';
  $showOn = isset($adsenseib30_settings['showOn'.$numAd])?$adsenseib30_settings['showOn'.$numAd]:'';
  $device = isset($adsenseib30_settings['adDevice'.$numAd])?$adsenseib30_settings['adDevice'.$numAd]:'';
  $category = isset($adsenseib30_settings['adCategory'.$numAd])?$adsenseib30_settings['adCategory'.$numAd]:'';

  $deviceValues = array('desktop and mobile', 'mobile', 'desktop');
  $positionValues = array('0','middle','end', '1','2', '3','4', '5','6', '7', '8', '9', '10', 'before end', 'H2 first', 'H2 second', 'H2 third', 'H3 first', 'H3 second', 'H3 third');
  $aftpar = __("Después del párrafo ",'adsensei-b30');
  $positionDisplayvalues = array(__('Antes del primer párrafo','adsensei-b30'),__('A mitad del contenido','adsensei-b30'),__('Al final del contenido','adsensei-b30'),$aftpar.'1', $aftpar.'2', $aftpar.'3',$aftpar.'4', $aftpar.'5',$aftpar.'6', $aftpar.'7', $aftpar.'8',$aftpar.'9' ,$aftpar.'10' , __('Antes del último párrafo','adsensei-b30'),__('Después del primer H2','adsensei-b30'), __('Después del segundo H2','adsensei-b30'), __('Después del tercer H2','adsensei-b30'), __('Después del primer H3','adsensei-b30'),  __('Después del segundo H3','adsensei-b30'),  __('Después del tercer H3','adsensei-b30') );
  $deviceDisplayValues = array(__('Dispositivos móviles y PCs','adsensei-b30'),__('Sólo dispositivos móviles','adsensei-b30'),__('Sólo PCs','adsensei-b30'));
  $alignValues = array('left','wrapleft','center','right','wrapright');
  $alignDisplayvalues = array(__('Izquierda','adsensei-b30'),__('Izquierda envuelto','adsensei-b30'),__('Centrado','adsensei-b30'),__('Derecha','adsensei-b30'),__('Derecha envuelto','adsensei-b30'));

  $showOnValues = array('posts','pages','both','shortcode');
  $showOnDisplayvalues = array(__('Posts','adsensei-b30'),__('Páginas','adsensei-b30'),__('Posts y páginas','adsensei-b30'), __('Usar sólo como shortcode','adsensei-b30'));

  $categoryValues = $catIds;
  $categoryDisplayvalues = $catNames;
?>

    <div class="rm_title <?php echo esc_attr('ad'.$numAd) ?>  <?php if ($adEnabled == 'false'){ echo "titleDisabled"; } ?> ">
          <span class="aTitle" id="<?php echo esc_attr('adTitle'.$numAd); ?>" style="color:#eee">
            <?php if ($adName==null){
                echo esc_html(__('Anuncio','adsensei-b30') . ' ' . $numAd);
              } else {
                echo esc_html($adName);
              }
            ?>
          </span>
          <a href="#ad" name="#ad'.$numAd.'" id="editAd" onclick="newName(<?php echo esc_attr($numAd) ?>)"><?php _e('Editar nombre','adsensei-b30'); ?></a> |
            <?php
							if (($adEnabled)=='true'){
	              echo wp_kses_post('<a href="#ad'.$numAd.'" name="#ad'.$numAd.'" id="disableAd'.$numAd.'" class="disableAd" onclick="disableAd('. $numAd .')">' . __('Desactivar','adsensei-b30') . '</a>');
	              echo wp_kses_post('<a href="#ad'.$numAd.'" name="#ad'.$numAd.'" id="enableAd'.$numAd.'" class="enableAd" style="display:none" onclick="enableAd('. $numAd .')">' . __('Activar','adsensei-b30') . '</a>');
	            } else {
	              echo wp_kses_post('<a href="#ad'.$numAd.'" name="#ad'.$numAd.'" id="disableAd'.$numAd.'" class="disableAd" style="display:none" onclick="disableAd('. $numAd .')">' . __('Desactivar','adsensei-b30') . '</a>');
	              echo wp_kses_post('<a href="#ad'.$numAd.'" name="#ad'.$numAd.'" id="enableAd'.$numAd.'" class="enableAd" onclick="enableAd('. $numAd .')">' . __('Activar','adsensei-b30') . '</a>');
	            }
						?>
          <span class="shortcode" style="text-transform:lowercase; float:right; padding-right:20px;">[anuncio_b30 id=<?php echo esc_attr($numAd)?>]</span>
    </div>

		<div class="rm_section <?php echo esc_attr('ad'.$numAd) ?> <?php if ($adEnabled == 'false'){ echo "sectionDisabled"; }?>">
		    <div class="rm_input rm_textarea">
		        <label><?php _e('Código adsense','adsensei-b30'); ?> </label>
						<small><?php _e('Inserta aquí el código adsense', 'adsensei-b30'); ?></small>
		        <textarea id="<?php echo esc_attr($html_idCode) ?>" name="<?php echo esc_attr($html_idCode) ?>" type="text" ><?php echo esc_attr($adCode)?></textarea>
		        <div class="clearfix"></div>
		    </div>
        <div class="rm_input rm_select">
          <label><?php _e('Colocar anuncio: ', 'adsensei-b30'); ?></label>
          <?php adsenseib30_loadselect($positionValues, $positionDisplayvalues, $adPossition, $html_idPosition); ?>
          <small><?php _e('Posición dónde saldrá el anuncio','adsensei-b30'); ?></small>
          <div class="clearfix"></div>
        </div>
        <div class="rm_input rm_select">
          <label><?php _e('Alineación anuncio: ', 'adsensei-b30'); ?></label>
          <?php adsenseib30_loadselect($alignValues, $alignDisplayvalues, $adAlign, $html_idAlign); ?>
          <small><a href="https://wordpress.org/plugins/adsensei-b30/screenshots/" target="_blank"><?php _e('Ver ejemplos','adsensei-b30'); ?></a></small>
          <div class="clearfix"></div>
        </div>
        <div class="rm_input rm_text">
          <label><?php _e('Margen','adsensei-b30'); ?></label>
          <input class="" style="width:40px" id="<?php echo esc_attr($html_idMargin) ?>" name="<?php echo esc_attr($html_idMargin) ?>" type="text" value="<?php echo esc_attr( $adMargin==null?12:$adMargin )?>"/>
					<?php _e('píxeles','adsensei-b30'); ?>
          <small><?php _e('El margen que quieras (de 0 a 100)','adsensei-b30'); ?></small>
          <div class="clearfix"></div>
        </div>
        <div class="rm_input rm_select">
          <label><?php _e('Mostrar anuncio en:', 'adsensei-b30'); ?></label>
          <?php adsenseib30_loadselect($showOnValues, $showOnDisplayvalues, $showOn,$html_idShowOn); ?>
          <small><?php _e('Posts / Páginas / Posts + Páginas','adsensei-b30'); ?></small>
          <div class="clearfix"></div>
        </div>
        <div class="rm_input rm_select">
          <label><?php _e('Dispositivos visualización', 'adsensei-b30'); ?></label>
          <?php adsenseib30_loadselect($deviceValues, $deviceDisplayValues, $device,$html_idDevice); ?>
          <small><?php _e('Tipo de dispositivos en los que se visualizará el anuncio','adsensei-b30'); ?></small>
          <div class="clearfix"></div>
        </div>
        <div class="rm_input rm_select">
          <label><?php _e('Categoria', 'adsensei-b30'); ?></label>
          <?php adsenseib30_loadselect($categoryValues, $categoryDisplayvalues, $category,$html_idCategory); ?>
          <small><?php _e('Categoría en la que se mostrará el anuncio','adsensei-b30'); ?></small>
          <div class="clearfix"></div>
        </div>

				<input class="" id="<?php echo esc_attr($html_idAdName) ?>" name="<?php echo esc_attr($html_idAdName) ?>" type="hidden" value="<?php echo esc_attr($adName==null?__('Anuncio','adsensei-b30').' '.$numAd:$adName)?>"/>
				<input class="" id="<?php echo esc_attr($html_idAdEnabled) ?>" name="<?php echo esc_attr($html_idAdEnabled) ?>" type="hidden" value="<?php echo esc_attr($adEnabled==null?'true':$adEnabled)?>"/>
		</div>
<?php
}

function adsenseib30_loadselect($values, $displayvalues, $adPossition, $html_idPosition){
?>
    <select class="" name="<?php echo esc_attr($html_idPosition) ?>" id="<?php echo esc_attr($html_idPosition) ?>">
      <?php $index= 0;
        foreach($values as $value) {
        if($adPossition == $value) { $selected = 'selected="selected"'; } else { $selected = ''; } ?>
        <option value="<?php echo esc_attr($value) ?>" <?php echo esc_attr($selected) ?>><?php echo esc_html($displayvalues[$index]) ?></option>
      <?php $index++; } ?>
    </select>
<?php
}
