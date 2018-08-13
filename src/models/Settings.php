<?php
/**
 * Comments Work plugin for Craft CMS 3.x
 *
 * An easy to use comment plugin for Craft CMS 3
 *
 * @link      https://www.24hoursmedia.com
 * @copyright Copyright (c) 2018 24hoursmedia
 */

namespace twentyfourhoursmedia\commentswork\models;

use twentyfourhoursmedia\commentswork\CommentsWork;

use Craft;
use craft\base\Model;

/**
 * CommentsWork Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    24hoursmedia
 * @package   CommentsWork
 * @since     1.0.0
 */
class Settings extends Model
{

    public $autoApprove = true;
    public $allowAnonymous = false;

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['autoApprove', 'boolean'],
            ['allowAnonymous', 'boolean'],
            //['autoApprove', 'default', 'value' => true],
        ];
    }
}
