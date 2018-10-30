<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 26.10.2018
 * Time: 15:00
 */

namespace webivan\validateAction;

use webivan\validateAction\models\DocCommentModel;
use webivan\validateAction\models\IModel;
use webivan\validateAction\models\ParamsModel;
use yii\base\Behavior;
use yii\base\Controller;
use yii\base\DynamicModel;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;

class ValidateActionBehavior extends Behavior
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
            $method = $this->getMethod($event->sender, $event->method);

            /** @var IModel $model */

            if ($comment = $this->hasCommentValidate($method)) {
                $model = new DocCommentModel($event, $comment->getTagsByName('param'), $method);
            } else {
                $parameters = $method->getParameters();
                $model = new ParamsModel($event, $parameters, $method);
            }

            $validateModel = $model->createValidationModel();

            $validateModel->on(
                DynamicModel::EVENT_BEFORE_VALIDATE,
                function () use ($model, $validateModel) {
                    $this->beforeValidate($model, $validateModel);
                }
            );

            $validateModel->on(
                DynamicModel::EVENT_AFTER_VALIDATE,
                function () use ($event, $model) {
                    $this->updateParams($event, $model);
                }
            );

            $event->isValid = $validateModel->validate();
        } catch (\DomainException $e) {
            \Yii::error($e->getMessage());
        }
    }

    /**
     * @param IModel $model
     * @param DynamicModel $validateModel
     */
    protected function beforeValidate(IModel $model, DynamicModel $validateModel)
    {
        if ($model->hasErrors()) {
            foreach ($model->getErrors() as $attr => $errors) {
                $validateModel->addError($attr, $errors[0]);
            }
        }
    }

    /**
     * @param EventValidateAction $event
     * @param IModel $model
     */
    protected function updateParams(EventValidateAction $event, IModel $model)
    {
        if (!empty($model->getAttributes())) {
            $event->params = $model->getAttributes();
        }
    }

    /**
     * @param \ReflectionMethod $method
     * @return null|DocBlock
     */
    protected function hasCommentValidate(\ReflectionMethod $method)
    {
        $commentContent = $method->getDocComment();

        if (!empty($commentContent)) {
            $comment = $this->getComment($commentContent);

            if ($comment->hasTag('validate')) {
                return $comment;
            }
        }

        return null;
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
     * @param string $content
     * @return DocBlock
     */
    protected function getComment(string $content): DocBlock
    {
        $factory  = DocBlockFactory::createInstance();
        return $factory->create($content);
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
}