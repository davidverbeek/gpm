<?php

class Mage_Catalog_Helper_Customreview extends Mage_Core_Helper_Abstract {

	public function getProductReview($product) {
		if (array_key_exists('rating_summary', $product->getData())) {
			if($product->getRatingSummary()->getRatingSummary()) {
				$rating = '<div class="rating-box">';
				$rating .= '<div class="rating" style="width:'.$product->getRatingSummary()->getRatingSummary().'%"></div>';
				$rating .=	'<span class="no-display" itemprop="worstRating">0</span>';
				$rating .=	'<span class="no-display" itemprop="bestRating">5</span>';
				$rating .= '<span class="no-display" itemprop="ratingValue">'.round($product->getRatingSummary()->getRatingSummary()/20,1).'</span>
				</div>';
				return $rating;
			}
		}
	}

}