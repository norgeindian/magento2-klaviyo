<?php


namespace Klaviyo\Reclaim\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Quote\Setup\QuoteSetup;
use Magento\Sales\Setup\SalesSetup;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.1.1', '<')) {
            $installer = $setup;
            $installer->startSetup();

            /**
             * Create products table
             */
            // $this->createKlProductsTable($installer);
            // $this->createKlSyncTable($installer);

            $eavTable1 = $installer->getTable('quote');
            $eavTable2 = $installer->getTable('sales_order');

            $columns = [
                'kl_sms_consent' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'SMS Consent'
                ],
                'kl_email_consent' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Email Consent'
                ],
            ];

            $connection = $installer->getConnection();

            foreach ($columns as $name => $definition) {
                $connection->addColumn($eavTable1, $name, $definition);
                $connection->addColumn($eavTable2, $name, $definition);
            }

            $installer->endSetup();
        }
    }

    /**
     * @param SchemaSetupInterface $installer
     *
     * @return null
     */
    private function createKlProductsTable($installer)
    {
      $tableName = 'kl_products';
      $this->dropTableIfExists($installer, $tableName);

      $klProductsTable = $installer->getConnection()->newTable($tableName);
      $klProductsTable = $this->addColumnsToKlProductsTable($klProductsTable);
      $klProductsTable = $this->addIndexesToKlProductsTable($installer, $klProductsTable, $tableName);
      $installer->getConnection()->createTable($klProductsTable);
    }

    /**
     * @param Table $klProductsTable
     * @return Table
     */
    private function addColumnsToKlProductsTable($klProductsTable)
    {
      return $klProductsTable->addColumn(
        'id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        [
          'primary' => true,
          'identity' => true,
          'unsigned' => true,
          'nullable' => false
        ],
        'Primary Key'
      )
      ->addColumn(
        'payload',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        null,
        ['nullable' => false],
        'Payload'
      )
      ->addColumn(
        'status',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        55,
        ['nullable' => false],
        'Status'
      )
      ->addColumn(
        'created_at',
        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
        null,
        [],
        'Created at Time'
      )
      ->addColumn(
        'updated_at',
        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
        null,
        [],
        'Updated at Time'
      );
    }

    /**
     * @param SchemaSetupInterface $installer
     * @param Table $klProductsTable
     * @return \Magento\Framework\DB\Ddl\Table
     */
    private function addIndexesToKlProductsTable($installer, $klProductsTable, $tableName)
    {
        return $klProductsTable->addIndex(
            $installer->getIdxName($tableName, ['status']),
            ['status']
        )
          ->addIndex(
              $installer->getIdxName($tableName, ['created_at']),
              ['created_at']
          )
          ->addIndex(
              $installer->getIdxName($tableName, ['updated_at']),
              ['updated_at']
          );
    }


        /**
         * @param SchemaSetupInterface $installer
         *
         * @return null
         */
        private function createKlSyncTable($installer)
        {
          $tableName = 'kl_sync';
          $this->dropTableIfExists($installer, $tableName);

          $klSyncTable = $installer->getConnection()->newTable($tableName);
          $klSyncTable = $this->addColumnsToKlSyncTable($klSyncTable);
          $klSyncTable = $this->addIndexesToKlSyncTable($installer, $klSyncTable, $tableName);
          $installer->getConnection()->createTable($klSyncTable);
        }

        /**
         * @param Table $klSyncTable
         * @return Table
         */
        private function addColumnsToKlSyncTable($klSyncTable)
        {
          return $klSyncTable->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [
              'primary' => true,
              'identity' => true,
              'unsigned' => true,
              'nullable' => false
            ],
            'Primary Key'
          )
          ->addColumn(
            'payload',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Payload'
            )
          ->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            55,
            ['nullable' => false],
            'Status'
            )
          ->addColumn(
            'topic',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            55,
            ['nullable' => false],
            'Topic'
          )
          ->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [],
            'Created at Time'
          )
          ->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [],
            'Updated at Time'
          );
        }

        /**
         * @param SchemaSetupInterface $installer
         * @param Table $klSyncTable
         * @return \Magento\Framework\DB\Ddl\Table
         */
        private function addIndexesToKlSyncTable($installer, $klSyncTable, $tableName)
        {
            return $klSyncTable->addIndex(
                $installer->getIdxName($tableName, ['topic']),
                ['topic']
            )
              ->addIndex(
                  $installer->getIdxName($tableName, ['status']),
                  ['status']
              )
              ->addIndex(
                  $installer->getIdxName($tableName, ['created_at']),
                  ['created_at']
              )
              ->addIndex(
                  $installer->getIdxName($tableName, ['updated_at']),
                  ['updated_at']
              );
        }

    /**
     * @param SchemaSetupInterface $installer
     * @param string $table
     */
    private function dropTableIfExists($installer, $table)
    {
        if ($installer->getConnection()->isTableExists($installer->getTable($table))) {
            $installer->getConnection()->dropTable(
                $installer->getTable($table)
            );
        }
    }
}
