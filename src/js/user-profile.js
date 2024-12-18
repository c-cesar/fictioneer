// =============================================================================
// UNSET OAUTH CONNECTION
// =============================================================================

/**
 * Unset OAuth connection.
 *
 * @description Requests the deletion of an OAuth connection via AJAX, which
 * must be confirmed in a prompt with a localized confirmation string (DELETE
 * per default). In case this function is called maliciously, the request is
 * validated server-side as well.
 *
 * @since 4.5.0
 * @param {HTMLElement} button - The clicked button.
 */

function fcn_unsetOauth(button) {
  const confirm = prompt(button.dataset.warning);

  if (!confirm || confirm.toLowerCase() != button.dataset.confirm.toLowerCase()) {
    return;
  }

  const connection = _$$$(`oauth-${button.dataset.channel}`);

  // Mark as in-progress
  connection.classList.add('ajax-in-progress');

  // Request
  fcn_ajaxPost(payload = {
    'action': 'fictioneer_ajax_unset_my_oauth',
    'nonce': button.dataset.nonce,
    'channel': button.dataset.channel,
    'id': button.dataset.id
  })
  .then(response => {
    if (response.success) {
      // Successfully unset
      connection.classList.remove('_connected');
      connection.classList.add('_disconnected');
      connection.querySelector('button').remove();
      fcn_showNotification(connection.dataset.unset);
    } else {
      // Failed to unset
      connection.style.background = 'var(--notice-warning-background)';

      fcn_showNotification(
        response.data.failure ?? response.data.error ?? fictioneer_tl.notification.error,
        5,
        'warning'
      );

      // Make sure the actual error (if any) is printed to the console too
      if (response.data.error || response.data.failure) {
        console.error('Error:', response.data.error ?? response.data.failure);
      }
    }
  })
  .catch(error => {
    if (error.status && error.statusText) {
      connection.style.background = 'var(--notice-warning-background)';
      fcn_showNotification(`${error.status}: ${error.statusText}`, 5, 'warning');
    }

    console.error(error);
  })
  .then(() => {
    // Regardless of outcome
    connection.classList.remove('ajax-in-progress');
  });
}

// Listen to click on unset buttons
_$$('.button-unset-oauth').forEach(element => {
  element.addEventListener(
    'click',
    e => {
      fcn_unsetOauth(e.currentTarget);
    }
  );
});

// =============================================================================
// DELETE MY ACCOUNT
// =============================================================================

/**
 * Delete an user's account on request.
 *
 * @description Requests the deletion of an account via AJAX, which must be
 * confirmed in a prompt with a localized confirmation string (DELETE per
 * default). Roles above subscribers cannot delete their account this way.
 * In case this function is called maliciously, the request is validated
 * server-side as well.
 *
 * @since 4.5.0
 * @param {HTMLElement} button - The clicked button.
 */

function fcn_deleteMyAccount(button) {
  if (_$$$('button-delete-my-account').hasAttribute('disabled')) {
    return;
  }

  const confirm = prompt(button.dataset.warning);

  if (!confirm || confirm.toLowerCase() != button.dataset.confirm.toLowerCase()) {
    return;
  }

  // Disable button
  _$$$('button-delete-my-account').setAttribute('disabled', true);

  // Request
  fcn_ajaxPost({
    'action': 'fictioneer_ajax_delete_my_account',
    'nonce': button.dataset.nonce,
    'id': button.dataset.id
  })
  .then(response => {
    if (response.success) {
      // Successfully deleted
      localStorage.removeItem('fcnAuth');
      localStorage.removeItem('fcnProfileAvatar');
      location.reload();
    } else {
      // Could not be deleted
      _$$$('button-delete-my-account').innerHTML = response.data.button;

      fcn_showNotification(
        response.data.failure ?? response.data.error ?? fictioneer_tl.notification.error,
        5,
        'warning'
      );

      // Make sure the actual error (if any) is printed to the console too
      if (response.data.error || response.data.failure) {
        console.error('Error:', response.data.error ?? response.data.failure);
      }
    }
  })
  .catch(error => {
    if (error.status && error.statusText) {
      fcn_showNotification(`${error.status}: ${error.statusText}`, 5, 'warning');
      _$$$('button-delete-my-account').innerHTML = response.data.button;
    }

    console.error(error);
  });
}

// Listen for click on delete profile button
_$$$('button-delete-my-account')?.addEventListener(
  'click',
  e => {
    fcn_deleteMyAccount(e.currentTarget);
  }
);

// =============================================================================
// CLEAR DATA
// =============================================================================

const /** @const {DOMStringMap} */ fcn_profileDataTranslations = _$$$('profile-data-translations')?.dataset;

/**
 * Prompt with string submission before deletion of user data.
 *
 * @since 5.0.0
 * @param {HTMLElement} button - The clicked button.
 * @return {Boolean} True or false.
 */

function fcn_dataDeletionPrompt(button) {
  const confirm = prompt(button.dataset.warning);

  if (!confirm || confirm.toLowerCase() != button.dataset.confirm.toLowerCase()) {
    return false;
  }

  return true;
}

