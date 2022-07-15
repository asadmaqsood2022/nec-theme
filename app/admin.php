<?php

namespace App;

/**
 * Theme customizer
 */
add_action('customize_register', function (\WP_Customize_Manager $wp_customize) {
    // Add postMessage support
    $wp_customize->get_setting('blogname')->transport = 'postMessage';
    $wp_customize->selective_refresh->add_partial('blogname', [
        'selector' => '.brand',
        'render_callback' => function () {
            bloginfo('name');
        }
    ]);
});

/**
 * Customizer JS
 */
add_action('customize_preview_init', function () {
    wp_enqueue_script('sage/customizer.js', asset_path('scripts/customizer.js'), ['customize-preview'], null, true);
});

/**
 * Add ID page 404 to Permalinks Page
 */
add_action( 'load-options-permalink.php', function (){

    if( isset( $_POST['page_id_404'] ) ) :
        update_option( 'page_id_404', sanitize_title_with_dashes( $_POST['page_id_404'] ) );
    endif;

    add_settings_field( 'page_id_404', __( 'ID Error Page 404' ), function () {
        $value = get_option( 'page_id_404' );
        echo '<input type="text" value="' . esc_attr( $value ) . '" name="page_id_404" id="page_id_404" class="regular-text" />';
    }, 'permalink', 'optional' );
} );
