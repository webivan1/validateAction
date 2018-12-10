<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 30.10.2018
 * Time: 11:30
 */

namespace webivan\validateAction\tests\controllers;

use webivan\validateAction\tests\models\TestActiveRecord;
use webivan\validateAction\tests\models\TestUser;
use webivan\validateAction\ValidateActionBehavior;
use webivan\validateAction\ValidateActionTrait;
use yii\web\Request;
use yii\web\Response;
use yii\caching\FileCache;
use yii\web\Controller;

class InjectParamController extends Controller
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

    public function actionTestRequest(Request $request)
    {
        return $request;
    }

    public function actionTestResponse(Response $response)
    {
        return $response;
    }

    public function actionTestCache(FileCache $cache)
    {
        return $cache;
    }

    public function actionTestParams(Request $request, int $param1, string $param2 = 'test', Response $response)
    {
        return compact('param1', 'param2', 'request', 'response');
    }

    public function actionTestModel(TestActiveRecord $model = null)
    {
        return $model;
    }

    public function actionTestModelRequired(TestActiveRecord $model)
    {
        return $model;
    }

    public function actionTestUserModel(TestUser $user)
    {
        return $user;
    }

    public function actionTestModelEmpty(TestActiveRecord $model, TestActiveRecord $injectTestModel)
    {
        return [$model, $injectTestModel];
    }

    public function actionTestModelEmpty2(TestActiveRecord $injectTestModel)
    {
        return $injectTestModel;
    }
}