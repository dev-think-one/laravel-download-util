<?php

namespace LaravelDownloadUtil\Archivers;

use Carbon\Carbon;
use Closure;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class ZipArchiver
{
    protected string $ext = 'zip';

    protected FilesystemAdapter $storage;

    protected ?Closure $archiveCreationCallback = null;

    public function __construct(?FilesystemAdapter $storage = null)
    {
        $this->storage = $storage ?? Storage::disk(config('download-util.storage.default'));
    }

    public function setExtension(string $ext): static
    {
        $this->ext = ltrim($ext, '.');

        return $this;
    }

    public function setArchiveCreationCallback(?Closure $callback): static
    {
        $this->archiveCreationCallback = $callback;

        return $this;
    }

    public function create(array $files, ?string $filePrefix = null): ?string
    {
        $filePrefix = $filePrefix ?: Carbon::now()->format('Y-m-d-his');

        $archive         = new ZipArchive;
        $archiveFileName = $this->findArchiveFileName($filePrefix);
        $this->ensureDirectoryExists($archiveFileName);

        if (!empty($files)
            && $archive->open($this->storage->path($archiveFileName), ZipArchive::CREATE) === true
        ) {
            $this->addFilesToArchive($archive, $files);
            if ($archive->close()) {
                return $archiveFileName;
            }
        }

        return null;
    }

    protected function addFilesToArchive(ZipArchive $archive, array $files): ZipArchive
    {
        if ($this->archiveCreationCallback && is_callable($this->archiveCreationCallback)) {
            call_user_func_array($this->archiveCreationCallback, [
                $archive,
                $files,
                $this->storage,
            ]);
        } else {
            foreach ($files as $file) {
                $archive->addFile($file);
            }
        }

        return $archive;
    }

    protected function findArchiveFileName(string $filePrefix): string
    {
        $archiveFileName = "{$filePrefix}.{$this->ext}";
        $counter         = 1;
        while ($this->storage->exists($archiveFileName)) {
            if ($counter > config('download-util.archiver.name_duplication_limiter', 1000)) {
                $uuid            = Str::uuid();
                $archiveFileName = "{$filePrefix}__{$uuid}.{$this->ext}";

                break;
            }
            $archiveFileName = "{$filePrefix}__{$counter}.{$this->ext}";
            $counter++;
        }

        return $archiveFileName;
    }

    protected function ensureDirectoryExists(string $archiveFileName): void
    {
        $folderPath      = Str::beforeLast($archiveFileName, '/');
        if ($folderPath != $archiveFileName && !$this->storage->directoryExists($folderPath)) {
            $this->storage->createDirectory($folderPath);
        }
    }
}
