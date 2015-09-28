<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Article;
use App\Models\Section;

/**
 * Class SectionTest.
 * @group integration
 */
class SectionTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // Workaround for model event firing.
        // The package Bosnadev\Database used for automatic UUID creation relies
        // on model events (creating) to generate the UUID.
        //
        // Laravel/Lumen currently doesn't fire repeated model events during
        // unit testing, see: https://github.com/laravel/framework/issues/1181
        App\Models\Article::flushEventListeners();
        App\Models\Article::boot();
    }

    public function testGetSections()
    {
        /** @var Article $article */
        $article = $this->getFactory()
            ->get(Article::class)
            ->create();

        $sections = factory(Section::class, 5)->make();
        $article->sections()->saveMany($sections);

        $this->getJson('/articles/' . $article->article_id . '/sections');
        $object = json_decode($this->response->getContent());

        $this->shouldReturnJson();
        $this->assertResponseStatus(200);

        $this->assertEquals(count($object), 5);
    }

    public function testGetSectionsNestedInArticles()
    {
        /** @var Article $article */
        $article = $this->getFactory()
            ->get(Article::class)
            ->create();

        $sections = factory(Section::class, 5)->make();
        $article->sections()->saveMany($sections);

        $this->getJson('/articles/' . $article->article_id, ['with-nested' => 'sections']);
        $object = json_decode($this->response->getContent());
        $this->assertEquals(5, count($object->_sections));
    }

    public function testDeleteSection()
    {
        /** @var Article $article */
        $article = $this->getFactory()
            ->get(Article::class)
            ->create();

        /** @var \Illuminate\Database\Eloquent\Collection $sections */
        $sections = factory(Section::class, 5)->make();
        $article->sections()->saveMany($sections);

        $deleteSection = $sections->first();

        $this->deleteJson('/articles/' . $article->article_id . '/sections/' . $deleteSection->section_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();

        $this->assertCount(4, Article::find($article->article_id)->sections);
    }
}
