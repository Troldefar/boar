<?php

namespace app\models;

use \app\core\src\database\Entity;

final class SessionModel extends Entity {

	public function getTableName(): string {
		return 'Sessions';
	}
	
	public function getKeyField(): string {
		return 'SessionID';
	}
	
}