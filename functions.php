<?php

function display_recomended_products_block() {

    global $product;
    $cat_taxonomy  = 'product_cat'; // Taxonomy for product category
    $product_id = $product->get_id();
    //print_r($product->get_id());

    $cats = wp_get_post_terms($product_id, $cat_taxonomy ,array('fields'=>'ids'));
    $product_brand = $product->get_attribute('pa_brand');

    $main_cat = get_term_by( 'name', 'Whisky', 'product_cat' )->term_id;

    $product_cats = array();
    // Loop though terms ids (product categories)
    foreach( $cats as $single_cat ) {
        
        if ($single_cat == $main_cat )  continue;

        $children = get_terms( $cat_taxonomy, array(
            'parent'    => $single_cat,
            'hide_empty' => false,
            'fields'=>'ids'
        ) );

        $is_in_array = array_intersect($children, $cats);

        if($children && !empty($is_in_array)) continue;

        $product_cats = get_ancestors( $single_cat, $cat_taxonomy );
        array_unshift($product_cats,$single_cat);
    
    }
    //print_r($product_cats);

    echo "<div class='recomended_products_wrap clearfix'><h3 class='center--text heading'>". __('If you like this whisky, you will also like these', 'recomendedproductsforwc') . "</h3>";
    
    $recomended_products_count = 0;
    $recomended_products_id = array();
    $number_of_recomended_items = 12;
    $orderby = 'menu_order';
    $order_direction = 'DESC';
            
    $same_brand_products = get_posts( array(
        'post_type' => 'product',
        'numberposts' => $number_of_recomended_items,
        'post_status' => 'publish',
        'fields' => 'ids',
        'exclude' => array($product_id),
        'meta_query' => array(
            array(
                'key' => '_stock',
                'value' => '1',
                'compare' => '>=',
                'type' => 'NUMERIC'
            )
        ),
        'tax_query' => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'pa_brand',
                'field' => 'name',
                'terms' => $product_brand, /*brand slug*/
                'operator' => 'IN',
            ),
            array(
                'taxonomy' => 'pa_size',
                'field' => 'name',
                'terms' => array('5','10','20'), /*sizes to NOT include*/
                'operator' => 'NOT IN',
            )
        ),
        'orderby'        => $orderby,
        'order'          => $order_direction,
    ));

    foreach ($same_brand_products as $same_brand_product) {
        $recomended_products_id[] = $same_brand_product;
        $recomended_products_count++;
    }

    if ($recomended_products_count < $number_of_recomended_items){
        foreach($product_cats as $product_cat){
            
            $all_products_ids = get_posts( array(
                'post_type' => 'product',
                'numberposts' => $number_of_recomended_items,
                'post_status' => 'publish',
                'fields' => 'ids',
                'exclude' => array($product_id),
                'meta_query' => array(
                    array(
                        'key' => '_stock',
                        'value' => '1',
                        'compare' => '>=',
                        'type' => 'NUMERIC'
                    )
                ),
                'tax_query' => array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'id',
                        'terms' => $product_cat, // category name
                        'operator' => 'IN',
                    ),
                    array(
                        'taxonomy' => 'pa_size',
                        'field' => 'name',
                        'terms' => array('5','10','20'), // sizes to NOT include
                        'operator' => 'NOT IN',
                    )
                ),
                'orderby'        => $orderby,
                'order'          => $order_direction,
            ));
                
            if (!empty($all_products_ids) ){
                foreach ($all_products_ids as $single_product) {
                    $recomended_products_id[] = $single_product;
                    $recomended_products_count++;
                    if ($recomended_products_count == $number_of_recomended_items) break;
                }
            }
            if ($recomended_products_count == $number_of_recomended_items) break;
        }
    }
    ?>

    <div class='owl-carousel owl-theme'>
    <?php
    foreach($recomended_products_id as $recomended_product){
        $recomended_product = wc_get_product ($recomended_product);
    ?>
        <div <?php wc_product_class('center--text product-item', $recomended_product); ?>>
            <?php
            
            $attachment_ids = $recomended_product->get_gallery_image_ids();
            
            if ($attachment_ids && $attachment_ids != ''){
                foreach( $attachment_ids as $attachment_id ) 
                {		
                    $product_image = wp_get_attachment_image($attachment_id, 'thumbnail');  
                }
            } else {
                $product_image = '<img src="../../wp-content/uploads/2021/09/no-image-150x150.png" class="attachment-thumbnail size-thumbnail" alt="" loading="lazy"  width="150" height="150">';	
            }

            echo '<a class="grid__item--cover" href="' . get_permalink($recomended_product->get_id()) . '">' . $product_image . '</a>';
            
            $recomended_product_url = get_permalink( $recomended_product->get_id());
            $recomended_product_title = get_the_title( $recomended_product->get_id());
            $recomended_product_title = mb_strimwidth($recomended_product_title, 0, 44, "...");

            echo '<p class="bottle__info">';

            // attr
            $attr_size_values = get_the_terms($recomended_product->get_id(), 'pa_size');
            $attr_strength_values = get_the_terms($recomended_product->get_id(), 'pa_strength');
            //var_dump($attr_size_values);
            
            if ($attr_size_values){
                foreach($attr_size_values as $attr_size_value) {
                    echo '' . $attr_size_value->name . 'cl';
                }
            }
        
            if ($attr_size_values && $attr_strength_values) echo " &#47; ";
            // strength
            if ($attr_strength_values){
                foreach ($attr_strength_values as $attr_strength_value) {
                    echo '' . $attr_strength_value->name . '&#37;';
                }
            }
        
            echo "</p>";

           ?>

            <h3><a href="<?php echo $recomended_product_url; ?>"><?php echo $recomended_product_title; ?></a></h3>

            <?php
            // meta
            $price = $recomended_product->get_regular_price();
            $sale = $recomended_product->get_price();
            $recomended_product_id = $recomended_product->get_id();
            //echo do_shortcode("[product_reviews id='$recomended_product_id']");
/*
// The product average rating (or how many stars this product has)
$average_rating = $product->get_average_rating();

// The product stars average rating html formatted.
$average_rating_html = wc_get_rating_html(4);

// Display stars average rating html
echo $average_rating_html;
*/
            // price
            echo '<div class="price">';
                if ( !empty($sale) ) {
                    echo "<strong>&#163;", $sale, "</strong>";
                } else {
                    echo "<strong>&#163;", $price, "</strong>";
                }
            echo '</div>';
            
            ?>
        </div>
    

    <?php
    }
    ?>

    </div>



    <?php
    echo '</div>';
    //var_dump($recomended_products_id);
    ?>


