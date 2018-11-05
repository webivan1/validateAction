<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 30.10.2018
 * Time: 11:00
 */

namespace webivan\validateAction;

use webivan\validateAction\models\IFindColumn;
use webivan\validateAction\models\IFindItem;
use webivan\validateAction\contracts\IDriver;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\web\IdentityInterface;
use yii\web\User;

class InjectAction
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var IDriver
     */
    private $model;

    /**
     * @var string
     */
    private $name;

    /**
     * InjectAction constructor.
     * @param string $className
     * @param string $name
     * @param IDriver $model
     */
    public function __construct(string $className, string $name, IDriver $model)
    {
        $this->className = $className;
        $this->name = $name;
        $this->model = $model;
    }

    /**
     * @param object $model
     * @return bool
     */
    private function isTypeActiveRecord($model): bool
    {
        return $model instanceof ActiveRecord;
    }

    /**
     * @return object|null
     */
    private function hasContainer()
    {
        try {
            return \Yii::$container->get($this->className);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Run script
     */
    public function run()
    {
        if ($container = $this->hasContainer()) {
            if ($this->isTypeActiveRecord($container)) {
                $this->injectActiveRecord($container);
            } else {
                $this->model->setAttribute($this->name, $container);
            }
        } else {
            $this->model->setAttribute($this->name, null);
        }
    }

    /**
     * @param ActiveRecord $model
     */
    protected function injectActiveRecord(ActiveRecord $model)
    {
        $value = $this->model->getAttributes()[$this->name] ?? null;

        if ($user = $this->hasUserModel($model)) {
            $value = $user->isGuest ? null : $user->id;
        }

        $this->model->setAttribute($this->name, $this->findItemModel($model, $value));
    }

    /**
     * @param ActiveRecord $model
     * @return string
     */
    protected function getColumn(ActiveRecord $model)
    {
        if ($model instanceof IFindColumn) {
            return $model->columnForInjectAction();
        } else {
            return $model->primaryKey()[0];
        }
    }

    /**
     * @param ActiveRecord $model
     * @return bool|User
     */
    protected function hasUserModel(ActiveRecord $model)
    {
        return $model instanceof IdentityInterface && \Yii::$app->has('user')
            ? \Yii::$app->get('user')
            : false;
    }

    /**
     * @param ActiveRecord $model
     * @param mixed $value
     * @return null|ActiveRecord
     */
    protected function findItemModel(ActiveRecord $model, $value)
    {
        if ($model instanceof IFindItem) {
            return $model->findItemForInjectAction($value);
        } else {
            return $model->find()
                ->where([$this->getColumn($model) => Html::encode($value)])
                ->one();
        }
    }
}