/**
 * AJAX request to clear specific data.
 *
 * @since 4.5.0
 * @param {HTMLElement} button - The clicked button.
 * @param {String} action - The action to perform.
 */

function fcn_clearData(button, action) {
  // Update view
  const card = button.closest('.card');

  // Clear web storage
  localStorage.removeItem('fcnBookshelfContent');

  // Indicator
  card.classList.add('ajax-in-progress');
  button.remove();

  // Request
  fcn_ajaxPost({
    'action': action,
    'fcn_fast_ajax': 1,
    'nonce': button.dataset.nonce
  })
  .then(response => {
    // Check for success
    if (response.success) {
      card.querySelector('.card__content').innerHTML = response.data.success;
    } else {
      fcn_showNotification(
        response.data.failure ?? response.data.error ?? fictioneer_tl.notification.error,
        10,
        'warning'
      );

      // Make sure the actual error (if any) is printed to the console too
      if (response.data.error || response.data.failure) {
        console.error('Error:', response.data.error ?? response.data.failure);
      }
    }
  })
  .catch(error => {
    if (error.status && error.statusText) {
      fcn_showNotification(`${error.status}: ${error.statusText}`, 10, 'warning');
    }

    console.error(error);
  })
  .then(() => {
    card.classList.remove('ajax-in-progress');
  });
}

// =============================================================================
// BUTTON: CLEAR COMMENTS
// =============================================================================

// Listen for click on clear comments button
_$('.button-clear-comments')?.addEventListener(
  'click',
  e => {
    // Confirm clear request using localized string
    if (!fcn_dataDeletionPrompt(e.currentTarget)) {
      return;
    }

    // Clear data
    fcn_clearData(e.currentTarget, 'fictioneer_ajax_clear_my_comments');
  }
);

// =============================================================================
// BUTTON: CLEAR COMMENT SUBSCRIPTIONS
// =============================================================================

// Listen for click on clear comment subscriptions button
_$('.button-clear-comment-subscriptions')?.addEventListener(
  'click',
  e => {
    // Confirm clear request using localized string
    if (!fcn_dataDeletionPrompt(e.currentTarget)) {
      return;
    }

    // Clear data
    fcn_clearData(e.currentTarget, 'fictioneer_ajax_clear_my_comment_subscriptions');
  }
);

// =============================================================================
// BUTTON: CLEAR CHECKMARKS
// =============================================================================

// Listen for click on clear checkmarks button
_$('.button-clear-checkmarks')?.addEventListener(
  'click',
  e => {
    // Confirm clear request using localized string
    if (!fcn_dataDeletionPrompt(e.currentTarget)) {
      return;
    }

    // Update local storage and view
    const currentUserData = fcn_getUserData();
    currentUserData.checkmarks = { 'data': {}, 'updated': Date.now() };
    fcn_setUserData(currentUserData);

    // Update views
    fcn_updateCheckmarksView();

    // Clear data
    fcn_clearData(e.currentTarget, 'fictioneer_ajax_clear_my_checkmarks', true);
  }
);

// =============================================================================
// BUTTON: CLEAR REMINDERS
// =============================================================================

// Listen for click on clear reminders button
_$('.button-clear-reminders')?.addEventListener(
  'click',
  e => {
    // Confirm clear request using localized string
    if (!fcn_dataDeletionPrompt(e.currentTarget)) {
      return;
    }

    // Update local storage and view
    const currentUserData = fcn_getUserData();
    currentUserData.reminders = { 'data': {} };
    fcn_setUserData(currentUserData);

    // Update view
    fcn_updateRemindersView();

    // Clear data
    fcn_clearData(e.currentTarget, 'fictioneer_ajax_clear_my_reminders', true);
  }
);

// =============================================================================
// BUTTON: CLEAR FOLLOWS
// =============================================================================

// Listen for click on clear follows button
_$('.button-clear-follows')?.addEventListener(
  'click',
  e => {
    // Confirm clear request using localized string
    if (!fcn_dataDeletionPrompt(e.currentTarget)) {
      return;
    }

    // Update local storage and view
    const currentUserData = fcn_getUserData();
    currentUserData.follows = { 'data': {} };
    fcn_setUserData(currentUserData);

    // Update view
    fcn_updateFollowsView();

    // Clear data
    fcn_clearData(e.currentTarget, 'fictioneer_ajax_clear_my_follows', true);
  }
);

// =============================================================================
// BUTTON: CLEAR BOOKMARKS
// =============================================================================

// Listen for click on clear bookmarks button
_$('.button-clear-bookmarks')?.addEventListener(
  'click',
  e => {
    // Confirm clear request using localized string
    if (!fcn_dataDeletionPrompt(e.currentTarget)) {
      return;
    }

    // Remove bookmarks
    const currentUserData = fcn_getUserData();
    currentUserData.bookmarks = '{}';
    fcn_setUserData(currentUserData);
    fcn_bookmarks.data = {};

    // Update view
    e.currentTarget.closest('.card').querySelector('.card__content').innerHTML = fcn_profileDataTranslations.clearedSuccess;

    // Update local storage and database
    fcn_setBookmarks(fcn_bookmarks);
  }
);
