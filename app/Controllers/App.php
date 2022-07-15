<?php

namespace App\Controllers;

use Sober\Controller\Controller;
use WP_Query;

class App extends Controller
{

    /**
     * App constructor.
     */
    public function __construct(){

        /**
         * Tour Map
         */
        TourMap::class;
    }

    public function siteName()
    {
        return get_bloginfo('name');
    }

    public function getHeader()
    {
        if (function_exists('get_field')) {
            return get_field('header', 'options');
        } else {
            return 'ACF DISABLE';
        }
    }

    public function getFooter()
    {
        if (function_exists('get_field')) {
            return get_field('footer', 'options');
        } else {
            return 'ACF DISABLE';
        }
    }

    public static function title()
    {
        if (is_home()) {
            if ($home = get_option('page_for_posts', true)) {
                return get_the_title($home);
            }
            return __('Latest Posts', 'nec');
        }
        if (is_archive()) {
            return get_the_archive_title();
        }
        if (is_search()) {
            return sprintf(__('Search Results for %s', 'nec'), get_search_query());
        }
        if (is_404()) {

            return __('Not Found', 'nec');
        }
        return get_the_title();
    }

    /**
     * Search Result Count Posts
     * @return int
     */
    public function searchCount()
    {
        $s = get_query_var( 's' );
        $allsearch = new WP_Query('s=' . $s . '&showposts=0');
        return $allsearch ->found_posts;
    }
}
