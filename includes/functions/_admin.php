<?php

// =============================================================================
// ADMIN INCLUDES
// =============================================================================

/**
 * Remove obsolete data and clean up when the theme is deactivated/deleted.
 */

require_once __DIR__ . '/_cleanup.php';

/**
 * Add setting page for the theme.
 */

require_once __DIR__ . '/settings/_settings.php';

/**
 * Add meta fields to editor.
 */

require_once __DIR__ . '/_meta_fields.php';

/**
 * Extend user profile in admin panel.
 */

require_once __DIR__ . '/users/_admin_profile.php';

// =============================================================================
// ENQUEUE ADMIN STYLESHEETS
// =============================================================================

/**
 * Enqueues stylesheet for the admin panel
 *
 * @since 3.0
 */

function fictioneer_admin_styles() {
  wp_register_style( 'fictioneer-admin-panel', get_template_directory_uri() . '/css/admin.css', [], FICTIONEER_VERSION );
  wp_enqueue_style( 'fictioneer-admin-panel' );
}
add_action( 'admin_enqueue_scripts', 'fictioneer_admin_styles' );

// =============================================================================
// ENQUEUE ADMIN SCRIPTS
// =============================================================================

/**
 * Enqueue scripts and styles for admin panel
 *
 * @since 4.0
 *
 * @param string $hook_suffix The current admin page.
 */

function fictioneer_admin_scripts( $hook_suffix ) {
  wp_enqueue_script(
    'fictioneer-utility-scripts',
    get_template_directory_uri() . '/js/utility.min.js',
    ['jquery'],
    FICTIONEER_VERSION,
    true
  );

  wp_enqueue_script(
    'fictioneer-admin-script',
    get_template_directory_uri() . '/js/admin.min.js',
    ['jquery', 'fictioneer-utility-scripts'],
    FICTIONEER_VERSION,
    true
  );

  wp_localize_script(
    'fictioneer-admin-script',
    'fictioneer_ajax',
    array(
      'ajax_url' => admin_url( 'admin-ajax.php' ),
      'rest_url' => get_rest_url( null, 'fictioneer/v1/' ),
      'fictioneer_nonce' => wp_create_nonce( 'fictioneer_nonce' )
    )
  );

  // Admin-wide styles
  wp_enqueue_style(
    'fictioneer-admin-panel',
    get_template_directory_uri() . '/css/admin.css',
    [],
    FICTIONEER_VERSION
  );

  // Theme settings styles
  if ( strpos( $hook_suffix, 'page_fictioneer' ) !== false ) {
    wp_enqueue_style(
      'fictioneer-admin-settings',
      get_template_directory_uri() . '/css/settings.css',
      ['fictioneer-admin-panel'],
      FICTIONEER_VERSION
    );
  }

  // ACF
  acf_enqueue_scripts();
}
add_action( 'admin_enqueue_scripts', 'fictioneer_admin_scripts' );

// =============================================================================
// CHECK FOR UPDATES
// =============================================================================

if ( ! function_exists( 'fictioneer_check_for_updates' ) ) {
  /**
   * Check Github repository for a new release
   *
   * Makes a remote request to the Github API to check the latest release and,
   * if a newer version tag than what is installed is returned, updates the
   * 'fictioneer_latest_version' option. Only one request is made every 12 hours
   * unless you are on the updates page.
   *
   * @since Fictioneer 5.0
   * @since Fictioneer 5.7.5 Refactored.
   *
   * @return boolean True if there is a newer version, false if not.
   */

  function fictioneer_check_for_updates() {
    global $pagenow;

    // Setup
    $last_check = (int) get_option( 'fictioneer_update_check_timestamp', 0 );
    $latest_version = get_option( 'fictioneer_latest_version', FICTIONEER_RELEASE_TAG );
    $is_updates_page = $pagenow === 'update-core.php';

    // Only call API every n seconds, otherwise check database
    if ( ! empty( $latest_version ) && time() < $last_check + FICTIONEER_UPDATE_CHECK_TIMEOUT && ! $is_updates_page ) {
      return version_compare( $latest_version, FICTIONEER_RELEASE_TAG, '>' );
    }

    // Remember this check
    update_option( 'fictioneer_update_check_timestamp', time() );

    // Request to repository
    $response = wp_remote_get(
      'https://api.github.com/repos/Tetrakern/fictioneer/releases/latest',
      array( 'headers' => array( 'User-Agent' => 'FICTIONEER' ) )
    );

    // Abort if request failed or is not a 2xx success status code
    if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) >= 300 ) {
      return false;
    }

    // Decode JSON to array
    $release = json_decode( wp_remote_retrieve_body( $response ), true );

    // Abort if request did not return expected data
    if ( ! isset( $release['tag_name'] ) ) {
      return false;
    }

    // Remember latest version
    update_option( 'fictioneer_latest_version', $release['tag_name'] );

    // Compare with currently installed version
    return version_compare( $release['tag_name'], FICTIONEER_RELEASE_TAG, '>' );
  }
}

