<?php

/**
 * Copyright (c) 2015 S.L.I. Systems, Inc. (www.sli-systems.com) - All Rights
 * Reserved
 * This file is part of Learning Search Connect.
 * Learning Search Connect is distributed under a limited and restricted
 * license - please visit www.sli-systems.com/LSC for full license details.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE. TO THE MAXIMUM EXTENT PERMITTED BY APPLICABLE LAW, IN NO
 * EVENT WILL SLI BE LIABLE TO YOU OR ANY OTHER PARTY FOR ANY GENERAL, DIRECT,
 * INDIRECT, SPECIAL, INCIDENTAL OR CONSEQUENTIAL LOSS OR DAMAGES OF ANY
 * CHARACTER ARISING OUT OF THE USE OF THE CODE AND/OR THE LICENSE INCLUDING
 * BUT NOT LIMITED TO PERSONAL INJURY, LOSS OF DATA, LOSS OF PROFITS, LOSS OF
 * ASSIGNMENTS, DATA OR OUTPUT FROM THE SERVICE BEING RENDERED INACCURATE,
 * FAILURE OF CODE, SERVER DOWN TIME, DAMAGES FOR LOSS OF GOODWILL, BUSINESS
 * INTERRUPTION, COMPUTER FAILURE OR MALFUNCTION, OR ANY AND ALL OTHER DAMAGES
 * OR LOSSES OF WHATEVER NATURE, EVEN IF SLI HAS BEEN INFORMED OF THE
 * POSSIBILITY OF SUCH DAMAGES.
 */

/**
 * Feed generation manager.
 *
 * @package    SLI
 * @subpackage Search
 */
class SLI_Search_Model_FeedManager
{
    const DEMO = false;
    const FEED_MESSAGE_KEY_FORMAT = 'Feed %s';

    /**
     * Starts feed generation (and upload) for the given stores (default is all)
     *
     * @param array $storeIds
     *
     * @return array
     * @throws SLI_Search_Exception
     */
    public function generateFeedForStores(array $storeIds = array())
    {
        /** @var $feedHelper SLI_Search_Helper_Feed */
        $feedHelper = Mage::helper('sli_search/feed');
        /** @var $dataHelper SLI_Search_Helper_Data */
        $dataHelper = Mage::helper('sli_search/data');

        if ($feedHelper->feedLocksExist()) {
            throw new SLI_Search_Exception("One or more feeds are being generated. Generation temporarily locked.");
        }

        $feedHelper->checkWritePermissions();

        // ok, lets lock things
        $feedHelper->lockFeedProcessing();

        $feedStatus = array('error' => false);

        /** @var $feedGenerator SLI_Search_Model_FeedGenerator */
        $feedGenerator = Mage::getModel('sli_search/FeedGenerator');

        /** @var $stores Mage_Core_Model_Resource_Store_Collection */
        $stores = Mage::app()->getStores();

        $messages = array();
        /** @var $store Mage_Core_Model_Store */
        foreach ($stores as $store) {
            $storeId = $store->getId();
            if ($storeIds && !in_array($storeId, $storeIds)) {
                // skip not specified stores
                continue;
            }

            // set store context as $dataHelper (and others) depend on it - yay!
            Mage::app()->setCurrentStore($storeId);

            /** @var $logger SLI_Search_Helper_FeedLogger */
            $logger = Mage::helper('sli_search/feedLogger');
            $logger->setStoreId($storeId);

            if (!$dataHelper->isFeedEnabled($storeId)) {
                $logger->debug("Catalog Feed " . $storeId . " disabled");
                $messages[sprintf(self::FEED_MESSAGE_KEY_FORMAT, $storeId)] = "Disabled";
                continue;
            }

            $this->logSystemSettings($logger);

            // backup feed files per store
            $feedHelper->backUpFeeds($storeId);

            try {
                $feedGenerator->generateForStoreId($storeId, $logger);
                $message = 'Success';
                $protocolMessage = $dataHelper->isSftpEnabled() ? 'SFTP' : 'FTP';
                if (!self::DEMO && $dataHelper->isUseFtp()) {
                    $this->uploadFeed($storeId, $logger, $dataHelper->isSftpEnabled());
                    $message .= "; upload($protocolMessage) OK.";
                } else {
                    $message .= "; upload($protocolMessage) disabled.";
                }

                $messages[sprintf(self::FEED_MESSAGE_KEY_FORMAT, $storeId)] = $message;
            } catch (Exception $e) {
                Mage::logException($e);
                $message = "Exception generating catalog feed $storeId -> " . $e->getMessage();
                $logger->error($message, array('exception' => $e));
                $feedStatus['error'] = true;
                $messages[sprintf(self::FEED_MESSAGE_KEY_FORMAT, $storeId)] = $message;
            }
        }
        $feedStatus['messages'] = $messages;

        $feedHelper->unlockFeedGeneration();

        return $feedStatus;
    }

