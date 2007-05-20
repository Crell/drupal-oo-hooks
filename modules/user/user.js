/* $Id$ */

/**
 * On the admin/user/settings page, conditionally show all of the
 * picture-related form elements depending on the current value of the
 * "Picture support" radio buttons.
 */
if (Drupal.jsEnabled) {
  $(document).ready(function () {
    $('div.user-admin-picture-radios input[@type=radio]').click(function () {
      $('div.user-admin-picture-settings')[['hide', 'show'][this.value]]();
    });
  });
}
