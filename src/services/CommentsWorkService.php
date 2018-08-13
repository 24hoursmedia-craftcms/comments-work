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
use craft\db\Query;
use craft\elements\User;
use twentyfourhoursmedia\commentswork\CommentsWork;

use Craft;
use craft\base\Component;
use twentyfourhoursmedia\commentswork\elements\Comment;
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
    // Public Methods
    // =========================================================================

    /**
     * Check if anonymous posting is enabled for an element
     * @param Element $element
     * @return bool
     */
    public function allowAnonymous(Element $element)
    {
       return (bool)CommentsWork::$plugin->getSettings()->allowAnonymous;
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

    /**
     * @api
     * @param CommentModel $model
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
        return Craft::$app->getElements()->saveElement($comment, false, false);
    }

    /**
     * @api
     * @param $elementOrelementId
     * @param array $options
     * @return int
     */
    public function countComments($elementOrelementId, $options = [])
    {
        static $defaultOptions = [
            'status' => CommentModel::STATUS_APPROVED
        ];
        $options = array_merge($defaultOptions, $options);

        $elementId = is_numeric($elementOrelementId) ? $elementOrelementId : $elementOrelementId ? $elementOrelementId->id : null;
        $conditions = [];
        if ($elementId !== null) {
            $conditions['elementId'] = $elementId;
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
     * @api
     * @param $elementOrelementId
     * @param int $first
     * @param int $count
     * @param array $options
     * @return array
     */
    public function fetchComments($elementOrelementId, $first = 0, $count = 10, $options = [])
    {
        static $defaultOptions = [
            'status' => CommentModel::STATUS_APPROVED
        ];
        $options = array_merge($defaultOptions, $options);

        $elementId = is_numeric($elementOrelementId) ? $elementOrelementId : $elementOrelementId ? $elementOrelementId->id : null;
        $conditions = [];
        if ($elementId !== null) {
            $conditions['elementId'] = $elementId;
        }
        if ($options['status'] !== CommentModel::STATUS_ALL) {
            $conditions['status'] = $options['status'];
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
        return sha1($element->id . '_' . $element->uid);
    }

    /**
     * @param $id
     * @return Element|null|Comment|Comment[]
     */
    public function findById($id) {
        return Comment::findOne(['id' => $id]);
    }

    public function getStatusOptions()
    {
        return [
          Comment::STATUS_APPROVED => Comment::STATUS_APPROVED,
            Comment::STATUS_PENDING => Comment::STATUS_PENDING,
            Comment::STATUS_SPAM => Comment::STATUS_SPAM,
            Comment::STATUS_TRASHED => Comment::STATUS_TRASHED,

        ];

    }
}
