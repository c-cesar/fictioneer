<?php

// =============================================================================
// DEBUG
// =============================================================================

function fictioneer_display_execution_time() {
  // Abort on AJAX
  if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
    return;
  }

  // Abort on REST
  if ( strpos( $_SERVER['REQUEST_URI'], 'wp-json' ) !== false ) {
    return;
  }

  echo '<!-- Execution Time: ' . microtime( true ) - WP_START_TIMESTAMP . ' seconds -->';
}

if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
  // This should be set, but just to make sure...
  if ( ! defined( 'WP_START_TIMESTAMP' ) ) {
    define( 'WP_START_TIMESTAMP', microtime(true) );
  }

  add_action( 'shutdown', 'fictioneer_display_execution_time' );
}

// =============================================================================
// CONSTANTS/SETTINGS
// =============================================================================

// Version
define( 'FICTIONEER_VERSION', '5.18.1' );
define( 'FICTIONEER_MAJOR_VERSION', '5' );
define( 'FICTIONEER_RELEASE_TAG', 'v5.18.1' );

if ( ! defined( 'CHILD_VERSION' ) ) {
  define( 'CHILD_VERSION', null );
}

if ( ! defined( 'CHILD_NAME' ) ) {
  define( 'CHILD_NAME', null );
}

/*
 * Locations
 */

if ( ! defined( 'FICTIONEER_MU_REGISTRATION' ) ) {
  define(
    'FICTIONEER_MU_REGISTRATION',
    strpos( $_SERVER['REQUEST_URI'], 'wp-signup.php' ) !== false ||
    strpos( $_SERVER['REQUEST_URI'], 'wp-activate.php' ) !== false
  );
}

/*
 * Endpoints
 */

// Endpoint: OAuth 2.0
if ( ! defined( 'FICTIONEER_OAUTH_ENDPOINT' ) ) {
  define( 'FICTIONEER_OAUTH_ENDPOINT', 'oauth2' );
}

// Endpoint: EPUBs
if ( ! defined( 'FICTIONEER_EPUB_ENDPOINT' ) ) {
  define( 'FICTIONEER_EPUB_ENDPOINT', 'download-epub' );
}

// Endpoint: Logout (disable with false)
if ( ! defined( 'FICTIONEER_LOGOUT_ENDPOINT' ) ) {
  define( 'FICTIONEER_LOGOUT_ENDPOINT', 'fictioneer-logout' );
}

/*
 * Strings
 */

// String: CSS name of the primary font
if ( ! defined( 'FICTIONEER_PRIMARY_FONT_CSS' ) ) {
  define( 'FICTIONEER_PRIMARY_FONT_CSS', get_theme_mod( 'primary_font_family_value', 'Open Sans' ) );
}

// String: Display name of the primary font
if ( ! defined( 'FICTIONEER_PRIMARY_FONT_NAME' ) ) {
  define( 'FICTIONEER_PRIMARY_FONT_NAME', get_theme_mod( 'primary_font_family_value', 'Open Sans' ) );
}

// String: TTS regex (used to split text into sentences)
if ( ! defined( 'FICTIONEER_TTS_REGEX' ) ) {
  // Note: Because lookbehind assertions do not work in Safari, the script uses a little
  // detour to preserve punctuation marks: text.replace(regex, '$1|').split('|')
  define( 'FICTIONEER_TTS_REGEX', '([.!?:"\'\u201C\u201D])\s+(?=[A-Z"\'\u201C\u201D])' );
}

// String: Default list of allowed mime types for upload restrictions
define(
  'FICTIONEER_DEFAULT_UPLOAD_MIME_TYPE_RESTRICTIONS',
  'image/jpeg, image/png, image/webp, image/avif, image/gif, application/pdf, application/epub+zip, application/rtf, text/plain, image/svg+xml'
);

// String: Font Awesome CDN URL
if ( ! defined( 'FICTIONEER_FA_CDN' ) ) {
  define( 'FICTIONEER_FA_CDN', 'https://use.fontawesome.com/releases/v6.5.1/css/all.css' );
}

// String: Font Awesome CDN integrity (generated by https://www.srihash.org/)
if ( ! defined( 'FICTIONEER_FA_INTEGRITY' ) ) {
  define(
    'FICTIONEER_FA_INTEGRITY',
    'sha384-t1nt8BQoYMLFN5p42tRAtuAAFQaCQODekUVeKKZrEnEyp4H2R0RHFz0KWpmj7i8g'
  );
}

