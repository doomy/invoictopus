<?php
/**
 * Created by PhpStorm.
 * User: doomy
 * Date: 30.09.2018
 * Time: 9:25
 */

namespace Doomy\Components\Component;

use Doomy\Components\FlashMessage;
use Doomy\ExtendedNetteForm\Form;
use Doomy\Translator\Service\DummyTranslator;

class PopupComponent extends BaseComponent
{
    protected $modalHtmlId = 'popupComponent';
    protected $modalTitle = 'Popup';
    private $displayButtons = TRUE;

    /**
     * @var string
     */
    private $boundFormHtmlId;
    private $flashes = [];

    public function render()
    {
        $this->template->setFile(dirname(__FILE__) . '/../templates/modalContainer.latte');
        $this->template->modalHtmlId = $this->modalHtmlId;
        $this->template->modalTitle = $this->modalTitle;
        $this->template->additionalButtons = $this->getAdditionalButtons();
        $this->template->hiddenLinks = $this->getHiddenLinks();
        $this->template->displayButtons = $this->displayButtons;
        $this->template->flashes = $this->flashes;
        if (isset($this->translator)) {
            $this->template->setTranslator($this->translator);
        } else {
            $this->template->setTranslator(new DummyTranslator());
        }
        $this->template->boundFormHtmlId = $this->boundFormHtmlId;
        $this->template->uuid = uniqid();
        $this->template->render();
    }

    protected function getAdditionalButtons()
    {
        return [];
    }

    protected function getHiddenLinks()
    {
        return [];
    }

    public function handleReset()
    {
    }

    public function handleSave()
    {
    }

    public function injectControl($name, $control)
    {
        $this[$name] = $control;
    }

    public function setModalHtmlId($htmlId)
    {
        $this->modalHtmlId = $htmlId;
    }

    public function setModalTitle($modalTitle)
    {
        $this->modalTitle = $modalTitle;
    }

    public function setDisplayButtons($displayButtons)
    {
        $this->displayButtons = $displayButtons;
    }

    public function addFlashMessage(FlashMessage $flashMessage) {
        $this->flashes[] = $flashMessage;
    }

    public function bindForm(string $name, Form $form) {
        $this->injectControl($name, $form);
        $formId = $form->getElementPrototype()->getAttribute('id');
        if (empty($formId)) {
            $formId = "form_" . uniqid();
            $form->getElementPrototype()->setAttribute('id', $formId);
        }
        $this->boundFormHtmlId = $formId;
    }
}
