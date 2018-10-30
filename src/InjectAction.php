<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 30.10.2018
 * Time: 11:00
 */

namespace webivan\validateAction;

use webivan\validateAction\models\IModel;
use yii\db\ActiveRecord;
use yii\di\ServiceLocator;

class InjectAction
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var IModel
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
     * @param IModel $model
     */
    public function __construct(string $className, string $name, IModel $model)
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

        if ($this->hasUserModel()) {
            $value = !\Yii::$app->user->isGuest
                ? $this->findItemModel($model, \Yii::$app->user->id)
                : null;
        } else {
            $value = empty($value)
                ? $model
                : $this->findItemModel($model, $value);
        }

        $this->model->setAttribute($this->name, $value);
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
     * @return bool
     */
    protected function hasUserModel(): bool
    {
        return \Yii::$app->has('user') && \Yii::$app->user->identityClass === $this->className;
    }

    /**
     * @param ActiveRecord $model
     * @param mixed $value
     * @return null|ActiveRecord
     */
    protected function findItemModel(ActiveRecord $model, $value)
    {
        try {
            $columnName = $this->getColumnSearch($model);
            $query = $model->find()->where([$columnName => $value]);
            return $query->one();
        } catch (\Exception $e) {
            return null;
        }
    }
}