<?php
// $Id$
?>
<div class="<?php print $classes . ' ' . $zebra; ?>">

  <div class="clearfix">
  <?php if ($submitted): ?>
    <span class="submitted"><?php print $submitted; ?></span>
  <?php endif; ?>

  <?php if ($comment->new) : ?>
    <span class="new"><?php print drupal_ucfirst($new) ?></span>
  <?php endif; ?>

  <?php print $picture ?>

    <h3><?php print $title ?></h3>

    <div class="content">
      <?php print $content ?>
      <?php if ($signature): ?>
      <div class="clearfix">
        <div>—</div>
        <?php print $signature ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($links): ?>
    <div class="links"><?php print $links ?></div>
  <?php endif; ?>
</div>