/**
 * Show notice when a newer version is available
 *
 * @since Fictioneer 5.0
 */

function fictioneer_admin_update_notice() {
  global $pagenow;

  // Setup
  $last_notice = (int) get_option( 'fictioneer_update_notice_timestamp', 0 );
  $is_updates_page = $pagenow == 'update-core.php';

  // Show only once every n seconds
  if ( $last_notice + FICTIONEER_UPDATE_CHECK_TIMEOUT > time() && ! $is_updates_page ) {
    return;
  }

  // Update?
  if ( ! fictioneer_check_for_updates() ) {
    return;
  }

  // Render notice
  $message = sprintf(
    __( '<strong>Fictioneer %1$s</strong> is available. Please <a href="%2$s" target="_blank">download</a> and install the latest version at your next convenience.', 'fictioneer' ),
    get_option( 'fictioneer_latest_version', FICTIONEER_RELEASE_TAG ),
    'https://github.com/Tetrakern/fictioneer/releases'
  );

  echo "<div class='notice notice-warning is-dismissible'><p>$message</p></div>";

  // Remember notice
  update_option( 'fictioneer_update_notice_timestamp', time() );
}

if ( current_user_can( 'install_themes' ) ) {
  add_action( 'admin_notices', 'fictioneer_admin_update_notice' );
}

// =============================================================================
// ADD REMOVABLE QUERY ARGS
// =============================================================================

/**
 * Modifies the list of removable query arguments (admin panel only)
 *
 * @since Fictioneer 5.2.5
 *
 * @param array $args  The list of removable query arguments.
 *
 * @return array The modified list of removable query arguments.
 */

function fictioneer_removable_args( $args ) {
  $args[] = 'success';
  $args[] = 'failure';
  $args[] = 'info';
  $args[] = 'data';
  $args[] = 'fictioneer_nonce';
  $args[] = 'fictioneer-notice';
  return $args;
}
add_filter( 'removable_query_args', 'fictioneer_removable_args' );

// =============================================================================
// LIMIT DEFAULT BLOCKS
// =============================================================================

/**
 * Limit the available default blocks
 *
 * Fictioneer is has a particular and delicate content section, built around and
 * for the main purpose of displaying prose. Other features, such as the ePUB
 * converter heavily depend on _expected_ input or may break. There are certainly
 * more possible but the initial selection has been chosen carefully.
 *
 * @since 4.0
 */

