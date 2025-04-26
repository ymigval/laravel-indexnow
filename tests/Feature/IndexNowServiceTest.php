<?php

namespace Ymigval\LaravelIndexnow\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Ymigval\LaravelIndexnow\Exceptions\ExcessUrlsException;
use Ymigval\LaravelIndexnow\Exceptions\NonAbsoluteUrlException;
use Ymigval\LaravelIndexnow\Exceptions\SearchEngineUnknownException;
use Ymigval\LaravelIndexnow\IndexNowService;
use Ymigval\LaravelIndexnow\PreventSpam;
use Ymigval\LaravelIndexnow\Tests\TestCase;

class IndexNowServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set up a valid API key
        Config::set('indexnow.indexnow_api_key', 'validApiKey12345');

        // Enable deliveries
        Config::set('indexnow.enable_submissions', true);

        // Configure a base URL for the application
        Config::set('app.url', 'https://example.com');

        // Clean up any existing spam lock files
        $blockingFile = storage_path('app/indexnow/spam_blocking.json');
        if (file_exists($blockingFile)) {
            unlink($blockingFile);
        }
    }

    #[Test] public function it_can_set_search_engine()
    {
        // Arrange
        $service = new IndexNowService('microsoft_bing');

        // Act
        $service->setSearchEngine('yandex');

        // Assert
        $this->assertEquals('yandex.com', $service->getSearchEngine());
    }

    #[Test] public function it_throws_exception_for_invalid_search_engine()
    {
        // Arrange
        $service = new IndexNowService('microsoft_bing');

        // Assert
        $this->expectException(SearchEngineUnknownException::class);

        // Act
        $service->setSearchEngine('invalid_engine');
    }

    #[Test] public function it_can_add_urls()
    {
        // Arrange
        $service = new IndexNowService('microsoft_bing');

        // Act
        $service->setUrl('https://example.com/page1');
        $service->setUrl('https://example.com/page2');

        // Assert
        $this->assertCount(2, $service->getUrls());
        $this->assertEquals(['https://example.com/page1', 'https://example.com/page2'], $service->getUrls());
    }

    #[Test] public function it_deduplicates_urls()
    {
        // Arrange
        $service = new IndexNowService('microsoft_bing');

        // Act
        $service->setUrl('https://example.com/page');
        $service->setUrl('https://example.com/page'); // Duplicate

        // Assert
        $this->assertCount(1, $service->getUrls());
    }

    #[Test] public function it_can_submit_single_url()
    {
        // Arrange
        Http::fake([
            'https://www.bing.com/indexnow' => Http::response(['status' => 'success'], 200),
        ]);

        $service = new IndexNowService('microsoft_bing');

        // Act
        $result = $service->submit('https://example.com/page');

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals(202, $result['status']);
        $this->assertEquals(['https://example.com/page'], $result['urls']);

        // Verificar que la solicitud se realizó correctamente
        Http::assertSent(function ($request) {
            return $request->url() === 'https://www.bing.com/indexnow' &&
                $request->hasQuery('url', 'https://example.com/page') &&
                $request->hasQuery('key', 'validApiKey12345');
        });
    }

    #[Test] public function it_can_submit_multiple_urls()
    {
        // Arrange
        Http::fake([
            'https://www.bing.com/indexnow' => Http::response(['status' => 'success'], 200),
        ]);

        $service = new IndexNowService('microsoft_bing');
        $urls = [
            'https://example.com/page1',
            'https://example.com/page2',
            'https://example.com/page3',
        ];

        // Act
        $result = $service->submit($urls);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals(200, $result['status']);
        $this->assertEquals($urls, $result['urls']);

        // Verify that the request was completed successfully
        Http::assertSent(function ($request) use ($urls) {
            $data = $request->data();
            return $request->url() === 'https://www.bing.com/indexnow' &&
                $request->method() === 'POST' &&
                isset($data['urlList']) &&
                count($data['urlList']) === 3 &&
                $data['key'] === 'validApiKey12345' &&
                $data['host'] === 'example.com';
        });
    }

    #[Test] public function it_throws_exception_for_too_many_urls()
    {
        // Arrange
        $service = new IndexNowService('microsoft_bing');
        $urls = [];

        // Generate more than 10,000 URLs
        for ($i = 0; $i < 10001; $i++) {
            $urls[] = "https://example.com/page$i";
        }

        // Assert
        $this->expectException(ExcessUrlsException::class);

        // Act
        $service->submit($urls);
    }

    #[Test] public function it_converts_relative_urls_to_absolute()
    {
        // Arrange
        Http::fake([
            'https://www.bing.com/indexnow*' => Http::response(['status' => 'success'], 200),
        ]);

        $service = new IndexNowService('microsoft_bing');

        // Act
        $result = $service->submit('/relative-page');

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals(['https://example.com/relative-page'], $result['urls']);
    }

    #[Test] public function it_throws_exception_for_invalid_urls()
    {
        // Configure a base URL for the application
        Config::set('app.url', '');

        // Arrange
        $service = new IndexNowService('microsoft_bing');


        // Assert
        $this->expectException(NonAbsoluteUrlException::class);


        // Act - Try to send a URL that cannot be converted to absolute
        $service->submit('invalid-url-with-no-host');
    }

    #[Test] public function it_returns_message_when_submissions_are_disabled()
    {
        // Arrange
        Config::set('indexnow.enable_submissions', false);
        $service = new IndexNowService('microsoft_bing');

        // Act
        $result = $service->submit('https://example.com/page');

        // Assert
        $this->assertIsString($result);
        $this->assertStringContainsString('Enable Submissions is set to false', $result);
    }

    #[Test] public function it_respects_spam_prevention()
    {
        // Arrange - Mock PreventSpam to return false for isAllowed
        $this->mock(PreventSpam::class, function ($mock) {
            $mock->shouldReceive('isAllowed')->andReturn(false);
        });

        $service = new IndexNowService('microsoft_bing');

        // Act
        $result = $service->submit('https://example.com/page');

        // Assert
        $this->assertIsString($result);
        $this->assertStringContainsString('temporarily blocked', $result);
    }

    #[Test] public function it_extracts_host_from_urls()
    {
        // Arrange
        Http::fake([
            'https://www.bing.com/indexnow' => Http::response(['status' => 'success'], 200),
        ]);

        $service = new IndexNowService('microsoft_bing');
        $urls = [
            'https://customdomain.com/page1',
            'https://customdomain.com/page2',
        ];

        // Act
        $result = $service->submit($urls);

        // Assert
        $this->assertIsArray($result);

        // Verify that the host was extracted correctly
        Http::assertSent(function ($request) {
            $data = $request->data();
            return $request->url() === 'https://www.bing.com/indexnow' &&
                $data['host'] === 'customdomain.com';
        });
    }
}
