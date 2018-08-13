<?php

namespace twentyfourhoursmedia\commentswork\elements\db;

use Craft;
use craft\db\QueryAbortedException;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;


class CommentQuery extends ElementQuery
{
    // Properties
    // =========================================================================

    // General parameters
    // -------------------------------------------------------------------------

    /**
     * @var bool Whether to only return global sets that the user has permission to edit.
     */
    public $editable = false;

    /**
     * @var string|string[]|null The handle(s) that the resulting global sets must have.
     */
    public $commentStatus;


    /**
     * @inheritdoc
     */
    public function __construct($elementType, array $config = [])
    {
        // Default orderBy
        if (!isset($config['orderBy'])) {
            //$config['orderBy'] = 'dateCreated';
        }


        parent::__construct($elementType, $config);
    }

    /**
     * Sets the [[editable]] property.
     *
     * @param bool $value The property value (defaults to true)
     *
     * @return static self reference
     */
    public function editable(bool $value = true)
    {
        $this->editable = $value;

        return $this;
    }

    public function commentStatus($value)
    {

        $this->commentStatus = $value;
        return $this;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {

        $this->joinElementTable('cw_comments');


        //   $this->joinElementTable('elements_sites');

        $this->query->select([
            'elements_sites.siteId',
            'cw_comments.status',
            'cw_comments.title',
            'cw_comments.comment',
            'cw_comments.commentFormat',
            'cw_comments.elementId',
            'cw_comments.userId',

        ]);

        // $this->subQuery->andWhere(Db::parseParam('status', null));

        if ($this->commentStatus) {
            $this->subQuery->andWhere(Db::parseParam('cw_comments.status', $this->commentStatus));
        }


        return parent::beforePrepare();
    }

    // Private Methods
    // =========================================================================

    /**
     * Applies the 'editable' param to the query being prepared.
     *
     * @throws QueryAbortedException
     */
    private function _applyEditableParam()
    {
        if ($this->editable) {
            // Limit the query to only the global sets the user has permission to edit
            $editableSetIds = Craft::$app->getGlobals()->getEditableSetIds();
            $this->subQuery->andWhere(['elements.id' => $editableSetIds]);
        }
    }
}
