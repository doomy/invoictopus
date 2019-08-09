<?php

namespace Doomy\EntityCache;

class EntityCache
{
    private $cache;

    public function cacheById($entityClass, $id, $entity) {
        $this->cache[$entityClass][$id] = $entity;
        $this->flushAllCache($entityClass);
    }

    public function cacheAll($entityClass, $entities)
    {
        $this->cache[$entityClass]['all'] = $entities;
    }

    public function getById($entityClass, $id) {
        if (isset($this->cache[$entityClass][$id])) return $this->cache[$entityClass][$id];

        return false;
    }

    public function getAll($entityClass) {
        if (isset($this->cache[$entityClass]['all'])) return $this->cache[$entityClass]['all'];

        return false;
    }

    public function flush($entityClass = null) {
        if(isset($entityClass)) {
            unset($this->cache[$entityClass]);
        }
        else unset($this->cache);
    }

    public function flushById($entityClass, $id) {
        unset($this->cache[$entityClass][$id]);
        $this->flushAllCache($entityClass);
    }

    private function flushAllCache($entityClass) {
        unset($this->cache[$entityClass]['all']);
    }
}