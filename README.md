# Ext Yii 2 Validate action params, DI action

Установка
---------

```
composer require yii2-webivan1/yii2-validate-action-params
```
 
Настройка
---------
 
Любой контроллер, например SiteController, добавим к нему trait
`webivan\validateAction\ValidateActionTrait` и behaviors 
(Можно это сделать глобально в базовый класс контроллера)

```php
    class SiteController extends Controller 
    {
        use webivan\validateAction\ValidateActionTrait;
        
        public function behaviors()
        {
            return [
                'validation' => [
                    'class' => webivan\validateAction\ActionValidateBehavior::class,
                ]
            ];
        }
        
        // ...
    }
```

Теперь возьмем для примера такой action:

```php
    public function actionTest(int $num, string $name, array $data) 
    {
        // Все входные параметры будут провалидированы согласно типам
    }
```

Или

```php
    /**
     * @validate
     * @param int $num
     * @param string $name
     * @param array $data
     */
    public function actionTest($num, $name, array $data) 
    {
        // Все входные параметры будут провалидированы согласно phpdoc
    }
```

!!! Чтобы валидировать параметры через phpdoc Вам нужно добавить параметр @validate

Внедрение зависимостей в action
---

```php
    public function actionTest(int $num, Request $request) 
    {
        
    }
```

или

```php
    /**
     * @validate
     * @param int $num
     * @param \yii\web\Request $request
     */
    public function actionTest($num, $request) 
    {
        
    }
```