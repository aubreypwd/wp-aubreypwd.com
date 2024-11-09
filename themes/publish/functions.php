<?php
/**
 * Publish functions and definitions
 *
 * @package Publish
 * @since Publish 1.0
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * @since Publish 1.0
 */
if ( ! isset( $content_width ) )
	$content_width = 525; /* pixels */

if ( ! function_exists( 'publish_setup' ) ):
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 *
 * @since Publish 1.0
 */
function publish_setup() {

	/**
	 * Custom template tags for this theme.
	 */
	require( get_template_directory() . '/inc/template-tags.php' );

	/**
	 * Make theme available for translation
	 * Translations can be filed in the /languages/ directory
	 */
	load_theme_textdomain( 'publish', get_template_directory() . '/languages' );

	/**
	 * Add default posts and comments RSS feed links to head
	 */
	add_theme_support( 'automatic-feed-links' );

	/**
	 * Enable Custom Backgrounds
	 */
	add_theme_support( 'custom-background' );

	/**
	 * Enable editor style
	 */
	add_editor_style();

	/**
	 * This theme uses wp_nav_menu() in one location.
	 */
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'publish' ),
	) );

	/**
	 * Add support for the Aside Post Formats
	 */
	add_theme_support( 'post-formats', array( 'aside', 'link', 'gallery', 'status', 'quote', 'chat', 'image', 'video' ) );

	/**
	 * Add support for Infinite Scroll
	 * @since Publish 1.2
	 */
	add_theme_support( 'infinite-scroll', array(
		'footer' => 'page',
	) );
}
endif; // publish_setup
add_action( 'after_setup_theme', 'publish_setup' );

/**
 * Register widgetized area and update sidebar with default widgets
 *
 * @since Publish 1.0
 */
function publish_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Sidebar', 'publish' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
}
add_action( 'widgets_init', 'publish_widgets_init' );

/**
 * Enqueue scripts and styles
 */
function publish_scripts() {
	global $post;

	wp_enqueue_style( 'publish-style', get_stylesheet_uri() );

	wp_enqueue_script( 'small-menu', get_template_directory_uri() . '/js/small-menu.js', array( 'jquery' ), '20120206', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	if ( is_singular() && wp_attachment_is_image( $post->ID ) ) {
		wp_enqueue_script( 'keyboard-image-navigation', get_template_directory_uri() . '/js/keyboard-image-navigation.js', array( 'jquery' ), '20120202' );
	}
}
add_action( 'wp_enqueue_scripts', 'publish_scripts' );

/**
 * Echoes the theme's footer credits
 *
 * @since Publish 1.2
 */
function publish_footer_credits() {
	echo publish_get_footer_credits();
}
add_action( 'publish_credits', 'publish_footer_credits' );

/**
 * Returns the theme's footer credits
 *
 * @return string
 *
 * @since Publish 1.2
 */
function publish_get_footer_credits( $credits = '' ) {
	return sprintf(
		'%1$s %2$s',
		'<a href="http://wordpress.org/" rel="generator">Proudly powered by WordPress</a>',
		sprintf( __( 'Theme: %1$s by %2$s.', 'publish' ), 'Publish', '<a href="http://kovshenin.com/" rel="designer">Konstantin Kovshenin</a>' )
	);
}
add_filter( 'infinite_scroll_credit', 'publish_get_footer_credits' );

/**
 * Prepends the post format name to post titles on single view
 *
 * @param string $title
 * @return string
 *
 * @since Publish 1.2-wpcom
 */
function publish_post_format_title( $title, $post_id = false ) {
	if ( ! $post_id )
		return $title;

	$post = get_post( $post_id );

	// Prevent prefixes on menus and other areas that use the_title filter.
	if ( ! $post || $post->post_type != 'post' )
		return $title;

	if ( is_single() && (bool) get_post_format() )
		$title = sprintf( '<span class="entry-format">%1$s: </span>%2$s', get_post_format_string( get_post_format() ), $title );

	return $title;
}
add_filter( 'the_title', 'publish_post_format_title', 10, 2 );

/**
 * Implement the Custom Header feature
 */
require( get_template_directory() . '/inc/custom-header.php' );

/* Tweaks by @aubreypwd */

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
