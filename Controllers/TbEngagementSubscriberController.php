<?php

namespace Lt\Modules\MdEngagement\Controllers;

use Lt\Modules\MdEngagement\Services\TbEngagementSubscriberService;
use Lt\Modules\MdLt\Services\LtAuth;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtSession;

class TbEngagementSubscriberController {
    public function store(): string {
        try {
            $request = new LtRequest();

            $consent = $request->consent ?? null;
            if ($consent !== true && $consent !== 'true' && $consent !== '1' && $consent !== 1) {
                return LtResponse::json('Confirm that you want to receive email updates.', '4001', '100');
            }

            $request->validateRequest([
                'email' => 'required|email'
            ]);

            $service = new TbEngagementSubscriberService();
            $result = $service->store(strtolower(trim((string) $request->email)));

            $data = !empty($result['token']) ? ['unsubscribeToken' => $result['token']] : [];

            return LtResponse::json($result['message'] ?? 'Signup received.', '4000', '200', $data);
        } catch (\Throwable $e) {
            return LtResponse::error($e->getMessage(), '4002', '100');
        }
    }

    public function unsubscribe(): string {
        try {
            $request = new LtRequest();
            $token = trim((string) ($request->token ?? ''));

            if ($token === '' || !preg_match('/^[a-f0-9]{64}$/D', $token)) {
                return LtResponse::json('This unsubscribe link is invalid.', '4001', '100');
            }

            $service = new TbEngagementSubscriberService();
            $success = $service->unsubscribe($token);

            if (!$success) {
                return LtResponse::json('This unsubscribe link is invalid or expired.', '4004', '100');
            }

            return LtResponse::json('You are unsubscribed.', '4000', '200');
        } catch (\Throwable $e) {
            return LtResponse::error($e->getMessage(), '4002', '100');
        }
    }

    public function index(): string {
        try {
            if (!$this->isStaffAuthorized()) {
                return LtResponse::json('Staff access is required.', '4003', '100');
            }

            $request = new LtRequest();
            $page = max(1, (int) ($request->page ?? 1));
            $status = (string) ($request->status ?? '');

            if ($status !== '' && !in_array($status, ['active', 'unsubscribed'], true)) {
                return LtResponse::json('Choose a valid status.', '4001', '100');
            }

            $service = new TbEngagementSubscriberService();
            $data = $service->index($page, $status);

            return LtResponse::json('Records loaded.', '4000', '200', $data);
        } catch (\Throwable $e) {
            return LtResponse::error($e->getMessage(), '4002', '100');
        }
    }

    protected function isStaffAuthorized(): bool {
        if (!LtAuth::check()) {
            return false;
        }

        return true;
    }
}

