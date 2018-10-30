<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 29.10.2018
 * Time: 11:34
 */

namespace webivan\validateAction;

use yii\helpers\Inflector;

trait ValidateActionTrait
{
    /**
     * {@inheritdoc}
     */
    public function runAction($id, $params = [])
    {
        $event = new EventValidateAction([
            'params' => $params,
            'action' => $id,
            'method' => $this->createMethodById($id)
        ]);

        $this->trigger(EventValidateAction::EVENT_NAME, $event);

        if (!$event->isValid) {
            return false;
        }

        return parent::runAction($id, $event->params);
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