<?php

namespace Lt\Modules\MdEngagement\Services;

use Lt\Modules\MdEngagement\Models\TbEngagementEnquiry;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtSession;

class TbEngagementEnquiryService {
    protected $_enquiryModel;
    protected $_ltNow;
    protected $_ltId;
    protected $_userId;
    protected $_sqlConnect;

    public function __construct() {
        $this->_enquiryModel = new TbEngagementEnquiry();
        $this->_ltNow = date('Y-m-d H:i:s');
        $this->_ltId = function_exists('ltId') ? ltId() : bin2hex(random_bytes(16));
        $this->_userId = LtSession::get('ltUid') ?: LtSession::get('user_id');
        $this->_sqlConnect = class_exists('\DbConnect') ? \DbConnect::dbDriver() : null;
    }

    public function store(array $input = []): array {
        $name = trim((string) $input['name']);
        $email = strtolower(trim((string) $input['email']));
        $subject = trim((string) $input['subject']);
        $message = trim((string) $input['message']);

        if ($this->_sqlConnect) {
            $sql = 'INSERT INTO tb_engagement_enquiry ' .
                   '(lt_id, full_name, email, subject, message, status, created_at, updated_at) ' .
                   'VALUES (:lt_id, :full_name, :email, :subject, :message, :status, :created_at, :updated_at)';
            $stmt = $this->_sqlConnect->prepare($sql);
            $stmt->execute([
                ':lt_id' => $this->_ltId,
                ':full_name' => $name,
                ':email' => $email,
                ':subject' => $subject,
                ':message' => $message,
                ':status' => 'new',
                ':created_at' => $this->_ltNow,
                ':updated_at' => $this->_ltNow
            ]);
        } else {
            $this->_enquiryModel->ltId = $this->_ltId;
            $this->_enquiryModel->fullName = $name;
            $this->_enquiryModel->email = $email;
            $this->_enquiryModel->subject = $subject;
            $this->_enquiryModel->message = $message;
            $this->_enquiryModel->status = 'new';
            $this->_enquiryModel->createdAt = $this->_ltNow;
            $this->_enquiryModel->updatedAt = $this->_ltNow;
            if (method_exists($this->_enquiryModel, 'insert')) {
                $this->_enquiryModel->insert();
            }
        }

        return ['ltId' => $this->_ltId];
    }

    public function index(int $page = 1, string $status = ''): array {
        $page = max(1, $page);
        $limit = 25;
        $offset = ($page - 1) * $limit;
        $fetchLimit = $limit + 1;

        if ($this->_sqlConnect) {
            $params = [];
            $where = '';
            if ($status !== '' && in_array($status, ['new', 'read', 'resolved'], true)) {
                $where = 'WHERE status = :status';
                $params[':status'] = $status;
            }

            $sql = 'SELECT lt_id, full_name, email, subject, message, status, created_at, updated_at ' .
                   "FROM tb_engagement_enquiry {$where} " .
                   "ORDER BY created_at DESC, lt_id DESC " .
                   "LIMIT {$fetchLimit} OFFSET {$offset}";

            $stmt = $this->_sqlConnect->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            $query = $this->_enquiryModel->select();
            if ($status !== '') {
                $query->where('status', '=', $status);
            }
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

    public function update(string $id, string $status): bool {
        if (!in_array($status, ['new', 'read', 'resolved'], true)) {
            return false;
        }

        if ($this->_sqlConnect) {
            $sql = 'UPDATE tb_engagement_enquiry ' .
                   'SET status = :status, updated_at = :updated_at ' .
                   'WHERE lt_id = :lt_id';
            $stmt = $this->_sqlConnect->prepare($sql);
            $stmt->execute([
                ':status' => $status,
                ':updated_at' => $this->_ltNow,
                ':lt_id' => $id
            ]);
            return $stmt->rowCount() > 0;
        }

        $this->_enquiryModel->status = $status;
        $this->_enquiryModel->updatedAt = $this->_ltNow;
        if (method_exists($this->_enquiryModel, 'update')) {
            $this->_enquiryModel->update('ltId', '=', $id);
            return true;
        }
        return false;
    }
}