// String: Story page footer date format below 480px
if ( ! defined( 'FICTIONEER_DISCORD_EMBED_COLOR' ) ) {
  define( 'FICTIONEER_DISCORD_EMBED_COLOR', '9692513' );
}

// String: Truncation ellipsis
if ( ! defined( 'FICTIONEER_TRUNCATION_ELLIPSIS' ) ) {
  define( 'FICTIONEER_TRUNCATION_ELLIPSIS', '…' );
}

// String: Age confirmation redirect
if ( ! defined( 'FICTIONEER_AGE_CONFIRMATION_REDIRECT' ) ) {
  define( 'FICTIONEER_AGE_CONFIRMATION_REDIRECT', 'https://search.brave.com/' );
}

/*
 * Date Strings
 */

$short_date = get_option( 'fictioneer_subitem_short_date_format', 'M j' ) ?: 'M j';
$long_date = get_option( 'fictioneer_subitem_date_format', "M j, 'y" ) ?: "M j, 'y";

// String: Latest Updates shortcode list item date format
if ( ! defined( 'FICTIONEER_LATEST_UPDATES_LI_DATE' ) ) {
  define( 'FICTIONEER_LATEST_UPDATES_LI_DATE', $short_date );
}

// String: Latest Updates shortcode footer date format
if ( ! defined( 'FICTIONEER_LATEST_UPDATES_FOOTER_DATE' ) ) {
  define( 'FICTIONEER_LATEST_UPDATES_FOOTER_DATE', $long_date );
}

// String: Latest Chapters shortcode footer date format
if ( ! defined( 'FICTIONEER_LATEST_CHAPTERS_FOOTER_DATE' ) ) {
  define( 'FICTIONEER_LATEST_CHAPTERS_FOOTER_DATE', $long_date );
}

// String: Latest Stories shortcode footer date format
if ( ! defined( 'FICTIONEER_LATEST_STORIES_FOOTER_DATE' ) ) {
  define( 'FICTIONEER_LATEST_STORIES_FOOTER_DATE', $long_date );
}

// String: Story card list item date format
if ( ! defined( 'FICTIONEER_CARD_STORY_LI_DATE' ) ) {
  define( 'FICTIONEER_CARD_STORY_LI_DATE', $long_date );
}

// String: Story card footer date format
if ( ! defined( 'FICTIONEER_CARD_STORY_FOOTER_DATE' ) ) {
  define( 'FICTIONEER_CARD_STORY_FOOTER_DATE', $long_date );
}

// String: Chapter card footer date format
if ( ! defined( 'FICTIONEER_CARD_CHAPTER_FOOTER_DATE' ) ) {
  define( 'FICTIONEER_CARD_CHAPTER_FOOTER_DATE', $long_date );
}

// String: Collection card list item date format
if ( ! defined( 'FICTIONEER_CARD_COLLECTION_LI_DATE' ) ) {
  define( 'FICTIONEER_CARD_COLLECTION_LI_DATE', $long_date );
}

// String: Collection card footer date format
if ( ! defined( 'FICTIONEER_CARD_COLLECTION_FOOTER_DATE' ) ) {
  define( 'FICTIONEER_CARD_COLLECTION_FOOTER_DATE', $long_date );
}

// String: Post card footer date format
if ( ! defined( 'FICTIONEER_CARD_POST_FOOTER_DATE' ) ) {
  define( 'FICTIONEER_CARD_POST_FOOTER_DATE', $long_date );
}

// String: Page card footer date format
if ( ! defined( 'FICTIONEER_CARD_PAGE_FOOTER_DATE' ) ) {
  define( 'FICTIONEER_CARD_PAGE_FOOTER_DATE', $long_date );
}

// String: Article card footer date format
if ( ! defined( 'FICTIONEER_CARD_ARTICLE_FOOTER_DATE' ) ) {
  define( 'FICTIONEER_CARD_ARTICLE_FOOTER_DATE', $long_date );
}

// String: Story page footer date format below 480px
if ( ! defined( 'FICTIONEER_STORY_FOOTER_B480_DATE' ) ) {
  define( 'FICTIONEER_STORY_FOOTER_B480_DATE', $long_date );
}

/*
 * Integers
 */

// Integer: Commentcode expiration timer in seconds (-1 for infinite)
if ( ! defined( 'FICTIONEER_COMMENTCODE_TTL' ) ) {
  define( 'FICTIONEER_COMMENTCODE_TTL', 600 );
}

