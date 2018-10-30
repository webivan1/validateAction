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

class CommentsController extends Controller
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

    /**
     * @validate
     * @param int $data
     * @return int
     */
    public function actionTestInteger($data)
    {
        return $data;
    }

    /**
     * @validate
     * @param string $data
     * @return string
     */
    public function actionTestString($data)
    {
        return $data;
    }

    /**
     * @validate
     * @param float $data
     * @return float
     */
    public function actionTestFloat($data)
    {
        return $data;
    }

    /**
     * @validate
     * @param bool $data
     * @return bool
     */
    public function actionTestBoolean($data)
    {
        return $data;
    }

    /**
     * @validate
     * @param array $data
     * @return array
     */
    public function actionTestArray(array $data)
    {
        return $data;
    }

    /**
     * @validate
     * @param int $param1
     * @param string $param2
     * @param float $param3
     * @param bool $param4
     * @param array $param5
     * @param int $default
     * @return array
     */
    public function actionTestManyTypes(
        $param1,
        $param2,
        $param3,
        $param4,
        array $param5,
        $default = 10
    )
    {
        return compact('param1', 'param2', 'param3', 'param4', 'param5', 'default');
    }
}