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

    /**
     * Handles submission
     *
     * @return bool|\twentyfourhoursmedia\commentswork\elements\Comment|null
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    private function handleSubmit() {
        $commentsWork = CommentsWork::$plugin->commentsWorkService;
        $request = Craft::$app->getRequest();
        $element = Craft::$app->elements->getElementById($request->getParam('elementId'),null,$request->getParam('siteId'));
        if (!$element) {
            throw new BadRequestHttpException('Element not found');
        }

        // verify signature
        $signature = $request->getParam('signature');
        if ($signature !== $commentsWork->createFormSignature($element)) {
            throw new BadRequestHttpException('Invalid signature');
        }

        $identity = Craft::$app->getUser()->getIdentity();

        // check permissions
        $check = $commentsWork->canPost($element, $identity);
        if (!$check->allowed) {
            throw new BadRequestHttpException($check->message);
        }

        $model = $commentsWork->createModel($element, Craft::$app->getUser()->getIdentity());
        $model
            ->setTitle($request->getParam('title'))
            ->setComment($request->getParam('comment'))
            ->setCommentFormat($request->getParam('commentFormat', 'text'))
        ;

        // do not post empty content
        $valid = trim($model->title) || trim($model->comment);

        $resultCommentElement = null;
        if ($valid) {
            $resultCommentElement = $commentsWork->saveModel($model);
        }
        return $resultCommentElement;
    }

    /**
     * Handles normal posts, redirects after posting the comment.
     *
     * /actions/comments-work/default/post-comment
     *
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function actionPostComment()
    {
        $commentsWork = CommentsWork::$plugin->commentsWorkService;
        $request = Craft::$app->getRequest();
        $redirect = $request->getParam('redirect');
        $commentElement = $this->handleSubmit();

        // if the comment has been saved we have a comment element
        // set a flash message through the service
        if ($commentElement) {
            $commentsWork->setSuccessFlashMessage($commentElement);
        } else {
            $commentsWork->setErrorFlashMessage();
        }

        if (!$redirect) {
            return $this->redirectToPostedUrl();
        } else {
            return $this->redirect($redirect);
        }
    }

    /**
     * Posts a comment and returns a json object with a 'success' flag
     *
     * /actions/comments-work/default/xhr-post-comment
     *
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function actionXhrPostComment()
    {
        $request = Craft::$app->getRequest();
        if (!$request->isAjax) {
            throw new BadRequestHttpException('Not an ajax call');
        }
        $commentElement = $this->handleSubmit();
        $dto = [
            'success' => $commentElement !== null
        ];
        return $this->asJson($dto);
    }
}
