<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 30.10.2018
 * Time: 23:37
 */

namespace webivan\validateAction\models;


interface IFindItem
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function findItemForInjectAction($value);
}