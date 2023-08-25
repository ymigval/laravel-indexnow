<?php
namespace Ymigval\LaravelIndexnow\Facade;

use Illuminate\Support\Facades\Facade;

class IndexNow extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'IndexNow';
    }
}
