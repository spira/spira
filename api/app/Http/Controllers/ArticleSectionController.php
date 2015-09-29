<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Models\Article;
use App\Models\Sections\BlockquoteContent;
use App\Models\Sections\ImageContent;
use App\Models\Sections\RichTextContent;
use Spira\Model\Validation\ValidationException;

class ArticleSectionController extends ChildEntityController
{
    protected $relationName = 'sections';

    public function __construct(Article $parentModel, EloquentModelTransformer $transformer)
    {
        parent::__construct($parentModel, $transformer);
    }

    /**
     * @param $requestEntity
     * @param array $validationRules
     * @param bool $limitToKeysPresent
     * @return bool
     */
    public function validateRequest($requestEntity, $validationRules, $limitToKeysPresent = false)
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
        }

        foreach($contentRules as $attribute => $rule){
            $validationRules['content.'.$attribute] = $rule;
        }

        return parent::validateRequest($requestEntity, $validationRules, $limitToKeysPresent);
    }


}
