<?php
namespace Doomy\Translator\Model;

use Doomy\Repository\Model\Entity;

class SystemTranslation extends Entity
{
    const TABLE = 't_system_translation';
    const IDENTITY_COLUMN = 'system_translation_id';

    public $LANGUAGE_CODE;
    public $TRANSLATION;

}
