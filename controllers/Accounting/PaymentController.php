<?php

declare(strict_types=1);

namespace App\Controllers\Accounting;

use App\Controllers\Base\Controller;
use App\Dtos\Accounting\PaymentFormDto;
use App\Enums\Accounting\TransactionType;
use App\Services\Accounting\PaymentService;
use App\Utilities\Http\Request;

final class PaymentController extends Controller
{
    public function store(Request $request): void
    {
        $dto = PaymentFormDto::fromInput($request->all());
        (new PaymentService())->recordTransaction($this->user(), $request->param('code'), $dto);
        $this->done($dto->type === TransactionType::REFUND ? 'Refund recorded.' : 'Payment recorded.', null, 201);
    }
}
