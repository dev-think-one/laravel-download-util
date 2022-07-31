<?php

namespace LaravelDownloadUtil\Tests\Console;

use Illuminate\Support\Facades\Storage;
use LaravelDownloadUtil\Tests\TestCase;

class PruneOutdatedFilesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->storage = Storage::disk(config('download-util.storage.default'));
        $this->storage->delete($this->storage->allFiles());

        $this->storage->put('image-file.png', __DIR__ . '/../Fixtures/files/image-file.png');
        $this->storage->put('text-file.txt', __DIR__ . '/../Fixtures/files/text-file.txt');

        $this->assertTrue($this->storage->exists('image-file.png'));
        $this->assertTrue($this->storage->exists('text-file.txt'));
    }

    /** @test */
    public function no_params_do_nothing()
    {
        $this->artisan('download-util:prune-outdated')->assertSuccessful();

        $this->assertTrue($this->storage->exists('image-file.png'));
        $this->assertTrue($this->storage->exists('text-file.txt'));
    }

    /** @test */
    public function delete_by_extension()
    {
        $this->artisan('download-util:prune-outdated -E "png"')->assertSuccessful();

        $this->assertFalse($this->storage->exists('image-file.png'));
        $this->assertTrue($this->storage->exists('text-file.txt'));

        $this->artisan('download-util:prune-outdated -E ".txt"')->assertSuccessful();

        $this->assertFalse($this->storage->exists('image-file.png'));
        $this->assertFalse($this->storage->exists('text-file.txt'));
    }


    /** @test */
    public function delete_by_last_modified()
    {
        $this->artisan('download-util:prune-outdated -S 2')->assertSuccessful();

        $this->assertTrue($this->storage->exists('image-file.png'));
        $this->assertTrue($this->storage->exists('text-file.txt'));

        sleep(1);

        $this->artisan('download-util:prune-outdated -S 1')->assertSuccessful();

        $this->assertFalse($this->storage->exists('image-file.png'));
        $this->assertFalse($this->storage->exists('text-file.txt'));
    }
}
