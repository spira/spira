<?php

class RestController extends BaseController
{


    public static $entity;

    protected $transformer;

    public function __construct($extending = false)
    {
        parent::__construct();

        $entity = static::$entity;
        $this->extending = $extending;

        if (empty($this->transformer)) {
            $this->transformer = $entity . 'Transformer';
        }
    }

    public function getAll()
    {
        $entity = static::$entity;

        $entities = $entity::all();

        $this->transformer = $entity.'Transformer';

        return $this->response->collection($entities, new $this->transformer);
    }


    public function getOne($id)
    {
        $entity = static::$entity;

        $resource = $entity::find($id);

        if(! $resource) {
            return $this->response->errorNotFound($entity . ' not found');
        }

        return $this->response->item($resource, new $this->transformer);
    }

}