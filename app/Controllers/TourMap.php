<?php

namespace App\Controllers;

use Sober\Controller\Controller;
use WP_Query;
use ZipArchive;

class TourMap extends Controller {

    /**
     * @var string
     * @VERSION
     */
    protected $VERSION = '1.0.0';

    /**
     * TourMap constructor.
     * @since 1.0.0
     * @__construct
     */
    public function __construct(){

        /**
         * Is Admin Panel
         */
        //if( is_admin() ) :

        /**
         * Scripts and Css
         */
        add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );

        /**
         * Create Section Canvas
         */
        add_action('edit_form_after_title', array(&$this, 'createSection'));

        //else:

        /**
         * Scripts and Css Footer Frontend
         */
        add_action( 'wp_enqueue_scripts', array( &$this, 'frontend_enqueue_scripts' ) );
        add_action( 'wp_footer', array( &$this, 'frontend_footer_scripts' ) );

        //endif;

        /**
         * Ajax Action
         */
        add_action( 'wp_ajax_tour_attach_single', array(&$this, 'tour_attach_single_func') );
        add_action( 'wp_ajax_nopriv_tour_attach_single', array(&$this, 'tour_attach_single_func') );

        add_action( 'wp_ajax_tour_save', array(&$this, 'tour_save_func') );
        add_action( 'wp_ajax_nopriv_tour_save', array(&$this, 'tour_save_func') );

        add_action( 'wp_ajax_tour_get_item', array(&$this, 'tour_get_item_func') );
        add_action( 'wp_ajax_nopriv_tour_get_item', array(&$this, 'tour_get_item_func') );

