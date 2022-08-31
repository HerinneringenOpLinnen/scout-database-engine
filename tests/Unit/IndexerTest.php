<?php

namespace GetCandy\ScoutDatabaseEngine\Tests\Unit;

use GetCandy\ScoutDatabaseEngine\SearchIndex;
use GetCandy\ScoutDatabaseEngine\Tests\TestCase;
use GetCandy\ScoutDatabaseEngine\Tests\Stubs\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

class IndexerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_index_a_post()
    {
        $post = new Post();
        $post->title = 'Example Post';
        $post->body = 'Test 1 2 3';
        $post->save();

        $this->assertInstanceOf(Post::class, $post);

        $this->assertDatabaseCount('search_index', 2);

        $this->assertDatabaseHas('search_index', [
            'key' => $post->getScoutKey(),
            'index' => $post->searchableAs(),
            'field' => 'title',
            'content' => $post->title,
        ]);
    }

    /** @test */
    public function deletes_outdated_index_data()
    {
        $searchIndex = new SearchIndex();
        $searchIndex->key = '10';
        $searchIndex->index = 'posts';
        $searchIndex->field = 'title';
        $searchIndex->content = 'To be deleted';
        $searchIndex->save();

        $post = new Post();
        $post->id = 10;
        $post->title = 'Example Post';
        $post->body = 'Test 1 2 3';
        $post->save();

        $this->assertDatabaseMissing('search_index', [
            'key' => '10',
            'index' => 'posts',
            'field' => 'title',
            'content' => 'To be deleted',
        ]);
    }

    /** @test */
    public function deletes_old_index_data()
    {
        $post = new Post();
        $post->id = 15;
        $post->title = 'Example Post';
        $post->body = 'Test 1 2 3';
        $post->save();

        $this->assertDatabaseHas('search_index', [
            'key' => '15',
            'index' => 'posts',
            'field' => 'title',
            'content' => 'Example Post',
        ]);

        $post->delete();

        $this->assertDatabaseMissing('search_index', [
            'key' => '15',
            'index' => 'posts',
            'field' => 'title',
            'content' => 'Example Post',
        ]);
    }

    /** @test */
    public function can_flush_data()
    {
        $post = new Post();
        $post->title = 'Example Post';
        $post->body = 'Test 1 2 3';
        $post->save();

        $post = new Post();
        $post->title = 'Example Post 2';
        $post->body = 'Test 4 5 6';
        $post->save();

        $post = new Post();
        $post->title = 'Example Post 3';
        $post->body = 'Test 7 8 9';
        $post->save();

        // 3 models x 2 fields = 6
        $this->assertDatabaseCount('search_index', 6);

        Artisan::call('scout:flush "GetCandy\\\ScoutDatabaseEngine\\\Tests\\\Stubs\\\Post"');

        $this->assertDatabaseCount('search_index', 0);
    }
}
