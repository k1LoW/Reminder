<?php
App::uses('CakeEmail', 'Network/Email');
class ReminderMail {

    /**
     * send
     *
     */
    public static function send($data, $user){
        $email = new CakeEmail('reminder');
        $from = $email->from();
        if (empty($from)) {
            $email->from('reminder@example.com', 'Reminder');
        }
        $subject = $email->subject();
        if (empty($subject)) {
            $email->subject('Reminder');
        }
        $email->to($data['email']);
        $email->template('Reminder.reminder');
        $email->viewVars(array(
            'data' => $data,
            'user' => $user,
        ));
        return $email->send();
    }

}
