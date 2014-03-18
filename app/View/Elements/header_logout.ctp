<?php

if (!empty($this->Auth->user('username'))) {
    echo $this->Facebook->picture($facebook_id);
    echo $this->Facebook->logout(array('redirect' => array('controller' => 'users', 'action' => 'logout'), 'label' => __("Déconnexion", true)));
}
?>