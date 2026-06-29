<?php

namespace App\Jobs;

use App\Services\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFcmNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $fcmToken,
        public string $title,
        public string $body,
        public array  $data = []
    ) {}

    public function handle(FcmService $fcmService): void
    {
        $fcmService->send($this->fcmToken, $this->title, $this->body, $this->data);
    }
}