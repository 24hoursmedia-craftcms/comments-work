<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 13-08-18
 * Time: 14:53
 */

namespace twentyfourhoursmedia\commentswork\controllers;
use Craft;
use craft\web\Controller;
use craft\helpers\UrlHelper;

class SettingsController extends Controller
{

    public function actionIndex()
    {
        $this->requireLogin();
        $variables = [];

        return $this->renderTemplate('redirect/redirects', $variables);
    }

}