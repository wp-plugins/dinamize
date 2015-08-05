<?php

class Dinamize_Forms {

	const post_type = 'dinamize_form';

	private static $found_items = 0;
	private static $current = null;

	private $id;
	private $name;
	private $title;
	private $properties = array();
	private $responses_count = 0;
	private $inputMsgError = array();

	public static function count() {
		return self::$found_items;
	}

	public static function get_current() {
		return self::$current;
	}

	public static function register_post_type() {
		register_post_type( self::post_type, array(
			'labels' => array(
				'name' => __('Forms', 'dinamize'),
				'singular_name' => __( 'Forms', 'dinamize' ) ),
			'rewrite' => false,
			'query_var' => false ) );
	}

	public static function find( $args = '' ) {
		$defaults = array(
			'post_status' => 'any',
			'posts_per_page' => -1,
			'offset' => 0,
			'orderby' => 'ID',
			'order' => 'ASC' );

		$args = wp_parse_args( $args, $defaults );

		$args['post_type'] = self::post_type;

		$q = new WP_Query();
		$posts = $q->query( $args );

		self::$found_items = $q->found_posts;

		$objs = array();

		foreach ( (array) $posts as $post )
			$objs[] = new self( $post );

		return $objs;
	}

	public static function get_template( $args = '' ) {
		$defaults = array( 'title' => '' );
		$args = wp_parse_args( $args, $defaults );

		$title = $args['title'];

		self::$current = $form = new self;
		$form->title = ( $title ? $title : __( 'No title', 'dinamize' ) );

		$form = apply_filters( 'dinamize_form', $form, $args );

		return $form;
	}

	public static function get_instance( $post ) {
		$post = get_post( $post );

		if ( ! $post || self::post_type != get_post_type( $post ) ) {
			return false;
		}

		self::$current = $form = new self( $post );

		return $form;
	}

	private function __construct( $post = null ) {
		$post = get_post( $post );

		if ( $post && self::post_type == get_post_type( $post ) ) {
			$this->id = $post->ID;
			$this->name = $post->post_name;
			$this->title = $post->post_title;

			$properties = $this->get_properties();

			foreach ( $properties as $key => $value ) {
				if ( metadata_exists( 'post', $post->ID, '_' . $key ) ) {
					$properties[$key] = get_post_meta( $post->ID, '_' . $key, true );
				} elseif ( metadata_exists( 'post', $post->ID, $key ) ) {
					$properties[$key] = get_post_meta( $post->ID, $key, true );
				}
			}

			$this->properties = $properties;
		}

		$this->inputMsgError = Array();
		// E-mail inválido
		$line  = '<input type="hidden" class="emailInvalid" value="';
		$line .= __('Invalid email', 'dinamize');
		$line .- '" disabled />';
		$this->inputMsgError[] = $line;
		// Preenchimento obrigatório
		$line  = '<input type="hidden" class="required" value="';
		$line .= __('Required field', 'dinamize');
		$line .- '" disabled />';
		$this->inputMsgError[] = $line;
		// Data inválida
		$line  = '<input type="hidden" class="dateInvalid" value="';
		$line .= __('Invalid date', 'dinamize');
		$line .- '" disabled />';
		$this->inputMsgError[] = $line;
		
		do_action( 'dinamize_form', $this );
	}

	public function __get( $name ) {
		if ( 'id' == $name ) {
			return $this->id;
		} elseif ( 'title' == $name ) {
			return $this->title;
		} elseif ( $prop = $this->prop( $name ) ) {
			return $prop;
		}
	}

	public function initial() {
		return empty( $this->id );
	}

	public function prop( $name ) {
		$props = $this->get_properties();
		return isset( $props[$name] ) ? $props[$name] : null;
	}

	public function get_properties() {
		$properties = (array) $this->properties;

		$properties = wp_parse_args( $properties, array(
			'form-hash' => '',
			'html' => ''
		) );

		return $properties;
	}

	public function set_properties( $properties ) {
		$defaults = $this->get_properties();

		$properties = wp_parse_args( $properties, $defaults );
		$properties = array_intersect_key( $properties, $defaults );

		$this->properties = $properties;
	}

	public function id() {
		return $this->id;
	}

	public function name() {
		return $this->name;
	}

	public function title() {
		return $this->title;
	}

	public function set_title( $title ) {
		$title = trim( $title );

		if ( '' === $title ) {
			$title = __( 'No title', 'dinamize' );
		}

		$this->title = $title;
	}

	/* Generating Form HTML */

	public function form_html( $atts = array() ) {
		$form_html = $this->prop('html');
		
		$protocol = (is_ssl()) ? "https" : "http";
		$form_html = str_replace("{{protocol}}", $protocol, $form_html);
		$form_html = str_replace("{{formInputHidden}}", implode("", $this->inputMsgError), $form_html);
		
		return $form_html;
	}

	/* Save */

	public function save() {
		$props = $this->get_properties();

		$post_content = json_encode( $props );

		if ( $this->initial() ) {
			$post_id = wp_insert_post( array(
				'post_type' => self::post_type,
				'post_status' => 'publish',
				'post_title' => $this->title,
				'post_content' => $post_content ) );
		} else {
			$post_id = wp_update_post( array(
				'ID' => (int) $this->id,
				'post_status' => 'publish',
				'post_title' => $this->title,
				'post_content' => $post_content ) );
		}

		if ( $post_id ) {
			foreach ( $props as $prop => $value ) {
				update_post_meta( $post_id, '_' . $prop, $value );
			}

			if ( $this->initial() ) {
				$this->id = $post_id;
				do_action( 'dinamize_after_create', $this );
			} else {
				do_action( 'dinamize_after_update', $this );
			}

			do_action( 'dinamize_after_save', $this );
		}

		return $post_id;
	}

	public function delete() {
		if ( $this->initial() )
			return;

		if ( wp_delete_post( $this->id, true ) ) {
			$this->id = 0;
			return true;
		}

		return false;
	}

	public function shortcode( $args = '' ) {
		$args = wp_parse_args( $args );

		$shortcode = sprintf( '[dinamize-form id="%1$d"]', $this->id );

		return apply_filters( 'dinamize_form_shortcode', $shortcode, $args, $this );
	}
}

function dinamize_form( $id ) {
	return Dinamize_Forms::get_instance( $id );
}

function dinamize_get_current_form() {
	if ( $current = Dinamize_Forms::get_current() ) {
		return $current;
	}
}

function dinamize_form_shortcode_handle( $atts, $content = null, $code = '' ) {
	if ( is_feed() ) {
		return '[dinamize-form]';
	}

	if ( 'dinamize-form' == $code ) {
		$atts = shortcode_atts( array(
			'id' => 0,
		), $atts );

		$id = (int) $atts['id'];
		$form = dinamize_form( $id );
	}
	
	if ( ! $form ) {
		return '[dinamize-form 404 "Not Found"]';
	}

	return $form->form_html( $atts );
}
