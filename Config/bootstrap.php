<?php

// layout setting
Configure::write('Reminder.layout', 'default');

// setFlash settings
Configure::write('Reminder.setFlashElement', array(
    'success' => 'alert',
    'error' => 'alert',
));
Configure::write('Reminder.setFlashParams', array(
    'success' => array(),
    'error' => array(),
));
