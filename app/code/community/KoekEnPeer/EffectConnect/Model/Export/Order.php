<?php

	class KoekEnPeer_EffectConnect_Model_Export_Order extends KoekEnPeer_EffectConnect_Model_Export
	{
		public function updateOrder($observer)
		{
			$order     = $observer->getEvent()->getOrder();
			$ecOrderId = $order->getEffectconnect();

			if (!$ecOrderId)
			{
				return false;
			}

			if (in_array($order->getStatus(), $this->_getCompleteStatuses()))
			{
				$status = KoekEnPeer_EffectConnect_Model_Order::STATUS_COMPLETED;
			} else
			{
				$status = $status = Mage::getModel('effectconnect/order')
					->convertStatus($order->getState())
				;
			}

			if (!$status)
			{
				return false;
			}

			$trackingData = $this->getTracking(array($order->getId()));
			if (isset($trackingData[$order->getId()]))
			{
				$trackingNumbers  = $trackingData[$order->getId()]['numbers'];
				$trackingCarriers = $trackingData[$order->getId()]['carriers'];
			} else
			{
				$trackingNumbers  = array();
				$trackingCarriers = '';
			}

			$invoiceNumbers = array();
			foreach ($order->getInvoiceCollection() as $invoice)
			{
				$invoiceNumbers[] = $invoice->getIncrementId();
			}

			$this->getApi()
				->updateOrder(
					$ecOrderId,
					array(
						'status'           => $status,
						'invoice_internal' => implode(' / ', $invoiceNumbers),
						'tracking_code'    => implode(';', array_filter($trackingNumbers)),
						'tracking_carrier' => $trackingCarriers
					)
				)
			;

			return true;
		}

		public function getTracking(array $orderIds)
		{
			$preparedOrderIds = array_fill(0, count($orderIds), '?');
			$preparedOrderIds = implode(',', $preparedOrderIds);

			$statuses = $this->_getCompleteStatuses();

			$preparedStatuses = array_fill(0, count($statuses), '?');
			$preparedStatuses = implode(',', $preparedStatuses);

			$query = $this->getQuery(
				'order_shipping',
				array(
					'order_ids'    => $preparedOrderIds,
					'status_codes' => $preparedStatuses
				)
			);

			$trackingData = $this->getData(
				$query,
				array_merge($orderIds,$statuses)
			);
			if (empty($trackingData))
			{
				return false;
			}

			$trackingItems = array();
			foreach ($trackingData as $trackingItem)
			{
				$entityId        = $trackingItem['entity_id'];
				$trackingNumber  = $trackingItem['track_number'];
				$trackingCarrier = $trackingItem['title'];
				
				//ST: Added Tracking URL
				// $trackingUrl = $trackingItem['transsmart_tracking_url'];

				if (!isset($trackingItems[$entityId]))
				{
					$trackingItems[$entityId] = array(
						'numbers'  => array(),
						'carriers' => array()
					);
				}

				//ST: Remove below code
				if ($trackingNumber && !in_array($trackingNumber, $trackingItems[$entityId]['numbers']))
				{
					$trackingItems[$entityId]['numbers'][] = $trackingNumber;
				}
				// ST: Added below new code
				// if ($trackingUrl && !in_array($trackingUrl, $trackingItems[$entityId]['numbers']))
				// {
				// 	$trackingItems[$entityId]['numbers'][] = $trackingUrl;
				// }
				if ($trackingCarrier && !in_array($trackingCarrier, $trackingItems[$entityId]['carriers']))
				{
					$trackingItems[$entityId]['carriers'][] = $trackingCarrier;
				}
			}

			foreach ($trackingItems as &$trackingItem)
			{
				if (empty($trackingItem['numbers']))
				{
					$trackingItem['numbers'] = false;
				}

				$trackingItem['carriers'] = implode(', ', $trackingItem['carriers']);
			}

			return $trackingItems;
		}

		private function _getCompleteStatuses()
		{
			$statuses = Mage::getStoreConfig('effectconnect_options/order/complete_status');
			$statuses = explode(',', $statuses);
			$statuses = array_filter($statuses);
			if (empty($statuses))
			{
				$statuses[] = Mage_Sales_Model_Order::STATE_COMPLETE;
			}

			return $statuses;
		}
	}