<div class="jumbotron">
    <?php
    echo $this->Facebook->login(array('redirect' => array('controller' => 'events', 'action' => 'index'),
        'perms' => 'email,publish_stream,user_birthday,user_events,user_friends,user_location,friends_events,friends_location',
        'width' => '174', 'height' => '25', 'img' => 'fb-connect.png'), __('Login with Facebook', true))
    ?>
</div>
