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

### Setting Reminder.models

```php
<?php
  Configure::write('Reminder.models', [
    'User' => [
      'email' => 'email',
      'password' => 'password',
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

- app/View/Plugin/Reminder/Reminder/send.ctp
- app/View/Plugin/Reminder/Reminder/reset_password.ctp
- app/View/Plugin/Reminder/Reminder/complete.ctp

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
      'password' => 'password',
      'expire' => 60 * 60 * 24,
      'layout' => 'User/default'
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
