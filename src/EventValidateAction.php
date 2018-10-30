<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 29.10.2018
 * Time: 11:40
 */

namespace webivan\validateAction;

use yii\base\Event;

class EventValidateAction extends Event
{
    const EVENT_NAME = 'beforeRunAction';

    /**
     * @var array
     */
    public $params;

    /**
     * @var string
     */
    public $action;

    /**
     * @var string
     */
    public $method;

    /**
     * @var boolean
     */
    public $isValid = true;
}