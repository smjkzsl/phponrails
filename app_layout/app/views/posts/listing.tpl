<div id="sidebar">
  <h1>_{Tasks}:</h1>
  <ul>
    <li><?php  echo  $url_helper->link_to($text_helper->translate('Create new Post'), array('action' => 'add'))?></li>
  </ul> 
</div>

<div id="content">
  <h1>_{Posts}</h1>

  {?posts}
  <div class="listing">
  <table cellspacing="0" summary="_{Listing available Posts}">

  <tr>
    <?php  $content_columns = array_keys($Post->getContentColumns()); ?>
    {loop content_columns}
        <th scope="col"><?php  echo  $pagination_helper->sortable_link($content_column)?></th>
    {end}
    <th colspan="3" scope="col"><span class="auraltext">_{Item actions}</span></th>
  </tr>

  {loop posts}
    <tr {?post_odd_position}class="odd"{end}>
    {loop content_columns}
      <td class="field"><?php  echo  $post->get($content_column) ?></td>
    {end}
      <td class="operation"><?php  echo  $post_helper->link_to_show($post)?></td>
      <td class="operation"><?php  echo  $post_helper->link_to_edit($post)?></td>
      <td class="operation"><?php  echo  $post_helper->link_to_destroy($post)?></td>    
    </tr>
  {end}
   </table>
  </div>
  {end}
  
    {?post_pages.links}
        <div id="PostPagination">
        <div id="paginationHeader"><?php  echo translate('Showing page %page of %number_of_pages',array('%page'=>$post_pages->getCurrentPage(),'%number_of_pages'=>$post_pages->pages))?></div>
        {post_pages.links?}
        </div>
    {end}
  
</div>