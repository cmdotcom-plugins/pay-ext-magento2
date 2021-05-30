<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

    const TABLE_DATA = [
        'tableName' => 'cm_payments_order',
        'columns' => [
            'id' => [
                'type'   => Table::TYPE_BIGINT,
                'length' => null,
                'option' => ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            ],
            'order_id' => [
                'type'   => Table::TYPE_BIGINT,
                'length' => null,
                'option' => ['unsigned' => true, 'nullable' => false, 'default' => 0]
            ],
            'order_key' => [
                'type'   => Table::TYPE_TEXT,
                'length' => 255,
                'option' => ['nullable' => false]
            ],
            'created_at'      => [
                'type'   => Table::TYPE_TIMESTAMP,
                'length' => null,
                'option' => ['nullable' => false, 'default' => Table::TIMESTAMP_INIT]
            ],
            'updated_at'      => [
                'type'   => Table::TYPE_TIMESTAMP,
                'length' => null,
                'option' => ['nullable' => true, 'default' => Table::TIMESTAMP_INIT_UPDATE]
            ],
        ],
        'comment' => 'CM payments order data',
        'indexes' => ['order_id', 'order_key']
    ];

    /**
     * @inheritDoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable(self::TABLE_DATA['tableName']);

        if (!$connection->isTableExists($tableName)) {
            $table = $connection->newTable($tableName);
            foreach (self::TABLE_DATA['columns'] as $columnName => $columnData) {
                $table->addColumn($columnName, $columnData['type'], $columnData['length'], $columnData['option']);
            }
            if (!empty(self::TABLE_DATA['indexes'])) {
                foreach (self::TABLE_DATA['indexes'] as $sIndex) {
                    $table->addIndex($setup->getIdxName(self::TABLE_DATA['tableName'], $sIndex), $sIndex);
                }
            }
            $table->setComment(self::TABLE_DATA['comment']);
            $connection->createTable($table);
        }
    }
}
