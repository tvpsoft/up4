<?php

App::uses('AppModel', 'Model');

class Event extends AppModel {

    public $belongsTo = array(
        'Venue' => array(
            'className' => 'Venue',
            'foreignKey' => 'venue_id'
        )
    );

}

?>