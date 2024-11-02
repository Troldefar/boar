<?php

/**
|----------------------------------------------------------------------------
| Application entities
|----------------------------------------------------------------------------
| Model extender - This is where models interact with the database
| 
| @author RE_WEB
| @package \app\core\src
|
*/

namespace app\core\src\database;

use \app\core\src\database\table\Table;
use \app\core\src\miscellaneous\CoreFunctions;
use \app\core\src\traits\entity\EntityQueryTrait;
use \app\core\src\traits\entity\EntityMagicMethodTrait;
use \app\core\src\traits\entity\EntityHTTPMethodTrait;
use \app\core\src\traits\entity\EntityRelationsTrait;

abstract class Entity {

    use EntityQueryTrait;
    use EntityMagicMethodTrait;
    use EntityHTTPMethodTrait;
    use EntityRelationsTrait;

    private const INVALID_ENTITY_SAVE   = 'Entity has not yet been properly stored, did you call this method before ->save() ?';
    private const INVALID_ENTITY_STATUS = 'This entity does not have a status';
    private const INVALID_ENTITY_DATA   = 'Data can not be empty';
    private const INVALID_ENTITY_KEY    = 'Invalid entity key';
    private const INVALID_ENTITY_STATIC_METHOD = 'Invalid static method';
    private const INVALID_ENTITY_METHOD = 'Invalid non static method method';
    private const INITIAL_CLIENT_REQUEST_CACHED_POST_CREATED_TIMESTAMP = 'InitialClientRequestCreatedTimestamp';

    private $key;
    protected array $data = [];
    protected array $additionalConstructorMethods = [];
    
    private array $availableCallMethods = ['crud'];
    
    abstract protected function getKeyField()  : string;
    abstract protected function getTableName() : string;
    
    public function __construct($data = null, ?array $allowedFields = null) {
        $this->set($data, $allowedFields);
        if ($this->exists()) $this->checkAdditionalConstructorMethods();
    }

    public function checkAdditionalConstructorMethods() {
        if (empty($this->additionalConstructorMethods)) return;
        foreach ($this->additionalConstructorMethods as $method)
            $this->data[$method] = $this->dispatchMethod($method);
    }

    /**
     * @param array $values key:value pairs of values to set
     * @param array|null $allowedFields keys of fields allowed to be altered
     * @return object The current entity instance
     */

    protected function convertData($data = null, array $allowedFields = null) {
        if (is_object($data) === true) $data = (array)$data;
        if (is_array($data) === true) foreach($data as $key => $value) $data[$key] = is_string($value) && trim($value) === '' ? null : $value;
        if (is_string($data) && trim($data) === '') $data = null;
        if ($allowedFields != null) $data = array_intersect_key($data, array_flip($allowedFields));
        return $data;
    }

    public function set($data = null, array $allowedFields = null): Entity {
        $data = $this->convertData($data, $allowedFields);
        $key = $this->getKeyField();

        if ($data !== null && gettype($data) !== "array") $data = [$key => $data];

        if(isset($data[$key])) {
            $exists = $this->getQueryBuilder()->fetchRow([$key => $data[$key]]);
            //$exists = $this->getQueryBuilder()->select()->where([$key => $data[$key]])->run();
            if (is_array($exists)) $exists = CoreFunctions::first($exists);
            if(!empty($exists)) {
                $this->setKey($exists->{$this->getKeyField()});
                $this->setData((array)$exists);
                unset($this->data[$this->getKeyField()]);
                unset($data[$this->getKeyField()]);
            }
        }

        $this->data = array_merge($this->data, $data ?? []);
        
        return $this;
    }

    protected function setKey(string $key): void {
        $this->key = $key;
    }

    public function key(): ?string {
        return $this->key;
    }

    public function exists(): bool {
        return $this->key !== null;
    }

    public function setData(array $data) {
        $this->data = $data;
    }

