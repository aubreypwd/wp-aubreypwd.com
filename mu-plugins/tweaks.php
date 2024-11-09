<?php // phpcs:disable

// PWA for iOS.
function add_ios_pwa_meta_tags() {
	echo '<meta name="apple-mobile-web-app-capable" content="yes">';
	echo '<meta name="apple-mobile-web-app-status-bar-style" content="black">';
	echo '<link rel="manifest" href="' . site_url( '/manifest.json' ) . '">';
}
add_action('wp_head', 'add_ios_pwa_meta_tags');
add_action('admin_head', 'add_ios_pwa_meta_tags');

// Bring blog-roll back.
add_action( 'after_setup_theme', function() {
	add_filter( 'pre_option_link_manager_enabled', '__return_true' );
} );

// Disable autosave.
add_action( 'admin_enqueue_scripts', function() {
	wp_deregister_script( 'autosave' );
} );

// Disable categories and tags.
add_action( 'init', function() {
	unregister_taxonomy_for_object_type( 'category', 'post' );
	unregister_taxonomy_for_object_type( 'post_tag', 'post' );
	unregister_taxonomy( 'link_category' );

	add_action( 'admin_menu', function() {
		remove_menu_page( 'edit-tags.php?taxonomy=category' );
		remove_menu_page( 'edit-tags.php?taxonomy=post_tag' );
		remove_menu_page( 'edit-tags.php?taxonomy=link_category' );
	} );

	add_action( 'add_meta_boxes', function() {
		remove_meta_box( 'categorydiv', 'post', 'side' );
		remove_meta_box( 'tagsdiv-post_tag', 'post', 'side' );
	} );

	// Disable media categories, media tags, and link categories
	unregister_taxonomy_for_object_type( 'media_category', 'attachment' );
	unregister_taxonomy_for_object_type( 'media_post_tag', 'attachment' );
} );

// Code.
add_action( 'wp_head', function() {
	ob_start();

	?>

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/default.min.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
	<script>hljs.highlightAll();</script>

	<style>
		.hljs,
		pre,
		code {
			background-color: #f3f3f3;
		}

		pre {
			border-radius: 4px;
		}
	</style>

	<?php

	echo ob_get_clean();
} );

// Admin styles.
add_action( 'admin_head', function() {

	ob_start();

	?>

	<style>

		/* Hide Tag/Category Stuff */
		.inline-edit-col label:nth-child(2),
		.inline-edit-tags-wrap,
		.metabox-prefs label:nth-child(3),
		.term-slug-wrap,
		#posts-filter .manage-column.column-slug.desc,
		#posts-filter .slug.column-slug,
		#posts-filter #slug,
		#wp-admin-bar-wp-logo,
		a[href="edit-tags.php?taxonomy=link_category"],
		a[href="edit-tags.php?taxonomy=post_tag"],
		label[for="slugdiv-hide"],
		label[for="tagsdiv-post_tag-hide"],
		.column-categories {
			display: none !important;
		}

		/* Minimum height of post editor */
		iframe#content_ifr {
			height: 1024px;
		}

		/* Admin Bar */
		#wp-admin-bar-sqlite-db-integration,
		#wp-admin-bar-new-content {
			display: none;
		}

		/* Fix overscroll problems */
		html {
			overflow-x: hidden!important;
		}

		.wp-menu-separator:first-child {
			display: none;
		}

		#adminmenu {
			margin-top: 0;
		}

		#toplevel_page_wp-es {
			display: none;
		}

	</style>
	
	<?php

	echo ob_get_clean();
} );

// Editor styles.
add_action( 'admin_init', function() {
	
	remove_editor_styles(); // Remove theme editor styles.
	
	add_filter( 'mce_css', function() {
		return WPMU_PLUGIN_URL . '/editor-styles.css'; // Edit this file to change editor styles.
	} );
	
} );

// Post navigation.
function publish_content_nav_disabled( $nav_id ) {
	
	global $wp_query, $post;

	// Don't print empty markup on single pages if there's nowhere to navigate.
	if ( is_single() ) {
		$previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
		$next = get_adjacent_post( false, '', false );

		if ( ! $next && ! $previous )
			return;
	}

	// Don't print empty markup in archives if there's only one page.
	if ( $wp_query->max_num_pages < 2 && ( is_home() || is_archive() || is_search() ) )
		return;

	$nav_class = 'site-navigation paging-navigation';

	if ( is_single() )
		$nav_class = 'site-navigation post-navigation';

	?>
	<nav role="navigation" id="<?php echo $nav_id; ?>" class="<?php echo $nav_class; ?>">
		<h1 class="assistive-text"><?php _e( 'Post navigation', 'publish' ); ?></h1>

		<?php previous_post_link( '<div class="nav-previous">%link</div>', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'publish' ) . '</span> %title' ); ?>
		<?php next_post_link( '<div class="nav-next">%link</div>', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'publish' ) . '</span>' ); ?>

	</nav><!-- #<?php echo $nav_id; ?> -->
	<?php
}

add_action( 'admin_menu', function() {

	remove_menu_page( 'index.php' );

	// Redirect to the Posts screen if a non-admin tries to access the Dashboard
	global $pagenow;

	if ( 'index.php' === $pagenow ) {
			wp_safe_redirect( admin_url( 'edit.php' ) );
			exit;
	}
	
}, 999 );
