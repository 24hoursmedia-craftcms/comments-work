<?php
/**
 * Comments Work plugin for Craft CMS 3.x
 *
 * An easy to use comment plugin for Craft CMS 3
 *
 * @link      https://www.24hoursmedia.com
 * @copyright Copyright (c) 2018 24hoursmedia
 */

namespace twentyfourhoursmedia\commentswork\twigextensions;

use craft\base\Element;
use twentyfourhoursmedia\commentswork\CommentsWork;

use Craft;
use twentyfourhoursmedia\commentswork\models\CommentModel;

/**
 * Twig can be extended in many ways; you can add extra tags, filters, tests, operators,
 * global variables, and functions. You can even extend the parser itself with
 * node visitors.
 *
 * http://twig.sensiolabs.org/doc/advanced.html
 *
 * @author    24hoursmedia
 * @package   CommentsWork
 * @since     1.0.0
 */
class CommentsWorkTwigExtension extends \Twig_Extension
{
    // Public Methods
    // =========================================================================

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'CommentsWork';
    }

    /**
     * Returns an array of Twig filters, used in Twig templates via:
     *
     *      {{ 'something' | someFilter }}
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('commentAsHtml', [$this, 'commentAsHtml']),
        ];
    }

    /**
     * Returns an array of Twig functions, used in Twig templates via:
     *
     *      {% set this = someFunction('something') %}
     *
    * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('commentAsHtml', [$this, 'commentAsHtml']),
            new \Twig_SimpleFunction('signCommentForm', [$this, 'signForm'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Formats a comment as html
     * @param CommentModel $comment
     * @return string
     */
    public function commentAsHtml(CommentModel $comment)
    {

        $result = '';
        switch ($comment->commentFormat) {
            case CommentModel::FORMAT_PLAINTEXT:
                return nl2br(htmlspecialchars($comment->comment));
                break;
            default:
                return $comment->comment;

        }


        return $result;
    }

    public function signForm(Element $element)
    {
        return '<input type="hidden" name="signature" value="' . CommentsWork::$plugin->commentsWorkService->createFormSignature($element) . '" />';
    }
}
