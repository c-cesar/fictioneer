<?php
/**
 * Partial: Latest Updates List
 *
 * Renders the (buffered) HTML for the [fictioneer_latest_updates type="list"]
 * shortcode. Looks for the 'fictioneer_chapters_added' meta property and only
 * updates when the items in the chapters list increase. Admittedly, this can
 * be exploited by removing and re-adding chapters.
 *
 * @package WordPress
 * @subpackage Fictioneer
 * @since 5.23.0
 *
 * @internal $args['type']                Type argument passed from shortcode. Default 'default'.
 * @internal $args['single']              Whether to show only one chapter item. Default false.
 * @internal $args['count']               Number of posts provided by the shortcode.
 * @internal $args['author']              Author provided by the shortcode.
 * @internal $args['order']               Order of posts. Default 'DESC'.
 * @internal $args['post_ids']            Array of post IDs. Default empty.
 * @internal $args['author_ids']          Array of author IDs. Default empty.
 * @internal $args['excluded_authors']    Array of author IDs to exclude. Default empty.
 * @internal $args['excluded_cats']       Array of category IDs to exclude. Default empty.
 * @internal $args['excluded_tags']       Array of tag IDs to exclude. Default empty.
 * @internal $args['ignore_protected']    Whether to ignore protected posts. Default false.
 * @internal $args['taxonomies']          Array of taxonomy arrays. Default empty.
 * @internal $args['relation']            Relationship between taxonomies.
 * @internal $args['source']              Whether to show author and story.
 * @internal $args['vertical']            Whether to show the vertical variant.
 * @internal $args['seamless']            Whether to render the image seamless. Default false (Customizer).
 * @internal $args['aspect_ratio']        Aspect ratio for the image. Only with vertical.
 * @internal $args['lightbox']            Whether the image is opened in the lightbox. Default true.
 * @internal $args['thumbnail']           Whether the image is rendered. Default true (Customizer).
 * @internal $args['words']               Whether to show the word count of chapter items. Default true.
 * @internal $args['date']                Whether to show the date of chapter items. Default true.
 * @internal $args['terms']               Either inline, pills, none, or false. Default inline.
 * @internal $args['max_terms']           Maximum number of shown taxonomies. Default 10.
 * @internal $args['date_format']         String to override the date format. Default empty.
 * @internal $args['nested_date_format']  String to override the date format of nested items. Default empty.
 * @internal $args['footer']              Whether to show the footer. Default true.
 * @internal $args['footer_author']       Whether to show the chapter author. Default true.
 * @internal $args['footer_words']        Whether to show the chapter word count. Default true.
 * @internal $args['footer_date']         Whether to show the chapter date. Default true.
 * @internal $args['footer_rating']       Whether to show the story/chapter age rating. Default true.
 * @internal $args['classes']             String of additional CSS classes. Default empty.
 */


// No direct access!
defined( 'ABSPATH' ) OR exit;

// Setup
$render_count = 0;
$show_terms = ! in_array( $args['terms'], ['none', 'false'] );
$content_list_style = get_theme_mod( 'content_list_style', 'default' );

// Prepare query
$query_args = array(
  'fictioneer_query_name' => 'latest_updates_list',
  'post_type' => 'fcn_story',
  'post_status' => 'publish',
  'post__in' => $args['post_ids'], // May be empty!
  'order' => $args['order'],
  'orderby' => 'meta_value',
  'meta_key' => 'fictioneer_chapters_added',
  'posts_per_page' => $args['count'] + 4, // Account for non-eligible posts!
  'update_post_term_cache' => $show_terms, // Improve performance
  'no_found_rows' => true // Improve performance
);

// Use extended meta query?
if ( get_option( 'fictioneer_disable_extended_story_list_meta_queries' ) ) {
  // Extended syntax necessary due to 'fictioneer_chapters_added'
  $query_args['meta_query'] = array(
    array(
      'key' => 'fictioneer_story_hidden',
      'value' => '0'
    )
  );
} else {
  $query_args['meta_query'] = array(
    'relation' => 'OR',
    array(
      'key' => 'fictioneer_story_hidden',
      'value' => '0'
    ),
    array(
      'key' => 'fictioneer_story_hidden',
      'compare' => 'NOT EXISTS'
    )
  );
}

// Author?
if ( ! empty( $args['author'] ) ) {
  $query_args['author_name'] = $args['author'];
}

// Author IDs?
if ( ! empty( $args['author_ids'] ) ) {
  $query_args['author__in'] = $args['author_ids'];
}