        add_action( 'wp_ajax_tour_upload_zip', array(&$this, 'tour_upload_zip_func') );
        add_action( 'wp_ajax_nopriv_tour_upload_zip', array(&$this, 'tour_upload_zip_func') );

    }

    /**
     * Frontend Include Script and Style
     * @since 1.0.0
     *
     * @frontend_enqueue_scripts
     */
    public function frontend_enqueue_scripts() {

        /**
         *
         */
        if( 'virtual-tour' == get_post_type() ) :

            /**
             * Srtyle
             */
            wp_enqueue_style('tour-leaflet', 'https://unpkg.com/leaflet@1/dist/leaflet.css');
            wp_enqueue_style('tour-tippy', get_template_directory_uri() . '/assets/admin/tour/css/tippy.css');

            /**
             * Leaflet
             */
            wp_enqueue_script('tour-leaflet', 'https://unpkg.com/leaflet@1/dist/leaflet.js', [], null, true);
            wp_enqueue_script('tour-rastercoords', get_template_directory_uri() . '/assets/admin/tour/js/rastercoords.js', [], null, true);

            /**
             * Tooltip
             */
            wp_enqueue_script('tour-popper', get_template_directory_uri() . '/assets/admin/tour/js/popper.js', [], null, true);
            wp_enqueue_script('tour-tippy', get_template_directory_uri() . '/assets/admin/tour/js/tippy.js', [], null, true);

            /**
             * Script
             */
            wp_enqueue_script('tour-index', get_template_directory_uri() . '/assets/admin/tour/js/index_frontend.js', array('jquery'), '1.2.0', true, null, true);

        endif;

    }

    /**
     * Frontend Insert Footer Script
     * @since 1.0.0
     *
     * @frontend_footer_scripts
     */
    public function frontend_footer_scripts() {

        /**
         *
         */
        if( 'virtual-tour' == get_post_type() ) :

            global $wp_query;
            $post_id = $wp_query->post->ID;

            /**
             * Get Json Map
             */
            $json = get_post_meta( $post_id, '_tour_map_json', true );

            ?>
            <script>
                /**
                 * Global Variables
                 */
                    <?php if( $json ) : ?>
                var tour_json_map = <?= json_encode($json) ?>;
                <?php else: ?>
                var tour_json_map = {};
                <?php endif; ?>
                <?php $term_obj_list = get_the_terms( $post_id, 'tour_cat' );
                $terms_string = join(', ', wp_list_pluck($term_obj_list, 'name'));
                ?>
                var tour_ID = <?= get_the_ID() ?>;
                var tour_url_tiles = '<?= $this->get_s3_url() . $terms_string ?>/tiles';

            </script>
        <?php

        endif;

    }

    /**
     * Admin Include Scripts And CSS
     * @since 1.0.0
     *
     * @admin_enqueue_scripts
     */
    public function admin_enqueue_scripts() {

        /**
         * CSS
         * @wp_enqueue_style
         */
        wp_enqueue_style('tour-admin', get_template_directory_uri() . '/assets/admin/tour/css/main.css');
        wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1/dist/leaflet.css');


        /**
         * JS
         * @wp_enqueue_script
         */
        wp_enqueue_script('tour-blockUI', get_template_directory_uri() . '/assets/admin/tour/js/jquery.blockUI.js', array('jquery'), '2.7', true);
        wp_enqueue_script('tour-main', get_template_directory_uri() . '/assets/admin/tour/js/main.js', array('jquery'), '1.0.0', true);
        wp_enqueue_script('leaflet','https://unpkg.com/leaflet@1/dist/leaflet.js');
        wp_enqueue_script('tour-rastercoords', get_template_directory_uri() . '/assets/admin/tour/js/rastercoords.js', array('jquery'), '1.0.0', true);
        wp_enqueue_script('tour-index', get_template_directory_uri() . '/assets/admin/tour/js/index.js', array('jquery'), '1.0.0', true);

    }

    /**
     * @param $post
     * @since 1.0.0
     */
    public function createSection($post) {
        global $pagenow;


        if( 'post.php' === $pagenow && 'virtual-tour' == get_post_type() ) :

            $map_width  = ( get_post_meta( $post->ID, '_tour_map_width', true ) ) ? get_post_meta( $post->ID, '_tour_map_width', true ) : 700;
            $map_height = ( get_post_meta( $post->ID, '_tour_map_height', true ) ) ? get_post_meta( $post->ID, '_tour_map_height', true ) : 600;
            $json       = get_post_meta( $post->ID, '_tour_map_json', true );

            ?>
            <script>

                /**
                 * Global Variables
                 */
                    <?php if( $json ) : ?>
                var tour_json_map = <?= json_encode($json) ?>;
                <?php else: ?>
                var tour_json_map = {};
                <?php endif; ?>
                <?php $term_obj_list = get_the_terms( $post->ID, 'tour_cat' );
                $terms_string = join(', ', wp_list_pluck($term_obj_list, 'name')); ?>
                var tour_ID = <?= $post->ID ?>;
                var tour_FOLBER = '<?= get_template_directory() . '/assets/admin/tour/images/' . $post->ID ?>';
                var tour_url_tiles = '<?= $this->get_s3_url() . $terms_string ?>/tiles';

            </script>
            <!-- Tour -->
            <div class="tour empty">

                <!-- Panel -->
                <div class="panel">

                    <!-- Panel > Left -->
                    <div class="panel__left">

                        <div>
                            <a href="#add-background" class="btn btn-color tourUploadBackground" id="btn_upload_map_file">
                                <span class="dashicons dashicons-insert"></span>
                                <?= __( 'Upload Map | tiles.zip', 'nec' ) ?>
                            </a>
                            <div class="tourUploadMessage"></div>
                        </div>

                        <!-- Sizes -->
                        <div class="panel__left__sizes">
                            <!-- Size Map -->
                            <div class="panel__left__inp">
                                <label for="inp_tour_map__width">
                                    <?= __( 'W:', 'nec' ) ?>
                                </label>
                                <input type="number" value="<?= $map_width ?>" class="tour_map__width" id="inp_tour_map__width">
                            </div>

                            <div class="panel__left__inp">
                                <label for="inp_tour_map__height">
                                    <?= __( 'H:', 'nec' ) ?>
                                </label>
                                <input type="number" value="<?= $map_height ?>" class="tour_map__height" id="inp_tour_map__height">
                            </div>
                            <!-- End Size Map -->
                        </div>
                        <!-- End Sizes -->

                        <!-- Btn -->
                        <div class="panel__left__sizes">

                            <a href="#red" class="panel__left__sizes__red" id="tour_add_point_red">
                                <span>+</span>
                                <?= __( 'Add', 'nec' ) ?>
                            </a>

                            <a href="#blue" class="panel__left__sizes__blue" id="tour_add_point_blue">
                                <span>+</span>
                                <?= __( 'Add', 'nec' ) ?>
                            </a>

                        </div>
                        <!-- End Btn -->

                    </div>
                    <!-- End Panel > Left -->

                    <!-- Panel > Right -->
                    <div class="panel__left column">
                        <a href="#save" class="btn btn-color tourSave" data-post="<?= get_the_ID() ?>" id="tour_button_save_map" style="margin: 0">
                            <span class="dashicons dashicons-saved"></span>
                            <?= __( 'Save Map', 'nec' ) ?>
                        </a>
                        <div class="tourSaveMessage"></div>
                    </div>
                    <!-- End Panel > Right -->

                </div>
                <!-- End Panel -->

                <!-- Container -->
                <div class="tour__container">

                    <!-- Instruction -->
                    <p><?= __( 'Please download only the zip archive named tiles. The name of the archive should be like this: tiles.zip', 'nec' ) ?></p>
                    <!-- End Instruction -->

                    <!-- Inner -->
                    <div class="tour__container__inner">

                        <!-- Popper -->
                        <div class="tour__popper" id="tour__popper">

                            <span class="close" id="tour__popper__close">x</span>

                            <input type="hidden" value="" id="tour_point_selected">
                            <input type="hidden" value="" id="tour_point_selected_real_id">

                            <!-- Title -->
                            <h3 class="tour__popper__title">
                                <?= __( 'Settings Point', 'nec' ) ?>
                            </h3>
                            <!-- End Title -->

                            <!-- Red Marker -->
                            <div class="tour__popper__red">

                                <!-- row -->
                                <div class="tour__popper__row">
                                    <label for="tour_point_red_number">
                                        <?= __( 'Set Number', 'nec' ) ?>
                                    </label>
                                    <input
                                        type="number"
                                        value="1"
                                        id="tour_point_red_number"
                                        class="form-control"
                                    >
                                </div>
                                <!-- end row -->

                                <!-- row -->
                                <div class="tour__popper__row">
                                    <label for="tour_point_red_title">
                                        <?= __( 'Title', 'nec' ) ?>
                                    </label>
                                    <input
                                        type="text"
                                        value=""
                                        id="tour_point_red_title"
                                        class="form-control"
                                        placeholder="<?= __( 'Set Title Point', 'nec' ) ?>"
                                    >
                                </div>
                                <!-- end row -->

                                <!-- row -->
                                <div class="tour__popper__row">
                                    <label for="tour_point_red_post">
                                        <?= __( 'Attach Page Detail', 'nec' ) ?>
                                    </label>
                                    <select class="tour-js-select tourElementTourDetail" name="tour_detail" style="width:100%" id="tour_point_red_post">
                                        <option value="0" selected="selected">
                                            <?= __( 'Select Tour Detail', 'nec' ) ?>
                                        </option>

                                        <?php
                                        $posts = get_posts( [
                                            'post_status'           => 'publish',
                                            'post_type'             => 'tour-list',
                                            'posts_per_page'        => -1,
                                        ] );

                                        if( $posts ) :
                                            foreach( $posts as $pst ) :
                                                $title = ( mb_strlen( $pst->post_title ) > 50 ) ? mb_substr( $pst->post_title, 0, 49 ) . '...' : $pst->post_title;
                                                ?>
                                                <option value="<?= $pst->ID ?>">
                                                    <?= $title ?>
                                                </option>
                                            <?php
                                            endforeach;
                                        endif;
                                        ?>

                                    </select>
                                </div>
                                <!-- end row -->

                            </div>
                            <!-- End Red Marker -->

                            <!-- Blue Marker -->
                            <div class="tour__popper__blue">

                                <!-- row -->
                                <div class="tour__popper__row">
                                    <label for="tour_point_blue_title">
                                        <?= __( 'Title', 'nec' ) ?>
                                    </label>
                                    <input
                                        type="text"
                                        value=""
                                        id="tour_point_blue_title"
                                        class="form-control"
                                        placeholder="<?= __( 'Set Title Point', 'nec' ) ?>"
                                    >
                                </div>
                                <!-- end row -->

                                <!-- row -->
                                <div class="tour__popper__row">
                                    <label for="tour_point_blue_icon">
                                        <?= __( 'Set Icon', 'nec' ) ?>
                                    </label>
                                    <div></div>

                                    <button type="button" class="button button-primary button-large" style="width: 100%" id="tour_point_blue_icon_button">
                                        <?= __( 'Upload Icon', 'nec' ) ?>
                                    </button>

                                </div>
                                <!-- end row -->



                            </div>
                            <!-- End Blue Marker -->

                            <div class="tour__popper__footer">
                                <button type="button" class="button btn-blue" id="tour_point_remove">
                                    <?= __( 'Remove Point', 'nec' ) ?>
                                </button>
                            </div>

                        </div>
                        <!-- End Popper -->

                        <!-- Map -->
                        <div class="tour__container__canvas" id="map">

                        </div>
                        <!-- End Map -->
                    </div>
                    <!-- End Inner -->


                </div>
                <!-- End Container -->

            </div>
            <!-- Tour -->

        <?php

        endif;

    }

    /**
     * Ajax
     * @since 1.0.0
     */
    function tour_attach_single_func() {

        $return = [];

        $search_results = new WP_Query( array(
            's'                     => $_GET['q'],
            'post_status'           => 'publish',
            'ignore_sticky_posts'   => 1,
            'post_type'             => 'tour-list',
            'posts_per_page'        => 50,
        ) );
        if( $search_results->have_posts() ) :
            while( $search_results->have_posts() ) : $search_results->the_post();
                $title = ( mb_strlen( $search_results->post->post_title ) > 50 ) ? mb_substr( $search_results->post->post_title, 0, 49 ) . '...' : $search_results->post->post_title;
                $return[] = array( $search_results->post->ID, $title );
            endwhile;
        endif;

        wp_send_json_success( ['html' => $return] );
        wp_die();

    }

    /**
     * Ajax
     * @since 1.0.0
     */
    function tour_save_func() {

        $return = [];

        $json       = wp_unslash($_POST['map']);
        $post       = absint($_POST['post']);

        update_post_meta( $post, '_tour_map_width', absint($_POST['width']) );
        update_post_meta( $post, '_tour_map_height', absint($_POST['height']) );
        update_post_meta( $post, '_tour_map_json', $json );

        /**
         * Post Meta by Tour List Single
         * @update
         */
        //$arr_json = json_decode($json, true);
        if( $json['features'] ) :
            foreach ( $json['features'] as $item ) :
                $id_post = $item['data_post'];

                if( $item['data_post'] ) :
                    update_post_meta( $id_post, '_tour_marker_id', $item['data_id_marker'] );
                    update_post_meta( $id_post, '_tour_marker_number', $item['data_number'] );
                endif;

            endforeach;
        endif;

        if ($this->s3_enabled()) {

            $html = '<div class="updated">' .  __( 'Map uploaded to S3 successfully' ) . '</div>';

//            $uploaded = $this->s3_upload($post . '.json', $json);
//            if ($uploaded) {
//                $html = '<div class="updated">' .  __( 'Map uploaded to S3 successfully' ) . '</div>';
//
//            } else {
//                $html = '<div class="error">' .  __( 'Map uploaded to S3 error' ) . '</div>';
//            }
        } else {
//            $directory = get_template_directory() . '/assets/tour/';
//
//            $file_json  = fopen($directory . $post . '.json','w');
//            $file_svg   = fopen($directory . $post . '.svg','w');
//
//            fwrite($file_json, $json);
//            fclose($file_json);
//
//            fwrite($file_svg, $svg);
//            fclose($file_svg);

            $html = '<div class="success">' .  __( 'Map saved successfully' ) . '</div>';
        }

        wp_send_json_success( ['html' => $html ] );
        wp_die();

    }

    /**
     * Get Item
     */
    public function tour_get_item_func() {

        $post       = absint($_POST['post']);
        $number     = absint($_POST['number']);
        $html       = null;

        if( $post <> 0 ) :

            $args = array(
                'post_type' => 'tour-list',
                'post__in'  => [ $post ],
            );
            $query = new WP_Query( $args );
            $html = \App\template('partials/content-single-tour-list', ['query' => $query, 'next' => $nxt]);

        endif;
        wp_send_json_success([
            'html' => $html
        ]);
        wp_die();

    }

    /**
     * ZipArchive Extract
     */
    public function tour_upload_zip_func() {

        /**
         * POST
         */
        $file_id        = absint($_POST['file_id']);
        $archive        = get_attached_file( $file_id );
        $folber         = get_template_directory() . '/assets/admin/tour/images/'; // TODO: rename $folber to $folder
        $archive_name   = $_POST['file_name'];
        $post           = absint($_POST['post']);

        if( $file_id ) :

            $zip = new ZipArchive;
            $res = $zip->open($archive);

            if ($res === TRUE) :

                if ($this->s3_enabled()) {
                    $path = $folber . $post;

                    /**
                     * Delete the directory if it was created earlier
                     */
                    if (file_exists($path)) {
                        $this->rrmdir($path);
                    }

                    /**
                     * Create directory
                     */
                    mkdir($path);

                    /**
                     * Extract files
                     */
                    $zip->extractTo($path);
                    $zip->close();

                    /**
                     * Delete archive
                     */
                    wp_delete_attachment($file_id);

                    /**
                     * Move files to S3
                     */
                    if (!$this->upload_dir_to_s3($path)) {
                        wp_send_json_success([
                            'html'      => '',
                            'message'   => __('Can\'t upload files to S3', 'nec')
                        ]);
                    }

                    /**
                     * Delete files
                     */
                    $this->rrmdir($path);

                    /**
                     * Send Json
                     */
                    wp_send_json_success([
                        'html'      => '',
                        'message'   => __('Archive unpacked successfully and files uploaded to S3', 'nec')
                    ]);
                } else {
                    /**
                     * Extract
                     */
                    $zip->extractTo($folber);
                    $zip->close();

                    /**
                     * Rename Folber
                     */
                    rename($folber . $archive_name, $folber . $post);

                    /**
                     * Delete File
                     */
                    //wp_delete_attachment( $file_id );

                    /**
                     * Send Json
                     */
                    wp_send_json_success([
                        'html'      => '',
                        'message'   => __( 'Archive unpacked successfully', 'nec' )
                    ]);
                }
            else:

                /**
                 * Send Json
                 */
                wp_send_json_error([
                    'html'      => '',
                    'message'   => __( 'An error has occurred', 'nec' )
                ]);

            endif;

        else:

            /**
             * Send Json
             */
            wp_send_json_error([
                'html'      => '',
                'message'   => __( 'No archive selected', 'nec' )
            ]);

        endif;

        wp_die();

    }

    /**
     * @param $path
     */
    private function rrmdir($path) {
        $files = array_diff(scandir($path), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$path/$file")) ? $this->rrmdir("$path/$file") : unlink("$path/$file");
        }
        rmdir($path);
    }

    /**
     * @return bool
     */
    public function s3_enabled() {
        return false;

        global $as3cf;

        return is_a($as3cf, 'Amazon_S3_And_CloudFront') && $as3cf->is_plugin_setup(true);
    }

    /**
     * @param $filename
     * @param $content
     * @return bool
     */
    public function s3_upload($filename, $content) {
        global $as3cf;

        $region = $as3cf->get_setting('region');
        $provider_client = $as3cf->get_provider_client($region);
        $bucket = $as3cf->get_setting('bucket');

        $args = array(
            'Bucket'       => $as3cf->get_setting('bucket'),
            'Key'          => 'TourMap/' . $filename,
            'Body'         => $content,
            'ContentType'  => 'application/json',
            'CacheControl' => 'max-age=31536000',
            'Expires'      => date( 'D, d M Y H:i:s O', time() + 31536000 ),
        );

        try {
            $provider_client->upload_object($args);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param $filename
     * @return false|string
     */
    public function s3_get_contents($filename) {
        global $as3cf;

        $scheme = $as3cf->get_url_scheme();
        $bucket = $as3cf->get_setting('bucket');
        $region = $as3cf->get_setting('region');
        $domain = $as3cf->get_storage_provider()->get_url_domain($bucket, $region);

        return file_get_contents($scheme . '://' . $domain . '/TourMap/' . $filename);
    }

    private function upload_dir_to_s3($path) {
        global $as3cf;

        if (!is_dir($path)) return false;

        $region = $as3cf->get_setting('region');
        $provider_client = $as3cf->get_provider_client($region);
        $bucket = $as3cf->get_setting('bucket');

        $folder = basename($path);

        $this->delete_folder_from_s3($folder, $bucket, $provider_client);

        return $this->upload_file_to_s3($path, $folder, $provider_client, $bucket);
    }

    private function upload_file_to_s3($path, $folder, $provider_client, $bucket) {
        $files = array_diff(scandir($path), array('.','..'));
        foreach ($files as $file) {
            $file_path = $path . '/' . $file;
            if (is_dir($file_path)) {
                if (!$this->upload_file_to_s3($file_path, $folder . '/' . basename($file_path), $provider_client, $bucket)) {
                    return false;
                }
            } else {
                $args = array(
                    'Bucket'       => $bucket,
                    'Key'          => 'TourMap/' . $folder . '/' . $file,
                    'SourceFile'   => $file_path,
                    'ContentType'  => mime_content_type($file_path),
                    'CacheControl' => 'max-age=31536000',
                    'Expires'      => date( 'D, d M Y H:i:s O', time() + 31536000 ),
                );

                try {
                    $provider_client->upload_object($args);
                } catch (Exception $e) {
                    return false;
                }
            }
        }

        return true;
    }

    private function delete_folder_from_s3($folder, $bucket, $provider_client) {
        if (empty($folder)) return false;

        $args = array(
            'Bucket' => $bucket,
            'Prefix' => 'TourMap/' . $folder,
        );

        try {
            $result = $provider_client->list_objects($args);

            if (!empty($result['Contents'])) {
                foreach ($result['Contents'] as $object) {
                    if (!isset($object['Key'])) continue;

                    $provider_client->delete_object(array(
                        'Bucket' => $bucket,
                        'Key'    => $object['Key']
                    ));
                }
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function get_s3_url() {
//        global $as3cf;
//
//        $scheme = $as3cf->get_url_scheme();
//        $bucket = $as3cf->get_setting('bucket');
//        $region = $as3cf->get_setting('region');
//        $domain = $as3cf->get_storage_provider()->get_url_domain($bucket, $region);

        return  wp_upload_dir()['baseurl'].'/';
    }
}
