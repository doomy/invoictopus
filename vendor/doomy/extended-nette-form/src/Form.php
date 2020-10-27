<?php

namespace Doomy\ExtendedNetteForm;

use Doomy\Repository\Helper\DbHelper;
use Nette\Application\UI\Form as UIForm;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Controls\TextInput;

class Form extends UIForm
{

    public function __construct(Nette\ComponentModel\IContainer $parent = null, $name = null)
    {
        parent::__construct($parent, $name);
        $this->setBSrenderer();
    }

    public function addDate($name, $label = null)
    {
        if (!isset($label)) $label = DbHelper::normalizeNameFromDB($name);
        return $this[$name] = new DateInput($label);
    }

    public function addText($name, $label = null, $cols = NULL, $maxLength = NULL): TextInput
    {
        if (!isset($label)) $label = DbHelper::normalizeNameFromDB($name);
        $text = parent::addText($name, $label, $cols, $maxLength);
        $text->setAttribute('class', 'form-control');
        return $text;
    }

    public function addEmail($name, $label = null, $cols = NULL, $maxLength = NULL): TextInput
    {
        if (!isset($label)) $label = DbHelper::normalizeNameFromDB($name);
        $email = parent::addText($name, $label, $cols, $maxLength);
        $email->setAttribute('class', 'form-control');
        return $email;
    }

    public function addSelect($name, $label = NULL, array $items = NULL, $size = NULL): SelectBox
    {
        $select = parent::addSelect($name, $label, $items, $size);
        $select->setAttribute('class', 'form-control');
        return $select;
    }

    public function addSubmit($name, $caption = NULL): SubmitButton
    {
        $submit = parent::addSubmit($name, $caption);
        $submit->setAttribute("class", "btn btn-primary");
        return $submit;
    }

    public function addCheckbox($name, $caption = NULL): Checkbox
    {
        $checkbox = parent::addCheckbox($name, $caption);
        $checkbox->setAttribute("class", "form-check");
        return $checkbox;
    }

    public function addLinkButton($name, $label = NULL, $link = NULL)
    {
        $link = $this[$name] = new LinkButton($label, $link);
        $link->setAttribute("class", "btn btn-primary");
        return $link;
    }

    public function addInteger($name, $label = null, $cols = NULL, $maxLength = NULL): TextInput
    {
        if (!isset($label)) $label = DbHelper::normalizeNameFromDB($name);
        $integer = parent::addInteger($name, $label, $cols, $maxLength);
        $integer->setAttribute('class', 'form-control');
        return $integer;
    }

    public function setBSrenderer()
    {
        $renderer = $this->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = "div class='form-group'";
        $renderer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['container'] = NULL;
    }
}

?>