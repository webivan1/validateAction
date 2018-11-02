<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 30.10.2018
 * Time: 10:30
 */

namespace webivan\validateAction\tests;

use webivan\validateAction\tests\models\TestActiveRecord;
use webivan\validateAction\tests\models\TestUser;
use Yii;
use webivan\validateAction\tests\controllers\InjectParamController;
use yii\caching\FileCache;
use yii\web\IdentityInterface;
use yii\web\Request;
use yii\web\Response;

class ValidatorInjectParamsTest extends TestCase
{
    /**
     * @var InjectParamController
     */
    private $controller;

    protected function setUp()
    {
        $this->mockWebApplication();
        $this->controller = new InjectParamController('inject-param', Yii::$app);
    }

    public function testInjectRequest()
    {
        $request = Yii::$container->get(Request::class);
        $controller = clone $this->controller;
        $result = $controller->run('test-request', [
            'request' => 45
        ]);

        $this->assertEquals($result, $request);
    }

    public function testInjectResponse()
    {
        $response = Yii::$container->get(Response::class);
        $controller = clone $this->controller;
        $result = $controller->run('test-response', [
            'response' => 45,
            'request' => 'test'
        ]);

        $this->assertEquals($result, $response);
    }

    public function testInjectCache()
    {
        $cache = Yii::$container->get(FileCache::class);
        $controller = clone $this->controller;
        $result = $controller->run('test-cache', [
            'cache' => null,
            'response' => ['Response'],
            'request' => 'test'
        ]);

        $this->assertEquals($result, $cache);
    }

    public function testInjectWithParams()
    {
        $request = Yii::$container->get(Request::class);
        $response = Yii::$container->get(Response::class);

        $controller = clone $this->controller;
        $result = $controller->run('test-params', $params = [
            'param1' => 45
        ]);

        $this->assertEquals($result, array_merge($params, [
            'param2' => 'test',
            'request' => $request,
            'response' => $response
        ]));
    }

    public function testInjectModel()
    {
        $controller = clone $this->controller;
        $result = $controller->run('test-model');

        $this->assertNull($result);
    }

    public function testInjectModelParam()
    {
        $model = new TestActiveRecord();
        $controller = clone $this->controller;
        $result = $controller->run('test-model', [
            'model' => 1000
        ]);

        $this->assertEquals($model, $result);
    }

    public function testInjectModelParamRequired()
    {
        $controller = clone $this->controller;
        $result = $controller->run('test-model-required', [
            'model' => ['Array']
        ]);

        $this->assertNull($result);
    }

    public function testInjectUserModelLogged()
    {
        $testUserId = 1000;
        \Yii::$app->user->login(TestUser::findIdentity($testUserId));

        $this->assertFalse(\Yii::$app->user->getIsGuest());

        $controller = clone $this->controller;
        $result = $controller->run('test-user-model', [
            'user' => $testUserId
        ]);

        $this->assertTrue($result instanceof IdentityInterface);
        $this->assertEquals($testUserId, $result->id ?? null);
    }

    public function testInjectUserModelGuest()
    {
        $controller = clone $this->controller;

        $result1 = $controller->run('test-user-model', [
            'user' => 999
        ]);
        $result2 = $controller->run('test-user-model', [
            'user' => ['Test']
        ]);
        $result3 = $controller->run('test-user-model');

        $this->assertNull($result1);
        $this->assertNull($result2);
        $this->assertNull($result3);
    }
}