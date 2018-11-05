<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 05.11.2018
 * Time: 15:58
 */

namespace webivan\validateAction\components;

use webivan\validateAction\contracts\IDriver;
use webivan\validateAction\drivers\DocCommentDriver;
use webivan\validateAction\drivers\ParamsDriver;
use yii\base\Component;

class ValidatorActionComponent extends Component
{
    /**
     * @var string
     */
    public $phpdocDriver = DocCommentDriver::class;

    /**
     * @var string
     */
    public $paramsDriver = ParamsDriver::class;

    /**
     * @return IDriver
     */
    public function getDriverComment(): IDriver
    {
        return new $this->phpdocDriver(...func_get_args());
    }

    /**
     * @return IDriver
     */
    public function getDriverParams(): IDriver
    {
        return new $this->paramsDriver(...func_get_args());
    }
}