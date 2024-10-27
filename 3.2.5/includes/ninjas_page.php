<?php

function adsenseib30_ninjas_page() {
?>
  <div id="main_wrapper" class="items-container">
    <div class="logo" style="background-image: url('<?php echo esc_attr(plugins_url('assets/adsensei.png',__FILE__)); ?>')">
    </div>

    <?php
      $registernewsletter = get_option('adsb30-registernewsletter');
      if($registernewsletter == false){        
        isset($_GET['plugin_action']) ? $action = sanitize_text_field( $_GET['plugin_action'] ) : $action = '';
        $current_screen = get_current_screen()->base;
        $hascaps = is_network_admin() ? current_user_can('manage_network_plugins') : current_user_can('manage_options');
        if( ! in_array( $current_screen, ['ninjas-admin'])
            && $hascaps && $action != 'registroOK'
          ) {
    ?>
    <div class="card-grid-container-100">
      <form action="https://acumbamail.com/newform/subscribe/Nn8CTNcMiZIRiUKMXqxrNQXQTODE/31002/" method="post" class="">
        <div class="card-100">
          <h4>¿Quieres suscribirte a nuestra newsletter para mejorar los ingresos?</h4>
        </div>
        <div class="card-100">
      		<label for="id_u9mbzb"><b>Email</b></label>
          <input id="id_u9mbzb" name="email_1" type="email" placeholder="" required="" style="width: 100%; margin-top: 10px;"/>
      		<input type="hidden" name="char_01" value="<?php echo get_site_url(); ?>" maxlength="128" placeholder=""/>
      		<input type="text" name="a640352c" tabindex="-1" value="" style="position: absolute; left: -4900px;" aria-hidden="true" id="a640352c" autocomplete="off"/>
          <input type="email" name="b640352c" tabindex="-1" value="" style="position: absolute; left: -5000px;" aria-hidden="true" id="b640352c" autocomplete="off"/>
          <input type="checkbox" name="c640352c" tabindex="-1" style="position: absolute; left: -5100px;" aria-hidden="true" id="c640352c" autocomplete="off"/>
        	<input type="hidden" name="ok_redirect" id="id_redirect" value="<?php echo get_site_url(); ?>/wp-admin/admin.php?page=ninjas-admin&plugin_action=registroOK">
        </div>
        <div class="card-100">
        	<input type="submit" value="Suscribirme" class="" style="padding: 15px 20px;background-color: #2ebe5f;border: solid 1px #2ebe5f;border-radius: 3px;font-weight: 600;font-size: 18px;">
        </div>
      </form>
    </div>
    <?php
    }else if($action == 'registroOK' ){

      update_option('adsb30-registernewsletter', true) ;
    ?>
    <div class="card-grid-container-100">
    <div class="message">
      <h3>Para completar el alta, debes confirmar la suscripción haciendo clic en el correo electrónico que te hemos enviado. Podrás cancelar tu suscripción cuando lo desees.</h3>
    </div>
    </div>
    <?php
    }
  }
    ?>
     <a style="text-decoration:none" href="<?php echo esc_attr(admin_url('admin.php?page=migrate_content'));?>">
        <div class="card-grid-container-100" style="padding: 15px 20px;background-color: #2ebe5f;border: solid 1px #2ebe5f;border-radius: 3px;font-weight: 600;text-decoration: none;">
          <div class="card-100">
            <center><h1>Haz click aquí para migrar tus anuncios a la nueva versión?</h1></center>
          </div>
        </div>
      </a>
      
    <div class="card-grid-container">
      <div class="card">
        <img src="<?php echo esc_attr(plugins_url('assets/ninja1_.png',__FILE__)); ?>">
        <h1><?php _e('Kabuza','adsensei-b30'); ?></h1>
        <p><?php _e('Rápido y eficaz. Coloca anuncios de Adsensei, Amazon o cualquier tipo de código HTML por toda tu web','adsensei-b30'); ?></p>
        <a class="blue" href="<?php echo esc_attr(admin_url('admin.php?page=adsensei-admin'));?>">
          <?php _e('Coloca tus Anuncios','adsensei-b30'); ?>
        </a>
      </div>
      <div class="card">
        <img src="<?php echo esc_attr(plugins_url('assets/ninja2.png',__FILE__)); ?>">
        <h1><?php _e('Hinaka', 'adsensei-b30'); ?></h1>
        <p><?php _e('Coloca texto en tu página Home a la vez que mantienes tus últimas entradas/posts. Haz una página optimizada para SEO','adsensei-b30'); ?></p>
        <a class="darkRed" href="<?php echo esc_attr(admin_url('admin.php?page=home-text'));?>">
          <?php _e('Texto en Home', 'adsensei-b30'); ?>
        </a>
      </div>
      <div class="card">
        <img src="<?php echo esc_attr(plugins_url('assets/ninja3_.png',__FILE__)); ?>" alt="Blogger 3.0">
        <h1><?php _e('Kabuto','adsensei-b30'); ?></h1>
        <p><?php _e('Coloca textos en tus categorías prar poder rankearlas con mayor facilidad en los buscadores','adsensei-b30'); ?></p>
        <a class="yellow" href="<?php echo esc_attr(admin_url('admin.php?page=category-text')); ?>">
          <?php _e('Texto en Categorías','adsensei-b30'); ?>
        </a>
      </div>
    </div>
  </div>

<?php


}
