<?php

namespace App\Presenters;

use Doomy\Migrator\Migrator;
use Nette\Application\UI\Presenter;

class MigrationPresenter extends Presenter
{
    /**
     * @var Migrator
     */
    private $migrator;

    public function __construct(Migrator $migrator)
    {
        $this->migrator = $migrator;
    }

    public function renderMigrate()
    {
        header("Content-Type: text/plain");
        $this->migrator->migrate();
        die($this->migrator->getOutput());
    }
}