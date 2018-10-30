# Ext Yii 2 Validate action params, DI action

Install
---------

```
composer require yii2-webivan1/yii2-validate-action-params
```
 
Settings
---------

Add trait in any controller or global controller for trigger event before runAction
```php 
    use webivan\validateAction\ValidateActionTrait;
```

Add behavior in controller
```php
    public function behaviors()
    {
        return [
            'validation' => [
                'class' => webivan\validateAction\ActionValidateBehavior::class,
                // 'actions' => ['index', 'about'] // Validate only action
            ]
        ];
    }
```

Usage
-----

### Add types by params action [php7]

```php
    public function actionIndex(int $number, string $name, array $data) 
    {
        // ...
    }
```

### Or add phpdoc by action together with tag @validate

```php
    /**
     * @validate
     * @param integer $number
     * @param string $name
     * @param array $data
     */
    public function actionIndex($number, $name, array $data) 
    {
        // ...
    }
```

Dependency injection 
----

### You can add DI in action

```php 
    public function actionIndex(Request $request, int $number, string $name, array $data, Response $response) 
    {
        // ...
    }
```

### Or phpdoc. Write the full path to the class name!

````php 
    /**
     * @validate
     * @param \yii\web\Request $request
     * @param integer $number
     * @param string $name
     * @param array $data
     * @param \yii\web\Response $response
     */
    public function actionIndex($request, $number, $name, $data, $response) 
    {
        // ...
    }
````

### Add models [ActiveRecord]

```php 
    // Usually
    public function actionIndexOld($cityId) 
    {
        if (is_null($city = City::findOne(['id' => intval($cityId)]))) {
            throw new HttpExceprion(404);
        }
        
        return $city;
    }
    
    // Now
    public function actionIndexNew(City $city) 
    {
        return $city;
    }
    
    // Or
    /**
     * @validate
     * @param \app\models\City $city
     */
    public function actionIndexNew($city) 
    {
        return $city;
    }
```

### User model

```php 
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            throw new HttpException(404);
        }
        
        return true;
    }

    // If user is guest, Error 404
    public function actionIndexNew(User $user) 
    {
        // Error 404
    }
    
    // If user is login, User::findOne(['id' => \Yii::$app->user->id])
    public function actionIndexNew(User $user) 
    {
        return $user;
    }
```

### How to change the default query DI?

Add interface ` webivan\validateAction\models\IFindItem ` in model

### How to change the default column search model?

Add interface ` webivan\validateAction\models\IFindColumn ` in model

License
----
MIT