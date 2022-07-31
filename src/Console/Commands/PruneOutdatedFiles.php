<?php

namespace LaravelDownloadUtil\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
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

    protected Filesystem $storage;

    protected string $filterExtension = '';

    protected int $filterSeconds = 0;


    public function handle(): int
    {
        $disk                  = $this->argument('disk') ?: config('download-util.storage.default');
        $this->storage         = Storage::disk($disk);
        $this->filterExtension = (string) $this->option('extension');
        $this->filterSeconds   = (int) $this->option('seconds');

        /** @var $file */
        foreach ($this->storage->allFiles() as $file) {
            if ($this->needDeleteFile($file) && $this->storage->delete($file)) {
                $this->info("Deleted: {$file}");
            }
        }

        return 0;
    }

    protected function needDeleteFile(string $file): bool
    {
        if ($this->filterSeconds && !$this->filterSeconds($file)) {
            return false;
        }
        if ($this->filterExtension && !$this->filterExtension($file)) {
            return false;
        }

        return $this->filterSeconds || $this->filterExtension;
    }

    protected function filterSeconds(string $file): bool
    {
        return Carbon::createFromTimestamp($this->storage->lastModified($file))->lessThan(Carbon::now()->subSeconds($this->filterSeconds));
    }

    protected function filterExtension(string $file): bool
    {
        return Str::endsWith($file, '.'.ltrim($this->filterExtension, '.'));
    }
}
