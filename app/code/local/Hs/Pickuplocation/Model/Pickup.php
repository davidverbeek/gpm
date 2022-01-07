<?php
// app/code/local/Hs/Pickuplocation/Model
class Hs_Pickuplocation_Model_Pickup extends Mage_Shipping_Model_Carrier_Tablerate {
	
	protected $_code = 'hs_pickuplocation';

	/**
	 * Collect and get rates
	 *
	 * @param Mage_Shipping_Model_Rate_Request $request
	 * @return Mage_Shipping_Model_Rate_Result
	 */
	public function collectRates(Mage_Shipping_Model_Rate_Request $request)	{
		if (!$this->getConfigFlag('active')) {
			return false;
		}

		// exclude Virtual products price from Package value if pre-configured
		if (!$this->getConfigFlag('include_virtual_price') && $request->getAllItems()) {
			foreach ($request->getAllItems() as $item) {
				if ($item->getParentItem()) {
					continue;
				}
				if ($item->getHasChildren() && $item->isShipSeparately()) {
					foreach ($item->getChildren() as $child) {
						if ($child->getProduct()->isVirtual()) {
							$request->setPackageValue($request->getPackageValue() - $child->getBaseRowTotal());
						}
					}
				} elseif ($item->getProduct()->isVirtual()) {
					$request->setPackageValue($request->getPackageValue() - $item->getBaseRowTotal());
				}
			}
		}

		// Free shipping by qty
		$freeQty = 0;
		$freePackageValue = 0;
		if ($request->getAllItems()) {
			foreach ($request->getAllItems() as $item) {
				if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
					continue;
				}

				if ($item->getHasChildren() && $item->isShipSeparately()) {
					foreach ($item->getChildren() as $child) {
						if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
							$freeShipping = is_numeric($child->getFreeShipping()) ? $child->getFreeShipping() : 0;
							$freeQty += $item->getQty() * ($child->getQty() - $freeShipping);
						}
					}
				} elseif ($item->getFreeShipping()) {
					$freeShipping = is_numeric($item->getFreeShipping()) ? $item->getFreeShipping() : 0;
					$freeQty += $item->getQty() - $freeShipping;
					$freePackageValue += $item->getBaseRowTotal();
				}
			}
			$oldValue = $request->getPackageValue();
			$request->setPackageValue($oldValue - $freePackageValue);
		}

		if ($freePackageValue) {
			$request->setPackageValue($request->getPackageValue() - $freePackageValue);
		}
		if (!$request->getConditionName()) {
			$conditionName = $this->getConfigData('condition_name');
			$request->setConditionName($conditionName ? $conditionName : $this->_default_condition_name);
		}

		// Package weight and qty free shipping
		$oldWeight = $request->getPackageWeight();
		$oldQty = $request->getPackageQty();

		$request->setPackageWeight($request->getFreeMethodWeight());
		$request->setPackageQty($oldQty - $freeQty);

		$result = $this->_getModel('shipping/rate_result');
		$rate = $this->getRate($request);

		$request->setPackageWeight($oldWeight);
		$request->setPackageQty($oldQty);

		if (!empty($rate) && $rate['price'] >= 0) {
			$method = $this->_getModel('shipping/rate_result_method');

			$method->setCarrier($this->_code);
			$method->setCarrierTitle($this->getConfigData('title'));

			$method->setMethod($this->_code);
			$method->setMethodTitle($this->getConfigData('name'));

			if ($request->getFreeShipping() === true || ($request->getPackageQty() == $freeQty)) {
				$shippingPrice = 0;
			} else {
				$shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
			}

			$method->setPrice($shippingPrice);
			$method->setCost($rate['cost']);

			$result->append($method);
		} elseif (empty($rate) && $request->getFreeShipping() === true) {
			/**
			 * was applied promotion rule for whole cart
			 * other shipping methods could be switched off at all
			 * we must show table rate method with 0$ price, if grand_total more, than min table condition_value
			 * free setPackageWeight() has already was taken into account
			 */
				$request->setPackageValue($freePackageValue);
				$request->setPackageQty($freeQty);
				$rate = $this->getRate($request);
				if (!empty($rate) && $rate['price'] >= 0) {
					$method = $this->_getModel('shipping/rate_result_method');

					$method->setCarrier($this->_code);
					$method->setCarrierTitle($this->getConfigData('title'));

					$method->setMethod($this->_code);
					$method->setMethodTitle($this->getConfigData('name'));

					$method->setPrice(0);
					$method->setCost(0);

					$result->append($method);
				}
		} else {
			$error = $this->_getModel('shipping/rate_result_error');
			$error->setCarrier($this->_code);
			$error->setCarrierTitle($this->getConfigData('title'));
			$error->setErrorMessage($this->getConfigData('specificerrmsg'));
			$result->append($error);
		}
		return $result;
	}
 
	public function getAllowedMethods() {
		return array(
			'hs_pickuplocation' => $this->getConfigData('name'),
		);
	}
	
}