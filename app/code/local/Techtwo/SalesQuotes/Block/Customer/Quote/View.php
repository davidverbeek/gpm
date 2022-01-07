<?php

class Techtwo_SalesQuotes_Block_Customer_Quote_View extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('salesquotes/customer/quote/view.phtml');
    }

    protected function _prepareLayout()
    {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->__('Quote # %s', $this->getQuote()->getId()));
        }
       
    }
    
    
    public function getCartHasItems() {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if ($quote && $quote->getIsCartAuto()) {
            return $quote->hasItems();
        }
    }


    public function getQuote()
    {
        return Mage::registry('current_quote');
    }

    public function getBackUrl()
    {
        return Mage::getUrl('*/*/history');
    }

    
    public function getProductOptions($item)
    {
        $options = array();
        if ($optionIds = $item->getOptionByCode('option_ids')) {
            $options = array();
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $item->getProduct()->getOptionById($optionId)) {

                    $quoteItemOption = $item->getOptionByCode('option_' . $option->getId());

                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setQuoteItemOption($quoteItemOption);

                    $options[] = array(
                        'label' => $option->getTitle(),
                        'value' => $group->getFormattedOptionValue($quoteItemOption->getValue()),
                        'print_value' => $group->getPrintableOptionValue($quoteItemOption->getValue()),
                        'option_id' => $option->getId(),
                        'option_type' => $option->getType(),
                        'custom_view' => $group->isCustomizedView()
                    );
                }
            }
        }
        if ($addOptions = $item->getOptionByCode('additional_options')) {
            $options = array_merge($options, unserialize($addOptions->getValue()));
        }
        return $options;
    }

    
    /**
     * Accept option value and return its formatted view
     *
     * @param mixed $optionValue
     * Method works well with these $optionValue format:
     *      1. String
     *      2. Indexed array e.g. array(val1, val2, ...)
     *      3. Associative array, containing additional option info, including option value, e.g.
     *          array
     *          (
     *              [label] => ...,
     *              [value] => ...,
     *              [print_value] => ...,
     *              [option_id] => ...,
     *              [option_type] => ...,
     *              [custom_view] =>...,
     *          )
     *
     * @return array
     */
    public function getFormatedOptionValue($optionValue)
    {
        $optionInfo = array();

        // define input data format
        if (is_array($optionValue)) {
            if (isset($optionValue['option_id'])) {
                $optionInfo = $optionValue;
                if (isset($optionInfo['value'])) {
                    $optionValue = $optionInfo['value'];
                }
            } elseif (isset($optionValue['value'])) {
                $optionValue = $optionValue['value'];
            }
        }

        // render customized option view
        if (isset($optionInfo['custom_view']) && $optionInfo['custom_view']) {
            $_default = array('value' => $optionValue);
            if (isset($optionInfo['option_type'])) {
                try {
                    $group = Mage::getModel('catalog/product_option')->groupFactory($optionInfo['option_type']);
                    return array('value' => $group->getCustomizedView($optionInfo));
                } catch (Exception $e) {
                    return $_default;
                }
            }
            return $_default;
        }

        // truncate standard view
        $result = array();
        if (is_array($optionValue)) {
            $_truncatedValue = implode("\n", $optionValue);
            $_truncatedValue = nl2br($_truncatedValue);
            return array('value' => $_truncatedValue);
        } else {
            $_truncatedValue = Mage::helper('core/string')->truncate($optionValue, 55, '');
            $_truncatedValue = nl2br($_truncatedValue);
        }

        $result = array('value' => $_truncatedValue);

        if (Mage::helper('core/string')->strlen($optionValue) > 55) {
            $result['value'] = $result['value'] . ' <a href="#" class="dots" onclick="return false">...</a>';
            $optionValue = nl2br($optionValue);
            $result = array_merge($result, array('full_view' => $optionValue));
        }

        return $result;
    }
    

}
