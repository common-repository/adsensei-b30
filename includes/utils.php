<?php

class adsenseib30_utils {

    private function __construct() {}

    public static function register_settings() {
      register_setting('adsenseib30_settings_group', 'adsenseib30_settings');
      register_setting('category_text_settings_group', 'category_text_settings');
      register_setting('home_text_settings_group', 'home_text_settings');
    }

  }
