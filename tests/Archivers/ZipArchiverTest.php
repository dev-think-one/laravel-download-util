<?php

namespace LaravelDownloadUtil\Tests\Archivers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelDownloadUtil\Archivers\ZipArchiver;
use LaravelDownloadUtil\Tests\TestCase;

class ZipArchiverTest extends TestCase
{
    protected \Illuminate\Filesystem\FilesystemAdapter $storage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storage = Storage::disk(config('download-util.storage.default'));
        $this->storage->delete($this->storage->allFiles());
    }

    /** @test */
    public function create_archive_with_other_extension()
    {
        $archiver = new ZipArchiver();
        $archiver->setExtension('foo.bar');
        $path = $archiver->create([__DIR__.'/../Fixtures/files/text-file.txt'], 'arch');

        $this->assertStringEndsWith('.foo.bar', $path);

        $path = $archiver->setExtension('.baz')->create([__DIR__.'/../Fixtures/files/text-file.txt'], 'fold/arch');
        $this->assertEquals('fold/arch.baz', $path);
    }

    /** @test */
    public function create_archive_name_limit()
    {
        config()->set('download-util.archiver.name_duplication_limiter', 2);

        $archiver = new ZipArchiver();
        $path     = $archiver->create([__DIR__.'/../Fixtures/files/text-file.txt'], 'arch');
        $this->assertEquals('arch.zip', $path);
        $path = $archiver->create([__DIR__.'/../Fixtures/files/text-file.txt'], 'arch');
        $this->assertEquals('arch__1.zip', $path);
        $path = $archiver->create([__DIR__.'/../Fixtures/files/text-file.txt'], 'arch');
        $this->assertEquals('arch__2.zip', $path);
        $path = $archiver->create([__DIR__.'/../Fixtures/files/text-file.txt'], 'arch');
        $this->assertNotEquals('arch__3.zip', $path);
        $this->assertTrue(Str::startsWith($path, 'arch__'));
        $this->assertTrue(strlen($path) > 20);
    }

    /** @test */
    public function create_archive()
    {
        $archiver = new ZipArchiver();

        $path = $archiver->create([
            __DIR__.'/../Fixtures/files/text-file.txt',
            __DIR__.'/../Fixtures/files/image-file.png',
        ], 'arch');

        $this->assertNotEmpty($path);

        $this->assertTrue($this->storage->exists($path));

        $path1 = $archiver->create([
            __DIR__.'/../Fixtures/files/text-file.txt',
            __DIR__.'/../Fixtures/files/image-file.png',
        ], 'arch');

        $this->assertNotEmpty($path1);
        $this->assertEquals('arch__1.zip', $path1);
    }

    /** @test */
    public function empty_list_returns_null()
    {
        $archiver = new ZipArchiver();
        $path     = $archiver->create([], 'arch');

        $this->assertNull($path);
    }

    /** @test */
    public function use_callback()
    {
        $archiver = new ZipArchiver();
        $archiver->setArchiveCreationCallback(function ($archive, $files) {
            $this->assertCount(2, $files);
            foreach ($files as $file) {
                $archive->addFile($file);
            }
        });
        $path = $archiver->create([
            __DIR__.'/../Fixtures/files/text-file.txt',
            __DIR__.'/../Fixtures/files/image-file.png',
        ], 'arch');

        $this->assertNotNull($path);
    }
}
