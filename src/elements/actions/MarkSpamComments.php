<?php
namespace twentyfourhoursmedia\commentswork\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use twentyfourhoursmedia\commentswork\elements\Comment;
use yii\base\Exception;

class MarkSpamComments extends ElementAction
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('comments-work', 'Mark as Spam…');
    }

    /**
     * @inheritdoc
     */
    public static function isDestructive(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getConfirmationMessage()
    {
        return Craft::t('comments-work', 'Are you sure you want to mark the selected comment(s) as spam?');
    }

    /**
     * Performs the action on any elements that match the given criteria.
     *
     * @param ElementQueryInterface $query The element query defining which elements the action should affect.
     *
     * @return bool Whether the action was performed successfully.
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        try {
            foreach ($query->all() as $comment) {
                /* @var $comment \twentyfourhoursmedia\commentswork\elements\Comment */
                $comment->status = Comment::STATUS_SPAM;
                Craft::$app->elements->saveElement($comment, false, false);

            }
        } catch (Exception $exception) {
            $this->setMessage($exception->getMessage());
            return false;
        }

        $this->setMessage(Craft::t('comments-work', 'Comments marked as spam.'));
        return true;
    }
}
