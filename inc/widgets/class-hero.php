<?php

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class Primer_Hero extends Primer_Base_Widget {

	/**
	 * Widget constructor
	 */
	public function __construct() {

		$widget_options = array(
			'classname'                   => 'primer-widgets primer-widget-hero widget_text',
			'description'                 => __( 'A specialy crafted widget to be used in the hero section of Primer related themes.', 'primer' ),
			'customize_selective_refresh' => true,
		);

		parent::__construct(
			'primer_hero',
			__( 'Homepage Hero Widget', 'primer' ),
			$widget_options
		);

	}

	/**
	 * Widget form fields
	 *
	 * @param array $instance The widget options
	 *
	 * @return string|void
	 */
	public function form( $instance ) {

		parent::form( $instance );

		$fields = $this->get_fields( $instance );

		echo '<div class="primer-widget primer-widget-hero">';

		echo '<div class="title">';

		// Title field
		$this->render_form_input( array_shift( $fields ) );

		echo '</div>';

		echo '<div class="form">';

		foreach ( $fields as $key => $field ) {

			$method = $field['form_callback'];

			if ( is_callable( array( $this, $method ) ) ) {

				$this->$method( $field );

			}

		}

		echo '</div>'; // End form

		echo '</div>'; // End primer-widget-contact

	}

	/**
	 * Front-end display
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		$fields = $this->get_fields( $instance );

		if ( $this->is_widget_empty( $fields ) ) {

			return;

		}

		$this->before_widget( $args, $fields );

		echo '<div class="textwidget">';

		foreach ( $fields as $key => $field ) {

			if ( empty( $field['value'] ) || ! $field['show_front_end'] ) {

				continue;

			}

			$escape_callback = $field['escaper'];

			if ( 'button_text' === $key ) {

				if ( ! isset( $fields['button_link'] ) || empty( $fields['button_link']['value'] ) ) {

					continue;

				}

				$link_escaper = $fields['button_link']['escaper'];

				/**
				 * Filter that let's someone change css class of the link generated by the widget
				 *
				 * @filter primer_widget_hero_link_class
				 *
				 * @since NEXT
				 */
				$class = apply_filters( 'primer_widget_hero_link_class', [ 'button' ] );

				printf(
					'<p><a href="%1$s" class="%2$s">%3$s</a></p>',
					$link_escaper( $fields['button_link']['value'] ),
					esc_attr( implode( ' ', $class ) ),
					$escape_callback( $field['value'] )
				);

				continue;

			}

			echo $escape_callback( $field['value'] );// xss ok

		}

		$this->after_widget( $args, $fields );

		echo '</div>'; // End div.textwidget

	}

	/**
	 * Initialize fields for use on front-end of forms
	 *
	 * @param array $instance
	 * @param array $fields
	 * @param bool  $ordered
	 *
	 * @return array
	 */
	protected function get_fields( array $instance, array $fields = [], $ordered = false ) {

		$fields = [
			'title'   => [
				'label'       => __( 'Title:', 'primer' ),
				'description' => __( 'The title of widget. Leave empty for no title.', 'primer' ),
				'value'       => ! empty( $instance['title'] ) ? $instance['title'] : '',
				'sortable'    => false,
			],
			'message' => [
				'label'         => __( 'Message:', 'primer' ),
				'type'          => 'textarea',
				'sanitizer'     => function( $value ) {
					return current_user_can( 'unfiltered_html' ) ? $value : wp_kses_post( stripslashes( $value ) );
				},
				'escaper'       => function( $value ) use ( $instance ) {
					return wpautop( apply_filters( 'widget_text', $value, $instance, $this ) );
				},
				'form_callback' => 'render_form_textarea',
				'sortable'      => false,
			],
			'button_text' => [
				'label'          => __( 'Button Text:', 'primer' ),
				'type'           => 'text',
				'sortable'       => false,
			],
			'button_link'   => [
				'label'          => __( 'Button Link URL:', 'primer' ),
				'placeholder'    => __( 'Paste URL or type to search', 'primer' ),
				'type'           => 'text',
				'class'          => 'widefat link',
				'sanitizer'      => 'esc_url_raw',
				'escaper'        => 'esc_url',
				'sortable'       => false,
				'show_front_end' => false,
			],
		];

		/**
		 * Register custom fields for the hero widgets
		 *
		 * @since NEXT
		 *
		 * @var array $fields
		 * @var object $instance
		 */
		$fields = apply_filters( 'primer_widget_hero_custom_fields', $fields, $instance );
		$fields = parent::get_fields( $instance, $fields, $ordered );

		/**
		 * Filter the hero widget fields
		 *
		 * @since NEXT
		 *
		 * @var array $fields
		 * @var object $instance
		 */
		return (array) apply_filters( 'primer_widget_hero_fields', $fields, $instance );

	}

	/**
	 * Print footer script and styles
	 */
	public function enqueue_scripts() {

		$suffix = SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( 'jquery-ui-autocomplete' );

		wp_enqueue_script( 'primer-widgets-hero-admin', get_template_directory_uri() . "/assets/js/widgets/hero-admin{$suffix}.js", [ 'jquery', 'jquery-ui-autocomplete' ], PRIMER_VERSION, true );

		// We need the internal linking token
		wp_localize_script(
			'primer-widgets-hero-admin',
			'primer_widgets_hero_admin',
			[
				'_ajax_linking_nonce' => wp_create_nonce( 'internal-linking' ),
			]
		);

	}

	/**
	 * Print customizer script
	 */
	public function print_customizer_scripts() {

		$this->enqueue_scripts();

		wp_print_scripts( 'primer-widgets-hero-admin' );

	}

}
