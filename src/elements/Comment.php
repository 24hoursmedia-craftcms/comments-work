<?php
/**
 * Comments Work plugin for Craft CMS 3.x
 *
 * An easy to use comment plugin for Craft CMS 3
 *
 * @link      https://www.24hoursmedia.com
 * @copyright Copyright (c) 2018 24hoursmedia
 */

namespace twentyfourhoursmedia\commentswork\elements;

use craft\helpers\UrlHelper;
use craft\web\Request;
use twentyfourhoursmedia\commentswork\CommentsWork;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use twentyfourhoursmedia\commentswork\elements\actions\ApproveComments;
use twentyfourhoursmedia\commentswork\elements\actions\DeleteComments;
use twentyfourhoursmedia\commentswork\elements\actions\MakePendingComments;
use twentyfourhoursmedia\commentswork\elements\actions\MarkSpamComments;
use twentyfourhoursmedia\commentswork\elements\actions\TrashComments;
use twentyfourhoursmedia\commentswork\elements\db\CommentQuery;
use twentyfourhoursmedia\commentswork\models\CommentModel;
use twentyfourhoursmedia\commentswork\records\CommentRecord;
use craft\elements\actions\Edit;
use yii\web\User;

class Comment extends Element
{
    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_SPAM = 'SPAM';
    const STATUS_TRASHED = 'TRASHED';

    const FORMAT_PLAINTEXT = 'text';

    public $elementId;
    public $status;
    public $userId;
    public $siteId;
    public $title;
    public $comment;
    public $commentFormat;
    public $dateCreated;
    public $dateUpdated;


