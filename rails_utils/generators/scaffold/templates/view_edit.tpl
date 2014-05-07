<div id="sidebar">
  <h1>_{Tasks}:</h1>
  <ul>
    <li><?php  echo '<?php  echo  '?>$url_helper->link_to($text_helper->translate('Back to overview'), array('action' => 'listing'))?></li>
    <li><?php  echo '<?php  echo  '?>$url_helper->link_to($text_helper->translate('Show this <?php  echo AkInflector::humanize($singular_name)?>'), array('action' => 'show', 'id'=>$<?php  echo $singular_name?>->getId()))?></li>
  </ul> 
</div>

<div id="content">
  <h1>_{<?php  echo AkInflector::humanize($plural_name)?>}</h1>

  <?php  echo '<?php  echo '?> $form_tag_helper->start_form_tag(array('action'=>'edit', 'id' => $<?php  echo $singular_name?>->getId())) ?>

  <div class="form">
    <h2>_{Editing <?php  echo AkInflector::humanize($singular_name)?>}</h2>
    <?php  echo '<?php  echo  '?> $controller->renderPartial('form') ?>
  </div>

  <div id="operations">
    <?php  echo '<?php  echo '.$helper_var_name?>->save() ?> <?php  echo '<?php  echo  '.$helper_var_name?>->cancel()?>
  </div>

  <?php  echo '<?php  echo '?> $form_tag_helper->end_form_tag() ?>
</div>
