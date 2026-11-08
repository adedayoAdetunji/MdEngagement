<?php

namespace Lt\Modules\MdEngagement\Services;

use Lt\Modules\MdEngagement\Models\TbEngagementSubscriber;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtSession;

class TbEngagementSubscriberService {
    protected $_subscriberModel;
    protected $_ltNow;
    protected $_ltId;
    protected $_userId;
    protected $_sqlConnect;

    public function __construct() {
        $this->_subscriberModel = new TbEngagementSubscriber();
        $this->_ltNow = date('Y-m-d H:i:s');
        $this->_ltId = function_exists('ltId') ? ltId() : bin2hex(random_bytes(16));
        $this->_userId = LtSession::get('ltUid') ?: LtSession::get('user_id');
        $this->_sqlConnect = class_exists('\DbConnect') ? \DbConnect::dbDriver() : null;
    }

    public function store(string $email): array {
        $normalizedEmail = strtolower(trim($email));
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        if ($this->_sqlConnect) {
            $checkStmt = $this->_sqlConnect->prepare(
                'SELECT lt_id, email, unsubscribed_at FROM tb_engagement_subscriber WHERE email = :email'
            );
            $checkStmt->execute([':email' => $normalizedEmail]);
            $existing = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            if ($existing) {
                return [
                    'token' => null,
                    'isNew' => false,
                    'message' => 'Subscriber already registered.'
                ];
            }

            $sql = 'INSERT INTO tb_engagement_subscriber ' .
                   '(lt_id, email, token_hash, status, consented_at, created_at, updated_at) ' .
                   'VALUES (:lt_id, :email, :token_hash, :status, :consented_at, :created_at, :updated_at)';
            $stmt = $this->_sqlConnect->prepare($sql);
            $stmt->execute([
                ':lt_id' => $this->_ltId,
                ':email' => $normalizedEmail,
                ':token_hash' => $tokenHash,
                ':status' => 'active',
                ':consented_at' => $this->_ltNow,
                ':created_at' => $this->_ltNow,
                ':updated_at' => $this->_ltNow
            ]);
        } else {
            $this->_subscriberModel->ltId = $this->_ltId;
            $this->_subscriberModel->email = $normalizedEmail;
            $this->_subscriberModel->tokenHash = $tokenHash;
            $this->_subscriberModel->status = 'active';
            $this->_subscriberModel->consentedAt = $this->_ltNow;
            $this->_subscriberModel->createdAt = $this->_ltNow;
            $this->_subscriberModel->updatedAt = $this->_ltNow;
            if (method_exists($this->_subscriberModel, 'insert')) {
                $this->_subscriberModel->insert();
            }
        }

        return [
            'token' => $token,
            'isNew' => true,
            'message' => 'Signup received.'
        ];
    }

    public function unsubscribe(string $token): bool {
        $hash = hash('sha256', $token);

        if ($this->_sqlConnect) {
            $sql = 'UPDATE tb_engagement_subscriber ' .
                   'SET status = :status, unsubscribed_at = COALESCE(unsubscribed_at, :unsubscribed_at), ' .
                   'updated_at = :updated_at ' .
                   'WHERE token_hash = :hash';
            $stmt = $this->_sqlConnect->prepare($sql);
            $stmt->execute([
                ':status' => 'unsubscribed',
                ':unsubscribed_at' => $this->_ltNow,
                ':updated_at' => $this->_ltNow,
                ':hash' => $hash
            ]);

            $verifyStmt = $this->_sqlConnect->prepare(
                'SELECT 1 FROM tb_engagement_subscriber WHERE token_hash = :hash'
            );
            $verifyStmt->execute([':hash' => $hash]);
            return (bool) $verifyStmt->fetchColumn();
        }

        return false;
    }

    public function index(int $page = 1, string $status = ''): array {
        $page = max(1, $page);
        $limit = 25;
        $offset = ($page - 1) * $limit;
        $fetchLimit = $limit + 1;

        if ($this->_sqlConnect) {
            $filter = match ($status) {
                'active' => 'WHERE unsubscribed_at IS NULL',
                'unsubscribed' => 'WHERE unsubscribed_at IS NOT NULL',
                default => ''
            };

            $sql = 'SELECT lt_id, email, status, consented_at, unsubscribed_at, created_at, updated_at ' .
                   "FROM tb_engagement_subscriber {$filter} " .
                   "ORDER BY consented_at DESC, lt_id DESC " .
                   "LIMIT {$fetchLimit} OFFSET {$offset}";

            $stmt = $this->_sqlConnect->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            $query = $this->_subscriberModel->select();
            $rows = $query->get();
            if (!is_array($rows)) {
                $rows = [];
            }
            $rows = array_slice($rows, $offset, $fetchLimit);
        }

        $hasMore = count($rows) > $limit;
        $items = array_slice($rows, 0, $limit);

        return [
            'items' => $items,
            'hasMore' => $hasMore,
            'page' => $page
        ];
    }
}

