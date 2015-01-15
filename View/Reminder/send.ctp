<?php echo $this->Form->create('Reminder', array(
          'url' => array(
              'plugin' => 'reminder',
              'controller' => 'reminder',
              'action' => 'send', $model
          )
      )); ?>
<?php echo $this->Form->input('email', array('type' => 'email')); ?>
<?php echo $this->Form->submit(__('Send')); ?>
<?php echo $this->Form->end(); ?>