// Integer: AJAX cache TTL in milliseconds
if ( ! defined( 'FICTIONEER_AJAX_TTL' ) ) {
  define( 'FICTIONEER_AJAX_TTL', 60000 );
}

// Integer: AJAX login cache TTL in milliseconds
if ( ! defined( 'FICTIONEER_AJAX_LOGIN_TTL' ) ) {
  define( 'FICTIONEER_AJAX_LOGIN_TTL', 15000 );
}

// Integer: AJAX POST debounce rate in milliseconds
if ( ! defined( 'FICTIONEER_AJAX_POST_DEBOUNCE_RATE' ) ) {
  define( 'FICTIONEER_AJAX_POST_DEBOUNCE_RATE', 700 );
}

// Integer: Author search keyword limit (use simple text input if exceeded)
if ( ! defined( 'FICTIONEER_AUTHOR_KEYWORD_SEARCH_LIMIT' ) ) {
  define( 'FICTIONEER_AUTHOR_KEYWORD_SEARCH_LIMIT', 300 );
}

// Integer: Update check timeout
if ( ! defined( 'FICTIONEER_UPDATE_CHECK_TIMEOUT' ) ) {
  define( 'FICTIONEER_UPDATE_CHECK_TIMEOUT', 43200 ); // 12 hours
}

// Integer: Storygraph API cache TTL
if ( ! defined( 'FICTIONEER_API_STORYGRAPH_CACHE_TTL' ) ) {
  define( 'FICTIONEER_API_STORYGRAPH_CACHE_TTL', 3600 );
}

// Integer: Storygraph API number of stories per page
if ( ! defined( 'FICTIONEER_API_STORYGRAPH_STORIES_PER_PAGE' ) ) {
  define( 'FICTIONEER_API_STORYGRAPH_STORIES_PER_PAGE', 10 );
}

// Integer: Maximum number of displayed custom pages
if ( ! defined( 'FICTIONEER_MAX_CUSTOM_PAGES_PER_STORY' ) ) {
  define( 'FICTIONEER_MAX_CUSTOM_PAGES_PER_STORY', 4 );
}

// Integer: The number of chapter before and after folding in chapter lists
if ( ! defined( 'FICTIONEER_CHAPTER_FOLDING_THRESHOLD' ) ) {
  define( 'FICTIONEER_CHAPTER_FOLDING_THRESHOLD', 5 );
}

// Integer: Expiration time for shortcode Transients (0 for infinite, -1 to disable)
if ( ! defined( 'FICTIONEER_SHORTCODE_TRANSIENT_EXPIRATION' ) ) {
  define( 'FICTIONEER_SHORTCODE_TRANSIENT_EXPIRATION', 300 );
}

// Integer: Timeout between refreshes of story comment counts (0 for always)
if ( ! defined( 'FICTIONEER_STORY_COMMENT_COUNT_TIMEOUT' ) ) {
  define( 'FICTIONEER_STORY_COMMENT_COUNT_TIMEOUT', 900 );
}

// Integer: Requests per minute
if ( ! defined( 'FICTIONEER_REQUESTS_PER_MINUTE' ) ) {
  define( 'FICTIONEER_REQUESTS_PER_MINUTE', 5 );
}

// Integer: Maximum number of IDs in 'post__in' and 'post__not_in' query arguments
// to prevent performance degradation. If exceeded, the whole argument will be
// ignored in certain queries, which account for.
if ( ! defined( 'FICTIONEER_QUERY_ID_ARRAY_LIMIT' ) ) {
  define( 'FICTIONEER_QUERY_ID_ARRAY_LIMIT', 100 );
}

// Integer: After how many seconds after saving is a post considered "old"
if ( ! defined( 'FICTIONEER_OLD_POST_THRESHOLD' ) ) {
  define( 'FICTIONEER_OLD_POST_THRESHOLD', 30 );
}

// Integer: Time until a user's Patreon data expires in seconds
if ( ! defined( 'FICTIONEER_PATREON_EXPIRATION_TIME' ) ) {
  define( 'FICTIONEER_PATREON_EXPIRATION_TIME', WEEK_IN_SECONDS );
}

/*
 * Booleans
 */

// Prevent warnings
if ( ! defined( 'WP_DEBUG' ) ) {
  define( 'WP_DEBUG', false );
}

// Boolean: Theme cache purging on post update
if ( ! defined( 'FICTIONEER_CACHE_PURGE_ASSIST' ) ) {
  define( 'FICTIONEER_CACHE_PURGE_ASSIST', true );
}

