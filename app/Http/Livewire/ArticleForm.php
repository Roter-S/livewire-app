<?php

namespace App\Http\Livewire;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class ArticleForm extends Component
{
    use WithFileUploads;

    public Article $article;

    public $image;

    public $newCategory;

    public $showCategoryModal = false;

    public function openCategoryForm()
    {
        $this->newCategory = new Category();
        $this->showCategoryModal = true;
    }

    public function closeCategoryForm()
    {
        $this->showCategoryModal = false;
        $this->newCategory = null;
    }

    public function saveNewCategory()
    {
        $this->newCategory->save();
        $this->article->category_id = $this->newCategory->id;
        $this->closeCategoryForm();
    }

    protected function rules()
    {
        return [
            'image' => [
                Rule::requiredIf(!$this->article->image),
                Rule::when($this->image, ['image', 'max:2048'])
            ],
            'article.title' => ['required', 'min:4'],
            'article.slug' => [
                'required',
                'alpha_dash',
                Rule::unique('articles', 'slug')->ignore($this->article)
            ],
            'article.content' => ['required'],
            'article.category_id' => [
                'required',
                Rule::exists('categories', 'id')
            ],
            'newCategory.name' => [],
            'newCategory.slug' => [],
        ];
    }

    public function mount(Article $article)
    {
        $this->article = $article;
    }

    public function updated($prortyName)
    {
        $this->validateOnly($prortyName);
    }

    public function updatedArticleTitle($title)
    {
        $this->article->slug = Str::slug($title);
    }

    public function updatedNewCategoryName($name)
    {
        $this->newCategory->slug = Str::slug($name);
    }

    public function save()
    {

        $this->validate();

        if ($this->image) {
            $this->article->image = $this->uploadImage();
        }

        Auth::user()->articles()->save($this->article);

        session()->flash('status', __('Article.saved'));

        $this->redirectRoute('articles.index');
    }

    public function render()
    {
        return view('livewire.article-form', [
            'categories' => Category::pluck('name', 'id')
        ]);
    }

    /**
     * @return mixed
     */
    public function uploadImage()
    {
        if ($oldImage = $this->article->image) {
            Storage::disk('public')->delete($oldImage);
        }
        return $this->image->store('/', 'public');
    }
}
