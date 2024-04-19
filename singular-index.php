<?php
/**
 * Template Name: Index
 *
 * @package WordPress
 * @subpackage Fictioneer
 * @since 5.14.0
 */
?>

<?php

// Setup
$post_id = get_the_ID();

// Header
get_header();

// Query all stories
$args = array(
  'post_type' => 'fcn_story',
  'post_status' => ['publish'],
  'posts_per_page' => -1,
  'order' => 'ASC',
  'orderby' => 'name',
  'update_post_term_cache' => false, // Improve performance
  'no_found_rows' => true // Improve performance
);

$stories = new WP_Query( $args );

// Sort stories
$sorted_stories = [];

if ( $stories->have_posts() ) {
  // Loop through posts...
  foreach ( $stories->posts as $story ) {
    // Skip hidden
    if ( get_post_meta( $story->ID, 'fictioneer_story_hidden', true ) ) {
      continue;
    }

    // Relevant data
    $title = trim( fictioneer_get_safe_title( $story->ID, 'story_index' ) );
    $first_char = mb_substr( $title, 0, 1, 'UTF-8' );

    // Normalize for numbers and other non-alphabetical characters
    if ( is_numeric( $first_char ) ) {
      $first_char = '#'; // Group under '#'
    }

    // Add index if necessary
    if ( ! isset( $sorted_stories[ $first_char ] ) ) {
      $sorted_stories[ $first_char ] = [];
    }

    $sorted_stories[ $first_char ][] = array(
      'id' => $story->ID,
      'title' => $title,
      'link' => get_post_meta( $story->ID, 'fictioneer_story_redirect_link', true ) ?: get_permalink( $story->ID ),
      'date' => get_the_date( $story->ID ),
      'total_words' => absint( get_post_meta( $story->ID, 'fictioneer_story_total_word_count', true ) ?? 0 ),
      'rating' => get_post_meta( $story->ID, 'fictioneer_story_rating', true ),
      'status' => get_post_meta( $story->ID, 'fictioneer_story_status', true )
    );
  }

  // Sort by index
  ksort( $sorted_stories );
}

?>

<main id="main" class="main singular index">

  <div class="observer main-observer"></div>

  <?php do_action( 'fictioneer_main' ); ?>

  <div class="main__background polygon polygon--main background-texture"></div>

  <div class="main__wrapper">

    <?php do_action( 'fictioneer_main_wrapper' ); ?>

    <?php while ( have_posts() ) : the_post(); ?>

      <?php
        // Setup
        $title = fictioneer_get_safe_title( $post_id, 'singular' );
        $this_breadcrumb = [ $title, get_the_permalink() ];
      ?>

      <article id="singular-<?php echo $post_id; ?>" class="singular__article padding-left padding-right padding-top padding-bottom">

        <header class="singular__header">
          <h1 class="singular__title"><?php echo $title; ?></h1>
        </header>

        <section class="singular__content content-section"><?php
          the_content();

          echo '<div class="index-wrapper">';

          foreach ( $sorted_stories as $index => $stories ) {
            echo "<div class='index-group'><h2 class='index-group__header'>{$index}</h2>";

            foreach ( $stories as $story ) {
              $words = sprintf(
                __( '%s Words', 'Index item.', 'fictioneer' ),
                number_format_i18n( $story['total_words'] )
              );

              echo "<div class='index-group__item post-{$story['id']}'><a href='{$story['link']}' class='index-group__title'>{$story['title']}</a>";
              echo "<div class='index-group__meta'>{$story['status']} &bull; {$words} &bull; {$story['rating']}</div></div>";
            }

            echo '</div>';
          }

          echo '</div>';
        ?></section>

        <footer class="singular__footer"><?php do_action( 'fictioneer_singular_footer' ); ?></footer>

      </article>

    <?php endwhile; ?>

  </div>

</main>

<?php
  // Footer arguments
  $footer_args = array(
    'post_type' => 'page',
    'post_id' => $post_id,
    'breadcrumbs' => array(
      [fcntr( 'frontpage' ), get_home_url()]
    )
  );

  // Add current breadcrumb
  $footer_args['breadcrumbs'][] = $this_breadcrumb;

  // Get footer with breadcrumbs
  get_footer( null, $footer_args );
?>