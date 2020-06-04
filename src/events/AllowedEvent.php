<?php
/**
 * Created by PhpStorm
 * User: eapbachman
 * Date: 04/06/2020
 */

namespace twentyfourhoursmedia\commentswork\events;

use craft\base\ElementInterface;
use craft\elements\User;
use twentyfourhoursmedia\commentswork\interfaces\AllowedInterface;
use yii\base\Event;

/**
 * Class CommentPermissionsEvent
 * Check if something is allowed for a user on an element.
 *
 *
 */
class AllowedEvent extends Event implements AllowedInterface
{
    /**
     * @var ElementInterface
     */
    public $element;

    /**
     * @var User | null
     */
    public $user;

    /**
     * @var bool
     */
    public $allowed = true;

    /**
     * A message why one is not allowed to post
     * @var null | string
     */
    public $message = '';

    public function __construct($element, $user)
    {
        parent::__construct();
        $this->element = $element;
        $this->user = $user;
    }

    public function isAllowed(): bool
    {
        // TODO: Implement isAllowed() method.
    }

    public function getMessage() : string
    {
        return (string)$this->message;
    }

}