// Boolean: Theme relationship cache purging on post update
if ( ! defined( 'FICTIONEER_RELATIONSHIP_PURGE_ASSIST' ) ) {
  define( 'FICTIONEER_RELATIONSHIP_PURGE_ASSIST', true );
}

// Boolean: Cache chapter lists on story pages as Transients
if ( ! defined( 'FICTIONEER_CHAPTER_LIST_TRANSIENTS' ) ) {
  define( 'FICTIONEER_CHAPTER_LIST_TRANSIENTS', true );
}

// Boolean: Menu items for search
if ( ! defined( 'FICTIONEER_SHOW_SEARCH_IN_MENUS' ) ) {
  define( 'FICTIONEER_SHOW_SEARCH_IN_MENUS', true );
}

// Boolean: Base theme switch in site settings
if ( ! defined( 'FICTIONEER_THEME_SWITCH' ) ) {
  define( 'FICTIONEER_THEME_SWITCH', true );
}

// Boolean: Attachment pages
if ( ! defined( 'FICTIONEER_ATTACHMENT_PAGES' ) ) {
  define( 'FICTIONEER_ATTACHMENT_PAGES', false );
}

// Boolean: Show OAuth ID Hashes (admin only)
if ( ! defined( 'FICTIONEER_SHOW_OAUTH_HASHES' ) ) {
  define( 'FICTIONEER_SHOW_OAUTH_HASHES', false );
}

// Boolean: Show AJAX comment submission disallowed content keys
if ( ! defined( 'FICTIONEER_DISALLOWED_KEY_NOTICE' ) ) {
  define( 'FICTIONEER_DISALLOWED_KEY_NOTICE', true );
}

// Boolean: Only allow assigned chapters in stories
// Warning: The chapter navigation always refers to the parent story!
if ( ! defined( 'FICTIONEER_FILTER_STORY_CHAPTERS' ) ) {
  define( 'FICTIONEER_FILTER_STORY_CHAPTERS', true );
}

// Boolean: Only show the full comment form after clicking into it
if ( ! defined( 'FICTIONEER_COLLAPSE_COMMENT_FORM' ) ) {
  define( 'FICTIONEER_COLLAPSE_COMMENT_FORM', true );
}

// Boolean: Storygraph API image nodes
if ( ! defined( 'FICTIONEER_API_STORYGRAPH_IMAGES' ) ) {
  define( 'FICTIONEER_API_STORYGRAPH_IMAGES', true );
}

// Boolean: Storygraph API hotlink permission
if ( ! defined( 'FICTIONEER_API_STORYGRAPH_HOTLINK' ) ) {
  define( 'FICTIONEER_API_STORYGRAPH_HOTLINK', false );
}

// Boolean: Storygraph API with chapters in /stories
if ( ! defined( 'FICTIONEER_API_STORYGRAPH_CHAPTERS' ) ) {
  define( 'FICTIONEER_API_STORYGRAPH_CHAPTERS', true );
}

// Boolean: Storygraph API Transients caching
if ( ! defined( 'FICTIONEER_API_STORYGRAPH_TRANSIENTS' ) ) {
  define( 'FICTIONEER_API_STORYGRAPH_TRANSIENTS', true );
}

// Boolean: Enable sticky cards
if ( ! defined( 'FICTIONEER_ENABLE_STICKY_CARDS' ) ) {
  define( 'FICTIONEER_ENABLE_STICKY_CARDS', true );
}

// Boolean: Enable story data meta cache
if ( ! defined( 'FICTIONEER_ENABLE_STORY_DATA_META_CACHE' ) ) {
  define( 'FICTIONEER_ENABLE_STORY_DATA_META_CACHE', true );
}

// Boolean: Enable nav menu Transients
if ( ! defined( 'FICTIONEER_ENABLE_MENU_TRANSIENTS' ) ) {
  define( 'FICTIONEER_ENABLE_MENU_TRANSIENTS', true );
}

// Boolean: Order stories by last updated chapter timestamp
if ( ! defined( 'FICTIONEER_ORDER_STORIES_BY_LATEST_CHAPTER' ) ) {
  define( 'FICTIONEER_ORDER_STORIES_BY_LATEST_CHAPTER', false );
}

// Boolean: Enable tracking of chapter changes in stories
if ( ! defined( 'FICTIONEER_ENABLE_STORY_CHANGELOG' ) ) {
  define( 'FICTIONEER_ENABLE_STORY_CHANGELOG', true );
}

