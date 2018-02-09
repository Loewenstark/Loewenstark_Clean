<?php

class Loewenstark_Clean_Model_Observer
extends Mage_Core_Model_Abstract
{
    /**
     * 
     * @param type $observer
     */
    public function adminhtmlPreDispatchEvent($observer)
    {
        try {
            $this->checkCronTab();
        } catch (Exception $ex) {

        }
    }

    /**
     * some crontab will hang on an incorrect state and magento
     * can not run this crontab anymore!
     * so we easily remove this state by surfing the admin area
     */
    public function checkCronTab()
    {
        // remove older than 60 days
        // 
        try {
            $date = strtotime('-80 days');
            $where = $this->_getConnection()->quoteInto('scheduled_at <= ?', $date);
            $this->_getConnection('core_write')
                    ->delete($this->_getTableName('cron_schedule'), $where);
        } catch (Exception $e) {
        }
        
        $model = Mage::getModel('cron/schedule');
        /* @var $model Mage_Cron_Model_Schedule */
        $status = array(
            Mage_Cron_Model_Schedule::STATUS_RUNNING,
            Mage_Cron_Model_Schedule::STATUS_PENDING,
        );
        // check if Aoe Scheduler is installed
        if ($model instanceof Aoe_Scheduler_Model_Schedule)
        {
            if(defined('Aoe_Scheduler_Model_Schedule::STATUS_DIDNTDOANYTHING'))
            {
                $status[] = Aoe_Scheduler_Model_Schedule::STATUS_DIDNTDOANYTHING;
            }
            if(defined('Aoe_Scheduler_Model_Schedule::STATUS_DISAPPEARED'))
            {
                $status[] = Aoe_Scheduler_Model_Schedule::STATUS_DISAPPEARED;
            }
            if(defined('Aoe_Scheduler_Model_Schedule::STATUS_REPEAT'))
            {
                $status[] = Aoe_Scheduler_Model_Schedule::STATUS_REPEAT;
            }            
            $status = array_unique($status);
        }
        $date = strtotime('-8 hours');
        $where = $this->_getConnection()->quoteInto('scheduled_at <= ?', $date)
                . ' AND '
                . $this->_getConnection()->quoteInto('status IN(?)', $status);
        $this->_getConnection('core_write')
                ->delete($this->_getTableName('cron_schedule'), $where);
        
        $where = $this->_getConnection()->quoteInto('scheduled_at <= ?', $date)
                . ' AND (status IS NULL OR status = \'\')';
        $this->_getConnection('core_write')
                ->delete($this->_getTableName('cron_schedule'), $where);
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
