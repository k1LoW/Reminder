<?php

App::uses('ReminderAppModel', 'Reminder.Model');
App::uses('ReminderMail', 'Reminder.Network/Email');
App::uses('ReminderException', 'Reminder.Error');

class Reminder extends ReminderAppModel {

    public $actsAs = array(
        'Yav.AdditionalValidationRules',
        'Yav.AdditionalValidationPatterns',
    );

    public $validate = array();

    /**
     * send
     *
     */
    public function send($data, $modelName){
        $models = Configure::read('Reminder.models');

        if (empty($data)) {
            return;
        }

        $emailField = $models[$modelName]['email'];
        $this->validate = array(
            $emailField => array(
                'notEmpty' => array(
                    'rule' => array('notEmpty'),
                    'required' => true,
                    'last' => true
                ),
                'email' => array(
                    'rule' => array('formatFuzzyEmail'),
                    'allowEmpty' => true,
                    'last' => true,
                ),
            ),
        );

        $model = ClassRegistry::init($modelName);
        
        // email validation only
        $this->create();
        $this->set($data[$modelName]);
        $result = $this->validates();
        if ($result === false) {
            $model->validationErrors = $this->validationErrors;
            throw new ReminderException();
        }

        $account = $model->findAccount($data);
        if (empty($account)) {
            return true;
        }

        $email = $data[$modelName][$emailField];
        $hash = sha1(uniqid('', true) . json_encode($account));

        $url = Router::url(array(
            'plugin' => 'reminder',
            'controller' => 'reminder',
            'action' => 'reset_password',
            $hash), true);

        $this->disactivate($modelName, $email);

        $data = array(
            'model' => $modelName,
            'model_id' => $account[$modelName][$model->primaryKey],
            'email' => $email,
            'hash' => $hash,
            'url' => $url,
        );

        $this->create();
        $this->set($data);
        $result = $this->save($data);
        if ($result === false) {
            throw new OutOfBoundsException(__('Could not save, please check your inputs.', true));
        }

        ReminderMail::send($data, $account);
        return true;
    }

    /**
     * disactivate
     *
     */
    public function disactivate($modelName, $email){
        $fields = array(
            'Reminder.expired' => "'" . date('Y-m-d H:i:s') . "'"
        );
        $conditions = array(
            'Reminder.model' => $modelName,
            'Reminder.email' => $email,
            'Reminder.expired' => null,
        );
        $result = $this->updateAll($fields, $conditions);
        if (!$result) {
            throw new InternalErrorException();
        }
    }

    /**
     * findReminder
     *
     */
    public function findReminder($hash){
        $models = Configure::read('Reminder.models');

        $query = array(
            'conditions' => array(
                'Reminder.hash' => $hash,
            ),
        );
        $result = $this->find('first', $query);

        if (empty($result)) {
            throw new NotFoundException();
        }
        return $result;
    }

    /**
     * findActiveReminder
     *
     */
    public function findActiveReminder($hash){
        $models = Configure::read('Reminder.models');

        $query = array(
            'conditions' => array(
                'Reminder.hash' => $hash,
                'expired' => null,
            ),
        );
        $result = $this->find('first', $query);

        if (empty($result)) {
            throw new UnauthorizedException();
        }

        $modelName = $result['Reminder']['model'];
        $created = $result['Reminder']['created'];
        $expire = $models[$modelName]['expire'];

        if (strtotime($created) > strtotime('+' . $expire . ' seconds')) {
            throw new UnauthorizedException();
        }

        return $result;
    }

    /**
     * findAccount
     *
     */
    public function findAccount($reminder){
        $modelName = $reminder['Reminder']['model'];
        $model_id = $reminder['Reminder']['model_id'];
        $model = ClassRegistry::init($modelName);
        $query = array(
            'conditions' => array(
                "{$model->alias}.{$model->primaryKey}" => $model_id
            )
        );
        $account = $model->find('first', $query);
        return $account;
    }

    /**
     * resetPassword
     *
     */
    public function resetPassword($data, $hash){
        if (empty($data)) {
            return;
        }

        $reminder = $this->findActiveReminder($hash);
        $modelName = $reminder['Reminder']['model'];
        $model_id = $reminder['Reminder']['model_id'];
        $model = ClassRegistry::init($modelName);

        $data[$modelName][$model->primaryKey] = $model_id;
        $result = $model->resetPassword($data);
        if ($result === true) {
            $reminder['Reminder']['expired'] = date('Y-m-d H:i:s');
            $this->save($reminder);
            return true;
        }
        return false;
    }
}
