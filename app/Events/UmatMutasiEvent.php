<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UmatMutasiEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;

    /**
     * Create a new event instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new Channel('siparoki-notifications'),
        ];

        if (!empty($this->payload['kub_tujuan_id'])) {
            $channels[] = new Channel('kub.' . $this->payload['kub_tujuan_id']);
        }
        if (!empty($this->payload['wilayah_tujuan_id'])) {
            $channels[] = new Channel('wilayah.' . $this->payload['wilayah_tujuan_id']);
        }
        if (!empty($this->payload['kapela_tujuan_id'])) {
            $channels[] = new Channel('kapela.' . $this->payload['kapela_tujuan_id']);
        }
        if (!empty($this->payload['kub_asal_id'])) {
            $channels[] = new Channel('kub.' . $this->payload['kub_asal_id']);
        }

        return $channels;
    }

    /**
     * Broadcast as custom event name.
     */
    public function broadcastAs(): string
    {
        return 'UmatMutasiEvent';
    }
}
