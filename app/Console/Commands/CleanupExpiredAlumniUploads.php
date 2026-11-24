<?php

namespace App\Console\Commands;

use App\Models\AlumniUploadSession;
use App\Services\AlumniVideoStorage;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('alumni:cleanup-expired-uploads')]
#[Description('Delete expired, unconfirmed alumni video uploads.')]
class CleanupExpiredAlumniUploads extends Command
{
    public function handle(AlumniVideoStorage $storage): int
    {
        AlumniUploadSession::query()
            ->where('expires_at', '<', now())
            ->where('status', '!=', 'completed')
            ->lazyById()
            ->each(function (AlumniUploadSession $upload) use ($storage): void {
                if ($upload->video()->exists()) {
                    return;
                }

                try {
                    $storage->delete($upload->object_key);
                    $upload->update(['status' => 'expired']);
                } catch (Throwable $exception) {
                    report($exception);
                }
            });

        return self::SUCCESS;
    }
}
