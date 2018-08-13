<?php
/**
 * Comments Work plugin for Craft CMS 3.x
 *
 * An easy to use comment plugin for Craft CMS 3
 *
 * @link      https://www.24hoursmedia.com
 * @copyright Copyright (c) 2018 24hoursmedia
 */

namespace twentyfourhoursmedia\commentswork\controllers;

use twentyfourhoursmedia\commentswork\CommentsWork;

use Craft;
use craft\web\Controller;
use twentyfourhoursmedia\commentswork\models\CommentModel;
use yii\web\BadRequestHttpException;

/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    24hoursmedia
 * @package   CommentsWork
 * @since     1.0.0
 */
class DefaultController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['post-comment'];


    public function actionPostComment()
    {
        $commentsWork = CommentsWork::$plugin->commentsWorkService;

        $request = Craft::$app->getRequest();
        $redirect = $request->getParam('redirect');
        $element = Craft::$app->elements->getElementById($request->getParam('elementId'));
        if (!$element) {
            throw new BadRequestHttpException('Element not found');
        }

        // verify signature
        $signature = $request->getParam('signature');
        if ($signature !== $commentsWork->createFormSignature($element)) {
            throw new BadRequestHttpException('Invalid signature');
        }

        $identity = Craft::$app->getUser()->getIdentity();
        if (!$identity && !$commentsWork->allowAnonymous($element)) {
            throw new BadRequestHttpException('Anonymous posting not allowed');
        }

        $model = $commentsWork->createModel($element, Craft::$app->getUser()->getIdentity());
        $model
            ->setTitle($request->getParam('title'))
            ->setComment($request->getParam('comment'))
            ->setCommentFormat($request->getParam('commentFormat', 'text'));

        // do not post empty content
        $valid = trim($model->title) || trim($model->comment);
        if ($valid) {
            $commentsWork->saveModel($model);
        }
        if (!$redirect) {
            return $this->redirectToPostedUrl();
        } else {
            return $this->redirect($redirect);
        }
    }
}
