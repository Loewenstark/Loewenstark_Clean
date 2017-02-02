<?php

class Loewenstark_Clean_Model_Cron
extends Mage_Core_Model_Abstract
{
    public function cleanQuote()
    {
        // remove old entries older than 1 year
        $date = date('Y-m-d H:i:s', strtotime('-1 year'));
        try {
            $where = $this->_getConnection()->quoteInto('updated_at <= ?', $date);
            $this->_getConnection('core_write')
                    ->delete($this->_getTableName('sales_flat_quote'), $where);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        try {
            $where = $this->_getConnection()->quoteInto('created_at <= ?', $date);
            $this->_getConnection('core_write')
                    ->delete($this->_getTableName('log_quote'), $where);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        // remove all guest older than 4 days
        $date = date('Y-m-d H:i:s', strtotime('-4 days'));
        try {
            $where = $this->_getConnection()->quoteInto('updated_at <= ?', $date)
                    . ' AND '
                    . '( customer_is_guest = 0 OR customer_id IS NULL OR customer_id = 0 )'
            ;
            $this->_getConnection('core_write')
                    ->delete($this->_getTableName('sales_flat_quote'), $where);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        try {
            // remove all old quotes - order has been generated
            $where = $this->_getConnection()->quoteInto('is_active = ?', 0);
            $this->_getConnection('core_write')
                    ->delete($this->_getTableName('sales_flat_quote'), $where);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
    
    public function cleanLog()
    {
        try {
            $date = date('Y-m-d H:i:s', strtotime('-60 days'));
            $where = $this->_getConnection()->quoteInto('visit_time = ?', $date);
            $this->_getConnection('core_write')
                    ->delete($this->_getTableName('log_url'), $where);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        try {
            $date = date('Y-m-d H:i:s', strtotime('-60 days'));
            $where = $this->_getConnection()->quoteInto('last_visit_at = ?', $date);
            $this->_getConnection('core_write')
                    ->delete($this->_getTableName('log_visitor'), $where);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        try {
            $date = date('Y-m-d H:i:s', strtotime('-60 days'));
            $where = $this->_getConnection()->quoteInto('last_visit_at = ?', $date);
            $this->_getConnection('core_write')
                    ->delete($this->_getTableName('log_visitor_online'), $where);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        try {
            $date = date('Y-m-d H:i:s', strtotime('-60 days'));
            $where = $this->_getConnection()->quoteInto('login_at = ?', $date);
            $this->_getConnection('core_write')
                    ->delete($this->_getTableName('log_customer'), $where);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * 
     * @return Mage_Core_Model_Resource
     */
    protected function _resource()
    {
        return Mage::getSingleton('core/resource');
    }

    /**
     * 
     * @param string $name
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getConnection($name = 'core_read')
    {
        return $this->_resource()->getConnection($name);
    }

    /**
     * 
     * @param string $modelEntity
     * @return string
     */
    protected function _getTableName($modelEntity)
    {
        return $this->_resource()->getTableName($modelEntity);
    }
}
