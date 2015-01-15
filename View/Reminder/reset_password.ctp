<?php echo $this->Form->create($modelName, array(
          'url' => array(
              'plugin' => 'reminder',
              'controller' => 'reminder',
              'action' => 'reset_password', $hash
          )
      )
      ); ?>
<?php echo $this->Form->input('password', array('type' => 'password')); ?>
<?php echo $this->Form->input('password_confirm', array('type' => 'password')); ?>
<?php echo $this->Form->submit(__('Send')); ?>
<?php echo $this->Form->end(); ?>
