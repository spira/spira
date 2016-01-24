<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\AbstractPost;
use App\Models\Article;
use App\Models\PostSectionsDisplay;
use App\Models\Section;
use App\Models\Sections\BlockquoteContent;
use App\Models\Sections\MediaContent;
use App\Models\Sections\PromoContent;
use App\Models\Sections\RichTextContent;

/**
 * Class SectionTest.
 * @group integration
 */
class ArticleSectionTest extends TestCase
{
    protected $baseRoute = '/articles';
    protected $factoryClass = Article::class;

    public function setUp()
    {
        parent::setUp();
        $class = $this->factoryClass;
        // Workaround for model event firing.
        // The package Bosnadev\Database used for automatic UUID creation relies
        // on model events (creating) to generate the UUID.
        //
        // Laravel/Lumen currently doesn't fire repeated model events during
        // unit testing, see: https://github.com/laravel/framework/issues/1181
        $class::flushEventListeners();
        $class::boot();
    }

    public function testGetSections()
    {
        $post = $this->makePost();

        $sections = $this->getFactory(Section::class)->count(5)->make();
        $post->sections()->saveMany($sections);

        $this->withoutAuthorization($post)->getJson($this->baseRoute.'/'.$post->post_id.'/sections');
        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(200);
        $this->shouldReturnJson();
        $this->assertJsonArray();

        $this->assertEquals(count($object), 5);
        if ($object[0]->type !== PromoContent::CONTENT_TYPE) { //promo content does not have a content body so the type will not be stdClass
            $this->assertInstanceOf(stdClass::class, $object[0]->content);
        }
    }

    public function testGetSectionsNestedInPosts()
    {
        $post = $this->makePost();

        $sections = $this->getFactory(Section::class)->count(5)->make();
        $post->sections()->saveMany($sections);

        $this->withoutAuthorization($post)->getJson($this->baseRoute.'/'.$post->post_id, ['with-nested' => 'sections']);
        $object = json_decode($this->response->getContent());
        $this->assertEquals(5, count($object->_sections));
    }

    public function testAddSectionsToPosts()
    {
        $post = $this->makePost();

        $sections = $this->getFactory(Section::class)->count(5)->make();
        $post->sections()->saveMany($sections);

        $newSections = $this->getFactory(Section::class)->count(2)->transformed();

        $this->withAdminAuthorization()->postJson($this->baseRoute.'/'.$post->post_id.'/sections', $newSections);

        $this->assertResponseStatus(201);
        $class = $this->factoryClass;
        $this->assertCount(7, $class::find($post->post_id)->sections);
    }

    public function testDeleteSection()
    {
        $post = $this->makePost();

        /** @var \Illuminate\Database\Eloquent\Collection $sections */
        $sections = $this->getFactory(Section::class)->count(5)->make();
        $post->sections()->saveMany($sections);

        $deleteSection = $sections->first();

        $this->withAdminAuthorization()->deleteJson($this->baseRoute.'/'.$post->post_id.'/sections/'.$deleteSection->section_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $class = $this->factoryClass;
        $this->assertCount(4, $class::find($post->post_id)->sections);
    }

    public function testAddInvalidSectionContent()
    {
        $post = $this->makePost();

        $section = $this->getFactory(Section::class, RichTextContent::CONTENT_TYPE)
            ->customize(['content' => 10])
            ->transformed();

        $this->withAdminAuthorization()->postJson($this->baseRoute.'/'.$post->post_id.'/sections', [$section]);

        $this->assertResponseStatus(422);
    }

    public function testAddInvalidSectionType()
    {
        $post = $this->makePost();

        $section = $this->getFactory(Section::class, RichTextContent::CONTENT_TYPE)
            ->customize(['type' => 'not_a_type'])
            ->transformed();

        $this->withAdminAuthorization()->postJson($this->baseRoute.'/'.$post->post_id.'/sections', [$section]);

        $this->assertResponseStatus(422);
    }

    public function testAddInvalidSections()
    {
        $post = $this->makePost();

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

        $mediaSection = $this->getFactory(Section::class, MediaContent::CONTENT_TYPE)
            ->customize(['content' => [
                'media' => 'not-an-array', //should validate array
            ]])
            ->transformed();

        $promoSection = $this->getFactory(Section::class, PromoContent::CONTENT_TYPE)
            ->customize(['content' => []])
            ->transformed();

        $this->withAdminAuthorization()->postJson($this->baseRoute.'/'.$post->post_id.'/sections', [$richTextSection, $blockquoteSection, $mediaSection, $promoSection]);

        $this->assertResponseStatus(422);

        echo $this->response->getContent();
    }

    public function testAddSortedSections()
    {
        // @todo fix mapping issue on sort_order
        // MapperParsingException[object mapping [sort_order] trying to serialize a value with no field associated with it, current value [01a82263-4cbe-324f-9854-c66485b99d1d]]

        $this->markTestSkipped('fix mapping issue on sort_order');

        $post = $this->makePost();

        $newSections = $this->getFactory(Section::class)->count(5)->transformed();

        $sectionsDisplay = $this->getFactory(PostSectionsDisplay::class)->customize([
            'sort_order' => array_pluck($newSections, 'sectionId'),
        ])->transformed();

        $this->withAdminAuthorization()->postJson($this->baseRoute.'/'.$post->post_id.'/sections', $newSections);
        $this->assertResponseStatus(201);

        $this->withAdminAuthorization()->patchJson($this->baseRoute.'/'.$post->post_id, ['sectionsDisplay' => $sectionsDisplay]);
        $this->assertResponseStatus(204);
        $class = $this->factoryClass;
        $updatedPost = $class::find($post->post_id);

        $this->assertCount(5, $updatedPost->sections);
        $this->assertNotNull($updatedPost->sections_display);
        $this->assertNotNull($updatedPost->sections_display['sort_order']);
        $this->assertCount(5, $updatedPost->sections_display['sort_order']);
    }

    /** @return AbstractPost */
    protected function makePost()
    {
        return $this->getFactory($this->factoryClass)->create();
    }

    public function withoutAuthorization($post = null, $user = null)
    {
        return parent::withoutAuthorization();
    }
}
