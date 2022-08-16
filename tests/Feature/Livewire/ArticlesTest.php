<?php

namespace Tests\Feature\livewire;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ArticlesTest extends TestCase
{
    /** @test */

    function article_component_renders_properly()
    {
        $this->get('/')
            ->assertSeeLivewire('articles');
    }
}