// Taxonomies?
if ( ! empty( $args['taxonomies'] ) ) {
  $query_args['tax_query'] = fictioneer_get_shortcode_tax_query( $args );
}

// Excluded tags?
if ( ! empty( $args['excluded_tags'] ) ) {
  $query_args['tag__not_in'] = $args['excluded_tags'];
}

// Excluded categories?
if ( ! empty( $args['excluded_cats'] ) ) {
  $query_args['category__not_in'] = $args['excluded_cats'];
}

// Excluded authors?
if ( ! empty( $args['excluded_authors'] ) ) {
  $query_args['author__not_in'] = $args['excluded_authors'];
}

// Ignore protected?
if ( $args['ignore_protected'] ) {
  add_filter( 'posts_where', 'fictioneer_exclude_protected_posts' );
}

// Apply filters
$query_args = apply_filters( 'fictioneer_filter_shortcode_latest_updates_query_args', $query_args, $args );

// Query stories
$entries = fictioneer_shortcode_query( $query_args );

// Remove temporary filters
remove_filter( 'posts_where', 'fictioneer_exclude_protected_posts' );

?>

<section class="latest-updates _list <?php echo $args['classes']; ?>">
  <?php if ( $entries->have_posts() ) : ?>

    <ul class="post-list _latest-updates">
      <?php while ( $entries->have_posts() ) : $entries->the_post(); ?>

        <?php
          // Setup
          $post_id = $post->ID;
          $story = fictioneer_get_story_data( $post_id, false ); // Does not refresh comment count!
          $permalink = get_post_meta( $post_id, 'fictioneer_story_redirect_link', true ) ?: get_permalink( $post_id );
          $tags = get_option( 'fictioneer_show_tags_on_story_cards' ) ? get_the_tags( $post ) : false;
          $chapter_list = [];

          // Skip if no chapters
          if ( $story['chapter_count'] < 1 ) {
            continue;
          }

          // Count actually rendered items to account for buffer
          if ( ++$render_count > $args['count'] ) {
            break;
          }

          // Thumbnail
          $thumbnail = null;

          if ( $args['thumbnail'] ) {
            $thumbnail = fictioneer_render_thumbnail(
              array(
                'post_id' => $post_id,
                'title' => $story['title'],
                'permalink' => $permalink,
                'size' => 'snippet',
                'classes' => 'post-list-item__image',
                'lightbox' => $args['lightbox'],
                'aspect_ratio' => $args['aspect_ratio'] ?? '2/2.5'
              ),
              false
            );
          }

          // Extra classes
          $classes = [];

          if ( ! empty( $post->post_password ) ) {
            $classes[] = '_password';
          }

          if ( $args['seamless'] ) {
            $classes[] = '_seamless';
          }

          if ( $content_list_style !== 'default' ) {
            $classes[] = "_{$content_list_style}";
          }

          if ( ! $thumbnail ) {
            $classes[] = '_no-thumbnail';
          }

          if ( ! $show_terms || ! ( $story['has_taxonomies'] || $tags ) ) {
            $classes[] = '_no-tax';
          }

          if ( ! $args['footer'] ) {
            $classes[] = '_no-footer';
          }

          if ( ! $args['footer_author'] ) {
            $classes[] = '_no-footer-author';
          }

          if ( ! $args['words'] || ! $args['footer_words'] ) {
            $classes[] = '_no-chapter-words';
          }

          if ( ! $args['date'] || ! $args['footer_date'] ) {
            $classes[] = '_no-chapter-dates';
          }

          if ( ! $args['footer_rating'] ) {
            $classes[] = '_no-footer-rating';
          }

          // Search for viable chapters...
          $search_list = array_reverse( $story['chapter_ids'] );
          $chapter_post = null;

          foreach ( $search_list as $chapter_id ) {
            $chapter_post = get_post( $chapter_id );

            if ( ! $chapter_post || get_post_meta( $chapter_id, 'fictioneer_chapter_hidden', true ) ) {
              continue;
            }

            if ( $args['ignore_protected'] && $chapter_post->post_password ) {
              continue;
            }

            break;
          }

          // No viable chapter
          if ( ! $chapter_post ) {
            continue;
          }

          // Meta
          $meta = [];

          $meta['chapter'] = '<a href="' . get_the_permalink( $chapter_post ) . '" class="post-list-item__meta-chapter"><i class="fa-solid fa-caret-right chapter-link-icon"></i> ' . fictioneer_get_safe_title( $chapter_post, 'shortcode-latest-updates-list-chapter' ) . '</a>';

          if ( $args['words'] && $args['footer_words'] ) {
            $meta['words'] = '<span class="post-item-item__meta-words">' . sprintf(
              _x( '%s&nbsp;Words', 'Word count in Latest * shortcode (type: list).', 'fictioneer' ),
              number_format_i18n( fictioneer_get_word_count( $chapter_post->ID ) )
            ) . '</span>';
          }

          if ( $story['rating'] && $args['footer_rating'] ) {
            $meta['rating'] = '<span class="post-item-item__meta-rating">' . fcntr( $story['rating'] ) . '</span>';
          }

          if ( get_option( 'fictioneer_show_authors' ) && $args['footer_author'] ) {
            $author = fictioneer_get_author_node( $chapter_post->post_author, 'post-item-item__meta-author' );

            if ( $author ) {
              $meta['author'] = $author;
            }
          }

          if ( $args['date'] && $args['footer_date'] ) {
            $format = $args['date_format'] ?: $args['nested_date_format'] ?: '';

            $meta['date'] = '<span class="post-item-item__meta-publish-date _floating-right">' . get_the_date( $format, $chapter_post->ID ) . '</span>';
          }

          $meta = apply_filters( 'fictioneer_filter_shortcode_latest_updates_list_meta', $meta, $post, $story, $chapter_post );

          // Attributes
          $attributes = [];

          if ( $args['aspect_ratio'] ) {
            $attributes['style'] = '--post-item-image-aspect-ratio: ' . $args['aspect_ratio'];
          }

          $attributes = apply_filters( 'fictioneer_filter_shortcode_list_attributes', $attributes, $post, 'latest-updates' );

          $output_attributes = '';

          foreach ( $attributes as $key => $value ) {
            $output_attributes .= esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
          }

        ?>

        <li class="post-<?php echo $post_id; ?> post-list-item _latest-updates <?php echo implode( ' ', $classes ); ?>" <?php echo $output_attributes; ?>>

          <?php
            if ( $thumbnail ) {
              echo $thumbnail;
            }
          ?>

          <a href="<?php echo $permalink; ?>" class="post-list-item__title _link"><?php
            $html_title = empty( $post->post_password ) ? '' : '<i class="fa-solid fa-lock protected-icon"></i> ';
            $html_title .= $story['title'];

            echo apply_filters( 'fictioneer_filter_shortcode_list_title', $story['title'], $post, 'shortcode-latest-updates-list' );
          ?></a>

          <?php if ( $args['footer'] ) : ?>
            <div class="post-list-item__meta _pseudo-separator"><?php echo implode( ' ', $meta ); ?></div>
          <?php endif; ?>

          <?php if ( $show_terms && ( $story['has_taxonomies'] || $tags ) ) : ?>
            <div class="post-list-item__tax <?php echo $args['terms'] === 'inline' ? '_pseudo-separator' : '_pills'; ?>"><?php
              $inline = $args['terms'] === 'pills' ? '' : '_inline';
              $terms = [];

              if ( $story['fandoms'] ) {
                foreach ( $story['fandoms'] as $fandom ) {
                  $terms[ $fandom->term_id ] = '<a href="' . get_tag_link( $fandom ) . '" class="tag-pill ' . $inline . ' _fandom">' . $fandom->name . '</a>';
                }
              }

              if ( $story['genres'] ) {
                foreach ( $story['genres'] as $genre ) {
                  $terms[ $genre->term_id ] = '<a href="' . get_tag_link( $genre ) . '" class="tag-pill ' . $inline . ' _genre">' . $genre->name . '</a>';
                }
              }

              if ( $tags ) {
                foreach ( $tags as $tag ) {
                  $terms[ $tag->term_id ] = '<a href="' . get_tag_link( $tag ) . '" class="tag-pill ' . $inline . '">' . $tag->name . '</a>';
                }
              }

              if ( $story['characters'] ) {
                foreach ( $story['characters'] as $character ) {
                  $terms[ $character->term_id ] = '<a href="' . get_tag_link( $character ) . '" class="tag-pill ' . $inline . ' _character">' . $character->name . '</a>';
                }
              }

              $terms = apply_filters( 'fictioneer_filter_shortcode_latest_updates_terms', $terms, $story, $args );

              echo implode( ' ', array_slice( $terms, 0, $args['max_terms'] ) );
            ?></div>
          <?php endif; ?>

        </li>

      <?php endwhile; ?>
    </ul>

  <?php else : ?>

    <div class="no-results"><?php _e( 'Nothing to show.', 'fictioneer' ); ?></div>

  <?php endif; wp_reset_postdata(); ?>
</section>