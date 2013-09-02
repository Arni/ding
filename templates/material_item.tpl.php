<?php
/**
 * @file
 *
 */
?>
<div class="material-item <?php print $zebra; ?>">
  <div class="left-column">
    <div class="item-checkbox"><?php print $checkbox; ?></div>
    <?php print $cover; ?>
  </div>
  <div class="right-column">
    <h3 class="item-title"><?php print $title; ?></h3>
    <?php if (isset($material_message)) : ?>
    <span class="<?php print $material_message['class']; ?>"><?php print $material_message['message']; ?></span>
    <?php endif; ?>
    <ul class="item-information-list">
      <?php foreach ($information as $info) : ?>
        <li class="item-information <?php print $info['class']; ?>">
          <div class="item-information-label"><?php print $info['label']?>:</div>
          <div class="item-information-data"><?php print $info['data']; ?></div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
