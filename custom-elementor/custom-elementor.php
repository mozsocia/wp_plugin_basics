<?php
/*
Plugin Name: custom-elementor
Plugin URI: https://werwer.com/
Description: elementor custom .
Version: 4.2.3
Author: moz
Author URI: https://autsdfsdfic.com/wordpress-plugins/
License: GPLv2 or later
Text Domain: moz
*/
namespace WPC;

// use Elementor\Plugin; ?????

class Widget_Loader{

  private static $_instance = null;

  public static function instance()
  {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }


  private function include_widgets_files(){
    require_once(__DIR__ . '/widgets/advertisement.php');
  }

  public function register_widgets($widgets_manager){

    $this->include_widgets_files();

    // \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Widgets\Advertisement());

    $widgets_manager->register( new Widgets\Advertisement() );

  }

  public function __construct(){
    add_action('elementor/widgets/register', [$this, 'register_widgets'], 99);
  }
}

// Instantiate Plugin Class
Widget_Loader::instance();
