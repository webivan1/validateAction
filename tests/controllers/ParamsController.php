<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 29.10.2018
 * Time: 15:40
 */

namespace webivan\validateAction\tests\controllers;

use webivan\validateAction\ValidateActionBehavior;
use webivan\validateAction\ValidateActionTrait;
use yii\web\Controller;

class ParamsController extends Controller
{
    use ValidateActionTrait;

    public function behaviors()
    {
        return [
            'validator' => [
                'class' => ValidateActionBehavior::class
            ]
        ];
    }

    public function actionTestInteger(int $data)
    {
        return $data;
    }

    public function actionTestString(string $data)
    {
        return $data;
    }

    public function actionTestFloat(float $data)
    {
        return $data;
    }

    public function actionTestBoolean(bool $data)
    {
        return $data;
    }

    public function actionTestArray(array $data)
    {
        return $data;
    }

    public function actionTestManyTypes(
        int $param1,
        string $param2,
        float $param3,
        bool $param4,
        array $param5,
        int $default = 10
    )
    {
        return compact('param1', 'param2', 'param3', 'param4', 'param5', 'default');
    }
}