<?php

namespace Ymigval\LaravelIndexnow\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Ymigval\LaravelIndexnow\SearchEngine;
use Ymigval\LaravelIndexnow\Tests\TestCase;

class SearchEngineTest extends TestCase
{
    #[Test] public function it_returns_correct_driver_url_for_valid_search_engines()
    {
        // Test for Microsoft Bing
        $this->assertEquals(
            'www.bing.com',
            SearchEngine::getDriverUrl('microsoft_bing')
        );

        // Test for IndexNow
        $this->assertEquals(
            'api.indexnow.org',
            SearchEngine::getDriverUrl('indexnow')
        );

        // Test for Yandex
        $this->assertEquals(
            'yandex.com',
            SearchEngine::getDriverUrl('yandex')
        );

        // Test for seznam
        $this->assertEquals(
            'search.seznam.cz',
            SearchEngine::getDriverUrl('seznam')
        );

        // Test for naver
        $this->assertEquals(
            'searchadvisor.naver.com',
            SearchEngine::getDriverUrl('naver')
        );
    }

    #[Test] public function it_returns_null_for_invalid_search_engine()
    {
        $this->assertNull(SearchEngine::getDriverUrl('invalid_engine'));
    }

    #[Test] public function it_returns_null_for_empty_search_engine()
    {
        $this->assertNull(SearchEngine::getDriverUrl(''));
    }

    #[Test] public function it_returns_all_available_engines()
    {
        $engines = SearchEngine::getAllEngines();

        // Verify the returned array contains all expected search engines
        $expectedEngines = [
            'indexnow',
            'microsoft_bing',
            'yandex',
            'seznam',
            'naver'
        ];

        foreach ($expectedEngines as $engine) {
            $this->assertContains($engine, $engines);
        }

        // Also verify the count matches
        $this->assertCount(count($expectedEngines), $engines);
    }
}
