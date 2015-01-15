<?php echo $this->Form->create($modelName, array(
          'url' => array(
              'plugin' => 'reminder',
              'controller' => 'reminder',
              'action' => 'send', $model
          )
      )); ?>
<?php echo $this->Form->input($emailField, array('type' => 'email')); ?>
<?php echo $this->Form->submit(__('Send')); ?>
<?php echo $this->Form->end(); ?>
