<?php

namespace App;

use Roots\Sage\Container;

/**
 * Get the sage container.
 *
 * @param string $abstract
 * @param array  $parameters
 * @param Container $container
 * @return Container|mixed
 */
function sage($abstract = null, $parameters = [], Container $container = null)
{
    $container = $container ?: Container::getInstance();
    if (!$abstract) {
        return $container;
    }
    return $container->bound($abstract)
        ? $container->makeWith($abstract, $parameters)
        : $container->makeWith("sage.{$abstract}", $parameters);
}

/**
 * Get / set the specified configuration value.
 *
 * If an array is passed as the key, we will assume you want to set an array of values.
 *
 * @param array|string $key
 * @param mixed $default
 * @return mixed|\Roots\Sage\Config
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/c0970285/src/Illuminate/Foundation/helpers.php#L254-L265
 */
function config($key = null, $default = null)
{
    if (is_null($key)) {
        return sage('config');
    }
    if (is_array($key)) {
        return sage('config')->set($key);
    }
    return sage('config')->get($key, $default);
}

/**
 * @param string $file
 * @param array $data
 * @return string
 */
function template($file, $data = [])
{
    return sage('blade')->render($file, $data);
}

/**
 * Retrieve path to a compiled blade view
 * @param $file
 * @param array $data
 * @return string
 */
function template_path($file, $data = [])
{
    return sage('blade')->compiledPath($file, $data);
}

/**
 * @param $asset
 * @return string
 */
function asset_path($asset)
{
    return sage('assets')->getUri($asset);
}

/**
 * @param string|string[] $templates Possible template files
 * @return array
 */
function filter_templates($templates)
{
    $paths = apply_filters('sage/filter_templates/paths', [
        'views',
        'resources/views'
    ]);
    $paths_pattern = "#^(" . implode('|', $paths) . ")/#";

    return collect($templates)
        ->map(function ($template) use ($paths_pattern) {
            /** Remove .blade.php/.blade/.php from template names */
            $template = preg_replace('#\.(blade\.?)?(php)?$#', '', ltrim($template));

            /** Remove partial $paths from the beginning of template names */
            if (strpos($template, '/')) {
                $template = preg_replace($paths_pattern, '', $template);
            }

            return $template;
        })
        ->flatMap(function ($template) use ($paths) {
            return collect($paths)
                ->flatMap(function ($path) use ($template) {
                    return [
                        "{$path}/{$template}.blade.php",
                        "{$path}/{$template}.php",
                    ];
                })
                ->concat([
                    "{$template}.blade.php",
                    "{$template}.php",
                ]);
        })
        ->filter()
        ->unique()
        ->all();
}

/**
 * @param string|string[] $templates Relative path to possible template files
 * @return string Location of the template
 */
function locate_template($templates)
{
    return \locate_template(filter_templates($templates));
}

/**
 * Determine whether to show the sidebar
 * @return bool
 */
function display_sidebar()
{
    static $display;
    isset($display) || $display = apply_filters('sage/display_sidebar', false);
    return $display;
}

/**
 * @param $arr
 * @param  bool  $die
 */
function debug($arr, $die = false){
    echo '<pre>' . print_r($arr, true) . '</pre>';
    if ($die) die;
}

/**
 * @param $str
 *
 * @return string
 */
function h($str){
    return htmlspecialchars($str, ENT_QUOTES);
}

/**
 * @param  int  $maxchar
 *
 * @return string|string[]|null
 */
function get_short_title($maxchar = 55){
    $title = get_the_title();
    if( iconv_strlen($title, 'utf-8') < $maxchar )
        return $title;
    $title = iconv_substr( $title, 0, $maxchar, 'utf-8' );
    $title = preg_replace('@(.*)\s[^\s]*$@s', '\\1 ...', $title);

    return $title;
}

/**
 *
 */
function true_breadcrumbs(){

    $page_num = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

    $separator = ' / ';
    if( is_front_page() ){

        if( $page_num > 1 ) {
            echo '<a href="' . site_url() . '"></a>' . $separator . $page_num . '';
        } else {
            echo '';
        }

    } else {

        echo '<a href="' . site_url() . '"></a>' . $separator;


        if( is_single() ){

            the_category( ', ' ); echo $separator; the_title();

        } elseif ( is_page() ){

            the_title();

        } elseif ( is_category() ) {

            single_cat_title();

        } elseif( is_tag() ) {

            single_tag_title();

        } elseif ( is_day() ) {

            echo '<a href="' . get_year_link( get_the_time( 'Y' ) ) . '">' . get_the_time( 'Y' ) . '</a>' . $separator;
            echo '<a href="' . get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ) . '">' . get_the_time( 'F' ) . '</a>' . $separator;
            echo get_the_time('d');

        } elseif ( is_month() ) {

            echo '<a href="' . get_year_link( get_the_time( 'Y' ) ) . '">' . get_the_time( 'Y' ) . '</a>' . $separator;
            echo get_the_time('F');

        } elseif ( is_year() ) {

            echo get_the_time( 'Y' );

        } elseif ( is_author() ) {

            global $author;
            $userdata = get_userdata( $author );
            echo '(а) ' . $userdata->display_name;

        } elseif ( is_404() ) {
            echo '404';

        }

        if ( $page_num > 1 ) {
            echo ' (' . $page_num . ')';
        }

    }

}
