<?php
declare(strict_types = 1);
namespace Param\CcppCreditCard\Setup;

use Param\CcppCreditCard\Model\Total\InstallmentFee;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->addOrderTotalField($setup, InstallmentFee::TOTAL_CODE, InstallmentFee::LABEL);
        $this->addOrderTotalField($setup, InstallmentFee::BASE_TOTAL_CODE, InstallmentFee::BASE_LABEL);
        foreach (['sales_invoice', 'sales_creditmemo'] as $entity)
        {
            foreach ([InstallmentFee::TOTAL_CODE => InstallmentFee::LABEL,
                            InstallmentFee::BASE_TOTAL_CODE => InstallmentFee::BASE_LABEL] as $attributeCode => $attributeLabel)
            {
                $setup->getConnection()->addColumn($setup->getTable($entity), $attributeCode, [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => (float)null,
                    'comment' => $attributeLabel,
                ]);
            }
        }
        $setup->endSetup();
    }

    public function addOrderTotalField(
        SchemaSetupInterface $setup,
        string $fieldName,
        string $fieldComment
    ) {
        $setup->getConnection()->addColumn($setup->getTable('sales_order'), $fieldName, [
            'type' => Table::TYPE_DECIMAL,
            'length' => '12,4',
            'nullable' => false,
            'default' => (float)null,
            'comment' => $fieldComment,
        ]);
    }
}
