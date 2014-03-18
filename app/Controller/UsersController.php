<?php

App::uses('AppController', 'Controller');

class UsersController extends AppController {

    public $uses = array();

    function beforeFilter() {
        $this->Auth->loginRedirect = array('controller'=>'events','action' => 'index');     //We want user to be redirected to index page after successfully login.    
    }

    function index() {                                 
    }

    function login() {                                 
    }

    function logout() {                                 
        $this->Session->destroy();
        $this->redirect($this->Auth->logout());
    }

}
