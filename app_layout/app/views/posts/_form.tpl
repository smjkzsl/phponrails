<?php  echo $active_record_helper->error_messages_for('post');?>


    <p>
        <label for="post_name">_{Name}</label><br />
        <?php  echo $active_record_helper->input('post', 'name')?>
    </p>
