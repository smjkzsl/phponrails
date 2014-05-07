<div id="sidebar">
  <h1>_{Tasks}:</h1>
  <ul>
    <li><?php  echo  $url_helper->link_to($text_helper->translate('Edit this Post'), array('action' => 'edit', 'id'=>$post->getId()))?></li>
    <li><?php  echo  $url_helper->link_to($text_helper->translate('Back to overview'), array('action' => 'listing'))?></li>
  </ul> 
</div>

<div id="content">
  <h1>_{Posts}</h1>

  <div class="show">
    <?php  $content_columns = array_keys($Post->getContentColumns()); ?>
    {loop content_columns}
      <label><?php  echo  $text_helper->translate($text_helper->humanize($content_column))?>:</label> <span class="static"><?php  echo  $post->get($content_column) ?></span><br />
    {end}
  </div>
</div>