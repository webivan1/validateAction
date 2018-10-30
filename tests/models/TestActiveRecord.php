<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 29.10.2018
 * Time: 17:28
 */

namespace webivan\validateAction\tests\models;

use webivan\validateAction\models\IFindItem;
use yii\db\ActiveRecord;

class TestActiveRecord extends ActiveRecord implements IFindItem
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function findItemForInjectAction($value)
    {
        return $value === 1000 ? new self : null;
    }
}