<?php


 /*
   Plugin Name: Custom API
   Plugin URI: 
   description: Custom API routes for WooCommerce
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
        // print_r($slug);
        return $data;
    }

    function products_by_slug_variation( $slug ) {
        global $product;

        // Check if variations exist, if not, return normal single
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
                'name' => $product->get_name(),
                'price' => $product->get_price(),
                'regular_price' => $product->get_regular_price(),
                'sale_price' => $product->get_sale_price(),
                'image' => $product->get_image(),
                'stock_quantity' => $product->get_stock_quantity(),
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
        // print_r($slug);
        return $data;
    }





    function custom_product_list() {
        global $product;

        $query = new WC_Product_Query( array(
            'limit'    => 10,
            'orderby'  => 'date',
            'order'    => 'DESC',
            'return'   => 'ids',
            'paginate' => true,
        ) );

        $products = $query->get_products();


        $productResults = [
            "total"    => $products->total,
            "products" => [],
        ];


        if ( sizeof($products) > 0 ) :
        foreach($products->products as $productID) :

            $attachment_ids[0] = get_post_thumbnail_id( $productID );
            $attachment = wp_get_attachment_image_src($attachment_ids[0], false );
            $custom_product = wc_get_product($productID);


            $product_variation = [];
            if($custom_product->has_child()) {
                $variations = $custom_product->get_available_variations();
                foreach($variations as $variation) {
                    $var = [
                        "attributes" => $variation['attributes'],
                        "image" => $variation['image']['src'],
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

            } else {
                    $variationPrice = ['min' => '', 'max' => ''];
                    $custom_variations = [];
            }
             

            array_push( $productResults['products'], [
                'name' => $custom_product->get_name(),
                'price' => $custom_product->get_price(),
                'currency' => get_woocommerce_currency_symbol(),
                
                'image' => $attachment,
                'variationPrice' => $variationPrice,

                // 'color' => $color = strtolower($custom_product->get_attribute('Color')),

                'custom_variations' => $product_variation
            ]);

        endforeach;
        endif;
    
       
    

        return $productResults;
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
        register_rest_route( 'wc/v3', '/custom-product-list', array(
            'method' => 'GET',
            'callback' => 'custom_product_list'
        ));
    }

    

    // function get_min_max_woocommerce() {

    // }