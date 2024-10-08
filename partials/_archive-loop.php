<?php
/**
 * Partial: Archive Loop
 *
 * Renders a list of post type specific cards.
 *
 * @package WordPress
 * @subpackage Fictioneer
 * @since 3.0
 * @see category.php
 * @see tag.php
 * @see taxonomy-fcn_character.php
 * @see taxonomy-fcn_content_warning.php
 * @see taxonomy-fcn_fandom.php
 * @see taxonomy-fcn_genre.php
 *
 * @internal $args['taxonomy']  The current taxonomy type.
 */


// No direct access!
defined( 'ABSPATH' ) OR exit;

// Setup
$page = get_query_var( 'paged', 1 ) ?: 1; // Main query
$order = fictioneer_sanitize_query_var( $_GET['order'] ?? 0, ['desc', 'asc'], 'desc' );
$orderby = fictioneer_sanitize_query_var( $_GET['orderby'] ?? 0, fictioneer_allowed_orderby(), 'modified' );
$ago = $_GET['ago'] ?? 0;
$ago = is_numeric( $ago ) ? absint( $ago ) : sanitize_text_field( $ago );

// Prepare sort-order-filter arguments
$hook_args = array(
  'page' => $page,
  'order' => $order,
  'orderby' => $orderby,
  'ago' => $ago,
  'taxonomy' => $args['taxonomy']
);

?>

<?php do_action( 'fictioneer_archive_loop_before', $hook_args ); ?>

<?php if ( have_posts() ) : ?>

  <section class="archive__posts container-inline-size">
    <ul id="archive-list" class="scroll-margin-top card-list _no-mutation-observer">
      <?php
        while ( have_posts() ) {
          the_post();

          // Setup
          $type = get_post_type();
          $card_args = array(
            'cache' => fictioneer_caching_active( 'card_args' ) && ! fictioneer_private_caching_active(),
            'show_type' => true,
            'order' => $order,
            'orderby' => $orderby,
            'ago' => $ago
          );

          // Special conditions for chapters...
          if ( $type == 'fcn_chapter' ) {
            if (
              get_post_meta( $post->ID, 'fictioneer_chapter_no_chapter', true ) ||
              get_post_meta( $post->ID, 'fictioneer_chapter_hidden', true )
            ) {
              continue;
            }
          }

          // Echo correct card
          fictioneer_echo_card( $card_args );
        }

        // Pagination
        $pag_args = array(
          'prev_text' => fcntr( 'previous' ),
          'next_text' => fcntr( 'next' ),
          'add_fragment' => '#archive-list'
        );

        if ( $GLOBALS['wp_query']->found_posts > get_option( 'posts_per_page' ) ) {
          ?><li class="pagination"><?php echo fictioneer_paginate_links( $pag_args ); ?></li><?php
        }
      ?>
    </ul>
  </section>

<?php else : ?>

  <section class="archive__posts container-inline-size">
    <ul id="archive-list" class="scroll-margin-top card-list _no-mutation-observer">
      <li class="no-results">
        <span><?php _e( 'No matching posts found.', 'fictioneer' ); ?></span>
      </li>
    </ul>
  </section>

<?php endif; ?>

<?php do_action( 'fictioneer_archive_loop_after', $hook_args ); ?>
