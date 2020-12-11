<?php

namespace Doomy\Translator\Service;

use Nette\Localization\ITranslator;

class DummyTranslator implements ITranslator
{
    public function translate($message, ...$parameters): string
    {
        return $message;
    }
}
