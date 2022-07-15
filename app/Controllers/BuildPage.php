<?php


namespace App\Controllers;

use Sober\Controller\Controller;
use WP_Query;
use function App\debug;

class BuildPage extends Controller
{

    public function __construct(){
        add_action('wp_ajax_nopriv_newsEvents', array(&$this, 'newsEvents'));
        add_action('wp_ajax_newsEvents', array(&$this, 'newsEvents'));

        add_action('wp_ajax_nopriv_taxonomyFilter', array(&$this, 'taxonomyFilter'));
        add_action('wp_ajax_taxonomyFilter', array(&$this, 'taxonomyFilter'));
    }

    /**
     * Build
     * @return $this
     */
    public function build()
    {
        return $this;
    }

    /**
     * @return array|false|string
     */
    public function fields()
    {
        if (function_exists('get_fields')) {
            return get_fields();
        } else {
            return 'ACF DISABLE';
        }
    }

    /**
     * @return array|false
     */
    public function newsEvents($args = [])
    {

        $defaults = [
            'posts_per_page' => 3,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post_status'    => 'publish',
            'post_type'      => ['news', 'event'],
        ];

        $args = wp_parse_args( $args, $defaults );

        /**
         * Ajax
         *
         *
         * @array
         */
        if( wp_doing_ajax() ) :

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
            if( sanitize_text_field($_POST['month']) && !empty(sanitize_text_field($_POST['month'])) ) :

                /**
                 *
                 */
                if( sanitize_text_field($_POST['month_custom']) && !empty(sanitize_text_field($_POST['month_custom'])) ) :

                    $month = getdate(strtotime(date(sanitize_text_field($_POST['month_custom']))));

                else:

                    $month = getdate(date(sanitize_text_field($_POST['month'])));

                endif;

                /**
                 *
                 *
                 */
                //$args_ajax['date_query']['year']        = $month[0];        //Year
                /*$args_ajax['date_query'] = [
                    [
                        'monthnum' => $month['mon']
                    ]
                ]; */   //Month

                $dateTime_start = new \DateTime(sanitize_text_field($_POST['month']));
                $dateTime_end   = new \DateTime(sanitize_text_field($_POST['month']));

                $start_date     = $dateTime_start->modify('first day of this month')->modify('-1 days')->format('Y-m-d');
                $end_date       = $dateTime_end->modify('last day of this month')->modify('+1 days')->format('Y-m-d');

                $args_ajax['date_query'] = [
                    'after'     =>  $start_date,
                    'before'    =>  $end_date,
                ];

//                ob_start();
//                debug($args_ajax);
//                $message = ob_get_contents();
//                ob_end_clean();
//                wp_send_json_success( [ 'html' =>  $message] );
//                wp_die();


                /**
                 * Html Panel Month
                 */
                $month_panel = \App\template('components/repeater/_recfilter_month', [
                    'month'         => sanitize_text_field($_POST['month']),
                    'month_prev'    => date('Y-m-d', strtotime('-1 month', strtotime(sanitize_text_field($_POST['month'])))),
                    'month_next'    => date('Y-m-d', strtotime('+1 month', strtotime(sanitize_text_field($_POST['month']))))
                ]);

            endif;

            /**
             * News | taxonomy cat
             * @array
             */
            if( absint($_POST['news']) || absint($_POST['news']) <> 0 ) :

                $args_ajax['post_type'][] = 'news';
                $args_ajax['tax_query'] = [
                    [
                        'taxonomy' => 'news_cat',
                        'field'    => 'term_id',
                        'terms'    => absint($_POST['news']),
                    ]
                ];

            endif;

            /**
             * Event | taxonomy cat
             * @array
             */
            if( absint($_POST['event']) || absint($_POST['event']) <> 0 ) :

                $args_ajax['post_type'][] = 'event';
                $args_ajax['tax_query'] = [
                    [
                        'taxonomy' => 'event_cat',
                        'field'    => 'term_id',
                        'terms'    => absint($_POST['event']),
                    ]
                ];

            endif;

            /**
             * Place | acf field
             * @array
             */
            if( sanitize_text_field($_POST['place']) && !empty(sanitize_text_field($_POST['place'])) ) :

                $args_ajax['meta_query'] = [
                    'relation'		=> 'AND',
                    [
                        'key'       => 'place',
                        'value'     => sanitize_text_field($_POST['place']),
                        'compare'   => '=',
                    ]
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
            $query = new WP_Query($args);
            $posts = $query->get_posts();
            $items = '<div class="grid-sizer"></div>';

            if ($posts) :

//                wp_send_json_success( ['items' => $posts] );
//                wp_die();
                foreach( $posts as $post ) :
                    $items .= '<div class="recfilter_section__list-item item">' . \App\template('components/repeater/_news_events_card', ['item' => $post]) . '</div>';
                endforeach;

                wp_send_json_success( ['html' => $items, 'month' => $month_panel] );

            else:

                $items = '<div class="recfilter_section__records-empty">' . __('No records were found for your search. Try changing the request.') . '</div>';

                wp_send_json_error( ['html' => $items, 'month' => $month_panel] );

            endif;

            wp_die();

        endif;

        /**
         * Standart
         * @array
         */
        $query = new WP_Query($args);

        if ($query->have_posts()) {
            return $query->posts;
        } else {
            return false;
        }
    }

    public function getPrograms($args = [])
    {

        $defaults = [
            'taxonomy'      => 'programs_cat',
            'hide_empty'    => false,
        ];

        $args = wp_parse_args( $args, $defaults );

        $terms = get_terms( $args );

        return $terms;
    }

    /**
     * Get Categories
     * @param array $args
     * @return mixed
     */
    public function getCategories($args = [])
    {

        $defaults = [
            'taxonomy'     => 'category',
            'type'         => 'post',
            'child_of'     => 0,
            'parent'       => '',
            'orderby'      => 'name',
            'order'        => 'ASC',
            'hide_empty'   => 1,
            'hierarchical' => 1,
            'exclude'      => '',
            'include'      => '',
            'number'       => 0,
            'pad_counts'   => false,
            'post_status'  => 'publish',
        ];

        $args = wp_parse_args( $args, $defaults );

        $categories = get_categories( $args );

        return $categories;
    }


    /**
     * @return array|false
     */
    public function taxonomyFilter($args = []) {

        /**
         * Ajax
         *         *
         */
        if( wp_doing_ajax() ) :

            /**
             * Type
             */
            $type = sanitize_text_field($_POST['type']);

            $defaults = [
                'posts_per_page' => 100,
                'orderby'        => 'title',
                'order'          => 'ASC',
                'post_status'    => 'publish',
                'post_type'      => ['programs'],
            ];

            $args = wp_parse_args( $args, $defaults );

            /**
             * Search Input
             */
            if( 'search' == $type ) :

                $html = '<div class="empty">' . __( 'Not Found' ) . '</div>';
                /**
                 * Get Ids Category
                 */
                $terms = get_terms( [
                    'taxonomy'      => array( 'programs_cat' ),
                    'hide_empty'    => false,
                    'search'        => sanitize_text_field($_POST['search']),
                ] );

                $cat = [];
                if( $terms ) :

                    $html = '<ul>';

                    /**
                     * Category
                     */
                    foreach ( $terms as $term ) :
                        $html .= \App\template('components/ajax/_item_taxonomy_filter_search', ['item' => $term, 'type' => 'category']);
                        array_push($cat, $term->term_id);
                    endforeach;

                    /**
                     * Post
                     */
                    $args['tax_query'] = [
                        [
                            'taxonomy' => 'programs_cat',
                            'field'    => 'term_id',
                            'terms'    => $cat,
                        ]
                    ];
                    $query = new WP_Query($args);
                    $posts = $query->get_posts();

                    if( $posts ) :

                        foreach ( $posts as $item ) :
                            $html .= \App\template('components/ajax/_item_taxonomy_filter_search', ['item' => $item, 'type' => 'post']);
                        endforeach;

                    endif;

                    $html .= '</ul>';

                else:


                    $args['s'] = sanitize_text_field($_POST['search']);
                    $query = new WP_Query($args);
                    $posts = $query->get_posts();

                    if( $posts ) :

                        $html = '<ul>';

                        foreach ( $posts as $item ) :
                            $html .= \App\template('components/ajax/_item_taxonomy_filter_search', ['item' => $item, 'type' => 'post']);
                        endforeach;

                        $html .= '</ul>';

                    endif;

                endif;

                /**
                 * Send Json
                 */
                wp_send_json_success( [ 'html' => $html ] );
                wp_die();

            endif;

            /**
             * Filter By Select
             */
            if( 'post' == $type ) :

                /**
                 * Category
                 * Schol
                 */
                if( sanitize_text_field($_POST['category']) && !empty(sanitize_text_field($_POST['category'])) ) :

                    $args['tax_query'] =
                        [ 'relation' => 'OR',
                            [
                                'taxonomy' => 'programs_cat',
                                'field'    => 'term_id',
                                'terms'    => explode(',', $_POST['category']),
                            ]
                        ];

                endif;
                /**
                 * Category
                 * level
                 */
                if( sanitize_text_field($_POST['level']) && !empty(sanitize_text_field($_POST['level'])) ) :

                    $args['tax_query'][] = [
                        'taxonomy' => 'programs_level',
                        'field'    => 'term_id',
                        'terms'    => explode(',', $_POST['level']),
                    ];

                endif;

                /**
                 * Place
                 */
                if( sanitize_text_field($_POST['place']) && !empty(sanitize_text_field($_POST['place'])) ) :

                    $places     = sanitize_text_field($_POST['place']);
                    $args['tax_query'][] = [
                        'taxonomy' => 'program_location',
                        'field'    => 'term_id',
                        'terms'    => $places,
                    ];

                endif;

                /**
                 * Query
                 * @array
                 */
                $query = new WP_Query($args);
                $posts = $query->get_posts();

                $html = '<div class="empty">' . __( 'Not Found' ) . '</div>';
                if( $posts ) :

                    $html = '<h3 class="taxfilter_section__result-title">' . __( 'Your Results' ) . '</h3>';

                    $html .= '<ul class="resultTaxonomyAjaxList grid_section__list grid_section__list-cat column-3">';

                    $html .= '<li class="header">';
                    $html .= '<b>' . __( 'Program Name' ) . '</b>';
                    $html .= '<b>' . __( 'Program Level' ) . '</b>';
                    $html .= '<b>' . __( 'School' ) . '</b>';
                    $html .= '<b>' . __( 'Campus' ) . '</b>';
                    $html .= '</li>';

                    foreach ( $posts as $item ) :
                        $html .= \App\template('components/ajax/_item_taxonomy_filter_select', ['item' => $item]);
                    endforeach;

                    $html .= '</ul>';

                endif;

                wp_send_json_success( [ 'html' => $html ] );
                wp_die();

            endif;



        endif;

    }





}
