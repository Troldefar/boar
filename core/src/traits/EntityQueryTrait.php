<?php

namespace app\core\src\traits;

use \app\core\src\database\querybuilder\QueryBuilder;
use \app\core\src\database\table\Table;
use \app\core\src\database\EntityMetaData;
use \app\core\src\miscellaneous\CoreFunctions;

trait EntityQueryTrait {

    private const INVALID_ENTITY_DATA   = 'Data can not be empty';
    private const INVALID_ENTITY_STATUS = 'This entity does not have a status';
    private const FIND_OR_CREATE_NEW_DATA_ENTRY = ' was created due to a data entry';
    private const INVALID_ENTITY = 'Invalid entity';
    private const SQL_IS_NOT_NULL = 'IS NOT NULL';
    private const SQL_FETCH_MODE_FETCH = 'fetch';

    public function patchEntity(): self {
        $this->getQueryBuilder()->patch($this->data, $this->getKeyField(), $this->key())->run(self::SQL_FETCH_MODE_FETCH);
        return $this;
    }

    public function patchField(array|object $data): self {
        $data = (array)$data;

        unset($data['eg-csrf-token-label']);
        unset($data['action']);
        
        $this->getQueryBuilder()->patch($data, $this->getKeyField(), $this->key())->run(self::SQL_FETCH_MODE_FETCH);
        return $this;
    }
    
    public function createEntity() {
        $this->getQueryBuilder()->create($this->data)->run();
        $this->setKey(app()->getConnection()->getLastInsertedID());
        return $this;
    }

    public function init() {
		return $this->getQueryBuilder()->initializeNewEntity($this->data);
	}

    public function softDelete(): self {
		$this->set([Table::DELETED_AT_COLUMN => new \DateTime('Y-m-d H:i:s')])->save();
        return $this;
	}

    public function restore(): self {
	    $this->set([Table::DELETED_AT_COLUMN => null])->save();
        return $this;
	}

    public function query(): QueryBuilder {
        return (new QueryBuilder(get_called_class(), $this->getTableName(), $this->getKeyField()));
    }

    public function delete() {
        return $this->getQueryBuilder()->delete()->where([$this->getKeyField() => $this->key()])->run();
    }

     public function truncate() {
        return $this->getQueryBuilder()->truncate()->run();
    }

     public function trashed() {
        return $this->getQueryBuilder()->select()->where([Table::DELETED_AT_COLUMN => self::SQL_IS_NOT_NULL])->run();
    }

    public function getQueryBuilder(?string $table = null): QueryBuilder {
        $table ??= $this->getTableName();
        return (new QueryBuilder(get_called_class(), $table, $this->getKeyField()));
    }

    public function find(string $field, string $value): array {
        return $this->query()->select()->where([$field => $value])->run();
    }

    public function findByMultiple(array $conditions): array {
        return $this->query()->select()->where($conditions)->run();
    }

    public function addMetaData(array $data): self {
        if (empty($data)) throw new \InvalidArgumentException(self::INVALID_ENTITY_DATA);

        (new EntityMetaData())
            ->set([
                Table::ENTITY_TYPE_COLUMN => $this->getTableName(), 
                Table::ENTITY_ID_COLUMN => $this->key() ?? 0,
                'Data' => json_encode($data), 
                'IP' => app()->getRequest()->getIP()
            ])
            ->save(addMetaData: false);
            
        return $this;
    }

    public function getTableColumns() {
        return (new QueryBuilder(get_called_class(), $this->getTableName(), $this->getKeyField()))->select()->run(); 
    }

    public function getMetaData(): QueryBuilder {
        return (new EntityMetaData())->getQueryBuilder();
    }

    public function setStatus(int $status): self {
        var_dump($this);
        if (!$this->get(Table::STATUS_COLUMN)) throw new \app\core\src\exceptions\ForbiddenException(self::INVALID_ENTITY_STATUS);
        $this->set([Table::STATUS_COLUMN => $status])->save();
        return $this;
    }

    public function coupleEntity(\app\core\src\database\Entity $entity) {
		$entity->set([$this->getKeyField() => $this->key()]);
		$entity->init();
	}

    public function setSortOrder(int $sortOrder): self {
        $this->set([Table::SORT_ORDER_COLUMN => $sortOrder]);
        return $this;
    }

    public function setRelationelTableSortOrder(string $table, int $sortOrder, $additionalConditions = []): void {
        $this->getQueryBuilder($table)
            ->patch([Table::SORT_ORDER_COLUMN => $sortOrder])
            ->where($additionalConditions)
            ->run();
    }

    public function all(): array {
        return (new QueryBuilder(get_called_class(), $this->getTableName(), $this->getKeyField()))->select()->run();
    }

    public function search(array $arguments): array {
        return $this->query()->select()->where($arguments)->run();
    }

    public function findOrCreate(string $whereKey, string $whereValue, array $data = []): \app\core\src\database\Entity {
        $lookup = $this->find($whereKey, $whereValue);
        if (!empty($lookup)) return CoreFunctions::first($lookup);
        $cEntity = (new $this());
        $cEntity->setData($data);
        $cEntity->save();
        $cEntity->addMetaData([$this->getTableName() . self::FIND_OR_CREATE_NEW_DATA_ENTRY])->save();
        return $cEntity;
    }

    public function complete() {
		$this->patchField([Table::COMPLETED_COLUMN => 1]);
	}

    public function add(object $arguments): ?array {
        return $this->crud($arguments);
    }

    public function edit(object $arguments): ?array {
        return $this->crud($arguments, 'edit');
    }

}