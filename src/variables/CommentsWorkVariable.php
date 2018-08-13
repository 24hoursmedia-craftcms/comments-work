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
     * Whatever you want to output to a Twig template can go into a Variable method.
     * You can have as many variable functions as you want.  From any Twig template,
     * call it like this:
     *
     *     {{ craft.commentsWork.service }}
     *
     * Or, if your variable requires parameters from Twig:
     *
     *     {{ craft.commentsWork.exampleVariable(twigValue) }}
     *
     * @param null $optional
     * @return string
     */
    public function service()
    {
        return CommentsWork::$plugin->commentsWorkService;
    }
}
