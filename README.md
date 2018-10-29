# Ext Yii 2 Validate action params by comments

Установка
---------

```
composer require yii2-webivan1/yii2-validate-action-params
```
Или 
```
"require": {
    "yii2-webivan1/yii2-validate-action-params": "dev-master"
}
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

Теперь возьмем такой action:

```php
    public function actionFlat($buildId, $houseId, $floor, $number) 
    {
        
    }
```

Чтобы провалидировать данные параметры `$buildId, $houseId, $floor, $number`.
Надо всего лишь добавить комментарии к этому экшену:

```php
    /**
     * @param integer $buildId
     * @param integer $houseId
     * @param integer|null $floor
     * @param integer|null $number
     */
    public function actionFlat($buildId, $houseId, $floor = 1, $number = null) 
    {
        
    }
```

Пример с моделью ActiveRecord, мы указываем тип параметра 
namespace модели (Важно указать полный путь к модели).
Парамер запроса будет будет искаться по данной модели (Model::find()->where([PRIMARY_KEY => {VALUE}])->one().


```php
    /**
     * @param \app\models\House $house
     */
    public function actionHouse(House $house) 
    {
        return $this->render('index', compact('house'));
    }
```

!!!Чтобы использовать колонку для поиска не PrimaryKey модели, то укажите в своей модели метод:
```php 
    public function getValidationColumnKey(): string
    {
        // Укажите любую колонку по которой будет идти поиск
        return 'token_id'; 
    } 
```

Для того чтобы получить текущего юзера в экшене, можно не передавать его в queryParams 
А behavior все сделает сам, пример:

```php
    /**
     * Считывается значение параметра $user и подставляется в класс ActiveRecord.
     * !При этом надо указать полный путь до модели \app\models\User.
     * User будет подставляться сам из Yii::$app->getUser()->id если не гость. 
     *
     * @param \app\models\User $user
     */
    public function actionMyOwnComments(User $user) 
    {
        return $user->comments;
    }
```