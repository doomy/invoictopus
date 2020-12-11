<?php

namespace Doomy\Components\Component;

use Doomy\ExtendedNetteForm\Form;
use Doomy\Translator\Service\Translator;

class DynamicPopupForm extends PopupComponent
{
    const EVENT_DYNAMIC_FORM_SAVE = 'event_dynamic_form_save';
    const FORM_NAME = 'dynamicForm';

    private $dynamicForm = NULL;
    private $fields = [];
    protected $ajax = TRUE;

    public function __construct()
    {

        //$this->injectControl(static::FORM_NAME, $this->getDynamicForm());
        $this->bindForm(static::FORM_NAME, $this->getDynamicForm());
        parent::__construct();
    }

    public function render()
    {
        parent::render();
    }

    public function handleRedraw() {
        $this->redrawControl($this->getSnippetName());
    }

    public function addField($field, $name) {
        $this->getDynamicForm()[$name] = $field;
    }

    public function isFieldSet($fieldName) {
        return isset($this->getDynamicForm()[$fieldName]);
    }

    public function getDynamicForm() {
        if (!empty ($this->dynamicForm)) {
            return $this->dynamicForm;
        }

        return $this->dynamicForm = $this->initForm();
    }

    protected function initForm(): Form
    {
        $form = new Form();
        if ($this->ajax) {
            $form->getElementPrototype()->class('ajax');
        }
        $form->onSuccess[] = function($form, $values) {
            $this->triggerEvent(static::EVENT_DYNAMIC_FORM_SAVE, ['formValues' => $values]);
        };
        return $form;
    }
}