<?php
}
add_action('recomended_products_block', 'display_recomended_products_block', 10 );

/**
 * WooCommerce Product Reviews Shortcode
 */
 
add_shortcode( 'product_reviews', 'woocommerce_product_reviews_shortcode' );
 
function woocommerce_product_reviews_shortcode( $atts ) {


   if ( empty( $atts ) ) return '';
 
   if ( ! isset( $atts['id'] ) ) return '';
  
   print_r ($atts);
   $recomended_product = wc_get_product ($atts['id']);

    $rating_count = $recomended_product->get_rating_count();
    $review_count = $recomended_product->get_review_count();
    $average      = $recomended_product->get_average_rating();


   print_r ($rating_count);
   print_r ("<br/><br/>");
   print_r ($review_count);
   print_r ("<br/><br/>");
   print_r ($average);
   print_r ("<br/><br/>");
   echo '<div class="star-rating"><span style="width:'.( ( 5 / 5 ) * 100 ) . '%"><strong itemprop="ratingValue" class="rating">'.$average.'</strong> '.__( 'out of 5', 'woocommerce' ).'</span></div>';
       
   $comments = get_comments( 'post_id=' . $atts['id'] );
    
   if ( ! $comments ) return '';

   $html = '';
    
   $html .= '<div class="woocommerce-tabs"><div id="reviews"><ol class="commentlist">';
    
   foreach ( $comments as $comment ) {   
      $rating = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );
      $html .= '<li class="review">';
      $html .= get_avatar( $comment, '60' );
      $html .= '<div class="comment-text">';
      if ( $rating ) $html .= wc_get_rating_html( $rating );
      $html .= '<p class="meta"><strong class="woocommerce-review__author">';
      $html .= get_comment_author( $comment );
      $html .= '</strong></p>';
      $html .= '<div class="description">';
      $html .= $comment->comment_content;
      $html .= '</div></div>';
      $html .= '</li>';
   }
    
   $html .= '</ol></div></div>';
    
   return $html;
}