<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 30.10.2018
 * Time: 8:33
 */

namespace webivan\validateAction\models;

use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\{
    Integer, Nullable, Object_, String_, Null_, Float_, Array_, Boolean, Compound
};
use webivan\validateAction\EventValidateAction;
use webivan\validateAction\InjectAction;
use yii\base\DynamicModel;

class ParamsModel implements IModel
{
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
     * @var array
     */
    private $errors = [];

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

        if (in_array($name, $this->required)) {
            array_push($this->rules, [$name, 'required']);
        }

        if (!$type) {
            return;
        }

        if (isset($defaultRules[$type])) {
            $rule = $defaultRules[$type];
            array_unshift($rule, $name);
            array_push($this->rules, $rule);
        } else if (class_exists($type)) {
            $inject = new InjectAction($type, $name, $this);
            $inject->run();
        }
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

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param string $attr
     * @param string $message
     * @return void
     */
    public function addError(string $attr, string $message)
    {
        if (isset($this->errors[$attr])) {
            $this->errors[$attr][] = $message;
        } else {
            $this->errors[$attr] = [];
            $this->addError($attr, $message);
        }
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
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
}