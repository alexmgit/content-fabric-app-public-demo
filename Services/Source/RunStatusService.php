<?php

namespace App\Services\Source;

use App\Enums\Source\RunStatus;
use App\Models\Source\Run;

class RunStatusService
{
    public function markCompleted(Run $run): void
    {
        $run->update([
            'status' => RunStatus::COMPLETED->value,
        ]);
    }

    public function markFailed(Run $run): void
    {
        $run->update([
            'status' => RunStatus::FAILED->value,
        ]);
    }
}
