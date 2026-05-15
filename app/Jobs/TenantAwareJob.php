<?php

namespace App\Jobs;

use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class TenantAwareJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $tenantUlid;

    /**
     * Subclasses MUST call parent::__construct() first when overriding,
     * otherwise $tenantUlid will not be set and the job will fail at runtime.
     */
    public function __construct()
    {
        $this->tenantUlid = Tenant::current()?->ulid
            ?? throw new \RuntimeException('TenantAwareJob dispatched without tenant context.');
    }

    final public function handle(): void
    {
        Tenant::switchByUlid($this->tenantUlid);
        try {
            $this->execute();
        } finally {
            Tenant::clear();
        }
    }

    abstract protected function execute(): void;
}
