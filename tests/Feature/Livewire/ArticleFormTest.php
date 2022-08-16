<?php

namespace Tests\Feature\Livewire;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ArticleFormTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function guests_cannot_create_or_update_article()
    {
        $this->get(route('articles.create'))
            ->assertRedirect('login');

        $article = Article::factory()->create();
        $this->get(route('articles.edit', $article))
            ->assertRedirect('login');
    }

    /** @test */
    function article_form_renders_properly()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('articles.create'))
            ->assertSeeLivewire('article-form');

        $article = Article::factory()->create();
        $this->actingAs($user)->get(route('articles.edit', $article))
            ->assertSeeLivewire('article-form');
    }

    /** @test */
    function blade_template_is_wired_properly()
    {
        Livewire::test('article-form')
            ->assertSeeHtml('wire:submit.prevent="save"')
            ->assertSeeHtml('wire:model="article.title"')
            ->assertSeeHtml('wire:model="article.slug"');
    }

    /** @test */
    function can_create_a_new_articles()
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('post-image.png');

        $user = User::factory()->create();

        $category = Category::factory()->create();

        Livewire::actingAs($user)->test('article-form')
            ->set('image', $image)
            ->set('article.title', 'New Article')
            ->set('article.slug', 'new-Article')
            ->set('article.content', 'Article Content')
            ->set('article.category_id', $category->id)
            ->call('save')
            ->assertSessionHas('status')
            ->assertRedirect(route('articles.index'));

        $this->assertDatabaseHas('articles', [
            'image' => $imagePath = Storage::disk('public')->files()[0],
            'title' => 'New Article',
            'slug' => 'new-article',
            'content' => 'Article Content',
            'user_id' => $user->id
        ]);

        Storage::disk('public')->assertExists($imagePath);
    }

    /** @test */
    function can_update_articles()
    {
        $article = Article::factory()->create();

        $user = User::factory()->create();

        Livewire::actingAs($user)->test('article-form', ['article' => $article])
            ->assertSet('article.title', $article->title)
            ->assertSet('article.slug', $article->slug)
            ->assertSet('article.content', $article->content)
            ->set('article.title', 'Updated title')
            ->set('article.slug', 'updated-slug')
            ->call('save')
            ->assertSessionHas('status')
            ->assertRedirect(route('articles.index'));

        $this->assertDatabaseCount('articles', 4);

        $this->assertDatabaseHas('articles', [
            'title' => 'Updated title',
            'slug' => 'updated-slug',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    function can_update_articles_image()
    {
        Storage::fake('public');

        $oldImage = UploadedFile::fake()->image('old-image.png');
        $oldImagePath = $oldImage->store('/', 'public');
        $newImage = UploadedFile::fake()->image('new-image.png');

        $article = Article::factory()->create([
            'image' => $oldImagePath
        ]);

        $user = User::factory()->create();

        Livewire::actingAs($user)->test('article-form', ['article' => $article])
            ->set('image', $newImage)
            ->call('save')
            ->assertSessionHas('status')
            ->assertRedirect(route('articles.index'));

        Storage::disk('public')
            ->assertExists($article->fresh()->image)
            ->assertMissing($oldImagePath);
    }

    /** @test */
    function image_is_required()
    {
        Livewire::test('article-form')
            ->set('article.title', 'Article title')
            ->set('article.content', 'Article content')
            ->call('save')
            ->assertHasErrors(['image' => 'required'])
            ->assertSeeHtml(__('validation.required', ['attribute' => 'image']));;
    }

    /** @test */
    function image_field_must_be_of_type_image()
    {
        Livewire::test('article-form')
            ->set('image', 'string-not-allowed')
            ->call('save')
            ->assertHasErrors(['image' => 'image'])
            ->assertSeeHtml(__('validation.image', ['attribute' => 'image']));;
    }

    /** @test */
    function image_field_must_be_of_2mb_max()
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('post-image.png')->size(3000);

        Livewire::test('article-form')
            ->set('image', $image)
            ->call('save')
            ->assertHasErrors(['image' => 'max'])
            ->assertSeeHtml(__('validation.max.file', [
                'attribute' => 'image',
                'max' => '2048'
            ]));
    }

    /** @test */
    function title_is_required()
    {
        Livewire::test('article-form')
            ->set('article.content', 'New Article Content')
            ->call('save')
            ->assertHasErrors(['article.title' => 'required'])
            ->assertSeeHtml(__('validation.required', ['attribute' => 'title']));;
    }

    /** @test */
    function slug_is_required()
    {
        Livewire::test('article-form')
            ->set('article.title', 'New Article')
            ->set('article.slug', null)
            ->set('article.content', 'New Article Content')
            ->call('save')
            ->assertHasErrors(['article.slug' => 'required'])
            ->assertSeeHtml(__('validation.required', ['attribute' => 'slug']));;
    }

    /** @test */
    function category_is_required()
    {
        Livewire::test('article-form')
            ->set('article.title', 'New Article')
            ->set('article.slug', 'new-article')
            ->set('article.content', 'New Article Content')
            ->set('article.category_id', null)
            ->call('save')
            ->assertHasErrors(['article.category_id' => 'required'])
            ->assertSeeHtml(__('validation.required', ['attribute' => 'category id']));;
    }

    /** @test */
    function category_must_exist_in_database()
    {
        Livewire::test('article-form')
            ->set('article.title', 'New Article')
            ->set('article.slug', 'new-article')
            ->set('article.content', 'New Article Content')
            ->set('article.category_id', 1)
            ->call('save')
            ->assertHasErrors(['article.category_id' => 'exists'])
            ->assertSeeHtml(__('validation.exists', ['attribute' => 'category id']));;
    }

    /** @test */
    function slug_must_only_contain_letters_numbers_dashes_and_underscores()
    {
        Livewire::test('article-form')
            ->set('article.title', 'New Article')
            ->set('article.slug', 'new-article-%&')
            ->set('article.content', 'New Article Content')
            ->call('save')
            ->assertHasErrors(['article.slug' => 'alpha_dash'])
            ->assertSeeHtml(__('validation.alpha_dash', ['attribute' => 'slug']));
    }

    /** @test */
    function slug_must_be_unique()
    {
        $article = Article::factory()->create();

        Livewire::test('article-form')
            ->set('article.title', 'New Article')
            ->set('article.slug', $article->slug)
            ->set('article.content', 'New Article Content')
            ->call('save')
            ->assertHasErrors(['article.slug' => 'unique'])
            ->assertSeeHtml(__('validation.unique', ['attribute' => 'slug']));
    }

    /** @test */
    function unique_rule_should_be_ignored_when_updating_the_same_slug()
    {
        $article = Article::factory()->create();

        $user = User::factory()->create();

        Livewire::actingAs($user)->test('article-form', ['article' => $article])
            ->set('article.title', 'New Article')
            ->set('article.slug', $article->slug)
            ->set('article.content', 'Article Content')
            ->call('save')
            ->assertHasNoErrors(['article.slug' => 'unique']);
    }

    /** @test */
    function title_must_be_4_characters_min()
    {
        Livewire::test('article-form')
            ->set('article.title', 'Art')
            ->set('article.content', 'Article Content')
            ->call('save')
            ->assertHasErrors(['article.title' => 'min'])
            ->assertSeeHtml(__('validation.min.string', ['attribute' => 'title', 'min' => 4]));
    }

    /** @test */
    function content_is_required()
    {
        Livewire::test('article-form')
            ->set('article.title', 'Article Title')
            ->call('save')
            ->assertHasErrors(['article.content' => 'required'])
            ->assertSeeHtml(__('validation.required', ['attribute' => 'content']));
    }

    /** @test */
    function real_time_validation_works_for_title()
    {
        Livewire::test('article-form')
            ->set('article.title', '')
            ->assertHasErrors(['article.title' => 'required'])
            ->set('article.title', 'New')
            ->assertHasErrors(['article.title' => 'min'])
            ->set('article.title', 'New Article')
            ->assertHasNoErrors('article.title');
    }

    /** @test */
    function real_time_validation_works_for_content()
    {
        Livewire::test('article-form')
            ->set('article.content', '')
            ->assertHasErrors(['article.content' => 'required'])
            ->set('article.content', 'New Article')
            ->assertHasNoErrors('article.content');
    }

    /** @test */
    function slug_is_generated_automatically()
    {
        Livewire::test('article-form')
            ->set('article.title', 'Nuevo Articulo')
            ->assertSet('article.slug', 'nuevo-articulo');
    }
}

