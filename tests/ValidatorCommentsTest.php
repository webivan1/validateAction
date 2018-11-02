<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 30.10.2018
 * Time: 10:30
 */

namespace webivan\validateAction\tests;

use webivan\validateAction\tests\controllers\CommentsController;
use Yii;

class ValidatorCommentsTest extends TestCase
{
    /**
     * @var CommentsController
     */
    private $controller;

    protected function setUp()
    {
        $this->mockWebApplication();
        $this->controller = new CommentsController('comments', Yii::$app);
    }

    public function testValidInteger()
    {
        $controller = clone $this->controller;
        $result = $controller->run('test-integer', [
            'data' => 45
        ]);

        $this->assertEquals($result, 45);
    }

    public function testInvalidInteger()
    {
        $controller = clone $this->controller;
        $result = $controller->run('test-integer', [
            'data' => 'string'
        ]);

        $this->assertNull($result);
    }

    public function testValidString()
    {
        $controller = clone $this->controller;
        $result = $controller->run('test-string', [
            'data' => 'String'
        ]);

        $this->assertEquals($result, 'String');
    }

    public function testInvalidString()
    {
        $controller = clone $this->controller;
        $result = $controller->run('test-string', [
            'data' => 45.5
        ]);

        $this->assertNull($result);
    }

    public function testValidFloat()
    {
        $controller = clone $this->controller;
        $result = $controller->run('test-float', [
            'data' => 456.65
        ]);

        $this->assertEquals($result, 456.65);
    }

    public function testInvalidFloat()
    {
        $controller = clone $this->controller;
        $result = $controller->run('test-float', [
            'data' => ['Array']
        ]);

        $this->assertNull($result);
    }

    public function testValidBoolean()
    {
        $controller = clone $this->controller;
        $result = $controller->run('test-boolean', [
            'data' => true
        ]);

        $this->assertTrue($result);
    }

    public function testInvalidBoolean()
    {
        $controller = clone $this->controller;
        $result = $controller->run('test-boolean', [
            'data' => 'bool'
        ]);

        $this->assertNull($result);
    }

    public function testValidArray()
    {
        $controller = clone $this->controller;
        $result = $controller->run('test-array', [
            'data' => ['Array', 'Array']
        ]);

        $this->assertEquals($result, ['Array', 'Array']);
    }

    public function testInvalidArray()
    {
        $controller = clone $this->controller;
        $result = $controller->run('test-array', [
            'data' => 'String'
        ]);

        $this->assertNull($result);
    }

    public function testValidManyTypes()
    {
        $controller = clone $this->controller;
        $result = $controller->run('test-many-types', $params = [
            'param1' => 10,
            'param2' => 'Str',
            'param3' => 56.33,
            'param4' => false,
            'param5' => ['Array'],
            'default' => 100
        ]);

        $this->assertEquals($result, array_merge($params, [
            'default' => 100
        ]));
    }
}