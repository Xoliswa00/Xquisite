<?php

namespace App\Models\Traits;

trait TracksRequests
{
    //
      protected function getRequestId(): ?string
    {
        return app('request_id') ?? request()->header('X-Request-ID');
    }

    protected function getRequestContext(): array
    {
        return [
            'request_id' => $this->getRequestId(),
            'ip' => request()->ip(),
            'url' => request()->fullUrl(),
        ];
    }
}
