<?php

namespace app\core\src\factories;

use \app\core\src\database\Entity;

use \stdClass;

class EntityFactory extends AbstractFactory {

    protected const MODEL_AFFIX = 'Model';

    public function create(): Entity|stdClass {
        try {
            $entity = ('\\app\models\\' . $this->getHandler() . self::MODEL_AFFIX);
            $this->validateObject($entity);
            return new $entity($this->getKey());
        } catch (\Throwable $entityError) {
            return new stdClass();
        }
    }

}