<?php
Router::connect('/reminder/reset_password/', array(
    'plugin' => 'reminder', 'controller' => 'reminder', 'action' => 'reset_password')
);
Router::connect('/reminder/reset_password/*', array(
    'plugin' => 'reminder', 'controller' => 'reminder', 'action' => 'reset_password')
);

Router::connect('/reminder/complete/', array(
    'plugin' => 'reminder', 'controller' => 'reminder', 'action' => 'complete')
);
Router::connect('/reminder/complete/*', array(
    'plugin' => 'reminder', 'controller' => 'reminder', 'action' => 'complete')
);

Router::connect('/reminder/', array(
    'plugin' => 'reminder', 'controller' => 'reminder', 'action' => 'send'
));

Router::connect('/reminder/*', array(
    'plugin' => 'reminder', 'controller' => 'reminder', 'action' => 'send'
));

Router::connect('/reminder/:controller/:action/*', array(
    'plugin' => 'reminder')
);
