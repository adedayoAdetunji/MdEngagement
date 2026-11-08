<?php

namespace Lt\Modules\MdEngagement\Controllers;

use Lt\Modules\MdEngagement\Services\TbEngagementEnquiryService;
use Lt\Modules\MdLt\Services\LtAuth;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtSession;

class TbEngagementEnquiryController {
    public function store(): string {
        try {
            $request = new LtRequest();

            $request->validateRequest([
                'name'    => 'required|min:2|max:100',
                'email'   => 'required|email',
                'subject' => 'required|max:160',
                'message' => 'required|max:4000'
            ]);

            $service = new TbEngagementEnquiryService();
            $result = $service->store([
                'name'    => trim((string) $request->name),
                'email'   => strtolower(trim((string) $request->email)),
                'subject' => trim((string) $request->subject),
                'message' => trim((string) $request->message)
            ]);

            return LtResponse::json('Message received.', '4000', '200', $result);
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

            if ($status !== '' && !in_array($status, ['new', 'read', 'resolved'], true)) {
                return LtResponse::json('Choose a valid status.', '4001', '100');
            }

            $service = new TbEngagementEnquiryService();
            $data = $service->index($page, $status);

            return LtResponse::json('Records loaded.', '4000', '200', $data);
        } catch (\Throwable $e) {
            return LtResponse::error($e->getMessage(), '4002', '100');
        }
    }

    public function update(): string {
        try {
            if (!$this->isStaffAuthorized()) {
                return LtResponse::json('Staff access is required.', '4003', '100');
            }

            $request = new LtRequest();
            $id = trim((string) ($request->ltId ?? ''));
            $status = trim((string) ($request->status ?? ''));

            if ($id === '') {
                return LtResponse::json('Invalid enquiry identifier.', '4001', '100');
            }

            if (!in_array($status, ['new', 'read', 'resolved'], true)) {
                return LtResponse::json('Choose a valid status.', '4001', '100');
            }

            $service = new TbEngagementEnquiryService();
            $success = $service->update($id, $status);

            if (!$success) {
                return LtResponse::json('Enquiry could not be updated.', '4004', '100');
            }

            return LtResponse::json('Status saved.', '4000', '200', ['ltId' => $id, 'status' => $status]);
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

