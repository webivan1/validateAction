<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 26.10.2018
 * Time: 15:00
 */

namespace webivan\validateAction;

use yii\base\Behavior;
use yii\base\Controller;
use yii\base\DynamicModel;
use yii\base\Model;
use yii\db\ActiveRecord;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\{
    Integer, Nullable, Object_, String_, Null_, Float_, Array_, Boolean, Compound
};

class ActionValidateBehavior extends Behavior
{
    /**
     * @var string[]
     */
    public $actions = [];

    /**
     * @var integer|null
     */
    public $cacheObjectParam = 1800;

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var array
     */
    private $rules = [];

    /**
     * @return array
     */
    public function events()
    {
        return [
            EventValidateAction::EVENT_NAME => 'beforeRunAction',
        ];
    }

    /**
     * @param EventValidateAction $event
     * @return void
     */
    public function beforeRunAction(EventValidateAction $event)
    {
        if (!$this->isActionAccess($event->action)) {
            return;
        }

        try {
            $method = $this->getMethod(
                $event->sender, $event->method
            );

            $comment = $this->getComment($method);

            $model = $this->createValidationModel($comment->getTagsByName('param'), $event->params);

            $model->on(DynamicModel::EVENT_AFTER_VALIDATE, function () use ($event) {
                // Update params
                $event->params = $this->attributes;
            });

            $event->isValid = $model->validate();
        } catch (\DomainException $e) {
            \Yii::error($e->getMessage());
        }
    }

    /**
     * @param Controller $controller
     * @param string $methodName
     * @return \ReflectionMethod
     * @throws \DomainException
     */
    protected function getMethod(Controller $controller, string $methodName): \ReflectionMethod
    {
        $reflection = new \ReflectionClass($controller);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $methods = array_filter($methods, function (\ReflectionMethod $method) use ($methodName) {
            return $method->getName() === $methodName;
        });

        if (empty($methods)) {
            throw new \DomainException('Undefined method ' . $methodName . ' in class ' . get_class($controller));
        }

        return array_shift($methods);
    }

    /**
     * @param \ReflectionMethod $method
     * @return DocBlock
     */
    protected function getComment(\ReflectionMethod $method): DocBlock
    {
        $factory  = DocBlockFactory::createInstance();
        return $factory->create($method->getDocComment());
    }

    /**
     * @param string $action
     * @return bool
     */
    protected function isActionAccess(string $action): bool
    {
        return empty($this->actions)
            || (!empty($this->actions) && in_array($action, $this->actions));
    }

    /**
     * @param Param[] $params
     * @param array $queryParams
     * @return Model
     */
    protected function createValidationModel(array $params, array $queryParams): Model
    {
        /** @var Param $param */
        foreach ($params as $param) {
            $this->attributes[$param->getVariableName()] = $queryParams[$param->getVariableName()]
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
     * @param Type[] $types
     * @param string $name
     * @return void
     */
    protected function setRulesByType(array $types, string $name)
    {
        $required = true;

        $ruleParams = self::ruleParams();

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

                try {
                    $this->objectValidation($name, $objectName);
                } catch (\DomainException $e) {
                    \Yii::error($e->getMessage());
                }
            }
        }

        if ($required) {
            array_push($this->rules, [$name, 'required']);
        }
    }

    /**
     * @param string $attrName
     * @param string $className
     */
    protected function objectValidation(string $attrName, string $className)
    {
        $value = &$this->attributes[$attrName];

        if (!class_exists($className)) {
            throw new \DomainException('Undefined class ' . $className);
        }

        $model = new $className;

        if (!$model instanceof ActiveRecord) {
            throw new \DomainException('Object is not ActiveRecord');
        }

        if ($this->hasUserModel($model)) {
            if (\Yii::$app->user->isGuest) {
                return;
            } else {
                $value = $this->findItemModel($model, \Yii::$app->user->id);
            }
        } else {
            $value = $this->findItemModel($model, $value);
        }
    }

    /**
     * @param ActiveRecord $model
     * @param mixed $value
     * @return null|ActiveRecord
     */
    protected function findItemModel(ActiveRecord $model, $value)
    {
        $columnName = $this->getColumnSearch($model);

        $query = $model->find()->where([$columnName => $value]);

        if ($this->cacheObjectParam) {
            $query->cache($this->cacheObjectParam);
        }

        return $query->one();
    }

    /**
     * @param ActiveRecord $model
     * @return mixed
     */
    protected function getColumnSearch(ActiveRecord $model)
    {
        return method_exists($model, 'getValidationColumnKey')
            ? $model->getValidationColumnKey()
            : $model->primaryKey()[0];
    }

    /**
     * @param ActiveRecord $model
     * @return bool
     */
    protected function hasUserModel(ActiveRecord $model): bool
    {
        return \Yii::$app->user->identityClass === get_class($model);
    }

    /**
     * Default rule validation
     *
     * @return array
     */
    protected static function ruleParams(): array
    {
        return [
            Integer::class => ['integer'],
            Boolean::class => ['boolean'],
            String_::class => ['string'],
            Float_::class => ['number'],
            Array_::class => [
                'filter',
                'filter' => function ($value) {
                    return is_array($value);
                }
            ]
        ];
    }
}