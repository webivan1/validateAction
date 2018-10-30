<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 30.10.2018
 * Time: 23:40
 */

namespace webivan\validateAction\models;


interface IFindColumn
{
    public function columnForInjectAction(): string;
}