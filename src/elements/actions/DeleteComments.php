<?php

namespace twentyfourhoursmedia\commentswork\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use yii\base\Exception;


class DeleteComments extends ElementAction
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('app', 'Delete…');
    }

    /**
     * @inheritdoc
     */
    public static function isDestructive(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getConfirmationMessage()
    {
        return Craft::t('comments-work', 'Are you sure you want to delete the selected comment(s)?');
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
            foreach ($query->all() as $redirect) {

            Craft::$app->getElements()->deleteElement($redirect);

            }
        } catch (Exception $exception) {
            $this->setMessage($exception->getMessage());

            return false;
        }

        $this->setMessage(Craft::t('comments-work', 'Comments deleted.'));

        return true;
    }
}
