<?php

namespace Doomy\Translator\Service;

use Nette\Localization\ITranslator;
use Doomy\Ormtopus\DataEntityManager;
use Doomy\Translator\Model\SystemTranslation;

class Translator implements ITranslator
{
    private $language;
    private $data;

    public function __construct($language, DataEntityManager $data) {
        $this->language = $language;
        $this->data = $data;
    }

    public function setLanguage($language) {
        $this->language = $language;
    }

    public function getLanguage() {
        return $this->language;
    }

    public function translate($message, ...$parameters): string
    {
        // $message = str_replace("'", "''", $message);
        if (!$message) return '';
        $translation = $this->data->findOne(
            SystemTranslation::class,
            ['KEY' => str_replace("'", "''", $message), 'LANGUAGE_CODE' => $this->language]
        );
        if(!$translation) {
            $this->prefillTranslation($message);
            return $message;
        }
        return $translation->TRANSLATION;

        //return str_replace("''", "'", $translation->TRANSLATION);
    }

    private function prefillTranslation($message) {
        $this->data->save(
            SystemTranslation::class,
            [
                'LANGUAGE_CODE' => $this->language,
                'KEY' => $message,
                'TRANSLATION' => $message
            ]
        );
    }
}