    /**
     * @internal
     * @return string
     */
    public function get_AdminSummary()
    {
        return strtolower(substr(trim($this->title . ' ' . $this->comment), 0, 128)) . '...';
    }
    /**
     * @internal
     * @return string
     */
    public function get_UserName()
    {
        if (!$this->userId) {
            return '';
        }
        $user = Craft::$app->users->getUserById($this->userId);
        if ($user) {
            return $user->username;
        }
    }
    /**
     * @internal
     * @return string
     */
    public function get_ElementTitle()
    {
        if (!$this->elementId) {
            return '';
        }
        $element = Craft::$app->elements->getElementById($this->elementId);
        if ($element && isset($element->title)) {
            return $element->title;
        }
    }

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('comment', '');
    }

    /**
     * @inheritdoc
     */
    public static function refHandle()
    {
        return 'cw_comment';
    }


    /**
     * Returns whether elements of this type have traditional titles.
     *
     * @return bool Whether elements of this type have traditional titles.
     */
    public static function hasTitles(): bool
    {
        return true;
    }


    public static function isLocalized(): bool
    {
        return true;
    }


    public static function hasStatuses(): bool
    {
        return false;
    }

    /**
     * Returns whether elements of this type will be storing any data in the `content`
     * table (tiles or custom fields).
     *
     * @return bool Whether elements of this type will be storing any data in the `content` table.
     */
    public static function hasContent(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getIsEditable(): bool
    {
        return false;
    }


    protected static function defineSortOptions(): array
    {
        $attributes = [
            'title' => Craft::t('comments-work', 'Titel'),
            'status' => Craft::t('comments-work', 'Status'),
            'cw_comments.dateCreated' => Craft::t('comments-work', 'Date created'),

        ];
        return $attributes;
    }


    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes = [
            'title' => ['label' => Craft::t('comments-work', 'comment title')],
            'comment' => ['label' => Craft::t('comments-work', 'comment')],
            'commentFormat' => ['label' => Craft::t('comments-work', 'comment type')],
            'status' => ['label' => Craft::t('comments-work', 'status')],
            'dateCreated' => ['label' => Craft::t('app', 'Date Created')],
            '_adminSummary' => ['label' => Craft::t('app', 'Summary')],
            '_userName' => ['label' => Craft::t('comments-work', 'Posted by')],
            '_elementTitle' => ['label' => Craft::t('comments-work', 'Content item')],
        ];

        return $attributes;
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        $attributes = ['dateCreated', 'status', '_userName', '_adminSummary', '_elementTitle'];

        return $attributes;
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['title', 'comment'];
    }

    protected static function defineActions(string $source = null): array
    {
        $actions = [];

        // Edit
        $actions[] = Craft::$app->getElements()->createAction(
            [
                'type' => Edit::class,
                'label' => Craft::t('comments-work', 'Edit comment'),
            ]
        );


        // Delete
        $actions[] = DeleteComments::class;
        $actions[] = ApproveComments::class;
        $actions[] = MakePendingComments::class;
        $actions[] = MarkSpamComments::class;
        $actions[] = TrashComments::class;
        return $actions;
    }

    /**
     * Creates an [[ElementQueryInterface]] instance for query purpose.
     * @return CommentQuery.
     */
    public static function find(): ElementQueryInterface
    {


        return new CommentQuery(static::class);
    }

    /**
     * Defines the sources that elements of this type may belong to.
     *
     * @param string|null $context The context ('index' or 'modal').
     *
     * @return array The sources.
     * @see sources()
     */
    protected static function defineSources(string $context = null): array
    {
        if ($context === 'index') {
            $sources = [
                [
                    'key' => '*',
                    'label' => Craft::t('comments-work', 'All comments'),
                    'criteria' => []
                ],
                [
                    'key' => 'approved',
                    'label' => Craft::t('comments-work', 'Approved comments'),
                    'criteria' => ['commentStatus' => CommentModel::STATUS_APPROVED]
                ],
                [
                    'key' => 'pending',
                    'label' => Craft::t('comments-work', 'Pending comments'),
                    'criteria' => ['commentStatus' => CommentModel::STATUS_PENDING]
                ],
                [
                    'key' => 'spam',
                    'label' => Craft::t('comments-work', 'Spam comments'),
                    'criteria' => ['commentStatus' => CommentModel::STATUS_SPAM]
                ],
                [
                    'key' => 'trashed',
                    'label' => Craft::t('comments-work', 'Trashed comments'),
                    'criteria' => ['commentStatus' => CommentModel::STATUS_TRASHED]

                ],
            ];
        }
        return $sources;
    }


    public function getSupportedSites(): array
    {
        $supportedSites = [];
        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            //if($this->siteId < 1 || $this->siteId == $site->id) {
            $supportedSites[] = ['siteId' => $site->id, 'enabledByDefault' => false];
            //}
        }
        return $supportedSites;
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {

        return UrlHelper::cpUrl('comments-work/edit-comment?id=' . $this->id . '&siteId=' . $this->siteId);

        return $url;
    }

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return parent::rules();
        return [
            //['someAttribute', 'string'],
            //['someAttribute', 'default', 'value' => 'Some Default'],
        ];
    }


    /**
     * Returns the field layout used by this element.
     *
     * @return FieldLayout|null
     */
    public function getFieldLayout()
    {

        return null;
    }

    public function getGroup()
    {
        return null;


    }

    // Indexes, etc.
    // -------------------------------------------------------------------------


    // Events
    // -------------------------------------------------------------------------

    /**
     * Performs actions before an element is saved.
     *
     * @param bool $isNew Whether the element is brand new
     *
     * @return bool Whether the element should be saved
     */
    public function beforeSave(bool $isNew): bool
    {
        return true;
    }

    /**
     * Performs actions after an element is saved.
     *
     * @param bool $isNew Whether the element is brand new
     *
     * @return void
     */
    public function afterSave(bool $isNew)
    {
        // Get the redirect record
        if (!$isNew) {
            $record = CommentRecord::findOne($this->id);

            if (!$record) {
                throw new \Exception('Invalid redirect ID: ' . $this->id);
            }
        } else {
            $record = new CommentRecord();
            $record->id = $this->id;
        }


        $record->elementId = $this->elementId;
        $record->status = $this->status;
        $record->userId = $this->userId;
        $record->siteId = $this->siteId;
        $record->title = $this->title;
        $record->comment = $this->comment;
        $record->commentFormat = $this->commentFormat;

        $record->save(false);
    }

    /**
     * Performs actions before an element is deleted.
     *
     * @return bool Whether the element should be deleted
     */
    public function beforeDelete(): bool
    {
        return true;
    }

    /**
     * Performs actions after an element is deleted.
     *
     * @return void
     */
    public function afterDelete()
    {
    }

    /**
     * Performs actions before an element is moved within a structure.
     *
     * @param int $structureId The structure ID
     *
     * @return bool Whether the element should be moved within the structure
     */
    public function beforeMoveInStructure(int $structureId): bool
    {
        return true;
    }

    /**
     * Performs actions after an element is moved within a structure.
     *
     * @param int $structureId The structure ID
     *
     * @return void
     */
    public function afterMoveInStructure(int $structureId)
    {
    }

    public function populateWithPostData(Request $request)
    {
        $params = $request->getBodyParams();
        if (isset($params['title'])) {
            $this->title = $params['title'];
        }
        if (isset($params['comment'])) {
            $this->comment = $params['comment'];
        }
        if (isset($params['status'])) {
            $this->status = $params['status'];
        }
    }

    /**
     * @return \craft\base\ElementInterface|null
     */
    public function getSourceElement()
    {
        if ($this->elementId) {
            return craft::$app->elements->getElementById($this->elementId);
        }
    }

    /**
     * @return User
     */
    public function getPoster()
    {
        if ($this->userId) {
            return craft::$app->elements->getElementById($this->userId);
        }
    }
}
