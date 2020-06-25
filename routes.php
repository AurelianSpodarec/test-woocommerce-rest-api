<?php
 /*
   Plugin Name: Custom API
   Plugin URI: 
   description: Code for bespoke theme - do NOT disable
   Version: 
   Author:  
   Author URI:  
   License:  
   */
 
    function single_product_by_slug( $slug ) {
        global $product;

        $args = array (
            'post_type' => 'product',
            'name' => $slug['slug']
        );

        $post = get_posts($args);
        $product = wc_get_product( $post[0]->ID );

            $data = [
                'name' => $product->get_name(),
                'price' => $product->get_price(),
                'regular_price' => $product->get_regular_price(),
                'sale_price' => $product->get_sale_price(),
                'sale_from' => $product->get_date_on_sale_from(),
                'sale_to' => $product->get_date_on_sale_to(),

                'stock_quantity' => $product->get_stock_quantity(),
                
                'colors' => array($product->get_attribute( 'color' )),
                'images' => $product->get_image(),
                'variations' => $product->get_children(),
            ];
        
        
        if ( empty( $post ) ) {
            return null;
        }
        return $data;
    }
 
    function products_by_slug_variation( $slug ) {
        global $product;

        $args = array (
            'post_type' => 'product',
            'name' => $slug['slug']
        );

        $post = get_posts($args);
        $product = wc_get_product( $post[0]->ID );

        $attachment_ids[0] = get_post_thumbnail_id( $product->id );
        $attachment = wp_get_attachment_image_src($attachment_ids[0], false );

        if($product->has_child()) {
            $variations = $product->get_available_variations();
            $data = [
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'price' => $product->get_price(),
                'regular_price' => $product->get_regular_price(),
                'sale_price' => $product->get_sale_price(),
                'image' => $attachment,
                'stock_quantity' => $product->get_stock_quantity(),

                // 'stock_quantity' => $product->get_available_variations(),
                'stock_status' => $product->get_stock_status(),
                'backorders' => $product->get_backorders(),
                "variations" => $variations = $product->get_available_variations(),
                
                'description' => $product->get_description(),

                'weight' => $product->get_weight(),
                'length' => $product->get_length(),
                'width' => $product->get_width(),
                'height' => $product->get_height(),
                'dimensions' => $product->get_dimensions(),

                'reviews_allowed' => $product->get_reviews_allowed(),
                'rating_counts' => $product->get_rating_counts(),
                'get_average_rating' => $product->get_average_rating(),
                'get_review_count' => $product->get_review_count()

            ];
        } else {
            $data = [
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'price' => $product->get_price(),
                'regular_price' => $product->get_regular_price(),
                'sale_price' => $product->get_sale_price(),

                'stock_quantity' => $product->get_stock_quantity(),


                'stock_status' => $product->get_stock_status(),
                'backorders' => $product->get_backorders(),
                'image' => $attachment,

                'description' => $product->get_description(),
                 
                'weight' => $product->get_weight(),
                'length' => $product->get_length(),
                'width' => $product->get_width(),
                'height' => $product->get_height(),
                'dimensions' => $product->get_dimensions(),

                'reviews_allowed' => $product->get_reviews_allowed(),
                'rating_counts' => $product->get_rating_counts(),
                'get_average_rating' => $product->get_average_rating(),
                'get_review_count' => $product->get_review_count()
            ];
        }
        
        if ( empty( $post ) ) {
            return null;
        }

        return $data;
    }

    function custom_product_list( $args ) {
        global $product;

        // var_dump("@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@", $args['price']);

        $offset = 0;
        $limit = 9;
        $orderby = 'date';
        $order = 'desc';

        if ( isset( $args['page'])) {
            $custom_page = $args['page'];
        } else {
            $custom_page = 1;
        }

        if ( isset( $args['colors'])) {
            $custom_colors = explode(',', $args['colors']);
            $custom_operator = "IN";
        } else {
            $custom_colors = '';
            $custom_operator = "OUT";
        }

        if ( isset( $args['price'])) {
            $min_price = intval($args['price']['min']);
            $max_price = intval($args['price']['max']);
        } else {
            $min_price = 0;
            $max_price = 99999;
        };

        $query_args =   array(
            'page'   => $custom_page,
            'limit' => $limit,
            'paginate' => true,
            'orderby'  => $orderby,
            'order'    => $order,
            'price_between' => array($min_price, $max_price),
            'tax_query' => array( 
                array(
                    'taxonomy'      => 'pa_color',
                    'field'         => 'slug',
                    'terms'         => $custom_colors,
                    'operator'      => $custom_operator
                ),
            ),
            'return'   => 'ids'
        );
        $query = new WC_Product_Query( $query_args );



        $products = $query->get_products();

        $productResults = [
            "total"    => $products->total,
            "totalPages" => floor(($limit / $products->total) * 10),
            "offset"   => $offset,
            "page"     => $custom_page,
            "limit"    => $limit,
            "products" => [],
            "colors" =>  $custom_colors,
            "test " => $test,
        ];

        if ( sizeof($products) > 0 ) :
        foreach($products->products as $productID) :

            $attachment_ids[0] = get_post_thumbnail_id( $productID );
            $attachment        = wp_get_attachment_image_src($attachment_ids[0], false );
            $custom_product    = wc_get_product($productID);

            $product_variation = [];
            if($custom_product->has_child()) {
                $variations = $custom_product->get_available_variations();
                foreach($variations as $variation) {
                    $var = [
                        "attributes" => $variation['attributes'],
                        "image"      => $variation['image']['src'],
                    ];
                    $product_variation[] = $var;
                }
            }

            if($custom_product->has_child()) {   
                    $custom_variations = $product_variation;
                    $variationPrice = [
                        'min' => $custom_product->get_variation_price(),
                        'max' => $custom_product->get_variation_price('max')
                    ];
                    $color = "";

            } else {
                    $variationPrice = "";
                    $custom_variations = [];
                    $color = $custom_product->get_attribute('Color');
            }
             
            array_push( $productResults['products'], [
                'id'                => $custom_product->get_id(),
                'name'              => $custom_product->get_name(),
                'slug'              => $custom_product->get_slug(),
                'price'             => $custom_product->get_price(),
                'currency'          => get_woocommerce_currency_symbol(),

                'image'             => $attachment,
                'variationPrice'    => $variationPrice,

                'custom_variations' => $product_variation,
                'color' => $color,
            ]);

        endforeach;
        endif;

        return $productResults;
    }

    add_filter( 
        'woocommerce_product_data_store_cpt_get_products_query', 
        function ( $query, $query_vars ) {
            if ( ! empty( $query_vars['price_between'] ) ) {
                $query['meta_query'][] = array(
                    'key'     => '_price',
                    'value'   => $query_vars['price_between'],
                    'type'    => 'NUMERIC',
                    'compare' => 'BETWEEN',
                );
            }
            return $query;
        },
        10,
        2
    );


    function filter_options() {
        global $product;

        $response = [
            "colors" => get_terms("pa_color"),
            // "max-price" => woocommerce_price()
        ];

        return rest_ensure_response($response);
    }

    function get_args( ) {

        $response = [];

        $args = array();

        $args['page'] = array(
            'type'        => 'number',
        );
        return $args;
    }
    
    
    add_action('rest_api_init', 'register_routes');
    function register_routes() {
        register_rest_route( 'wc/v3', '/product/slug=(?P<slug>[a-zA-Z0-9-]+)', array(
            'method' => 'GET',
            'callback' => 'single_product_by_slug'
        ));
        register_rest_route( 'wc/v3', '/product/slug=(?P<slug>[a-zA-Z0-9-]+)/variations', array(
            'method' => 'GET',
            'callback' => 'products_by_slug_variation'
        ));
        register_rest_route( 'wc/v3', '/filter-options', array(
            'method' => 'GET',
            'callback' => 'filter_options'
        ));
        register_rest_route( 'wc/v3', '/custom-product-list', array(
            'method' => 'GET',
            'callback' => 'custom_product_list',
            'args' => get_args(),
        ));   
    }
    