<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 30.10.2018
 * Time: 11:30
 */

namespace webivan\validateAction\tests\controllers;

use webivan\validateAction\tests\models\TestActiveRecord;
use webivan\validateAction\ValidateActionBehavior;
use webivan\validateAction\ValidateActionTrait;
use yii\web\Request;
use yii\web\Response;
use yii\caching\FileCache;
use yii\web\Controller;

class InjectCommentsController extends Controller
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
     * @param Request $request
     * @return Request
     */
    public function actionTestRequest(Request $request)
    {
        return $request;
    }

    /**
     * @validate
     * @param \yii\web\Response $response
     * @return Response
     */
    public function actionTestResponse($response)
    {
        return $response;
    }

    /**
     * @validate
     * @param \yii\caching\FileCache $cache
     * @return FileCache
     */
    public function actionTestCache($cache)
    {
        return $cache;
    }

    /**
     * @validate
     * @param \yii\web\Request $request
     * @param int $param1
     * @param string|null $param2
     * @param \yii\web\Response $response
     * @return array
     */
    public function actionTestParams($request, $param1, $param2 = 'test', $response)
    {
        return compact('param1', 'param2', 'request', 'response');
    }

    /**
     * @validate
     * @param \webivan\validateAction\tests\models\TestActiveRecord|null $model
     * @return mixed
     */
    public function actionTestModel($model = null)
    {
        return $model;
    }

    /**
     * @validate
     * @param \webivan\validateAction\tests\models\TestActiveRecord $model
     * @return mixed
     */
    public function actionTestModelRequired($model)
    {
        return $model;
    }

    /**
     * @validate
     * @param \webivan\validateAction\tests\models\TestUser $user
     * @return \webivan\validateAction\tests\models\TestUser
     */
    public function actionTestUserModel($user)
    {
        return $user;
    }

    /**
     * @validate
     * @param \webivan\validateAction\tests\models\TestActiveRecord $model
     * @param \webivan\validateAction\tests\models\TestActiveRecord $injectTestModel
     * @return TestActiveRecord[]
     */
    public function actionTestModelEmpty($model, $injectTestModel)
    {
        return [$model, $injectTestModel];
    }

    /**
     * @validate
     * @param TestActiveRecord $injectTestModel
     * @param TestActiveRecord|null $model
     * @return TestActiveRecord[]
     */
    public function actionTestModelEmpty2(TestActiveRecord $injectTestModel, TestActiveRecord $model = null)
    {
        return [$injectTestModel, $model];
    }
}