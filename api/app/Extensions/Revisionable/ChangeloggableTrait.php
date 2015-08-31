<?php

namespace App\Extensions\Revisionable;

use App;
use Venturecraft\Revisionable\RevisionableTrait;

trait ChangeloggableTrait
{
    use RevisionableTrait;

    /**
     * Register a syncing model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @param  int  $priority
     *
     * @return void
     */
    public static function syncing($callback, $priority = 0)
    {
        static::registerModelEvent('syncing', $callback, $priority);
    }

    /**
     * Register a synced model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @param  int  $priority
     *
     * @return void
     */
    public static function synced($callback, $priority = 0)
    {
        static::registerModelEvent('synced', $callback, $priority);
    }

    /**
     * Create the event listeners for model events.
     *
     * @return  void
     */
    public static function bootChangeloggableTrait()
    {
        static::syncing(function ($model, $relation) {
            $model->preSync($relation);
        });

        static::synced(function ($model, $relation, $ids) {
            $model->postSync($relation, $ids);
        });
    }

    /**
     * Invoked before a model is synced.
     *
     * @param  string $key
     *
     * @return void
     */
    public function preSync($relation)
    {
        if ((!isset($this->revisionEnabled) || $this->revisionEnabled)
            && $this->isRelationRevisionable($relation)
        ) {
            // Get only the IDs from the relationship
            $ids = array_keys($this->$relation->modelKeys());

            // And store them under the relationship name
            $this->originalData = [$relation => $ids];
        }
    }

    /**
     * Called after a model is successfully synced.
     *
     * @param  string $key
     * @param  array  $ids
     *
     * @return void
     */
    public function postSync($key, array $ids)
    {
        if (isset($this->historyLimit) && $this->revisionHistory()->count() >= $this->historyLimit) {
            $limitReached = true;
        } else {
            $limitReached = false;
        }

        if (isset($this->revisionCleanup)) {
            $revisionCleanup = $this->revisionCleanup;
        } else {
            $revisionCleanup = false;
        }

        if (((!isset($this->revisionEnabled) || $this->revisionEnabled))
            && (!$limitReached || $revisionCleanup)
            && array_key_exists($key, $this->originalData)
        ) {
            $data = [
                'revisionable_type' => get_class($this),
                'revisionable_id' => $this->getKey(),
                'key' => $key,
                'old_value' => json_encode(array_get($this->originalData, $key)),
                'new_value' => json_encode($ids),
                'user_id' => $this->getUserId(),
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
            ];

            if ($limitReached && $revisionCleanup) {
                $toDelete = $this->revisionHistory()->orderBy('id', 'asc')->first();
                $toDelete->delete();
            }

            $revision = new Revision;
            \DB::table($revision->getTable())->insert($data);
        }
    }

    /**
     * Determines if the relationship is revisionable.
     *
     * @param  string $relation
     *
     * @return boolean
     */
    protected function isRelationRevisionable($relation)
    {
        if (isset($this->keepRevisionOf)) {
            return in_array($relation, $this->keepRevisionOf);
        }

        if (isset($this->dontKeepRevisionOf)) {
            return !in_array($relation, $this->dontKeepRevisionOf);

        }

        return true;
    }

    /**
     * Defines the polymorphic relationship
     *
     * @return mixed
     */
    public function revisionHistory()
    {
        return $this->morphMany(Revision::class, 'revisionable');
    }

    /**
     * Attempt to find the user id of the currently logged in user.
     *
     * @return string|null
     */
    private function getUserId()
    {
        $jwtAuth = App::make('Tymon\JWTAuth\JWTAuth');

        if ($user = $jwtAuth->user()) {
            return $user->user_id;
        }

        return null;
    }
}
