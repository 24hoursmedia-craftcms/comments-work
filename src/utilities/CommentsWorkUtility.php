<?php
/**
 * Comments Work plugin for Craft CMS 3.x
 *
 * An easy to use comment plugin for Craft CMS 3
 *
 * @link      https://www.24hoursmedia.com
 * @copyright Copyright (c) 2018 24hoursmedia
 */

namespace twentyfourhoursmedia\commentswork\utilities;

use twentyfourhoursmedia\commentswork\CommentsWork;
use twentyfourhoursmedia\commentswork\assetbundles\commentsworkutilityutility\CommentsWorkUtilityUtilityAsset;

use Craft;
use craft\base\Utility;

/**
 * Comments Work Utility
 *
 * Utility is the base class for classes representing Control Panel utilities.
 *
 * https://craftcms.com/docs/plugins/utilities
 *
 * @author    24hoursmedia
 * @package   CommentsWork
 * @since     1.0.0
 */
class CommentsWorkUtility extends Utility
{
    // Static
    // =========================================================================

    /**
     * Returns the display name of this utility.
     *
     * @return string The display name of this utility.
     */
    public static function displayName(): string
    {
        return Craft::t('comments-work', 'CommentsWorkUtility');
    }

    /**
     * Returns the utility’s unique identifier.
     *
     * The ID should be in `kebab-case`, as it will be visible in the URL (`admin/utilities/the-handle`).
     *
     * @return string
     */
    public static function id(): string
    {
        return 'commentswork-comments-work-utility';
    }

    /**
     * Returns the path to the utility's SVG icon.
     *
     * @return string|null The path to the utility SVG icon
     */
    public static function iconPath()
    {
        return Craft::getAlias("@twentyfourhoursmedia/commentswork/assetbundles/commentsworkutilityutility/dist/img/CommentsWorkUtility-icon.svg");
    }

    /**
     * Returns the number that should be shown in the utility’s nav item badge.
     *
     * If `0` is returned, no badge will be shown
     *
     * @return int
     */
    public static function badgeCount(): int
    {
        return 0;
    }

    /**
     * Returns the utility's content HTML.
     *
     * @return string
     */
    public static function contentHtml(): string
    {
        Craft::$app->getView()->registerAssetBundle(CommentsWorkUtilityUtilityAsset::class);

        $someVar = 'Have a nice day!';
        return Craft::$app->getView()->renderTemplate(
            'comments-work/_components/utilities/CommentsWorkUtility_content',
            [
                'someVar' => $someVar
            ]
        );
    }
}
