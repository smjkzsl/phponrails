<div id="sidebar">
  <h1>_{Tasks}:</h1>
  <ul>
    <li><?php  echo  $url_helper->link_to($text_helper->translate('Back to overview'), array('action' => 'listing'))?></li>
    <li><?php  echo  $url_helper->link_to($text_helper->translate('Show this Post'), array('action' => 'show', 'id'=>$post->getId()))?></li>
  </ul> 
</div>

<div id="content">
  <h1>_{Posts}</h1>

  <?php  echo  $form_tag_helper->start_form_tag(array('action'=>'edit', 'id' => $post->getId())) ?>

  <div class="form">
    <h2>_{Editing Post}</h2>
    <?php  echo   $controller->renderPartial('form') ?>
  </div>

  <div id="operations">
    <?php  echo $post_helper->save() ?> <?php  echo  $post_helper->cancel()?>
  </div>

  <?php  echo  $form_tag_helper->end_form_tag() ?>
</div>
