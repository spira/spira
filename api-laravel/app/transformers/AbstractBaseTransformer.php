<?php

use League\Fractal\TransformerAbstract;

abstract class AbstractBaseTransformer extends TransformerAbstract
{
    protected $excludeFields = [];
    /** @var bool|array  */
    public static $structure = false;

    /**
     * Transform all the properties from the fillable array
     * @param Eloquent $object
     * @return array
     */
    public function transform(\Eloquent $object)
    {
        $objectTransformed = $object->toArray();

        /**
         * Format all dates as Iso8601 strings, this includes the created_at and updated_at columns
         */
        foreach($object->getDates() as $dateColumn) {
            if(!empty($object->$dateColumn)) {
                $objectTransformed[$dateColumn] = $object->$dateColumn->toIso8601String();
            }
        }

        //get the relations for this object and transform them using their own transformer
        foreach($object->getRelations() as $relationKey => $relation) {

            //skip pivot for now, might transform its parent at some point, not necessary now
            if($relation instanceof \Illuminate\Database\Eloquent\Relations\Pivot) {
                continue;
            }

            //if this relation is a collection of items
            if($relation instanceof \Illuminate\Database\Eloquent\Collection) {
                if( count($relation->getIterator()) > 0) {
                    $relationObject = $relation->first();
                    $class = get_class($relationObject);
                    $relationTransformerRaw = $class.'Transformer';

                    $relationTransformer = new $relationTransformerRaw;
                    if($object->$relationKey) {
                        foreach($relation->getIterator() as $key => $item) {
                            //replace the entity in the object transformed, because it probably will have been transformed
                            //need to use the specific transformer
                            $objectTransformed[camel_case($relationKey)][$key] = $relationTransformer->transform($item);
                        }
                    }
                }
            } else {
                $relationTransformerRaw = ucfirst($relationKey).'Transformer';
                $relationTransformer = new $relationTransformerRaw;
                if($object->$relationKey) {
                    $objectTransformed[camel_case($relationKey)] = $relationTransformer->transform($object->$relationKey);
                }
            }
        }

        return $objectTransformed;
    }

}