<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 30.10.2018
 * Time: 8:33
 */

namespace webivan\validateAction\drivers;

use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\{
    Integer, Nullable, Object_, String_, Null_, Float_, Array_, Boolean, Compound
};
use webivan\validateAction\contracts\IDriver;
use webivan\validateAction\EventValidateAction;
use webivan\validateAction\InjectAction;
use webivan\validateAction\helpers\ErrorsTrait;
use yii\base\DynamicModel;

class DocCommentDriver implements IDriver
{
    use ErrorsTrait;

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
            $className = get_class($type);

            if ($type instanceof Nullable || $type instanceof Null_) {
                $required = false;
            }

            if (array_key_exists($className, $ruleParams)) {
                $this->addRule($ruleParams[$className], $name);
            }

            if ($type instanceof Object_) {
                $className = $this->getClassName($name, $type);

                if (class_exists($className)) {
                    (new InjectAction($className, $name, $this))->run();
                }
            }
        }

        !$required ?: $this->addRule(['required'], $name);
    }

    /**
     * @param string $name
     * @param Object_ $type
     * @return string
     */
    protected function getClassName(string $name, Object_ $type): string
    {
        $params = $this->method->getParameters();
        $className = $type->getFqsen();

        foreach ($params as $param) {
            if ($param->getName() === $name && $param->getType()) {
                $className = $param->getType()->getName();
            }
        }

        return $className;
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
}