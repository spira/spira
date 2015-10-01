<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Rbac\Item;

abstract class Item
{
    const TYPE_ROLE = 1;
    const TYPE_PERMISSION = 2;

    /**
     * @var int the type of the item. This should be either [[TYPE_ROLE]] or [[TYPE_PERMISSION]].
     */
    public $type;
    /**
     * @var string the name of the item. This must be globally unique.
     */
    public $name;
    /**
     * @var string the item description
     */
    public $description;
    /**
     * @var string name of the rule associated with this item
     */
    public $ruleName;
    /**
     * @var int UNIX timestamp representing the item creation time
     */
    public $createdAt;
    /**
     * @var int UNIX timestamp representing the item updating time
     */
    public $updatedAt;

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getRuleName()
    {
        return $this->ruleName;
    }

    /**
     * Add rule to the item.
     *
     * @param Rule $rule
     */
    public function attachRule(Rule $rule)
    {
        if (! is_null($this->ruleName)) {
            throw new \InvalidArgumentException('Only one rule can be attached, first detach the rule');
        }
        $this->ruleName = get_class($rule);
    }

    /**
     * Remove currently attached rule.
     */
    public function detachRule()
    {
        $this->ruleName = null;
    }
}
