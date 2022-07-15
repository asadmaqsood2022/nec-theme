<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class Helpers extends Controller
{

    /**
     * Helpers constructor.
     */
    public function __construct(){

        add_action('wp_ajax_nopriv_courses', array(&$this, 'catalogCourse'));
        add_action('wp_ajax_courses', array(&$this, 'catalogCourse'));

    }
    /**
     * Breadcrumbs
     */
    public function breadcrumbs($args = []) {

        /**
         * Default args
         */
        $defaults = [
            'separator'     => ' » ',
            'class'         => 'breadcrumbs'
        ];
        $args = wp_parse_args( $args, $defaults );

        /**
         * Get Current Page number
         */
        $page_num = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

        /**
         * List Html
         */
        $list = '<ul class="' . $args['class'] . '">';


        if( is_front_page() ){

            /**
             * is Home Page
             */
            if( $page_num > 1 ) :
                //$list .= '<li>' . '<a href="' . site_url() . '">' . __( 'Home' ) . '</a></li>';
            endif;

        } else {

            /**
             * is not Home Page
             */
            //$list .= '<li>' . '<a href="' . site_url() . '">' . __( 'Home' ) . '</a></li>';

            /**
             * Singl Page
             */
            if( is_single() ){

                $list .= '<li>';
                $list .= '<a href="' . home_url( 'news-events' ) . '">' . __( 'News and Events' ) . '</a>';
                $list .= '</li>';

                $title = ucfirst(get_post_type());
                if( 'research' == get_post_type() ) $title = __( 'Research and Reports', 'nec' );
                if( 'event' == get_post_type() ) $title = __( 'Events', 'nec' );

                $list .= '<li>';
                $list .= '<a href="' . home_url( get_post_type() ) . '">' . $title . '</a>';
                $list .= '</li>';

                $list .= '<li>';
                $list .= '<span>' . get_the_title() . '</span>';
                $list .= '</li>';

//                the_category( ', ' );
//                echo $args['separator'];
//                the_title();

            } elseif ( is_page() ){

                /**
                 * Global post
                 */
                global $post;

                /**
                 * is parent post
                 */
                if ( $post->post_parent ) :

                    $parent_id  = $post->post_parent;

                    while ( $parent_id ) :

                        $page = get_page( $parent_id );
                        $list .= '<li>' . '<a href="' . get_permalink( $page->ID ) . '">' . get_the_title( $page->ID ) . '</a></li>';
                        $parent_id = $page->post_parent;

                    endwhile;

                endif;

                /**
                 * Get Children
                 */
                $args_child = [
                    'post_parent'   => $post->post_parent, // Current post's ID
                    'numberposts'   => -1,
                    'post_type'     => 'page',
                ];
                $children = get_children( $args_child );


                if( $children && count($children) > 1) :

                    $list .= '<li>';
                    $list .= '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';
                    $list .= '<ul>';

                    foreach ($children as $child) :

                        if( $child->ID <> get_the_ID() ) :
                            $list .= '<li><a href="' . get_permalink( $child->ID ) . '">' . $child->post_title . '</a></li>';
                        endif;

                    endforeach;

                    $list .= '</ul>';
                    $list .= '</li>';

                else:

                    $list .= '<li><span>' . get_the_title() . '</span></li>';

                endif;


            } elseif ( is_category() ) {

                $list .= '<li>' . '<a href="' . get_category_link() . '">' . single_cat_title() . '</a></li>';

            } elseif ( get_post_type() == 'news' ) {

                $list .= '<li>' . '<a href="' . home_url( 'news-events' ) . '">' . __( 'News and Events', 'nec' ) . '</a></li>';
                $list .= '<li>' . '<a href="' . home_url( 'news' ) . '">' . __( 'News', 'nec' ) . '</a>';
                $list .= '<ul>';
                $list .= '<li><a href="' . home_url( 'blog' ) . '">' . __( 'Blog', 'nec' ) . '</a></li>';
                $list .= '<li><a href="' . home_url( 'event' ) . '">' . __( 'Events', 'nec' ) . '</a></li>';
                $list .= '<li><a href="' . home_url( 'research' ) . '">' . __( 'Research and Reports', 'nec' ) . '</a></li>';
                $list .= '</ul>';
                $list .= '</li>';

            } elseif ( get_post_type() == 'blog' ) {

                $list .= '<li>' . '<a href="' . home_url( 'news-events' ) . '">' . __( 'News and Events', 'nec' ) . '</a></li>';
                $list .= '<li>' . '<a href="' . home_url( 'blog' ) . '">' . __( 'Blog', 'nec' ) . '</a>';
                $list .= '<ul>';
                $list .= '<li><a href="' . home_url( 'news' ) . '">' . __( 'News', 'nec' ) . '</a></li>';
                $list .= '<li><a href="' . home_url( 'event' ) . '">' . __( 'Events', 'nec' ) . '</a></li>';
                $list .= '<li><a href="' . home_url( 'research' ) . '">' . __( 'Research and Reports', 'nec' ) . '</a></li>';
                $list .= '</ul>';
                $list .= '</li>';

            } elseif ( get_post_type() == 'event' ) {

                $list .= '<li>' . '<a href="' . home_url( 'news-events' ) . '">' . __( 'News and Events', 'nec' ) . '</a></li>';
                $list .= '<li>' . '<a href="' . home_url( 'event' ) . '">' . __( 'Events', 'nec' ) . '</a>';
                $list .= '<ul>';
                $list .= '<li><a href="' . home_url( 'blog' ) . '">' . __( 'Blog', 'nec' ) . '</a></li>';
                $list .= '<li><a href="' . home_url( 'news' ) . '">' . __( 'News', 'nec' ) . '</a></li>';
                $list .= '<li><a href="' . home_url( 'research' ) . '">' . __( 'Research and Reports', 'nec' ) . '</a></li>';
                $list .= '</ul>';
                $list .= '</li>';

            } elseif ( get_post_type() == 'research' ) {

                $list .= '<li>' . '<a href="' . home_url( 'news-events' ) . '">' . __( 'News and Events', 'nec' ) . '</a>';
                $list .= '<li>' . '<a href="' . home_url( 'research' ) . '">' . __( 'Research and Reports', 'nec' ) . '</a>';
                $list .= '<ul>';
                $list .= '<li><a href="' . home_url( 'blog' ) . '">' . __( 'Blog', 'nec' ) . '</a></li>';
                $list .= '<li><a href="' . home_url( 'news' ) . '">' . __( 'News and Events', 'nec' ) . '</a></li>';
                $list .= '<li><a href="' . home_url( 'event' ) . '">' . __( 'Events', 'nec' ) . '</a></li>';
                $list .= '</ul>';
                $list .= '</li>';

            } elseif( is_tag() ) {

                //single_tag_title();

            } elseif ( is_day() ) {


            } elseif ( is_month() ) {



            } elseif ( is_year() ) {



            } elseif ( is_author() ) {



            } elseif ( is_404() ) {

//                echo 'Error 404';

            }

            if ( $page_num > 1 ) :
//                echo ' (' . $page_num . ' page)';
            endif;

        }

        $list .= '</ul>';

        echo $list;

    }

    /**     * Catalog Programs Parse JSON
     */
  public function catalogIDFromType($catalog){

    $curl = curl_init();
    curl_setopt_array($curl, [
      CURLOPT_URL             => 'https://nec.acalogadmin.com/widget-api/catalogs/',
      CURLOPT_RETURNTRANSFER  => true,
      CURLOPT_ENCODING        => "",
      CURLOPT_MAXREDIRS       => 10,
      CURLOPT_TIMEOUT         => 0,
      CURLOPT_FOLLOWLOCATION  => true,
      CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST   => "GET",
    ] );

    $response = curl_exec($curl);
    curl_close($curl);

    if( $response ) :
      $catalogs = json_decode($response);
        $catalog_ids =[];
      foreach($catalogs->{'catalog-list'} as $cat){
        if ($cat->{'catalog-type'}->name == $catalog){
          array_push($catalog_ids,$cat->id);
        }
      }

      return $catalog_ids;

    endif;

  }

  public function programFromCode($catalog, $programCode){

    $curl = curl_init();
    curl_setopt_array($curl, [
      CURLOPT_URL             => 'https://nec.acalogadmin.com/widget-api/catalog/'.$catalog.'/programs/?page-size=100&page=1&code='.$programCode,
      CURLOPT_RETURNTRANSFER  => true,
      CURLOPT_ENCODING        => "",
      CURLOPT_MAXREDIRS       => 10,
      CURLOPT_TIMEOUT         => 0,
      CURLOPT_FOLLOWLOCATION  => true,
      CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST   => "GET",
    ] );

    $response = curl_exec($curl);
    curl_close($curl);

    if( $response ) :
      $programs = json_decode($response);
      foreach($programs->{'program-list'} as $program){
          return [$program->id, $program->{'legacy-id'}];
      }
      return false;

    endif;

  }

    /**
     * Catalog Programs Parse JSON
     */
    public function catalogPrograms($catalog = 0, $program){
        if(!$program) {
            return false;
        }
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL             => 'https://nec.acalogadmin.com/widget-api/catalog/' . $catalog . '/program/' . $program . '/',
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_ENCODING        => "",
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_TIMEOUT         => 0,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST   => "GET",
        ] );

        $response = curl_exec($curl);
        curl_close($curl);

        if( $response ) :

            return json_decode($response);

        endif;

    }

    /**
     * Catalog Course Parse JSON
     */
    public function catalogCourse($catalog = 0, $id = 0){

        if( wp_doing_ajax() ) :
            $catalog    = absint($_POST['catalog_id']);
            $id         = absint($_POST['course_id']);
        endif;

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL             => 'https://nec.acalogadmin.com/widget-api/catalog/' . $catalog . '/course/' . $id . '/',
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_ENCODING        => "",
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_TIMEOUT         => 0,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST   => "GET",
        ] );

        $response = curl_exec($curl);
        curl_close($curl);

        /**
         * Ajax
         */
        if( wp_doing_ajax() ) :

            wp_send_json_success([ 'html' => json_decode($response)->body ]);
            wp_die();

        endif;

        if( $response ) :

            return json_decode($response);

        endif;

    }

    /**
     * @param array $flatNav
     * @param int $parentId
     * @return array
     */
    public static function buildTreeMegamenu( $flatNav = [], $parentId = 0 )
    {
        $branch = [];

        //print_r($flatNav);

        foreach ($flatNav as $navItem) {
            if($navItem->menu_item_parent == $parentId) {
                $children = self::buildTreeMegamenu($flatNav, $navItem->ID);
                if($children) {
                    $navItem->children = $children;
                }

                $branch[$navItem->menu_order] = $navItem;
                unset($navItem);
            }
        }

        return $branch;
    }

    /**
     * @param array $arg
     * @return mixed
     */
    public static function megaMenu( $args = [] )
    {

        $html = null;
        $defaults = [
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        $args = wp_parse_args( $args, $defaults );

        $menu         = $args['menu'];
        $locations    = get_nav_menu_locations();
        if( $locations && isset( $locations[ $menu ] ) ) :
            $menu_items = wp_get_nav_menu_items( $locations[ $menu ] );
            $megamenu = self::buildTreeMegamenu($menu_items);

            if( $megamenu ) :

               foreach ( $megamenu as $item ) :
                  // print_r($item);
                    if( $item->children ) :
                        //print_r($item);
                        $html .= \App\template('partials/megamenu-row', ['item' => $item]);
                    endif;

               endforeach;

            endif;

        endif;

        return $html;
    }

}
