<?php

namespace Lt\Modules\MdEngagement\Services;

class EngagementService {
    protected $_sqlConnect;

    public function __construct() {
        if (class_exists('\DbConnect')) {
            $this->_sqlConnect = \DbConnect::dbDriver();
        }
    }

    protected function query(string $sql, array $params = []): ?\PDOStatement {
        if (!$this->_sqlConnect) {
            return null;
        }
        $statement = $this->_sqlConnect->prepare($sql);
        $statement->execute($params);
        return $statement;
    }
}