// Boolean: Enable showing of outdated browsers' missing features
if ( ! defined( 'FICTIONEER_ENABLE_BROWSER_NOTES' ) ) {
  define( 'FICTIONEER_ENABLE_BROWSER_NOTES', false );
}

// Boolean: Defer scripts
if ( ! defined( 'FICTIONEER_DEFER_SCRIPTS' ) ) {
  define( 'FICTIONEER_DEFER_SCRIPTS', true );
}

// Boolean: Asynchronous loading via onload pattern
if ( ! defined( 'FICTIONEER_ENABLE_ASYNC_ONLOAD_PATTERN' ) ) {
  define( 'FICTIONEER_ENABLE_ASYNC_ONLOAD_PATTERN', true );
}

/*
 * Arrays
 */

// Array: Example collection of Font Awesome icons
if ( ! defined( 'FICTIONEER_EXAMPLE_CHAPTER_ICONS' ) ) {
  define(
    'FICTIONEER_EXAMPLE_CHAPTER_ICONS',
    ['fa-solid fa-book', 'fa-solid fa-star', 'fa-solid fa-heart', 'fa-solid fa-bomb', 'fa-solid fa-wine-glass',
    'fa-solid fa-face-smile', 'fa-solid fa-shield', 'fa-solid fa-ghost', 'fa-solid fa-gear', 'fa-solid fa-droplet',
    'fa-solid fa-fire', 'fa-solid fa-radiation', 'fa-solid fa-lemon', 'fa-solid fa-globe', 'fa-solid fa-flask',
    'fa-solid fa-snowflake', 'fa-solid fa-cookie-bite', 'fa-solid fa-circle', 'fa-solid fa-square', 'fa-solid fa-moon',
    'fa-solid fa-brain', 'fa-solid fa-diamond', 'fa-solid fa-virus', 'fa-solid fa-horse-head', 'fa-solid fa-certificate',
    'fa-solid fa-scroll', 'fa-solid fa-spa', 'fa-solid fa-skull']
  );
}

// =============================================================================
// GLOBAL
// =============================================================================

/**
 * Provides various utility functions.
 */

require_once __DIR__ . '/includes/functions/_utility.php';

/**
 * Modify WordPress functions via hooks.
 */

require_once __DIR__ . '/includes/functions/_setup-wordpress.php';

/**
 * Actions whenever a post is created or updated.
 */

require_once __DIR__ . '/includes/functions/_service-posts.php';

/**
 * Set up the theme customizer.
 */

require_once __DIR__ . '/includes/functions/_customizer.php';

if ( is_customize_preview() ) {
  require_once __DIR__ . '/includes/functions/_customizer-settings.php';
}

/**
 * Set up the theme.
 */

require_once __DIR__ . '/includes/functions/_setup-theme.php';

/**
 * Add custom post types.
 */

require_once __DIR__ . '/includes/functions/_setup-types-and-terms.php';

/**
 * Cache plugins and Transients.
 */

require_once __DIR__ . '/includes/functions/_service-caching.php';

/**
 * Add custom shortcodes.
 */

require_once __DIR__ . '/includes/functions/_setup-shortcodes.php';

/**
 * Generate sitemap.
 */

require_once __DIR__ . '/includes/functions/_module-sitemap.php';

/**
 * Add SEO features to the site.
 */

require_once __DIR__ . '/includes/functions/_module-seo.php';

/**
 * Communicate with the Discord API.
 */

require_once __DIR__ . '/includes/functions/_module-discord.php';

/**
 * Generate ePUBs for stories.
 */

if ( get_option( 'fictioneer_enable_epubs' ) ) {
  require_once __DIR__ . '/includes/functions/_module-epub.php';
}

/**
 * Log-in and register subscribers via OAuth 2.0.
 */

require_once __DIR__ . '/includes/functions/_module-oauth.php';

/**
 * Handle comments.
 */

require_once __DIR__ . '/includes/functions/comments/_comments_controller.php'; // Used in REST
require_once __DIR__ . '/includes/functions/comments/_story_comments.php'; // Used in REST

if ( strpos( $_SERVER['REQUEST_URI'], 'wp-json/fictioneer/' ) !== false ) {
  require_once __DIR__ . '/includes/functions/comments/_comments_threads.php';
}

if ( is_admin() ) {
  // Required for AJAX
  require_once __DIR__ . '/includes/functions/comments/_comments_ajax.php';
  require_once __DIR__ . '/includes/functions/comments/_comments_form.php';
  require_once __DIR__ . '/includes/functions/comments/_comments_threads.php';
  require_once __DIR__ . '/includes/functions/comments/_comments_moderation.php';
}

