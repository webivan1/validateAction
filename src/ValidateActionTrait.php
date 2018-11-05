<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 29.10.2018
 * Time: 11:34
 */

namespace webivan\validateAction;

use webivan\validateAction\components\ValidatorActionComponent;
use yii\base\ActionEvent;
use yii\helpers\Inflector;

trait ValidateActionTrait
{
    /**
     * @var EventValidateAction
     */
    public $eventValidate;

    /**
     * {@inheritdoc}
     */
    public function runAction($id, $params = [])
    {
        $component = $this->initValidatorComponent();

        if ($component instanceof ValidatorActionComponent) {
            $this->eventValidate = new EventValidateAction([
                'component' => $component,
                'params' => $params,
                'action' => $id,
                'method' => $this->createMethodById($id)
            ]);

            $this->trigger(EventValidateAction::EVENT_NAME, $this->eventValidate);

            $this->on(self::EVENT_BEFORE_ACTION, function (ActionEvent $event) {
                $event->isValid = $this->eventValidate->isValid;
            });

            $params = $this->eventValidate->params;
        }

        return parent::runAction($id, $params);
    }

    /**
     * @return object
     */
    protected function initValidatorComponent()
    {
        return \Yii::$app->has('validator')
            ? \Yii::$app->get('validator')
            : \Yii::$container->get(ValidatorActionComponent::class);
    }

    /**
     * @param string $id
     * @return string
     */
    public function createMethodById(string $id)
    {
        return 'action' . Inflector::id2camel($id);
    }
}