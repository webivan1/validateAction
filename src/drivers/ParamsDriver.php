<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 30.10.2018
 * Time: 8:33
 */

namespace webivan\validateAction\drivers;

use webivan\validateAction\contracts\IDriver;
use webivan\validateAction\EventValidateAction;
use webivan\validateAction\helpers\ErrorsTrait;
use webivan\validateAction\InjectAction;
use yii\base\DynamicModel;

class ParamsDriver implements IDriver
{
    use ErrorsTrait;

    /**
     * @var EventValidateAction
     */
    private $event;

    /**
     * @var \ReflectionParameter[]
     */
    private $params;

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var array
     */
    private $rules = [];

    /**
     * @var array
     */
    private $required = [];

    /**
     * @var \ReflectionMethod
     */
    private $method;

    /**
     * @param EventValidateAction $event
     * @param \ReflectionParameter[] $params
     * @param \ReflectionMethod $method
     */
    public function __construct(EventValidateAction $event, array $params, \ReflectionMethod $method)
    {
        $this->event = $event;
        $this->params = $params;
        $this->method = $method;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setAttribute(string $name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @return DynamicModel
     */
    public function createValidationModel(): DynamicModel
    {
        /** @var \ReflectionParameter $param */
        foreach ($this->params as $param) {
            $this->attributes[$param->getName()] = $this->event->params[$param->getName()]
                ?? ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null);

            if (!$param->isDefaultValueAvailable()) {
                array_push($this->required, $param->getName());
            }

            $this->setRulesByType(
                $param->getType() ? $param->getType()->getName() : null,
                $param->getName()
            );
        }

        return DynamicModel::validateData($this->attributes, $this->rules);
    }

    /**
     * @param string|null $type
     * @param string $name
     * @return void
     */
    protected function setRulesByType($type, string $name)
    {
        $defaultRules = $this->ruleParams();

        !in_array($name, $this->required) ?: $this->addRule(['required'], $name);

        if (array_key_exists($type, $defaultRules)) {
            $this->addRule($defaultRules[$type], $name);
        } else if (!empty($type) && class_exists((string) $type)) {
            (new InjectAction($type, $name, $this))->run();
        }
    }

    /**
     * @param array $rule
     * @param string $name
     * @return void
     */
    public function addRule(array $rule, string $name)
    {
        array_unshift($rule, $name);
        array_push($this->rules, $rule);
    }

    /**
     * Default rule validation
     *
     * @return array
     */
    protected function ruleParams(): array
    {
        return [
            'int' => ['integer'],
            'bool' => ['boolean'],
            'string' => ['string'],
            'float' => ['number'],
            'array' => [function ($attr) {
                if (!is_array($this->attributes[$attr])) {
                    $this->addError($attr, "$attr in not array");
                }
            }]
        ];
    }
}