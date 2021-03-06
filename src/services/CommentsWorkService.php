<?php
/**
 * Comments Work plugin for Craft CMS 3.x
 *
 * An easy to use comment plugin for Craft CMS 3
 *
 * @link      https://www.24hoursmedia.com
 * @copyright Copyright (c) 2018 24hoursmedia
 */

namespace twentyfourhoursmedia\commentswork\services;

use craft\base\Element;
use craft\base\ElementInterface;
use craft\db\Query;
use craft\elements\User;
use twentyfourhoursmedia\commentswork\CommentsWork;

use Craft;
use craft\base\Component;
use twentyfourhoursmedia\commentswork\elements\Comment;
use twentyfourhoursmedia\commentswork\events\AllowedEvent;
use twentyfourhoursmedia\commentswork\interfaces\AllowedInterface;
use twentyfourhoursmedia\commentswork\models\CommentModel;
use twentyfourhoursmedia\commentswork\records\CommentRecord;

/**
 * CommentsWorkService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    24hoursmedia
 * @package   CommentsWork
 * @since     1.0.0
 */
class CommentsWorkService extends Component
{

    const EVENT_CAN_COMMENT = 'can_comment';

    // Public Methods
    // =========================================================================

    /**
     * Check if anonymous posting is enabled for an element
     *
     * @param Element $element
     * @return bool
     * @deprecated in favor of the ::canComment method
     */
    public function allowAnonymous(Element $element)
    {
        return $this->canComment($element, null)->allowed;
    }

    /**
     * Check if a user (or no user) is allowed to post a comment on an element
     * Event listeners can override the default allowAnonymous plugin setting.
     *
     * @param ElementInterface $element
     * @param \yii\web\User|null $user
     * @return AllowedEvent
     */
    public function canComment(ElementInterface $element, User $user = null) : AllowedInterface
    {
        $event = new AllowedEvent($element, $user);
        // set initial value to true for users, or to the default settings value for anonymous users
        if (!$user && !(bool)CommentsWork::$plugin->getSettings()->allowAnonymous) {
            $event->allowed = false;
            $event->message = 'Anonymous comments are not allowed';
        }
        $this->trigger(self::EVENT_CAN_COMMENT, $event);
        return $event;
    }

    public function createModel(Element $element, User $user = null)
    {
        $settings = CommentsWork::$plugin->getSettings();
        $autoApprove = (bool)$settings->autoApprove;

        $model = new CommentModel();
        $model->userId = $user ? $user->id : null;
        $model->elementId = $element->id;
        $model->siteId = $element->siteId;
        $model->status = $autoApprove ? CommentModel::STATUS_APPROVED : CommentModel::STATUS_PENDING;
        return $model;
    }

    /**
     * Saves the comment model to an element
     * Returns the saved element on success, or false otherwise.
     *
     * @param CommentModel $model
     * @return bool|Comment
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @api
     */
    public function saveModel(CommentModel $model)
    {
        $comment = new Comment();
        $comment->userId = $model->userId;
        $comment->siteId = $model->siteId;
        $comment->elementId = $model->elementId;
        $comment->status = $model->status;
        $comment->title = $model->title;
        $comment->comment = $model->comment;
        $comment->commentFormat = $model->commentFormat;
        $success = Craft::$app->getElements()->saveElement($comment, false, false);
        return $success ? $comment : false;
    }

    /**
     * @param $elementOrelementId
     * @param array $options
     * @return int
     * @api
     */
    public function countComments(Element $element = null, $options = [])
    {
        static $defaultOptions = [
            'status' => CommentModel::STATUS_APPROVED,
            'all_sites' => false
        ];
        $options = array_merge($defaultOptions, $options);


        $conditions = [];
        if ($element) {
            $conditions['elementId'] = $element->id;
        }
        if (!$options['all_sites'] && $element) {
            $conditions['siteId'] = $element->siteId;
        }
        if ($options['status'] !== CommentModel::STATUS_ALL) {
            $conditions['status'] = $options['status'];
        }

        $num = CommentRecord::find()
            ->where($conditions)
            ->count('id');
        return $num;
    }

