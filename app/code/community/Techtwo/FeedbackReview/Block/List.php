<?php
class Techtwo_FeedbackReview_Block_List extends Mage_Core_Block_Template
{
    protected $_limit = 0;
    protected $_orderRandomly = false;
    protected $_isHideOnNoReviews = false;

    public function setLimit($limit)
    {
        if ( !$limit )
        {
            $this->_limit = 0;
            return;
        }

        if ( !filter_var($limit, FILTER_VALIDATE_INT) )
        {
            Mage::log("Invalid limit '$limit' in techtwo feedbackreview_block_list");
            $this->_limit = 0;
            return;
        }

        $this->_limit = (int) $limit;
    }

    public function getLimit()
    {
        return $this->_limit;
    }

    public function orderRandomly($enable=true)
    {
        $this->_orderRandomly = is_bool($enable)? $enable: ('1'===$enable || 'true'===$enable);
    }

    public function isOrderRandomly()
    {
        return $this->_orderRandomly;
    }

    public function hideOnNoReviews($enable=true)
    {
        $this->_isHideOnNoReviews = is_bool($enable)? $enable: ('1'===$enable || 'true'===$enable);
    }

    public function isHideOnNoReviews()
    {
        return $this->_isHideOnNoReviews;
    }
}
