<?php


namespace Doomy\Components\Component;

use Doomy\Components\ComponentTemplateNotFoundException;
use Doomy\Translator\Service\DummyTranslator;
use Nette\Application\UI\Control;
use Doomy\Translator\Service\Translator;
use Nette\InvalidStateException;
use Nette\Localization\ITranslator;
use function defined;

class BaseComponent extends Control
{
    const EVENT_DYNAMIC_FORM_SAVE = 'EVENT_SAVE';

    protected ITranslator $translator;
    protected $events = [];
    protected $session;
    protected $snippetName = "";
    protected $translatorInitialized = FALSE;
    private $templatePath;

    public function __construct()
    {
    }

    public function render()
    {
        $this->template->setFile($this->getTemplatePath());
        if (isset($this->translator)) {
            $this->template->setTranslator($this->translator);
        } else {
            $this->template->setTranslator(new DummyTranslator());
        }
        $this->template->basePath = $this->getPresenter()->getHttpRequest()->url->basePath;
        $this->template->render();
    }

    public function onEvent($event, $callback)
    {
        $this->events[$event] = $callback;
    }

    public function getSnippetName() {
        return $this->snippetName;
    }

    public function setSnippetName($snippetName) {
        $this->snippetName = $snippetName;
    }

    protected function triggerEvent($event, $param = [])
    {
        if (isset($this->events[$event]) && is_callable($this->events[$event]))
            return call_user_func($this->events[$event], $param);
    }

    protected function successFlashMessage($message)
    {
        $this->flashMessage($message, 'alert alert-success');
    }

    protected function failFlashMessage($message)
    {
        $this->flashMessage($message, 'alert alert-danger');
    }

    public function flashMessage($message, $type = 'info'): \stdClass
    {
        if (isset($this->translator)) {
            if (!$this->translatorInitialized) {
                $this->initializeTranslator();
                $this->translatorInitialized = TRUE;
            }

            $message = $this->translator->translate($message);
        }
        return parent::flashMessage($message, $type);
    }

    protected function getSession() {
        if ($this->session) {
            return $this->session;
        }

        try {
            $session = $this->getPresenter()->getSession()->getSection($this->getUniqueId());
            return $this->session = $session;
        } catch (InvalidStateException $e) {
            return NULL;
        }
    }

    protected function initializeTranslator() {
        $identity = $this->getPresenter()->getUser()->getIdentity();

        if ($identity) {
            $userLanguage =  $identity->LANGUAGE_CODE;
            $this->translator->setLanguage($userLanguage);
        }
        $this->translatorInitialized = TRUE;
    }

    protected function getTemplatePath($class = NULL) {
        if (!empty($this->templatePath))
            return $this->templatePath;

        $namespaceParts = explode('\\', $class ? $class : static::class);
        $capturing = FALSE;
        $pathParts = [];

        foreach($namespaceParts as $namespacePart) {
            if ($capturing) $pathParts[] = lcfirst($namespacePart);
            if (!$capturing && strtolower($namespacePart) == 'component')
                $capturing = TRUE;
        }

        $templatePath = implode("/", $pathParts) . ".latte";

        $templateFullPath = '/templates/component/' . $templatePath;

        if (defined('APP_DIR') && file_exists(APP_DIR . $templateFullPath)) {
            return APP_DIR . $templateFullPath;
        }

        return $this->getPackageTemplatePath($templateFullPath);
    }

    private function getPackageTemplatePath(string $templateFullPath): string
    {
        $pkgDir = dirname((new \ReflectionClass(static::class))->getFileName());
        $parts = explode("/src/", $pkgDir);

        return $parts[0] . '/src' . $templateFullPath;
    }

    public function setTemplatePath($templatePath)
    {
        $this->templatePath = $templatePath;
    }

    public function setTranslator(Translator $translator): void
    {
        $this->translator = $translator;
    }
}
