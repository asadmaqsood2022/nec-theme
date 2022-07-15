<?php


namespace App\Controllers;

use Sober\Controller\Controller;
use WP_Query;
use function App\debug;

class Archive extends Controller
{

  public function __construct(){

    add_action('wp_ajax_nopriv_archive_posts', array(&$this, 'archivePosts'));
    add_action('wp_ajax_archive_posts', array(&$this, 'archivePosts'));
  }

  /**
   * @return array|false
   */
  public function archivePosts($args = [])
  {
    $defaults = [
      'posts_per_page' => 10,
      'orderby'        => 'date',
      'order'          => 'DESC',
      'post_status'    => 'publish',
      'post_type'      => ['news', 'blog', 'event', 'research'],
      //'nopaging'       => false,
      'offset'          => 0,
    ];
    if ($args['post_type'] == 'event'):
      $dateTime_start = new \DateTime();
      $dateTime_end   = new \DateTime();

      $start_date     = $dateTime_start->modify('first day of this month')->modify('-1 days');
      $end_date       = $dateTime_end->modify('last day of this month');

      $args['meta_query'] = array(
        'relation' => 'AND',
        array(
          'key' => 'start_date',
          'value' => $start_date->format('Ymd'),
          'type' => 'DATE',
          'compare' => '>='
        ),
        array(
          'key' => 'start_date',
          'value' => $end_date->format('Ymd'),
          'type' => 'DATE',
          'compare' => '<='
        )

      );
    endif;
    $args = wp_parse_args( $args, $defaults );
    /**
     * Ajax
     *
     * @array
     */
    if( wp_doing_ajax() ) :

      $args['paged'] = absint($_POST['paged']);

      $html       = null;
      $categories   = sanitize_text_field($_POST['category']);
      $categories   = explode(',', $categories);
      $categories   = array_diff($categories, array(''));

      /**
       * Post Type
       */
      $post_type = sanitize_text_field($_POST['post_type']);

      /**
       * Default Args Ajax
       *
       * @array
       */
      $args_ajax = [
        'posts_per_page' => absint($_POST['posts_per_page']),
      ];

      /**
       * Month | acf field
       *
       *
       * @array
       */
      if ($post_type == 'event'):
        $date_type = sanitize_text_field($_POST['type_date']);

        if( $date_type == 'date' ) :

          $dateTime = new \DateTime(sanitize_text_field($_POST['date']));
          $start_date     = $dateTime_start->modify('first day of this month')->modify('-1 days');
          $end_date       = $dateTime_end->modify('last day of this month');

        elseif ( $date_type == 'month' ) :

          $dateTime_start = new \DateTime(sanitize_text_field($_POST['date']));
          $dateTime_end   = new \DateTime(sanitize_text_field($_POST['date']));

          $start_date     = $dateTime_start->modify('first day of this month')->modify('-1 days');
          $end_date       = $dateTime_end->modify('last day of this month');

        else:

          $dateTime_start = new \DateTime();
          $dateTime_end   = new \DateTime();

          $start_date     = $dateTime_start->modify('first day of this month')->modify('-1 days');
          $end_date       = $dateTime_end->modify('last day of this month');

        endif;

        $args_ajax['meta_query'] = array(
          'relation' => 'AND',
          array(
            'key' => 'start_date',
            'value' => $start_date->format('Ymd'),
            'type' => 'DATE',
            'compare' => '>='
          ),
          array(
            'key' => 'start_date',
            'value' => $end_date->format('Ymd'),
            'type' => 'DATE',
            'compare' => '<='
          )

        );
      endif;



      /**
       * News | taxonomy cat
       * @array
       */
      $args_ajax['post_type'][] = $post_type;

      if( $categories ) :
        $cats = [];
        foreach ( $categories as $category ) :
          $cats[] = [
            'taxonomy' => $post_type . '_cat',
            'field'    => 'term_id',
            'terms'    => $category,
            'operator' => 'AND',
          ];
        endforeach;

        $args_ajax['tax_query'][] = [
          'taxonomy' => $post_type . '_cat',
          'field'    => 'term_id',
          'terms'    => explode(',', $_POST['category']),
        ];

      endif;

      /**
       * Parse Args
       * @array
       */
      $args = wp_parse_args( $args_ajax, $args );

      /**
       * Query
       * @array
       */
      global $wp_query;
      $temp = $wp_query;
//            $wp_query= null;
      $wp_query = new WP_Query($args);

      $html = \App\template('components/repeater/_archive_posts_content', ['records' => $wp_query]);

//            $wp_query = null;
      $wp_query = $temp;
      wp_reset_query();

      /**
       *
       */
      wp_send_json_success( [ 'category' => $category, 'html' => $html ] );

      wp_die();

    endif;

    /**
     * Standart
     * @array
     */
    $wp_query = new WP_Query($args);

    if ($wp_query->have_posts()) {
      return $wp_query;
    } else {
      return false;
    }

  }

}
