<?php
/**
 * Plugin Name: Insight - Levinger - Digital Terms
 * Plugin URI: https://github.com/udiinsight/insight-levinger-digital-terms
 * Description: תגיות Dynamic Data ל-Bricks עבור דף התקנון הדיגיטלי לבדיקת התאמה — פרטי התור מפרמטרים בקישור ופרטי המרכז הרפואי משדות דף המרכז.
 * Version: 2.1.0
 * Author: Insight Marketing
 * Author URI: https://insight-marketing.co.il
 * Text Domain: insight-levinger-digital-terms
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const ILDT_TAGS = array(
	'ildt_patient'               => 'שם המטופל (patient)',
	'ildt_date'                  => 'תאריך הבדיקה (date)',
	'ildt_time'                  => 'שעת הבדיקה (time)',
	'ildt_doctor'                => 'שם הרופא (doctor)',
	'ildt_health_url'            => 'קישור הצהרת בריאות (health)',
	'ildt_pay_url'               => 'קישור לתשלום (pay)',
	'ildt_center_name'           => 'מרכז — שם (כותרת הדף)',
	'ildt_center_address'        => 'מרכז — כתובת',
	'ildt_center_parking'        => 'מרכז — חניה',
	'ildt_center_transportation' => 'מרכז — תחבורה ציבורית',
	'ildt_center_waze'           => 'מרכז — קישור Waze',
);

function ildt_param( $key, $max = 80 ) {
	if ( ! isset( $_GET[ $key ] ) || is_array( $_GET[ $key ] ) ) {
		return '';
	}
	return mb_substr( sanitize_text_field( wp_unslash( $_GET[ $key ] ) ), 0, $max );
}

function ildt_url_param( $key ) {
	if ( ! isset( $_GET[ $key ] ) || is_array( $_GET[ $key ] ) ) {
		return '';
	}
	return esc_url_raw( wp_unslash( $_GET[ $key ] ), array( 'https', 'http' ) );
}

function ildt_get_center( $ref ) {
	if ( '' === $ref ) {
		return array();
	}
	if ( ctype_digit( $ref ) ) {
		$id = (int) $ref;
	} else {
		$found = get_posts(
			array(
				'post_type'      => 'medical-center',
				'name'           => sanitize_title( $ref ),
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'lang'           => 'he',
			)
		);
		$id = $found ? (int) $found[0] : 0;
	}
	if ( $id && function_exists( 'pll_get_post' ) ) {
		$he = pll_get_post( $id, 'he' );
		$id = $he ? (int) $he : $id;
	}
	if ( ! $id || 'medical-center' !== get_post_type( $id ) || 'publish' !== get_post_status( $id ) ) {
		return array();
	}

	$field = function ( $name ) use ( $id ) {
		$value = function_exists( 'get_field' ) ? get_field( $name, $id ) : get_post_meta( $id, $name, true );
		return is_string( $value ) ? trim( $value ) : '';
	};

	$waze = $field( 'address_waze' );
	if ( 0 === strpos( $waze, 'waze://' ) ) {
		$waze = 'https://waze.com/ul' . substr( $waze, strlen( 'waze://' ) );
	}
	$html = function ( $name ) use ( $field ) {
		$allowed = array( 'br' => array(), 'strong' => array(), 'b' => array() );
		return trim( wp_kses( $field( $name ), $allowed ) );
	};

	return array(
		'ildt_center_name'           => esc_html( get_the_title( $id ) ),
		'ildt_center_address'        => $html( 'address' ),
		'ildt_center_parking'        => $html( 'parking' ),
		'ildt_center_transportation' => $html( 'transportation' ),
		'ildt_center_waze'           => esc_url( $waze ),
	);
}

function ildt_format_date( $raw ) {
	if ( '' === $raw ) {
		return '';
	}
	foreach ( array( 'Y-m-d', 'd/m/Y', 'd.m.Y', 'd-m-Y', 'j/n/Y', 'j.n.Y' ) as $format ) {
		$date = DateTime::createFromFormat( '!' . $format, $raw, wp_timezone() );
		if ( $date && $date->format( $format ) === $raw ) {
			return wp_date( 'l, d/m/Y', $date->getTimestamp() + 12 * HOUR_IN_SECONDS );
		}
	}
	return '';
}

function ildt_values() {
	static $values = null;
	if ( null !== $values ) {
		return $values;
	}
	$time   = ildt_param( 'time', 5 );
	$values = array_merge(
		array_fill_keys( array_keys( ILDT_TAGS ), '' ),
		array(
			'ildt_patient'    => esc_html( ildt_param( 'patient', 60 ) ),
			'ildt_date'       => esc_html( ildt_format_date( ildt_param( 'date', 20 ) ) ),
			'ildt_time'       => preg_match( '/^([01]?\d|2[0-3]):[0-5]\d$/', $time ) ? $time : '',
			'ildt_doctor'     => esc_html( ildt_param( 'doctor', 80 ) ),
			'ildt_health_url' => esc_url( ildt_url_param( 'health' ) ),
			'ildt_pay_url'    => esc_url( ildt_url_param( 'pay' ) ),
		),
		ildt_get_center( ildt_param( 'center', 100 ) )
	);
	return $values;
}

function ildt_is_terms_page() {
	if ( ! is_singular() || ! defined( 'BRICKS_DB_PAGE_CONTENT' ) ) {
		return false;
	}
	$content = get_post_meta( get_queried_object_id(), BRICKS_DB_PAGE_CONTENT, true );
	return $content && false !== strpos( maybe_serialize( $content ), '{ildt_' );
}

add_filter(
	'bricks/dynamic_tags_list',
	function ( $tags ) {
		foreach ( ILDT_TAGS as $name => $label ) {
			$tags[] = array(
				'name'  => '{' . $name . '}',
				'label' => $label,
				'group' => 'תקנון דיגיטלי',
			);
		}
		return $tags;
	}
);

add_filter(
	'bricks/dynamic_data/render_tag',
	function ( $tag, $post, $context = 'text' ) {
		if ( ! is_string( $tag ) ) {
			return $tag;
		}
		$name = trim( $tag, '{}' );
		return isset( ILDT_TAGS[ $name ] ) ? ildt_values()[ $name ] : $tag;
	},
	20,
	3
);

$ildt_render_content = function ( $content ) {
	if ( ! is_string( $content ) || false === strpos( $content, '{ildt_' ) ) {
		return $content;
	}
	return preg_replace_callback(
		'/\{(ildt_[a-z_]+)\}/',
		function ( $m ) {
			return isset( ILDT_TAGS[ $m[1] ] ) ? ildt_values()[ $m[1] ] : $m[0];
		},
		$content
	);
};
add_filter( 'bricks/dynamic_data/render_content', $ildt_render_content, 20 );
add_filter( 'bricks/frontend/render_data', $ildt_render_content, 20 );

add_action(
	'template_redirect',
	function () {
		if ( ! ildt_is_terms_page() ) {
			return;
		}
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		nocache_headers();
		header( 'X-Robots-Tag: noindex, nofollow', true );
	}
);

add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( ildt_is_terms_page() ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
		}
		return $robots;
	}
);

add_filter(
	'wpseo_robots',
	function ( $robots ) {
		return ildt_is_terms_page() ? 'noindex, nofollow' : $robots;
	}
);

add_action(
	'wp_head',
	function () {
		if ( ildt_is_terms_page() ) {
			echo '<meta name="referrer" content="no-referrer">' . "\n";
		}
	},
	1
);
