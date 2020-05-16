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


            'images' => [$product->get_image()],
            'colors' => array($product->get_attribute( 'color' )),
            'imagess' => $product->get_gallery_image_ids(),
            'variations' => $product->get_children(),
            'test' => $product->get_attributes(),
            'test2' => $product->get_default_attributes(),
            'test3' => $product->get_attribute( 'attributeid' ), //get specific attribute value
  
        ];
        
        if ( empty( $post ) ) {
            return null;
        }
        // print_r($slug);
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

        $data = [
            'name' => $product->get_name(),
            'price' => $product->get_price(),
            'regular_price' => $product->get_regular_price(),
 

            "variations" => $variations = $product->get_available_variations()
  
        ];
        
        if ( empty( $post ) ) {
            return null;
        }
        // print_r($slug);
        return $data;
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
    }

    

    // function add_custom_users_api(){
    //     register_rest_route( 'mmw/v1', '/users/market=(?P<market>[a-zA-Z0-9-]+)/lat=(?P<lat>[a-z0-9 .\-]+)/long=(?P<long>[a-z0-9 .\-]+)', array(
    //         'methods' => 'GET',
    //         'callback' => 'get_custom_users_data',
    //     ));
    // }