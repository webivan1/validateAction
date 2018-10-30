<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 30.10.2018
 * Time: 10:30
 */

namespace webivan\validateAction\tests;

use webivan\validateAction\tests\models\TestActiveRecord;
use Yii;
use webivan\validateAction\tests\controllers\InjectParamController;
use yii\caching\FileCache;
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
        $model = new TestActiveRecord();

        $controller = clone $this->controller;
        $result = $controller->run('test-model');

        $this->assertEquals($result, $model);
    }

    public function testInjectModelParam()
    {
        $controller = clone $this->controller;
        $result = $controller->run('test-model', [
            'model' => 1000
        ]);

        $this->assertNull($result);
    }
}