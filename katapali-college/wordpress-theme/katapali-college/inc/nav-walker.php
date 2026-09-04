<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* Renders wp_nav_menu with the exact markup our stylesheet expects:
   <li class="has-sub"><a>Label <i class="chevron"></i></a><ul class="submenu">...</ul></li> */
class KC_Nav_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<ul class="submenu">';
	}
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul>';
	}
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$has_children = in_array( 'menu-item-has-children', $item->classes, true );
		$classes = $has_children ? 'has-sub' : '';
		if ( in_array( 'current-menu-item', $item->classes, true ) || in_array( 'current-menu-parent', $item->classes, true ) ) {
			$classes .= ' active';
		}
		$output .= '<li class="' . esc_attr( trim( $classes ) ) . '">';
		$output .= '<a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title );
		if ( $has_children ) {
			$output .= ' <i class="fa-solid fa-chevron-down" style="font-size:.6em;"></i>';
		}
		$output .= '</a>';
	}
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}
