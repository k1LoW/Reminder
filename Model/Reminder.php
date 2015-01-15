<?php

App::uses('ReminderAppModel', 'Reminder.Model');
App::uses('ReminderMail', 'Reminder.Network/Email');
App::uses('ReminderException', 'Reminder.Error');
App::uses('ReminderConfigLoader', 'Reminder.Lib');

class Reminder extends ReminderAppModel {

    public $validate = array();

    /**
     * send
     *
     */
    public function send($data, $modelName){
        $loader = ReminderConfigLoader::init($modelName);

        if (empty($data)) {
            return;
        }

        $model = ClassRegistry::init($modelName);        
        $account = $model->findAccount($data);
        if (empty($account)) {
            return true;
        }
        
        $emailField = $loader->load('email');
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

        ReminderMail::sendResetMail($data, $account);
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
        $loader = ReminderConfigLoader::init($modelName);

        $created = $result['Reminder']['created'];
        $expire = $loader->load('expire');

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
