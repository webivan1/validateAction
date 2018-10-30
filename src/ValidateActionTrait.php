<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 29.10.2018
 * Time: 11:34
 */

namespace webivan\validateAction;

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
        $this->eventValidate = new EventValidateAction([
            'params' => $params,
            'action' => $id,
            'method' => $this->createMethodById($id)
        ]);

        $this->trigger(EventValidateAction::EVENT_NAME, $this->eventValidate);

        $this->on(self::EVENT_BEFORE_ACTION, function (ActionEvent $event) {
            $event->isValid = $this->eventValidate->isValid;
        });

        return parent::runAction($id, $this->eventValidate->params);
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