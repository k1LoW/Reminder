# Password Reminder plugin for CakePHP

## Usage

### Create reminders table

    $ cake schema create reminders --plugin Reminder

### Load Reminder plugin

```php
<?php
  CakePlugin::load([
    'Reminder' => ['bootstrap' => true, 'routes' => true],
  ]);
```

### Set $reminder to app/Config/email.php

```php
<?php
  public $reminder = array(
    'transport' => 'Smtp',
    'subject' => 'Password reminder',
    'from' => array('reminder@example.com' => 'Reminder'),
    'host' => 'smtp.example.com',
    'username' => 'reminder@example.com',
    'password' => 'xxxxxxxxx',
    'log' => true,
    'charset' => 'utf-8',
    'headerCharset' => 'utf-8',
  );
```

### Set Reminder.models

```php
<?php
  Configure::write('Reminder.models', [
    'User' => [
      'email' => 'email',
      'expire' => 60 * 60 * 24,
    ],
  ]);
```

### Create User::resetPassword()

```php
<?php
  /**
   * resetPassword
   * for Reminder plugin
   *
   */
  public function resetPassword($data){
    $this->set($data);
    if (!empty($this->data['User']['password'])) {
      $this->data['User']['password'] = Security::hash($this->data['User']['password'], null, true);
    }
    if (!empty($this->data['User']['password_confirm'])) {
      $this->data['User']['password_confirm'] = Security::hash($this->data['User']['password_confirm'], null, true);
    }
    $result = $this->save(null, true);
    if ($result) {
      $this->data = $result;
      return true;
    } else {
      return false;
    }
  }
```

### Access /reminder/user

Access /reminder/user

## Design change

### View files

Create following view files.

- app/View/Plugin/Reminder/Reminder/send.ctp
- app/View/Plugin/Reminder/Reminder/reset_password.ctp
- app/View/Plugin/Reminder/Reminder/complete.ctp
- app/View/Plugin/Reminder/Emails/text/reminder.ctp

### Layout

```php
<?php
  // default layout setting
  Configure::write('Reminder.layout', 'ajax');
```

or

```php
<?php
  Configure::write('Reminder.models', [
    'User' => [
      'email' => 'email',
      'expire' => 60 * 60 * 24,
      'layout' => 'User/default', // User layout setting
    ],
    'Administrator' => [
      'email' => 'email',
      'expire' => 60 * 60,
      'layout' => 'Admin/default', // Administrator layout setting
    ],
  ]);
```

### setFlash Messages

```php
<?php
  // setFlash settings
  Configure::write('Reminder.setFlashElement', array(
      'success' => 'alert',
      'error' => 'alert',
  ));
  Configure::write('Reminder.setFlashParams', array(
      'success' => array(),
      'error' => array(),
  ));
```
