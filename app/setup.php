<?php

namespace App;

use Roots\Sage\Container;
use Roots\Sage\Assets\JsonManifest;
use Roots\Sage\Template\Blade;
use Roots\Sage\Template\BladeProvider;

/**
 * Theme assets
 */
add_action('wp_enqueue_scripts', function () {
    wp_dequeue_script('jquery');
    wp_dequeue_script('jquery-core');
    wp_dequeue_script('jquery-migrate');
    wp_enqueue_script('jquery', false, array(), false, true);
    wp_enqueue_script('jquery-core', false, array(), false, true);
    wp_enqueue_script('jquery-migrate', false, array(), false, true);

    wp_enqueue_style('font-fontawesome', 'https://use.fontawesome.com/releases/v5.0.13/css/all.css', false, null);
    wp_enqueue_style('font-ibm-serif', 'https://fonts.googleapis.com/css2?family=IBM+Plex+Serif:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap', false, null);
    wp_enqueue_style('font-ibm-sans', 'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap', false, null);

    wp_enqueue_style('sage/main.css', asset_path('styles/main.css'), false, 1.2);
    wp_enqueue_script('sage/main.js', asset_path('scripts/main.js'), ['jquery'], 1.1, true);

    if (is_single() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }

    wp_localize_script('sage/main.js', 'nec', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}, 100);

/**
 * Theme setup
 */
add_action('after_setup_theme', function () {
    /**
     * Enable features from Soil when plugin is activated
     * @link https://roots.io/plugins/soil/
     */
    add_theme_support('soil-clean-up');
    add_theme_support('soil-jquery-cdn');
    add_theme_support('soil-nav-walker');
    add_theme_support('soil-nice-search');
    add_theme_support('soil-relative-urls');

    /**
     * Enable plugins to manage the document title
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Register navigation menus
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation'        => __('Primary Navigation', 'nec'),
        'header_menu'               => __('Header Navigation', 'nec'),
        'top_header_menu'           => __('Top Header Navigation', 'nec'),
        'header_menu_mobile_help'   => __('Mobile Header Navigation Menu: Need Help?', 'nec'),
        'mega_small_menu'           => __('Mega Menu Small', 'nec'),
    ]);

    /**
     * Enable post thumbnails
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable HTML5 markup support
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', ['caption', 'comment-form', 'comment-list', 'gallery', 'search-form']);

    /**
     * Enable selective refresh for widgets in customizer
     * @link https://developer.wordpress.org/themes/advanced-topics/customizer-api/#theme-support-in-sidebars
     */
    add_theme_support('customize-selective-refresh-widgets');

    /**
     * Use main stylesheet for visual editor
     * @see resources/assets/styles/layouts/_tinymce.scss
     */
    add_editor_style(asset_path('styles/main.css'));
//    add_editor_style( asset_path ('/admin/styles/custom-editor-style.css' ));
    /**
     *
     */
    add_image_size( 'components-cards', 400, 400, true );
    add_image_size( 'thumbnail-560x320', 560, 320, true );
    add_image_size( 'thumbnail-500x400', 500, 400, true );
    add_image_size( 'thumbnail-400x540', 400, 540, true );
    add_image_size( 'thumbnail-400x260', 400, 260, true );
    add_image_size( 'thumbnail-120x120', 120, 120, true );
    add_image_size( 'thumbnail-400x300', 400, 300, true );
    add_image_size( 'thumbnail-490x360', 490, 360, true );
    add_image_size( 'thumbnail-180x240', 180, 240, true );
    add_image_size( 'thumbnail-600x600', 600, 600, true );

}, 20);

/**
 * Register sidebars
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3>',
        'after_title'   => '</h3>'
    ];
    register_sidebar([
                         'name' => __('Primary', 'nec'),
                         'id'   => 'sidebar-primary'
                     ] + $config);
    register_sidebar([
                         'name' => __('Footer', 'nec'),
                         'id'   => 'sidebar-footer'
                     ] + $config);
});

/**
 * Updates the `$post` variable on each iteration of the loop.
 * Note: updated value is only available for subsequently loaded views, such as partials
 */
add_action('the_post', function ($post) {
    sage('blade')->share('post', $post);
});

/**
 * Setup Sage options
 */
add_action('after_setup_theme', function () {
    /**
     * Add JsonManifest to Sage container
     */
    sage()->singleton('sage.assets', function () {
        return new JsonManifest(config('assets.manifest'), config('assets.uri'));
    });

    /**
     * Add Blade to Sage container
     */
    sage()->singleton('sage.blade', function (Container $app) {
        $cachePath = config('view.compiled');
        if ( ! file_exists($cachePath)) {
            wp_mkdir_p($cachePath);
        }
        (new BladeProvider($app))->register();

        return new Blade($app['view']);
    });

    /**
     * Create @asset() Blade directive
     */
    sage('blade')->compiler()->directive('asset', function ($asset) {
        return "<?= " . __NAMESPACE__ . "\\asset_path({$asset}); ?>";
    });
});

/**
 * Remove emoji
 */
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

/**
 * Remove feed links
 */
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'parent_post_rel_link', 10);
remove_action('wp_head', 'start_post_rel_link', 10);
remove_action('wp_head', 'adjacent_posts_rel_link', 10);
remove_action('wp_head', 'wp_generator');

/**
 * Theme Settings page
 */
if (function_exists('acf_add_options_page')) {
    $parent = acf_add_options_page(
        [
            'page_title' => 'Theme General Settings',
            'menu_title' => 'Theme Settings',
            'menu_slug'  => 'theme-general-settings',
            'capability' => 'edit_posts',
            'redirect'   => false
        ]
    );
}

/**
 * hide admin bar
 */
//show_admin_bar(false);

/**
 * Template Redirect
 */
add_action( 'template_redirect', function (){

    /**
     * 404 Page
     */
    if( is_404()) :

        $id_404_page = get_option( 'page_id_404' );
        wp_redirect( get_the_permalink( $id_404_page ) );
        exit();

    endif;

    /**
     * Search Page
     */
    if ( is_search() && ! empty( $_GET['s'] ) ) :

        wp_redirect( home_url( "/search/" ) . urlencode( get_query_var( 's' ) ) );
        exit();

    endif;

} );


/**
 * Search Orderby
 */
add_filter( 'pre_get_posts', function ($query) {

        if ( is_search() ) {

            $orderby = [
                'orderby'   => 'relevance-desc',
                'order'     => 'desc'
            ];
            if( get_query_var( 'orderby' ) ) :
                $orderby = explode( '-', get_query_var( 'orderby' ) );
                $orderby = [
                    'orderby'   => $orderby[0],
                    'order'     => $orderby[1]
                ];
            endif;
            $query->set('orderby', $orderby['orderby']);
            $query->set('order', $orderby['order']);
            return $query;
        }

} );

/**
 * More Link
 */
add_filter('excerpt_more', function () {

    global $post;
    return ' <a href="'. get_permalink($post->ID) . '">[...]</a>';

}, 20);


