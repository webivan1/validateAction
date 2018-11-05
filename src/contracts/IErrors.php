<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 05.11.2018
 * Time: 15:55
 */

namespace webivan\validateAction\contracts;

interface IErrors
{
    /**
     * @return array
     */
    public function getErrors(): array;

    /**
     * @param string $attr
     * @param string $message
     * @return void
     */
    public function addError(string $attr, string $message);

    /**
     * @return bool
     */
    public function hasErrors(): bool;

    /**
     * @param array $errors
     */
    public function setErrors(array $errors);
}