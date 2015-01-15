<?php

// default layout setting
Configure::write('Reminder.layout', 'default');

// setFlash settings
Configure::write('Reminder.setFlashElement', array(
    'success' => 'default',
    'error' => 'default',
));
Configure::write('Reminder.setFlashParams', array(
    'success' => array(),
    'error' => array(),
));
