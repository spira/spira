<?php


namespace App\Http\Controllers;


use App\Models\Section;
use Spira\Model\Model\BaseModel;

class AbstractSectionController extends ChildEntityController
{
    protected $relationName = 'sections';

    /**
     * @param $requestEntity
     * @param array $validationRules
     * @param BaseModel $existingModel
     * @param bool $limitToKeysPresent
     * @return bool
     */
    public function validateRequest($requestEntity, $validationRules, BaseModel $existingModel = null, $limitToKeysPresent = false)
    {
        $contentRules = $this->getContentRules($requestEntity);

        foreach ($contentRules as $attribute => $rule) {
            $validationRules['content.'.$attribute] = $rule;
        }

        return parent::validateRequest($requestEntity, $validationRules, $existingModel, $limitToKeysPresent);
    }

    /**
     * @param $requestEntity
     * @return array
     */
    protected function getContentRules($requestEntity)
    {
        if (!isset(Section::$contentTypeMap[$requestEntity['type']])){
            return [];
        }

        return with(new Section::$contentTypeMap[$requestEntity['type']])->getValidationRules();
    }
}