function fictioneer_allowed_block_types() {
  $allowed = array(
    // WordPress
    'core/image',
    'core/paragraph',
    'core/heading',
    'core/list',
    'core/list-item',
    'core/gallery',
    'core/quote',
    'core/pullquote',
    'core/table',
    'core/code',
    'core/preformatted',
    'core/html',
    'core/separator',
    'core/spacer',
    'core/more',
    'core/buttons',
    'core/button',
    'core/audio',
    'core/video',
    'core/file',
    'core/embed',
    'core-embed/youtube',
    'core-embed/soundcloud',
    'core-embed/spotify',
    'core-embed/vimeo',
    'core-embed/twitter',
    // Plugins
    'cloudinary/gallery',
    'jetpack/business-hours',
    'jetpack/button',
    'jetpack/calendly',
    'jetpack/contact-form',
    'jetpack/field-text',
    'jetpack/field-name',
    'jetpack/field-email',
    'jetpack/field-url',
    'jetpack/field-date',
    'jetpack/field-telephone',
    'jetpack/field-textarea',
    'jetpack/field-checkbox',
    'jetpack/field-consent',
    'jetpack/field-checkbox-multiple',
    'jetpack/field-radio',
    'jetpack/field-select',
    'jetpack/contact-info',
    'jetpack/address',
    'jetpack/email',
    'jetpack/phone',
    'jetpack/eventbrite',
    'jetpack/gif',
    'jetpack/google-calendar',
    'jetpack/image-compare',
    'jetpack/instagram-gallery',
    'jetpack/mailchimp',
    'jetpack/map',
    'jetpack/markdown',
    'jetpack/opentable',
    'jetpack/pinterest',
    'jetpack/podcast-player',
    'jetpack/rating-star',
    'jetpack/recurring-payments',
    'jetpack/repeat-visitor',
    'jetpack/revue',
    'jetpack/send-a-message',
    'jetpack/whatsapp-button',
    'jetpack/slideshow',
    'jetpack/story',
    'jetpack/subscriptions',
    'jetpack/tiled-gallery',
    'jetpack/payments-intro',
    'jetpack/payment-buttons'
  );

  if ( current_user_can( 'fcn_shortcodes' ) || current_user_can( 'manage_options' ) ) {
    $allowed[] = 'core/shortcode';
  }

  return $allowed;
}

if ( ! get_option( 'fictioneer_enable_all_blocks' ) ) {
  add_filter( 'allowed_block_types_all', 'fictioneer_allowed_block_types' );
}

// =============================================================================
// ADD OR UPDATE TERM
// =============================================================================

if ( ! function_exists( 'fictioneer_add_or_update_term' ) ) {
  /**
   * Add or update term
   *
   * @since Fictioneer 4.6
   *
   * @param string $name      Name of the term to add or update.
   * @param string $taxonomy  Taxonomy type of the term.
   * @param array  $args      Optional. An array of arguments.
   *
   * @return int|boolean The term ID or false.
   */

  function fictioneer_add_or_update_term( $name, $taxonomy, $args = [] ) {
  	$parent = $args['parent'] ?? 0;
  	$alias_of = $args['alias_of'] ?? '';
  	$description = $args['description'] ?? '';
    $result = false;

    // Does term already exist?
  	$old = get_term_by( 'name', $name, $taxonomy );

    // Get parent or create one if it does not yet exist
  	if ( $parent != 0 ) {
  		$parent = get_term_by( 'name', $parent, $taxonomy );
      $parent = $parent ? $parent->term_id : fictioneer_add_or_update_term( $args['parent'], $taxonomy );
  	}

    // Get alias or create one if it does not yet exist
  	if ( ! empty( $alias_of ) ) {
  		$alias_of = get_term_by( 'name', $alias_of, $taxonomy );

      if ( ! $alias_of ) {
        $alias_of = fictioneer_add_or_update_term( $args['alias_of'], $taxonomy );
        $alias_of = $alias_of ? get_term_by( 'term_id', $alias_of, $taxonomy ) : false;
      }

      $alias_of = $alias_of ? $alias_of->slug : '';
  	}

  	if ( ! $old ) {
      // Create term
  		$result = wp_insert_term(
  			$name,
  			$taxonomy,
  			array(
  				'alias_of' => $alias_of,
  				'parent' => $parent,
  				'description' => $description
  			)
  		);
  	} else {
      // Update term
  		$result = wp_update_term(
  			$old->term_id,
  			$taxonomy,
  			array(
  				'alias_of' => $alias_of,
  				'parent' => $parent,
  				'description' => $description
  			)
  		);
  	}

    if ( ! is_wp_error( $result ) ) {
      return $result['term_id'];
    } else {
      return false;
    }
  }
}

