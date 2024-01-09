<?php

namespace Ymigval\LaravelIndexnow\Tests\Feature;

use Ymigval\LaravelIndexnow\Exceptions\SearchEngineUnknownException;
use Ymigval\LaravelIndexnow\Tests\TestCase;

class IndexNowServiceTest extends TestCase
{
    public function test_submit_multi_url()
    {
        $indexNow = $this->app->make('IndexNow');
        $indexNow->setUrl('/new');
        $indexNow->setUrl('http://example.com/super-mario');
        $indexNow->setUrl('/nintendo');
        $status = $indexNow->submit();

        $this->assertIsArray($status);
        $this->assertCount(3, $status['urls']);
    }

    public function test_submit_one_url()
    {
        $indexNow = $this->app->make('IndexNow');

        $status = $indexNow->submit('/cat');

        $this->assertIsArray($status);
        $this->assertContains('http://example.com/cat', $status);
    }

    public function test_submit_no_urls()
    {
        $indexNow = $this->app->make('IndexNow');
        $status = $indexNow->submit();
        $this->assertEquals($status, 'No URLs provided for indexing.');
    }

    public function test_submit_no_is_allowed()
    {
        $indexNow = $this->app->make('IndexNow');
        $status = $indexNow->submit('/test');
        $this->assertEquals($status, 'The use of IndexNow has been temporarily blocked to prevent potential spam.');
    }

    /**
     * @test A hypothetical case where an undefined driver is provided in the configuration
     */
    public function test_unknown_driver_exception()
    {
        $this->expectException(SearchEngineUnknownException::class);
        $this->expectExceptionMessage('Unknown search engine driver for IndexNow.');
        $this->expectExceptionCode(404);

        $indexNow = $this->app->make('IndexNow');
        $indexNow->setDriver('unknown_driver');
    }

    public function test_submit_with_key_file()
    {
        $indexNow = $this->app->make('IndexNow');
        $indexNow->keyFile('http://localhost/8bbf5df8bbaa457aab35bb3ccbb99aec.txt');
        $status = $indexNow->submit(['/cat', '/cat3']);

        $this->assertIsArray($status);
        $this->assertContains('http://example.com/cat', $status);
    }
}
