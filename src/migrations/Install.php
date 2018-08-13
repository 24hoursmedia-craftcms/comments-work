<?php
/**
 * Comments Work plugin for Craft CMS 3.x
 *
 * An easy to use comment plugin for Craft CMS 3
 *
 * @link      https://www.24hoursmedia.com
 * @copyright Copyright (c) 2018 24hoursmedia
 */

namespace twentyfourhoursmedia\commentswork\migrations;

use twentyfourhoursmedia\commentswork\CommentsWork;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;
use twentyfourhoursmedia\commentswork\models\CommentModel;
use twentyfourhoursmedia\commentswork\records\CommentRecord;

/**
 * Comments Work Install Migration
 *
 * If your plugin needs to create any custom database tables when it gets installed,
 * create a migrations/ folder within your plugin folder, and save an Install.php file
 * within it using the following template:
 *
 * If you need to perform any additional actions on install/uninstall, override the
 * safeUp() and safeDown() methods.
 *
 * @author    24hoursmedia
 * @package   CommentsWork
 * @since     1.0.0
 */
class Install extends Migration
{

    const TBL_COMMENTS = CommentRecord::TABLE;

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    protected function createTables()
    {
        $tablesCreated = false;
        $tableSchema = Craft::$app->db->schema->getTableSchema(self::TBL_COMMENTS);
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                self::TBL_COMMENTS,
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    // Custom columns in the table
                    'elementId' => $this->integer()->notNull(),
                    'siteId' => $this->integer()->notNull(),
                    'userId' => $this->integer()->null(),
                    'title' => $this->string(255)->null()->defaultValue(''),
                    'comment' => $this->text()->null(),
                    'commentFormat' => $this->string(16)->notNull()->defaultValue('text'),
                    'status' => $this->string(16)->null()->defaultValue(CommentModel::STATUS_PENDING),
                ]
            );
        }

        return $tablesCreated;
    }

    protected function createIndexes()
    {

        $this->createIndex(
            $this->db->getIndexName(
                self::TBL_COMMENTS,
                'status',
                false
            ),
            self::TBL_COMMENTS,
            'status',
            false
        );
    }

    protected function addForeignKeys()
    {

        $this->addForeignKey(
            $this->db->getForeignKeyName(self::TBL_COMMENTS, 'siteId'),
            self::TBL_COMMENTS,
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(self::TBL_COMMENTS, 'elementId'),
            self::TBL_COMMENTS,
            'elementId',
            '{{%elements}}',
            'id',
            'CASCADE',
            'CASCADE'
        );


        $this->addForeignKey(
            $this->db->getForeignKeyName(self::TBL_COMMENTS, 'userId'),
            self::TBL_COMMENTS,
            'userId',
            '{{%elements}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    protected function insertDefaultData()
    {
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTableIfExists(self::TBL_COMMENTS);
    }
}