// =============================================================================
// CONVERT TAXONOMIES
// =============================================================================

if ( ! function_exists( 'fictioneer_convert_taxonomies' ) ) {
  /**
   * Convert taxonomies of post type from one type to another
   *
   * @since Fictioneer 4.7
   *
   * @param string  $post_type  Post type the taxonomy is attached to.
   * @param string  $target     Taxonomy to be converted to.
   * @param string  $source     Taxonomy to be converted from. Default 'post_tag'.
   * @param boolean $append     Whether to replace the post type's taxonomies with
   *                            the new ones or append them. Default false.
   * @param boolean $clean_up   Whether to delete the taxonomies from the post type
   *                            after transfer or keep them. Default false.
   */

  function fictioneer_convert_taxonomies( $post_type, $target, $source = 'post_tag', $append = false, $clean_up = false ) {
    $query_args = array(
      'post_type' => $post_type,
      'posts_per_page' => -1,
      'fields' => 'ids',
      'update_post_meta_cache' => false
    );

    $items = get_posts( $query_args );

    function terms_to_array( $n ) {
      return $n->name;
    }

    foreach ( $items as $item ) {
      $source_tax = get_the_terms( $item, $source );

      if ( ! $source_tax ) {
        continue;
      }

      $source_tax = array_map( 'terms_to_array', $source_tax );

      wp_set_object_terms( $item, $source_tax, $target, $append );

      if ( $clean_up ) {
        wp_delete_object_term_relationships( $item, $source );
      }
    }
  }
}

// =============================================================================
// AJAX: GET STORY CHAPTER GROUP OPTIONS
// =============================================================================

/**
 * AJAX: Get chapter group options for story
 *
 * @since Fictioneer 5.7.4
 */

function fictioneer_ajax_get_chapter_groups() {
  // Validate
  $user = fictioneer_get_validated_ajax_user( 'nonce', 'fictioneer_nonce' );
  $story_id = isset( $_GET['story_id'] ) ? fictioneer_validate_id( $_GET['story_id'], 'fcn_story' ) : null;

  if ( ! is_admin() || ! wp_doing_ajax() ) {
    wp_send_json_error( array( 'error' => __( 'Request did not pass validation.', 'fictioneer' ) ) );
  }

  if ( ! $user || ! current_user_can( 'edit_fcn_stories' ) ) {
    wp_send_json_error( array( 'error' => __( 'User did not pass validation.', 'fictioneer' ) ) );
  }

  if ( ! $story_id ) {
    wp_send_json_error( array( 'error' => __( 'Story ID did not pass validation.', 'fictioneer' ) ) );
  }

  // Setup
  global $wpdb;

  $story = fictioneer_get_story_data( $story_id, false );
  $groups = [];

  // Query groups
  if ( $story && ! empty( $story['chapter_ids'] ) ) {
    $post_ids_format = implode( ', ', array_fill( 0, count( $story['chapter_ids'] ), '%d' ) );

    $sql = $wpdb->prepare(
      "SELECT post_id, meta_value
      FROM $wpdb->postmeta
      WHERE meta_key = 'fictioneer_chapter_group'
      AND meta_value != ''
      AND post_id IN ($post_ids_format)",
      $story['chapter_ids']
    );

    $results = $wpdb->get_results( $sql );

    foreach ( $results as $result ) {
      if ( $result->meta_value ) {
        $groups[] = $result->meta_value;
      }
    }

    $groups = array_unique( $groups );
  }

  // Prepare HTML
  $html = '';

  if ( ! empty( $groups ) ) {
    foreach ( $groups as $group ) {
      $html .= "<option value='{$group}'></option>";
    }
  }

  // Send
  if ( empty( $html ) ) {
    wp_send_json_error( array( 'failure' => __( 'No groups found.', 'fictioneer' ) ) );
  } else {
    wp_send_json_success( array( 'html' => $html ) );
  }
}
add_action( 'wp_ajax_fictioneer_ajax_get_chapter_groups', 'fictioneer_ajax_get_chapter_groups' );

?>
