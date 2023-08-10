<?php

add_filter( 'wpsf_register_settings_slideshow_settings_general', 'slideshow_general_setting_fields' );

/**
 * Tabless example.
 *
 * @param array $slideshow_settings Settings.
 */
function slideshow_general_setting_fields( $slideshow_settings ) {
	// General Settings section.
	$slideshow_settings[] = array(
		'section_id'          => 'general',
		'section_title'       => __( 'SlideShow Settings', '' ),
		'section_description' => __( 'Slideshow general settings goes here.', '' ),
		'section_order'       => 5,
		'fields'              => array(
			array(
				'id'        => 'slideshow-images',
				'title'     => __( 'Images', '' ),
				'desc'      => __( 'Slideshow images goes here', '' ),
				'type'      => 'group',
				'subfields' => array(
					// accepts most types of fields.
					array(
						'id'          => 'image',
						'title'       => 'Slideshow Image',
						'desc'        => 'This is a description.',
						'placeholder' => 'This is a placeholder.',
						'type'        => 'file',
					),
				),
			),
		),
	);

	return $slideshow_settings;
}


