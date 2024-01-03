<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class MonduTransactionItem extends AbstractDb
{
    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mondu_transaction_items', 'entity_id');
    }

    /**
     * Delete records
     *
     * @param string $transactionId
     * @return int
     * @throws LocalizedException
     */
    public function deleteRecords($transactionId)
    {
        $table = $this->getMainTable();
        $where = [];

        $where[] = $this->getConnection()->quoteInto('`mondu_transaction_id` = ?', $transactionId);

        return $this->getConnection()->delete($table, $where);
    }
}
