<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 31.10.2018
 * Time: 22:00
 */

namespace webivan\validateAction\helpers;

trait ErrorsTrait
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param string $attr
     * @param string $message
     * @return void
     */
    public function addError(string $attr, string $message)
    {
        if (isset($this->errors[$attr])) {
            $this->errors[$attr][] = $message;
        } else {
            $this->errors[$attr] = [];
            $this->addError($attr, $message);
        }
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }
}