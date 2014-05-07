<div id="sidebar">
  <h1>_{Tasks}:</h1>
  <ul>
    <li><?php  echo  $url_helper->link_to($text_helper->translate('Back to overview'), array('action' => 'listing'))?></li>
    <li><?php  echo  $url_helper->link_to($text_helper->translate('Show this Post'), array('action' => 'show', 'id'=>$post->getId()))?></li>
  </ul> 
</div>


<div id="content">
  <h1>_{Posts}</h1>

  <p>_{Are you sure you want to delete this Post?}</p>
  <?php  echo  $form_tag_helper->start_form_tag(array('action' => 'destroy', 'id' => $post->getId())) ?>
  <?php  echo  $post_helper->confirm_delete() ?>
  <?php  echo  $form_tag_helper->end_form_tag() ?>
</div>
