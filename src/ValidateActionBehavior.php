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
use yii\base\ErrorException;
use yii\base\ModelEvent;
use webivan\validateAction\contracts\IDriver;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;

class ValidateActionBehavior extends Behavior
{
    /**
     * @var string[]
     */
    public $actions = [];

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

        $this->handle($event);
    }

    /**
     * @param EventValidateAction $event
     * @return void
     */
    protected function handle(EventValidateAction $event)
    {
        $method = $this->getMethod($event->sender, $event->method);

        if ($comment = $this->hasCommentValidate($method)) {
            $model = $event->component->getDriverComment($event, $comment->getTagsByName('param'), $method);
        } else {
            $model = $event->component->getDriverParams($event, $method->getParameters(), $method);
        }

        $validateModel = $model->createValidationModel();

        $validateModel->on(
            DynamicModel::EVENT_BEFORE_VALIDATE,
            function (ModelEvent $modelEvent) use ($model, $event) {
                $this->beforeValidate($model, $modelEvent, $event);
            }
        );

        $validateModel->on(
            DynamicModel::EVENT_AFTER_VALIDATE,
            function () use ($event, $model) {
                $this->updateParams($event, $model);
            }
        );

        $event->isValid = $validateModel->validate();
    }

    /**
     * @param IDriver $model
     * @param ModelEvent $modelEvent
     * @param EventValidateAction $event
     */
    protected function beforeValidate(IDriver $model, ModelEvent $modelEvent, EventValidateAction $event)
    {
        $validateModel = $modelEvent->sender;
        !$model->hasErrors() ?: $validateModel->addErrors($model->getErrors());
        !$validateModel->hasErrors() ?: $event->setErrors($validateModel->getErrors());
    }

    /**
     * @param EventValidateAction $event
     * @param IDriver $model
     */
    protected function updateParams(EventValidateAction $event, IDriver $model)
    {
        empty($model->getAttributes()) ?: $event->params = $model->getAttributes();
    }

    /**
     * @param \ReflectionMethod $method
     * @return null|DocBlock
     */
    protected function hasCommentValidate(\ReflectionMethod $method)
    {
        $commentContent = $method->getDocComment();
        empty($commentContent) ?: $comment = $this->getComment($commentContent);

        if (isset($comment) && $comment->hasTag('validate')) {
            return $comment;
        }

        return null;
    }

    /**
     * @param Controller $controller
     * @param string $methodName
     * @return \ReflectionMethod
     * @throws ErrorException
     */
    protected function getMethod(Controller $controller, string $methodName): \ReflectionMethod
    {
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod($methodName);

        if (empty($method)) {
            throw new ErrorException('Undefined method ' . $methodName . ' in class ' . get_class($controller));
        }

        return $method;
    }

    /**
     * @param string $content
     * @return DocBlock
     */
    protected function getComment(string $content): DocBlock
    {
        return DocBlockFactory::createInstance()->create($content);
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