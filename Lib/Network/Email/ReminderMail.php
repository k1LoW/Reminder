<?php
App::uses('CakeEmail', 'Network/Email');
App::uses('ReminderConfigLoader', 'Reminder.Lib');
class ReminderMail {

    /**
     * sendResetMail
     *
     */
    public static function sendResetMail($data, $user){
        $modelName = $data['model'];
        $loader = ReminderConfigLoader::init($modelName);

        $email = new CakeEmail('reminder');
        $from = $email->from();
        if (empty($from)) {
            $email->from('reminder@example.com', 'Reminder');
        }
        $subject = $loader->load('subject');
        if (empty($subject)) {
            $subject = $email->subject();
        }
        if (empty($subject)) {
            $subject = 'Reminder'; // default
        }
        $email->subject($subject);
        $email->to($data['email']);
        $template = $loader->load('view.reset_mail');
        if (empty($template)) {
            $template = 'reset_mail';
        }
        $email->template('Reminder.' . $template);
        $email->viewVars(array(
            'data' => $data,
            'user' => $user,
        ));
        return $email->send();
    }

}