function fictioneer_conditional_require_comments() {
  if ( comments_open() ) {
    require_once __DIR__ . '/includes/functions/comments/_comments_form.php';
    require_once __DIR__ . '/includes/functions/comments/_comments_threads.php';
    require_once __DIR__ . '/includes/functions/comments/_comments_moderation.php';
  }
}
add_action( 'wp', 'fictioneer_conditional_require_comments' );

/**
 * Add functions for users.
 */

require_once __DIR__ . '/includes/functions/users/_user_data.php';

/**
 * Add the follow feature.
 */

if ( get_option( 'fictioneer_enable_follows' ) ) {
  require_once __DIR__ . '/includes/functions/users/_follows.php';
}

/**
 * Add the reminders feature.
 */

if ( get_option( 'fictioneer_enable_reminders' ) ) {
  require_once __DIR__ . '/includes/functions/users/_reminders.php';
}

/**
 * Add the checkmarks features.
 */

if ( get_option( 'fictioneer_enable_checkmarks' ) ) {
  require_once __DIR__ . '/includes/functions/users/_checkmarks.php';
}

/**
 * Add the bookmarks feature.
 */

if ( get_option( 'fictioneer_enable_bookmarks' ) && is_admin() ) {
  // Only used for AJAX
  require_once __DIR__ . '/includes/functions/users/_bookmarks.php';
}

/**
 * Add content helper functions.
 */

require_once __DIR__ . '/includes/functions/_helpers-templates.php';

/**
 * Add content query functions.
 */

require_once __DIR__ . '/includes/functions/_helpers-query.php';

/**
 * Add role functions.
 */

require_once __DIR__ . '/includes/functions/_setup-roles.php';

/**
 * Add forms.
 */

require_once __DIR__ . '/includes/functions/_module-forms.php';

/**
 * Add API.
 */

if ( get_option( 'fictioneer_enable_storygraph_api' ) ) {
  require_once __DIR__ . '/includes/functions/_api.php';
}

/**
 * Add search.
 */

require_once __DIR__ . '/includes/functions/_module-search.php';

/**
 * Generate SEO schema graphs.
 */

if ( get_option( 'fictioneer_enable_seo' ) && ! fictioneer_seo_plugin_active() ) {
  require_once __DIR__ . '/includes/functions/_module-schemas.php';
}

// =============================================================================
// ADMIN ONLY
// =============================================================================

if ( is_admin() ) {

  /**
   * Functions only required in the admin panel.
   */

  require_once __DIR__ . '/includes/functions/_setup-admin.php';

}

// =============================================================================
// FRONTEND ONLY
// =============================================================================

if ( ! is_admin() ) {

  /**
   * Add general hooks.
   */

  require_once __DIR__ . '/includes/functions/hooks/_general_hooks.php';

  /**
   * Add post and page hooks.
   */

  function fictioneer_conditional_require_frontend_hooks() {
    if (
      is_page_template( 'stories.php' ) ||
      is_page_template( 'singular-story.php' ) ||
      is_page_template( 'singular-mirror-story.php' ) ||
      is_singular( 'fcn_story' )
    ) {
      require_once __DIR__ . '/includes/functions/hooks/_story_hooks.php';
    }

    if ( is_page_template( 'chapters.php' ) || is_singular( 'fcn_chapter' ) ) {
      require_once __DIR__ . '/includes/functions/hooks/_chapter_hooks.php';
    }

    if ( is_page_template( 'recommendations.php' ) || is_singular( 'fcn_recommendation' ) ) {
      require_once __DIR__ . '/includes/functions/hooks/_recommendation_hooks.php';
    }

    if ( is_page_template( 'collections.php' ) || is_singular( 'fcn_collection' ) ) {
      require_once __DIR__ . '/includes/functions/hooks/_collection_hooks.php';
    }

    if ( is_page_template( 'user-profile.php' ) ) {
      require_once __DIR__ . '/includes/functions/hooks/_profile_hooks.php';
    }

    if ( is_singular( 'post' ) ) {
      require_once __DIR__ . '/includes/functions/hooks/_post_hooks.php';
    }
  }
  add_action( 'wp', 'fictioneer_conditional_require_frontend_hooks' );

  /**
   * Add mobile menu hooks.
   */

  require_once __DIR__ . '/includes/functions/hooks/_mobile_menu_hooks.php';
}
