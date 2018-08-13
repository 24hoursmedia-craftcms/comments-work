<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 13-08-18
 * Time: 09:54
 */

namespace twentyfourhoursmedia\commentswork\models;

use Craft;
use craft\base\Model;
use craft\elements\User;

class CommentModel extends Model
{

    const STATUS_ALL = 'ALL';   // not to be stored, for querying only

    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_SPAM = 'SPAM';
    const STATUS_TRASHED = 'TRASHED';

    const FORMAT_PLAINTEXT = 'text';

    public $id;
    public $elementId;
    public $siteId;
    public $userId;
    public $title;
    public $comment;
    public $commentFormat = self::FORMAT_PLAINTEXT;
    public $status;
    public $dateCreated;

    /**
     * @var User
     */
    public $user;

    /**
     * @param mixed $title
     * @return CommentModel
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }



    /**
     * @param mixed $comment
     * @return CommentModel
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @param mixed $commentFormat
     * @return CommentModel
     */
    public function setCommentFormat($commentFormat)
    {
        $this->commentFormat = $commentFormat;
        return $this;
    }

    public function isAnonymous()
    {
        return !$this->userId;

    }

    /**
     * @param mixed $status
     * @return CommentModel
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }




    public function rules()
    {

    }

}