    /**
     * @param Element $element
     * @param int $first
     * @param int $count
     * @param array $options
     * @return array
     * @api
     */
    public function fetchComments(Element $element, $first = 0, $count = 10, $options = [])
    {
        static $defaultOptions = [
            'status' => CommentModel::STATUS_APPROVED,
            'all_sites' => false
        ];
        $options = array_merge($defaultOptions, $options);


        $conditions = [];
        $conditions['elementId'] = $element->id;
        if ($options['status'] !== CommentModel::STATUS_ALL) {
            $conditions['status'] = $options['status'];
        }
        if (!$options['all_sites']) {
            $conditions['siteId'] = $element->siteId;
        }

        $records = CommentRecord::find()
            ->where($conditions)
            ->addOrderBy(['dateCreated' => SORT_DESC, 'id' => SORT_DESC])
            ->offset($first)
            ->limit($count)
            ->all();

        $models = array_map(function (CommentRecord $record) {
            return $this->populateModelFromRecord($record);
        }, $records);

        return $models;

    }


    /**
     * Create a signature to prevent malicious users to submit to arbitrary content
     * @param Element $element
     * @return string
     */
    public function createFormSignature(Element $element)
    {
        return sha1($element->id . '_' . $element->uid . '_' . $element->siteId);
    }

    /**
     * @param $id
     * @return Element|null|Comment|Comment[]
     */
    public function findById($id)
    {
        return Comment::find()->enabledForSite(false)->id($id)->one();
    }

    /**
     * @param $id
     * @return Element|null|Comment|Comment[]
     */
    public function findAnyById($id)
    {
        return Comment::find()->enabledForSite(false)->id($id)->commentStatus(null)->one();
    }

    public function getStatusOptions() : array
    {
        return [
            Comment::STATUS_APPROVED => Comment::STATUS_APPROVED,
            Comment::STATUS_PENDING => Comment::STATUS_PENDING,
            Comment::STATUS_SPAM => Comment::STATUS_SPAM,
            Comment::STATUS_TRASHED => Comment::STATUS_TRASHED,
        ];
    }

    /**
     * Set a flash message for the user indicating success
     * @param Comment $element
     * @internal
     */
    public function setErrorFlashMessage()
    {
        $message = $this->createFlashMessage([
            'error' => true,
            'expires' => time() + self::FLASHMESSAGE_TTL
        ]);
        $session = Craft::$app->getSession()->setFlash(self::FLASHMESSAGE_KEY, $message, true);
    }

    /**
     * Checks the user session is a comment has just been posted
     * @api
     */
    public function checkJustPosted()
    {
        $message = Craft::$app->getSession()->getFlash(self::FLASHMESSAGE_KEY, null, true);
        if (!is_array($message)) {
            return false;
        }
        // feed through creator to ensure all keys are set
        $message = $this->createFlashMessage($message);
        // expired?
        if (time() > $message['expires']) {
            return false;
        }
        return $message;
    }

    /**
     * @return \craft\elements\db\ElementQueryInterface|\twentyfourhoursmedia\commentswork\elements\db\CommentQuery
     * @api
     */
    public function createElementQuery()
    {
        return Comment::find();
    }

    protected function populateModelFromRecord(CommentRecord $record)
    {
        $model = new CommentModel();
        $model->id = $record->id;
        $model->siteId = $record->siteId;
        $model->userId = $record->userId;
        $model->elementId = $record->elementId;
        $model->status = $record->status;
        $model->title = $record->title;
        $model->comment = $record->comment;
        $model->commentFormat = $record->commentFormat;
        $model->dateCreated = $record->dateCreated;
        $model->user = $record->userId ? Craft::$app->getUsers()->getUserById($record->userId) : null;
        return $model;
    }


    const FLASHMESSAGE_KEY = '_comment_post';
    const FLASHMESSAGE_TTL = 20; // number of seconds that a flash message stays valid

    /**
     * Create a message from a template
     * @param array $options
     * @return array
     */
    private function createFlashMessage($options = [])
    {
        return array_merge([
            'error' => null,
            'expires' => null,
            'status' => null
        ], $options);
    }

    /**
     * Set a flash message for the user indicating success
     * @param Comment $element
     * @internal
     */
    public function setSuccessFlashMessage(Comment $element)
    {
        $message = $this->createFlashMessage([
            'error' => false,
            'expires' => time() + self::FLASHMESSAGE_TTL,
            'status' => $element->status
        ]);
        $session = Craft::$app->getSession()->setFlash(self::FLASHMESSAGE_KEY, $message, true);
    }


}
