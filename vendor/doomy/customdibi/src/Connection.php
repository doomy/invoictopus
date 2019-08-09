<?php


namespace Doomy\CustomDibi;

use Dibi\Connection as DibiConnection;

class Connection extends DibiConnection
{
    private $tables;

    public function __construct(array $config, string $name = null)
    {
        $config['password'] = (string) $config['password'];
        parent::__construct($config, $name);
    }

    public function __sleep()
    {
        return [];
    }

    public function __wakeup()
    {
        return [];
    }

    public function tableExists($table) {
        if (empty($this->tables)) {
            $this->tables = $this->initTables();
        }

        return in_array($table, $this->tables);
    }

    private function initTables() {
        $tables = [];
        $schema = $this->getConfig('database');
        $res = $this->query("SELECT table_name FROM information_schema.tables WHERE table_schema = '$schema'");
        foreach($res->fetchAll() as $row) {
            $tables[] = $row['table_name'];
        }

        return $tables;
    }
}