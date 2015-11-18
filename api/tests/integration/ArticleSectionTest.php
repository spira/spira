<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Article;
use App\Models\ArticleSectionsDisplay;
use App\Models\Section;
use App\Models\Sections\BlockquoteContent;
use App\Models\Sections\MediaContent;
use App\Models\Sections\PromoContent;
use App\Models\Sections\RichTextContent;

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
        $article = $this->getFactory(Article::class)
            ->create();

        $sections = $this->getFactory(Section::class)->count(5)->make();
        $article->sections()->saveMany($sections);

        $this->getJson('/articles/'.$article->article_id.'/sections');
        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(200);
        $this->shouldReturnJson();
        $this->assertJsonArray();

        $this->assertEquals(count($object), 5);
        if ($object[0]->type !== PromoContent::CONTENT_TYPE) { //promo content does not have a content body so the type will not be stdClass
            $this->assertInstanceOf(stdClass::class, $object[0]->content);
        }
    }

    public function testGetSectionsNestedInArticles()
    {
        /** @var Article $article */
        $article = $this->getFactory(Article::class)
            ->create();

        $sections = $this->getFactory(Section::class)->count(5)->make();
        $article->sections()->saveMany($sections);

        $this->getJson('/articles/'.$article->article_id, ['with-nested' => 'sections']);
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
        $this->withAuthorization()->putJson('/articles/'.$article->article_id.'/sections', $newSections);

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

        $this->withAuthorization()->deleteJson('/articles/'.$article->article_id.'/sections/'.$deleteSection->section_id);

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

        $this->withAuthorization()->putJson('/articles/'.$article->article_id.'/sections', [$section]);

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

        $this->withAuthorization()->putJson('/articles/'.$article->article_id.'/sections', [$section]);

        $this->assertResponseStatus(422);
    }

    public function testPutInvalidSections()
    {
        /** @var Article $article */
        $article = $this->getFactory(Article::class)
            ->create();

        $richTextSection = $this->getFactory(Section::class, RichTextContent::CONTENT_TYPE)
            ->customize(['content' => [
                'body' => 10, //should validate text
            ]])
            ->transformed();

        $blockquoteSection = $this->getFactory(Section::class, BlockquoteContent::CONTENT_TYPE)
            ->customize(['content' => [
                'body' => 10, //should validate text
            ]])
            ->transformed();

        $imageSection = $this->getFactory(Section::class, MediaContent::CONTENT_TYPE)
            ->customize(['content' => [
                'media' => 'not-an-array', //should validate array
            ]])
            ->transformed();

        $promoSection = $this->getFactory(Section::class, PromoContent::CONTENT_TYPE)
            ->customize(['content' => [
            ]])
            ->transformed();

        $this->withAuthorization()->putJson('/articles/'.$article->article_id.'/sections', [$richTextSection, $blockquoteSection, $imageSection, $promoSection]);

        $this->assertResponseStatus(422);
    }

    public function testPutSortedSections()
    {

        /** @var Article $article */
        $article = $this->getFactory(Article::class)
            ->create();

        $newSections = $this->getFactory(Section::class)->count(5)->transformed();

        $sectionsDisplay = $this->getFactory(ArticleSectionsDisplay::class)->customize([
            'sort_order' => array_pluck($newSections, 'sectionId'),
        ])->transformed();

        $this->withAuthorization()->putJson('/articles/'.$article->article_id.'/sections', $newSections);
        $this->assertResponseStatus(201);

        $this->patchJson('/articles/'.$article->article_id, ['sectionsDisplay' => $sectionsDisplay]);
        $this->assertResponseStatus(204);

        $updatedArticle = Article::find($article->article_id);

        $this->assertCount(5, $updatedArticle->sections);
        $this->assertNotNull($updatedArticle->sections_display);
        $this->assertNotNull($updatedArticle->sections_display['sort_order']);
        $this->assertCount(5, $updatedArticle->sections_display['sort_order']);
    }
}
