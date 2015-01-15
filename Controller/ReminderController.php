<?php

App::uses('ReminderAppController', 'Reminder.Controller');
App::uses('ReminderConfigLoader', 'Reminder.Lib');

class ReminderController extends ReminderAppController {

    public $uses = array(
    );

    /**
     * beforeFilter
     *
     */
    public function beforeFilter(){
        parent::beforeFilter();
        if (!empty($this->Auth)) {
            $this->Auth->allow();
        }
        $models = Configure::read('Reminder.models');
        if (empty($models)) {
            throw new InternalErrorException();
        }

        $this->layout = Configure::read('Reminder.layout');
    }

    /**
     * send
     *
     */
    public function send($model){
        $models = Configure::read('Reminder.models');
        $modelName = Inflector::classify($model);

        $loader = ReminderConfigLoader::init($modelName);
        $layout = $loader->load('layout');

        try {
            $this->Reminder = ClassRegistry::init('Reminder.Reminder');
            $result = $this->Reminder->send($this->request->data, $modelName);
            if ($result === true) {
                $this->request->data = null;
                $this->Session->setFlash(
                    __('Reminder mail has been sent'),
                    Configure::read('Reminder.setFlashElement.success'),
                    Configure::read('Reminder.setFlashParams.success'));
                $view = $loader->load('view.sent');
                if (empty($view)) {
                    $view = 'sent';
                }
                return $this->render($view, $layout);
            }
        } catch (ReminderException $e) {
            $this->Session->setFlash($e->getMessage(),
            Configure::read('Reminder.setFlashElement.error'),
            Configure::read('Reminder.setFlashParams.error'));
        }

        $emailField = $loader->load('email');

        $this->set(array(
            'model' => $model,
            'modelName' => $modelName,
            'emailField' => $emailField,
        ));

        $view = $loader->load('view.send');
        if (empty($view)) {
            $view = 'send';
        }
        return $this->render($view, $layout);
    }

    /**
     * reset_password
     *
     */
    public function reset_password($hash){
        if (empty($hash)) {
            throw new NotFoundException();
        }

        try {
            $result = $this->Reminder->resetPassword($this->request->data, $hash);

            if ($result === true) {
                $this->Session->setFlash(
                    __('Password reset complete'),
                    Configure::read('Reminder.setFlashElement.success'),
                    Configure::read('Reminder.setFlashParams.success'));
                $this->redirect(array(
                    'action' => 'complete', $hash
                ));
            } elseif ($result === false) {
                $this->Session->setFlash(__('Validation Error'),
                Configure::read('Reminder.setFlashElement.error'),
                Configure::read('Reminder.setFlashParams.error'));
            }
        } catch (ReminderException $e) {
            $this->Session->setFlash($e->getMessage(),
            Configure::read('Reminder.setFlashElement.error'),
            Configure::read('Reminder.setFlashParams.error'));
        }

        $reminder = $this->Reminder->findActiveReminder($hash);
        $modelName = $reminder['Reminder']['model'];
        $loader = ReminderConfigLoader::init($modelName);
        $layout = $loader->load('layout');

        $account = $this->Reminder->findAccount($reminder);
        if (empty($account)) {
            throw new NotFoundException();
        }

        $this->set(array(
            'hash' => $hash,
            'account' => $account,
            'modelName' => $modelName,
        ));

        $view = $loader->load('view.reset_password');
        if (empty($view)) {
            $view = 'reset_password';
        }
        return $this->render($view, $layout);
    }

    /**
     * complete
     *
     */
    public function complete($hash){
        $reminder = $this->Reminder->findReminder($hash);
        $modelName = $reminder['Reminder']['model'];
        $loader = ReminderConfigLoader::init($modelName);
        $layout = $loader->load('layout');

        $account = $this->Reminder->findAccount($reminder);
        if (empty($account)) {
            throw new NotFoundException();
        }

        $this->set(array(
            'hash' => $hash,
            'account' => $account,
            'modelName' => $modelName,
        ));

        $view = $loader->load('view.complete');
        if (empty($view)) {
            $view = 'complete';
        }
        return $this->render($view, $layout);
    }
}
