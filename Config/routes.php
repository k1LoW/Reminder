<?php

Router::connect('/reminder/:controller/:action/*', array(
    'plugin' => 'Reminder')
);

Router::connect('/reminder/*', array(
    'plugin' => 'Reminder', 'controller' => 'reminder', 'action' => 'send')
);
