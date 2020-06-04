<?php
/**
 * Comments Work plugin for Craft CMS 3.x
 *
 * An easy to use comment plugin for Craft CMS 3
 *
 * @link      https://www.24hoursmedia.com
 * @copyright Copyright (c) 2018 24hoursmedia
 */

namespace twentyfourhoursmedia\commentswork\variables;

use twentyfourhoursmedia\commentswork\CommentsWork;

use Craft;
use twentyfourhoursmedia\commentswork\services\CommentsWorkService;

/**
 * Comments Work Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.commentsWork }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    24hoursmedia
 * @package   CommentsWork
 * @since     1.0.0
 */
class CommentsWorkVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Get the commentswork service from twig
     *
     * @example
     * {{ craft.commentsWork.service }}
     *
     * @return CommentsWorkService
     */
    public function service() : CommentsWorkService
    {
        return CommentsWork::$plugin->commentsWorkService;
    }
}
