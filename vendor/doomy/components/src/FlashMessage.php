<?php

namespace Doomy\Components;

class FlashMessage
{
    const TYPE_SUCCESS = 'alert-success';
    const TYPE_WARNING = 'alert-warning';
    const TYPE_INFO = 'alert-info';

    public $message;

    public function __construct($message, $type = self::TYPE_SUCCESS)
    {
        $this->type = "alert $type";
        $this->message = $message;
    }

}
