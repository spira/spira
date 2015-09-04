<?php

use App\Models\Article;
use Faker\Factory as Faker;

/**
 * Class ArticleModelTest.
 * @group article
 */
class ArticleModelTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        App\Models\Article::flushEventListeners();
        App\Models\Article::boot();
    }

    public function testAutomaticExcerpt()
    {
        $faker = Faker::create('au_AU');
        $faker->seed(1);

        $articleWithoutExcerpt = factory(Article::class)->make([
            'excerpt' => null,
            'content' => implode("\n\n", $faker->paragraphs(3)), //use seeded faker paragraph so the unit test will always use the same data
        ]);

        $excerpt = $articleWithoutExcerpt->excerpt;

        $this->assertNotNull($excerpt, 'Article excerpt is not null');
        $this->assertEquals(Article::defaultExcerptWordCount, str_word_count($excerpt), 'Word count is the expected default');
    }

    public function testManualExcerpt()
    {
        $excerpt = 'This is the article excerpt';
        $articleWithExcerpt = factory(Article::class)->make([
            'excerpt' => $excerpt,
        ]);

        $this->assertEquals($excerpt, $articleWithExcerpt->excerpt, 'Article excerpt has not been overridden');
    }
}
