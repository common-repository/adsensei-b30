<?php

class TextInHome {

  private function __construct() {}

  public static function outputText($content) {
    if(in_the_loop()){
      if((is_home() || is_front_page()) && !is_paged()) {
        $home_text_settings = get_option('home_text_settings');
        $homeText = $home_text_settings['homeText'];
        echo $homeText;
      }
    }
  }
}

?>
