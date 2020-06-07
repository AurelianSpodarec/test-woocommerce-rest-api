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

                'stock_quantity' => $product->get_available_variations(),


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



    // , $fr_products_per_page
    function custom_product_list( $args ) {
        global $product;


        $offset = 0;
        $limit = 9;
        
        if ( isset( $args['page'])) {
            $custom_page = $args['page'];
        };
       
        if ( isset( $args['colors'])) {
            $custom_colors =explode(',', $args['colors']);
        }
       
        // $custom_page = "papa";
        // $custom_colors = "tes";
        
        // var_dump($args['page']);

        // Search products between X and Y price


        $query = new WC_Product_Query( array(
            'page'   => $custom_page,
            'limit' => $limit,
            'paginate' => true,

            'orderby'  => 'date',
            'order'    => 'DESC',
            'return'   => 'ids',
            'orderby' => 'price',
            'paginate' => true,
            // 'tax_query' => array( 
            //     array(
            //         'taxonomy'      => 'pa_color',
            //         'field'         => 'slug',
            //         'terms'         => $custom_colors,//$custom_colors,
            //         'operator'      => 'IN'
            //     ),
            // ),

            // 'meta_query'     => array( 
            //     array(
            //         'key' => '_regular_price',
            //         'value' => array(50, 100),
            //         'compare' => 'BETWEEN',
            //         'type' => 'NUMERIC'
            //     )
            
            // ),
      
           
            
        ) );
 
        // $args = array (
        //     'value' => array( 20, 30 ),  
        // );
        // $min_max_price = wc_get_min_max_price_meta_query(  $args );

        $products = $query->get_products();

        $productResults = [
            "total"    => $products->total,
            "totalPages" => floor(($limit / $products->total) * 10),
            "offset"   => $offset,
            "page"     => $custom_page,
            "limit"    => $limit,
            "products" => [],
            // "pageee" => $page,
            "colors" =>  $custom_colors,
            "test " => $test,
            // "args" => $args['custom_page']
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
        // var_dump($args, "sd");
        return $args;
    }
    
    add_action('rest_api_init', 'register_routes');
    function register_routes() {

        // $str = '/custom-product-list?(?:page=(?P<page>[\d]+))?';
        // parse_str($str, $output);

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
    // preg_match('/custom_page=(?P<custom_page>\d*)&custom_colors=(?P<custom_colors>[^&]*)/', $input_line, $output_array);


// filter[limit]=25&filter[offset]="+offset;



//     add_filter( 'woocommerce_product_data_store_cpt_get_products_query', 'custom_change_products_response', 10, 2 );
// function custom_change_products_response($query, $query_vars) {
//     // if ( ! empty( $query_vars['customvar'] ) ) {
// 	// 	$query['meta_query'][] = array(
// 	// 		'key' => 'customvar',
// 	// 		'value' => esc_attr( $query_vars['customvar'] ),
// 	// 	);
//     // }
    
//     $let = ["sdfsfdsfsf"];

//     return $let;
// } 
  

// // // https://github.com/woocommerce/woocommerce/wiki/wc_get_products-and-WC_Product_Query
// // add_filter( 'wp_rest_filter_add_filter_param', 'handle_custom_query_var', 10, 2 );
 

// // function handle_custom_query_var( $query, $query_vars ) {
   

// //     return "sdsd";
// //  }

// //  add_filter( 'woocommerce_product_data_store_cpt_get_products_query', 'handle_custom_query_var', 10, 2 );

















// function custom_change_product_response($response, $object, $request) {

//     $variations = $response->data['variations'];
//     $variations_res = [];
//     $variations_array = [];

//     if (!empty($variations) && is_array($variations)) {
//         foreach ($variations as $variation) {
//             $variation_id = $variation;
//             $variation = new WC_Product_Variation($variation_id);

//             $variations_res['id'] = $variation_id;
//             $variations_res['on_sale'] = $variation->is_on_sale();
//             $variations_res['regular_price'] = (float)$variation->get_regular_price();
//             $variations_res['sale_price'] = (float)$variation->get_sale_price();
//             $variations_res['sku'] = $variation->get_sku();
//             $variations_res['quantity'] = $variation->get_stock_quantity();
//             $variations_res['image'] = wp_get_attachment_image_src(get_post_thumbnail_id( $variation_id ), false );;

//             if ($variations_res['quantity'] == null) {
//                 $variations_res['quantity'] = '';
//             }
            
//             $variations_res['stock'] = $variation->get_stock_quantity();

//             $attributes = array();
//             // variation attributes
//             foreach ( $variation->get_variation_attributes() as $attribute_name => $attribute ) {
//                 // taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`
//                 $attributes[] = array(
//                     'name'   => wc_attribute_label( str_replace( 'attribute_', '', $attribute_name ), $variation ),
//                     'slug'   => str_replace( 'attribute_', '', wc_attribute_taxonomy_slug( $attribute_name ) ),
//                     'attribute_pa_color' => $attribute,
//                 );
//             }

//             $variations_res['attributes'] = $attributes;
//             $variations_array[] = $variations_res;
//         }
//     }
//     $response->data['product_variations'] = $variations_array;

//     return $response;
// }
// add_filter('woocommerce_rest_prepare_product_object', 'custom_change_product_response', 20, 3);
// add_filter('woocommerce_rest_prepare_product_variation_object', 'custom_change_product_response', 20, 3);


