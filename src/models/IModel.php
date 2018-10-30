<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 30.10.2018
 * Time: 8:35
 */

namespace webivan\validateAction\models;

use webivan\validateAction\EventValidateAction;
use yii\base\DynamicModel;

interface IModel
{
    /**
     * IModel constructor.
     * @param EventValidateAction $event
     * @param array $params
     * @param \ReflectionMethod $method
     */
    public function __construct(EventValidateAction $event, array $params, \ReflectionMethod $method);

    /**
     * @return array
     */
    public function getAttributes(): array;

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setAttribute(string $name, $value);

    /**
     * @return array
     */
    public function getRules(): array;

    /**
     * @return array
     */
    public function getErrors(): array;

    /**
     * @return boolean
     */
    public function hasErrors(): bool;

    /**
     * @param string $attr
     * @param string $message
     * @return void
     */
    public function addError(string $attr, string $message);

    /**
     * @return DynamicModel
     */
    public function createValidationModel(): DynamicModel;
}