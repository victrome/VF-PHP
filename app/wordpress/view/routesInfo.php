<?php
if (count($logs) > 0) :
  foreach ($logs as $key => $log) : ?>
    <div id="vfNotice<?php echo $key; ?>" class="notice notice-<?php echo $log['type']; ?>">
      <p>
        <strong><?php echo $log['title']; ?>: </strong>
        <?php echo $log['message']; ?>
      </p>
    </div>
  <?php endforeach; ?>
<?php else : ?>
  <div id="vfNotice0" class="notice notice-warning">
    <p>
      <strong>No new routes found</strong>
    </p>
  </div>
<?php endif; ?>