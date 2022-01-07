<?php
    $installer  = $this;
    $connection = $installer->getConnection();

    $deprecatedTable = $installer->getTable('plus_mapping');
    $table           = $installer->getTable('effectconnect_mapping');
    if ($connection->isTableExists($deprecatedTable))
    {
        $connection->renameTable($deprecatedTable, $table);
    } else
    {
        $installer->addAttribute(
            'order',
            'effectconnect',
            array('type' => 'int')
        );
        $installer->addAttribute(
            'quote',
            'effectconnect',
            array('type' => 'int')
        );

        $installer->addAttribute(
            'order',
            'ext_order',
            array(
                'type'     => 'varchar',
                'nullable' => false,
                'grid'     => true
            )
        );
        $installer->addAttribute(
            'quote',
            'ext_order',
            array(
                'type'     => 'varchar',
                'nullable' => false,
                'grid'     => true
            )
        );

        $mappingTable = $connection
            ->newTable($table)
            ->addColumn(
                'mapping_id',
                Varien_Db_Ddl_Table::TYPE_INTEGER,
                11,
                array(
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'identity' => true
                )
            )
            ->addColumn(
                'channel_id',
                Varien_Db_Ddl_Table::TYPE_INTEGER,
                11,
                array(
                    'nullable' => false,
                    'default'  => 0
                )
            )
            ->addColumn(
                'store_id',
                Varien_Db_Ddl_Table::TYPE_INTEGER,
                11,
                array(
                    'nullable' => false,
                    'default'  => 0
                )
            )
            ->addColumn(
                'customer_group_id',
                Varien_Db_Ddl_Table::TYPE_INTEGER,
                11,
                array(
                    'default'  => 0
                )
            )
            ->addColumn(
                'discount_code',
                Varien_Db_Ddl_Table::TYPE_VARCHAR,
                64
            )
            ->addColumn(
                'price_attribute',
                Varien_Db_Ddl_Table::TYPE_VARCHAR,
                64
            )
            ->setComment('EffectConnect channel mapping')
        ;

        $connection->createTable($mappingTable);
    }

    $installer->endSetup();