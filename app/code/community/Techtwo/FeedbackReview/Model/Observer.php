<?php
class Techtwo_FeedbackReview_Model_Observer
{
	public function flush_cache($observer)
	{
		if ( !Mage::helper('techtwo_feedbackreview')->isActive() )
			return;

		$file = Mage::helper('techtwo_feedbackreview')->getReviewCachefile();
		if ( file_exists($file) )
			unlink($file);
	}

}
?>