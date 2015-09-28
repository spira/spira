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
use App\Models\Sections\RichTextContent;

/**
 * Class SectionTest.
 * @group integration
 * @group testing
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
        $article = $this->getFactory(Article::class)
            ->create();

        $sections = $this->getFactory(Section::class)->count(5)->make();
        $article->sections()->saveMany($sections);

        $this->getJson('/articles/' . $article->article_id . '/sections');
        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(200);
        $this->shouldReturnJson();
        $this->assertJsonArray();

        $this->assertEquals(count($object), 5);
        $this->assertInstanceOf(stdClass::class, $object[0]->content);
    }

    public function testGetSectionsNestedInArticles()
    {
        /** @var Article $article */
        $article = $this->getFactory(Article::class)
            ->create();

        $sections = $this->getFactory(Section::class)->count(5)->make();
        $article->sections()->saveMany($sections);

        $this->getJson('/articles/' . $article->article_id, ['with-nested' => 'sections']);
        $object = json_decode($this->response->getContent());
        $this->assertEquals(5, count($object->_sections));
    }


    public function testAddSectionsToArticle()
    {
        /** @var Article $article */
        $article = $this->getFactory(Article::class)
            ->create();

        $sections = $this->getFactory(Section::class)->count(5)->make();
        $article->sections()->saveMany($sections);

        $newSections = $this->getFactory(Section::class)->count(2)->transformed();
        $this->putJson('/articles/' . $article->article_id . '/sections', $newSections);

        $this->assertResponseStatus(201);

        $this->assertCount(7, Article::find($article->article_id)->sections);
    }

    public function testDeleteSection()
    {
        /** @var Article $article */
        $article = $this->getFactory(Article::class)
            ->create();

        /** @var \Illuminate\Database\Eloquent\Collection $sections */
        $sections = $this->getFactory(Section::class)->count(5)->make();
        $article->sections()->saveMany($sections);

        $deleteSection = $sections->first();

        $this->deleteJson('/articles/' . $article->article_id . '/sections/' . $deleteSection->section_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();

        $this->assertCount(4, Article::find($article->article_id)->sections);
    }


    public function testPutInvalidSectionContent()
    {
        /** @var Article $article */
        $article = $this->getFactory(Article::class)
            ->create();

        $section = $this->getFactory(Section::class, RichTextContent::CONTENT_TYPE)
            ->customize(['content' => 10])
            ->transformed();

        $this->putJson('/articles/' . $article->article_id . '/sections', [$section]);

        $this->assertResponseStatus(422);
    }

    public function testPutInvalidSectionType()
    {
        /** @var Article $article */
        $article = $this->getFactory(Article::class)
            ->create();

        $section = $this->getFactory(Section::class, RichTextContent::CONTENT_TYPE)
            ->customize(['type' => 'not_a_type'])
            ->transformed();

        $this->putJson('/articles/' . $article->article_id . '/sections', [$section]);

        $this->assertResponseStatus(422);
    }

    /**
     * @group failing
     */
    public function testPutInvalidSectionObject()
    {
        /** @var Article $article */
        $article = $this->getFactory(Article::class)
            ->create();

        $section = $this->getFactory(Section::class, RichTextContent::CONTENT_TYPE)
            ->customize(['content' => [
                'body' => 10 //should validate text
            ]])
            ->transformed();

        $this->putJson('/articles/' . $article->article_id . '/sections', [$section]);

        $this->assertResponseStatus(422);


        $object = json_decode($this->response->getContent());
//        dd($object);

    }


}
