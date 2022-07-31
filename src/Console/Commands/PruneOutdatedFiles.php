<?php

namespace LaravelDownloadUtil\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PruneOutdatedFiles extends Command
{
    protected $signature = 'download-util:prune-outdated
    {disk? : Storage disk name}
    {--S|seconds= : Delete files older than this amount of seconds}
    {--E|extension= : Delete files older than this amount of seconds}
    ';

    protected $description = 'Delete outdated files';

    public function handle(): int
    {
        $disk    = $this->argument('disk') ?: config('download-util.storage.default');
        $storage = Storage::disk($disk);
        /** @var $file */
        foreach ($storage->allFiles() as $file) {
            $isDeleteFile = false;
            if ($seconds = (int) $this->option('seconds')) {
                $isDeleteFile = Carbon::createFromTimestamp($storage->lastModified($file))->lessThan(Carbon::now()->subSeconds($seconds));
            }
            if ($extension = (string) $this->option('extension')) {
                $isDeleteFile = Str::endsWith($file, '.' . ltrim($extension, '.'));
            }

            if ($isDeleteFile && $storage->delete($file)) {
                $this->info("Deleted: {$file}");
            }
        }

        return 0;
    }
}
