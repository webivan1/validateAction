<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 29.10.2018
 * Time: 11:40
 */

namespace webivan\validateAction;

use yii\base\Event;
use webivan\validateAction\contracts\IErrors;
use webivan\validateAction\components\ValidatorActionComponent;
use webivan\validateAction\helpers\ErrorsTrait;

class EventValidateAction extends Event implements IErrors
{
    use ErrorsTrait;

    const EVENT_NAME = 'beforeRunAction';

    /**
     * @var ValidatorActionComponent
     */
    public $component;

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