<?php

/**
 * Chron relevant stuff
 */

// No Direct Access
if( !defined( "WPINC" ) ) {
   die;
}

class adsenseiCron {

   public function __construct() {
      add_filter( 'cron_schedules', array($this, 'add_new_intervals'), 100 );

   }

   /**
    * Add new intervals for wp cron jobs
    * @param type $schedules
    * @return type
    */
   public function add_new_intervals( $schedules ) {
      // add weekly and monthly intervals
      $schedules['weekly'] = array(
          'interval' => 604800,
          'display' => __( 'Once Weekly' )
      );

      $schedules['monthly'] = array(
          'interval' => 2635200,
          'display' => __( 'Once a month' )
      );

      return $schedules;
   }
   
   
   

   public function schedule_event() {

      if( !wp_next_scheduled( 'adsensei_weekly_event' ) ) {
         wp_schedule_event( time(), 'weekly', 'adsensei_weekly_event' );

      }
      if( !wp_next_scheduled( 'adsensei_daily_event' ) ) {
         wp_schedule_event( time(), 'daily', 'adsensei_daily_event' );

      }
    }
}
$adsenseiCron = new adsenseiCron(); 
