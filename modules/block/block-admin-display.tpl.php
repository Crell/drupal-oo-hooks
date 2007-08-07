<?php
// $Id$

/**
 * @file block-admin-display.tpl.php
 * Default theme implementation to configure blocks.
 *
 * Available variables:
 * - $block_listing: An array of block controls within regions.
 * - $form_submit: Form submit button.
 * - $throttle: TRUE or FALSE depending on throttle module being enabled.
 *
 * Each $data in $block_listing contains:
 * - $data->is_region_first: TRUE or FALSE depending on the listed blocks
 *   positioning. Used here to insert a region header.
 * - $data->region_title: Region title for the listed block.
 * - $data->block_title: Block title.
 * - $data->region_select: Drop-down menu for assigning a region.
 * - $data->weight_select: Drop-down menu for setting weights.
 * - $data->throttle_check: Checkbox to enable throttling.
 * - $data->configure_link: Block configuration link.
 * - $data->delete_link: For deleting user added blocks.
 *
 * @see template_preprocess_block_admin_display()
 * @see theme_block_admin_display()
 */
?>
<?php drupal_add_js('misc/tableheader.js'); ?>

<table id="blocks">
  <thead>
    <tr>
      <th><?php print t('Block'); ?></th>
      <th><?php print t('Region'); ?></th>
      <th><?php print t('Weight'); ?></th>
      <?php if ($throttle): ?>
        <th><?php print t('Throttle'); ?></th>
      <?php endif; ?>
      <th colspan="2"><?php print t('Operations'); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php $row = 0; ?>
    <?php foreach ($block_listing as $data): ?>
      <?php if ($data->is_region_first): ?>
      <tr class="<?php print $row % 2 == 0 ? 'odd' : 'even'; ?>">
        <td colspan="<?php print $throttle ? '7' : '6'; ?>" class="region"><?php print $data->region_title; ?></td>
      </tr>
      <?php $row++; ?>
      <?php endif; ?>
      <tr class="<?php print $row % 2 == 0 ? 'odd' : 'even'; ?>">
        <td class="block"><?php print $data->block_title; ?></td>
        <td><?php print $data->region_select; ?></td>
        <td><?php print $data->weight_select; ?></td>
        <?php if ($throttle): ?>
          <td><?php print $data->throttle_check; ?></td>
        <?php endif; ?>
        <td><?php print $data->configure_link; ?></td>
        <td><?php print $data->delete_link; ?></td>
      </tr>
      <?php $row++; ?>
    <?php endforeach; ?>
  </tbody>
</table>

<?php print $form_submit; ?>
