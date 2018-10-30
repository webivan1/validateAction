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

class DocCommentModel implements IModel
{
    /**
     * @var EventValidateAction
     */
    private $event;

    /**
     * @var Param[]
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
    private $errors = [];

    /**
     * @var \ReflectionMethod
     */
    private $method;

    /**
     * @param EventValidateAction $event
     * @param Param[] $params
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
        /** @var Param $param */
        foreach ($this->params as $param) {
            $this->attributes[$param->getVariableName()] = $this->event->params[$param->getVariableName()]
                ?? $this->getDefaultValue($param->getVariableName())
                ?? null;

            $types = [];

            if ($param->getType() instanceof Compound) {
                $iterator = $param->getType()->getIterator();
                $iterator->rewind();

                /** @var Type $typeParam */
                foreach ($iterator as $typeParam) {
                    array_push($types, $typeParam);
                }
            } else {
                array_push($types, $param->getType());
            }

            $this->setRulesByType($types, $param->getVariableName());
        }

        return DynamicModel::validateData($this->attributes, $this->rules);
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    protected function getDefaultValue(string $name)
    {
        foreach ($this->method->getParameters() as $parameter) {
            if ($parameter->getName() === $name && $parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }
        }

        return null;
    }

    /**
     * @param Type[] $types
     * @param string $name
     * @return void
     */
    protected function setRulesByType(array $types, string $name)
    {
        $required = true;

        $ruleParams = $this->ruleParams();

        /** @var Type $type */
        foreach ($types as $type) {
            if ($type instanceof Nullable || $type instanceof Null_) {
                $required = false;
                continue;
            }

            if (isset($ruleParams[get_class($type)])) {
                /** @var array $rule */
                $rule = $ruleParams[get_class($type)];
                array_unshift($rule, $name);
                array_push($this->rules, $rule);
            }

            if ($type instanceof Object_) {
                $objectName = $type->getFqsen();

                if (class_exists($objectName)) {
                    $inject = new InjectAction($objectName, $name, $this);
                    $inject->run();
                }
            }
        }

        if ($required) {
            array_push($this->rules, [$name, 'required']);
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
            Integer::class => ['integer'],
            Boolean::class => ['boolean'],
            String_::class => ['string'],
            Float_::class => ['number'],
            Array_::class => [function ($attr) {
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