<?php

namespace Lt\Modules\MdEngagement\Controllers;

use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtSession;

class EngagementController {
    protected function ok(string $message, array $data = []): string {
        return LtResponse::json($message, '4000', '200', $data);
    }

    protected function fail(string $message, string $code = '4001'): string {
        return LtResponse::json($message, $code, '100');
    }
}

