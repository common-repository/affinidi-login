<?php

// ABSPATH prevent public user to directly access your .php files through URL.
defined('ABSPATH') or die('No script kiddies please!');

class Affinidi_Login_Admin_Options {

    const OPTION_NAME = 'affinidi_options';

    private $option_values;

    private $default_options;

	public function __construct( $default_settings = array() ) {
		$this->default_options = $default_settings;
		$this->option_values = array();

		// on initialisation, retrieve the options
		$this->option_values = get_option( self::OPTION_NAME, $this->default_options );
	}

	public function get_option_name() {
		return self::OPTION_NAME;
	}

	public function __get( $key ) {
		if ( isset( $this->option_values[ $key ] ) ) {
			return $this->option_values[ $key ];
		}
	}

	public function __set( $key, $value ) {
		$this->option_values[ $key ] = $value;
	}

	public function __isset( $key ) {
		return isset( $this->option_values[ $key ] );
	}

	public function get_options() {
		return $this->option_values;
	}

	public function save() {
		update_option( self::OPTION_NAME, $this->option_values );
	}
}