    private function checkClientCachedPOSTCreatedTimestampField() {
        if (!$this->propertyExists(self::INITIAL_CLIENT_REQUEST_CACHED_POST_CREATED_TIMESTAMP)) return;

        $initialClientRequestCreatedTimestamp = $this->get(self::INITIAL_CLIENT_REQUEST_CACHED_POST_CREATED_TIMESTAMP);
        $date = date('Y-m-d H:i:s', $initialClientRequestCreatedTimestamp);

        $this->set([Table::CREATED_AT_COLUMN => $date]);
        $this->appendHistory([Table::CREATED_AT_COLUMN . ' field was changed because InitialClientRequestCreatedTimestamp was set and set to: ' . $date]);
    }

    public function save(bool $addMetaData = false): self {
        $this->checkClientCachedPOSTCreatedTimestampField();

        if ($addMetaData) $this->addMetaData($this->data);
        if ($this->exists()) return $this->patchEntity();
        if (empty($this->data)) throw new \app\core\src\exceptions\EmptyException();

        return $this->createEntity();
    }

    public function setAndSave(array $data, $addMetaData = false): self {
        $this->setData($data);
        $this->save($addMetaData);
        return $this;
    }

    public function get(string $key): mixed {
        return $this->data[$key] ?? false; 
    }

    public function propertyExists(string $property): bool {
        return isset($this->data[$property]);
    }

    public function getData(): array {
        return $this->data;
    }

    public function getFrontendFriendlyData() {
        $toBeDisplayed = $this->getData();
        unset($toBeDisplayed[$this->getKeyField()]);
        return $toBeDisplayed;
    }

    public function checkAllowSave(): void {
        if (!$this->exists()) throw new \app\core\src\exceptions\EmptyException(self::INVALID_ENTITY_SAVE);
    }

    public function setTmpProperties(array $entityProperties): void {
        $this->set($entityProperties);
    }

    /**
     * Common language pivot table for general entites
     * @return string
     */

     protected function languagePivot(): string {
        return 'entity_language';
    }

    private function checkMethodValidity(string $method) {
        if (!method_exists($this, $method)) throw new \app\core\src\exceptions\NotFoundException(self::INVALID_ENTITY_METHOD);
    }

    public function setAllowedHTTPMethods() {
		$this->setValidHTTPMethods($this->ALLOWED_HTTP_METHODS);
	}

    /**
     * Dispatcher for entity methods
     * @throws \app\core\src\exceptions\NotFoundException
     */

    public function dispatchMethod(string $method, mixed $arguments = []) {
        $this->checkMethodValidity($method);
        return $this->{$method}($arguments);
    }

    /**
     * HTTP Request dispatcher for entity methods
     * @throws \app\core\src\exceptions\NotFoundException
     */

    public function dispatchHTTPMethod(string $httpRequestEntityMethod, mixed $httpBody) {
        $this->setAllowedHTTPMethods();
        $this->validateHTTPAction($httpBody, $httpRequestEntityMethod);
        return $this->dispatchMethod($httpRequestEntityMethod, $httpBody);
    }

    public function getCreatedTimestamp(string $date = ''): string {
        return date('d-m-Y H:i', strtotime(($date !== '' ? $date : $this->get(Table::CREATED_AT_COLUMN))));
    }

    public function getSortOrder(): ?int {
        return $this->get(Table::SORT_ORDER_COLUMN) ?? null;
    }

    public function requireExistence() {
        if (!$this->exists()) app()->getResponse()->notFound();
    }

    private function checkAvailableCallMethods(string $method): bool {
        return in_array($method, $this->availableCallMethods);
    }

    private function checkOverloadArgumentCount(int $count, array $possibleLengthRequirements): void {
        if (!in_array($count, $possibleLengthRequirements)) 
            throw new \app\core\src\exceptions\ForbiddenException('Invalid parameter numbers');
    }
    
}