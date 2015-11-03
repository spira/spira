<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Models\Article;
use Spira\Model\Model\BaseModel;
use App\Models\Sections\ImageContent;
use App\Models\Sections\PromoContent;
use App\Models\Sections\RichTextContent;
use App\Models\Sections\BlockquoteContent;
use App\Extensions\Controller\LocalizableTrait;
use App\Http\Transformers\EloquentModelTransformer;

class ArticleSectionController extends ChildEntityController
{
    use LocalizableTrait;

    protected $relationName = 'sections';

    public function __construct(Article $parentModel, EloquentModelTransformer $transformer)
    {
        parent::__construct($parentModel, $transformer);
    }

    /**
     * @param $requestEntity
     * @param array $validationRules
     * @param BaseModel $existingModel
     * @param bool $limitToKeysPresent
     * @return bool
     */
    public function validateRequest($requestEntity, $validationRules, BaseModel $existingModel = null, $limitToKeysPresent = false)
    {
        $contentRules = [];
        switch ($requestEntity['type']) {
            case RichTextContent::CONTENT_TYPE:

                $contentRules = with(new RichTextContent)->getValidationRules();

                break;
            case BlockquoteContent::CONTENT_TYPE:

                $contentRules = with(new BlockquoteContent)->getValidationRules();
                break;
            case ImageContent::CONTENT_TYPE:

                $contentRules = with(new ImageContent)->getValidationRules();
                break;
            case PromoContent::CONTENT_TYPE:

                $contentRules = with(new PromoContent)->getValidationRules();
                break;
        }

        foreach ($contentRules as $attribute => $rule) {
            $validationRules['content.'.$attribute] = $rule;
        }

        return parent::validateRequest($requestEntity, $validationRules, $existingModel, $limitToKeysPresent);
    }
}
