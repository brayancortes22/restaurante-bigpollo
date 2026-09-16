<?php

namespace App\Jobs;

use App\Actions\Invoices\EmitFactusInvoiceAction;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EmitFactusInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public Order $order
    ) {}

    public function handle(EmitFactusInvoiceAction $action): void
    {
        $action->execute($this->order);
    }
}
