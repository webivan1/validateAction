<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 05.11.2018
 * Time: 15:52
 */

namespace webivan\validateAction\contracts;

use webivan\validateAction\EventValidateAction;
use yii\base\DynamicModel;

interface IDriver extends IErrors
{
    /**
     * IDriver constructor.
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
     * @param array $rule
     * @param string $name
     * @return void
     */
    public function addRule(array $rule, string $name);

    /**
     * @return DynamicModel
     */
    public function createValidationModel(): DynamicModel;
}