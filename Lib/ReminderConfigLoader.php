<?php

/**
 * ReminderConfigLoader
 *
 */
class ReminderConfigLoader{

    private static $_instance = null;
    private $modelName = null;

    public function __construct($modelName){
        $models = Configure::read('Reminder.models');
        if (!array_key_exists($modelName, $models)) {
            throw new NotFoundException();
        }
        $this->modelName = $modelName;
    }

    public static function init($modelName) {
        if(is_null(self::$_instance)) {
            self::$_instance = new self($modelName);
        }
        return self::$_instance;
    }

    /**
     * load
     *
     */
    public function load($key){
        $models = Configure::read('Reminder.models');
        return Hash::get($models, $this->modelName . '.' . $key);
    }

}
