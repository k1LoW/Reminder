<?php
/**
 * ReminderException
 *
 */
class ReminderException extends CakeException {

    public function __construct($message = null, $code = 500) {
        if (empty($message)) {
            $message = __('Reminder Error.');
        }
        parent::__construct($message, $code);
    }
}
