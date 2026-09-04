<?php
if ( ! defined( 'ABSPATH' ) ) exit;
wp_redirect( get_post_type_archive_link( 'kc_gallery' ) . '#photo-' . get_the_ID() );
exit;
