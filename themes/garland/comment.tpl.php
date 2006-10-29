<div class="comment<?php print ($comment->new) ? ' comment-new' : ''; print ($comment->status == COMMENT_NOT_PUBLISHED) ? ' comment-unpublished' : ''; print ' '. $zebra; ?>">
  <?php print $picture ?>

<?php if ($submitted): ?>
  <span class="submitted"><?php print t('!date — !username', array('!username' => theme('username', $comment), '!date' => format_date($comment->timestamp))); ?></span>
<?php endif; ?>

<?php if ($comment->new) : ?>
  <a id="new"></a>
  <span class="new"><?php print drupal_ucfirst($new) ?></span>
<?php endif; ?>

  <h3><?php print $title ?></h3>

  <div class="content">
    <?php print $content ?>
  </div>

  <?php if ($links): ?>
    <div class="links"><?php print $links ?></div>
  <?php endif; ?>
</div>
