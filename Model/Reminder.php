<?php

App::uses('ReminderAppModel', 'Reminder.Model');
App::uses('ReminderMail', 'Reminder.Network/Email');

class Reminder extends ReminderAppModel {

    public $actsAs = array(
        'Cakeplus.ValidationPatterns',
        'Cakeplus.ValidationErrorI18n',
        'Cakeplus.AddValidationRule',
        'Yav.AdditionalValidationRules',
        'Yav.AdditionalValidationPatterns',
    );

    public $validate = array(
        'email' => array('require', 'notempty', 'fuzzy_email'),
    );

    /**
     * send
     *
     */
    public function send($data, $modelName){
        $models = Configure::read('Reminder.models');

        if (empty($data)) {
            return;
        }

        $this->create();
        $this->set($data);
        $result = $this->validates();
        if ($result === false) {
            throw new ValidationException();
        }

        $email = $data['Reminder']['email'];
        $model = ClassRegistry::init($modelName);
        $query = array(
            'conditions' => array(
                $model->alias . '.' . $models[$modelName]['email'] => $email
            ),
        );

        $current = $model->find('first', $query);
        if (empty($current)) {
            return true;
        }

        $hash = sha1(uniqid('', true) . json_encode($current));

        $url = Router::url(array(
            'plugin' => 'reminder',
            'controller' => 'reminder',
            'action' => 'reset_password',
            $hash), true);

        $this->disactivate($modelName, $email);

        $data = array(
            'model' => $modelName,
            'model_id' => $current[$modelName]['id'],
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

        ReminderMail::send($data, $current);
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
     * findUser
     *
     */
    public function findUser($reminder){
        $modelName = $reminder['Reminder']['model'];
        $model_id = $reminder['Reminder']['model_id'];
        $model = ClassRegistry::init($modelName);
        $user = $model->findById($model_id);
        return $user;
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