    /**
     * Sends the feed file using ftp or sftp to system configured location
     *
     * @param int $storeId
     * @param SLI_Search_Helper_FeedLogger $logger
     * @param bool $isSftp
     *
     * @throws SLI_Search_Exception
     * @throws Varien_Io_Exception
     */
    protected function uploadFeed($storeId, SLI_Search_Helper_FeedLogger $logger, $isSftp)
    {
        /** @var $feedHelper SLI_Search_Helper_Feed */
        $feedHelper = Mage::helper('sli_search/feed');
        /** @var $dataHelper SLI_Search_Helper_Data */
        $dataHelper = Mage::helper('sli_search/data');

        $pathToFeedFile = $feedHelper->getFeedFile($storeId);

        $logger->trace("Sending Feed...");
        $transport = $isSftp ? new Varien_Io_Sftp() : new Varien_Io_Ftp();
        if ($isSftp) {
            $transport->open(
                array(
                    'host' => $dataHelper->getFtpHost(),
                    'username' => $dataHelper->getFtpUser(),
                    'password' => $dataHelper->getFtpPass()
                )
            );
            $logger->trace("Uploading to remote location (VIA sftp): " . $dataHelper->getFtpPath() . '/' . $feedHelper->getFeedFileName($storeId));
            $result = $transport->write($dataHelper->getFtpPath() . '/' . $feedHelper->getFeedFileName($storeId), $pathToFeedFile);
        } else {
            $transport->open(
                array(
                    'host' => $dataHelper->getFtpHost(),
                    'user' => $dataHelper->getFtpUser(),
                    'password' => $dataHelper->getFtpPass(),
                    'path' => $dataHelper->getFtpPath(),
                    'passive' => 'true',
                )
            );
            $logger->trace("Uploading to remote location: " . $feedHelper->getFeedFileName($storeId));
            $result = $transport->write($feedHelper->getFeedFileName($storeId), $pathToFeedFile);
        }

        if ($result) {
            $logger->error("Feed Sent: $pathToFeedFile");
        } else {
            $logger->error("Feed Failed to Uploaded to FTP/SFTP: $pathToFeedFile");
            throw new SLI_Search_Exception("Feed Failed to Uploaded to FTP/SFTP.");
        }
    }

    /**
     * @param SLI_Search_Helper_FeedLogger $logger
     */
    protected function logSystemSettings(SLI_Search_Helper_FeedLogger $logger)
    {
        $resourceManager = Mage::getResourceModel('sli_search/feedManager');


        $variables = $resourceManager->getSystemSettings();

        $logger->debug('============================================================================');
        $logger->debug('DB Settings:');
        if (!empty($variables)) {
            foreach ($variables as $result) {
                $logger->debug(sprintf('  %s: %s', $result['Variable_name'], $result['Value']));
            }
        }

        $logger->debug('PHP Settings:');
        foreach (array('memory_limit', 'max_execution_time') as $key) {
            $logger->debug(sprintf('  %s: %s', $key, ini_get($key)));
        }
    }
}