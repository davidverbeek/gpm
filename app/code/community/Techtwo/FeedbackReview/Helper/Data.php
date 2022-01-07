<?php

class Techtwo_FeedbackReview_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_reviewBaseUrl;
    protected $_isActiveCache;

    /**
     * check required parameter ws and is active configuration
     * NOTE: disabling module in advance will remove output, but this is required for controllers, otherwise routers will keep sending an ok blank page.
     *
     * @return bool
     */
    public function isActive()
    {
        if ( NULL === $this->_isActiveCache )
        {
            $ws = Mage::getStoreConfig('feedbackreview/vars/ws');
            if ('' === $ws || !filter_var($ws, FILTER_VALIDATE_INT) )
                return false;
            $this->_isActiveCache = Mage::getConfig()->getModuleConfig('Techtwo_FeedbackReview')->is('active', 'true');
        }

        return $this->_isActiveCache;
    }

    /**
     * Retrieve the review base url as setup in general review_url in the configuration
     * @return string
     */
    public function getReviewBaseUrl()
    {
        if ( NULL === $this->_reviewBaseUrl )
            $this->_reviewBaseUrl = Mage::getStoreConfig('feedbackreview/general/review_url');
        return $this->_reviewBaseUrl;

    }

    /**
     * Retrieve the review url with all parameters appended
     * @return string
     */
    public function getReviewUrl()
    {
        // This is an example url, don't use it, i just put it here so i know how a good url looks like. The module ought to work with the configuration - Jonathan
        // http://beoordelingen.feedbackcompany.nl/samenvoordeel/scripts/flexreview/getreviewxml.cfm?ws=4249&basescore=10&publishdetails=1&nor=50
        $queries = Mage::getStoreConfig('feedbackreview/vars');
        //Mage::log($this->getReviewBaseUrl() . '?'.http_build_query($queries,'','&'),null,'feedbackreview.log');
        
        return $this->getReviewBaseUrl() . '?'.http_build_query($queries,'','&');
    }

	/** LOADS and CACHES data
	*
	* @return SimpleXMLElement
	*/
    public function getReviewsXml()
    {
        //$file = $this->getReviewCachefile(); 
        $file = Mage::getBaseDir().DS.'feedback_company_4400.xml'; 
        
		return @simplexml_load_file($file);
		
        if ( false !== $file && $this->downloadFile( $this->getReviewUrl() , $file, 3600) )
            return @simplexml_load_file($file);
        return false;
	}

    public function getReviewCachefile()
    {
        $ws = Mage::getStoreConfig('feedbackreview/vars/ws');
        if ( '' === $ws || !filter_var($ws, FILTER_VALIDATE_INT))
        {
            Mage::log("Techtwo Feedback Review has no valid 'ws' parameter configured", Zend_Log::ERR );
            return false;
        }

        return Mage::getBaseDir('cache').DS.'feedback_company_'.$ws.'.xml';
    }

    /**
     * @param $url
     * @param $saveToFile
     * @param bool $cacheTime
     * @return bool
     */
    protected function downloadFile( $url, $saveToFile, $cacheTime=false )
    {
		if ( $cacheTime && file_exists($saveToFile) && filemtime($saveToFile) + $cacheTime > time() )
		{
			return true;
		}

		// create a new cURL resource
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$data = curl_exec($ch);


		$res = curl_getinfo($ch);
		curl_close($ch);

		return 200 == $res['http_code'] && $data? false !== file_put_contents($saveToFile, $data):false;		
	}

    /**
    * @return array
    */
	public function getReviews()
	{
		$xml = $this->getReviewsXml();

        if ( !$xml || !isset($xml->reviewDetails) )
            return false;

        // print_r($xml);

        /*
        * Due 'xml' way a single node is an object and multiply nodes is an array.
        * This makes it hard to distinct a single review or multi review, so this makes an array
        */

        $reviews = array();
        //$_reviews = $xml->reviewDetails->reviewDetail; // this doesn't work since a single review wouldn't be wrapped in an array.
        $total_reviews = isset($xml->reviewDetails->reviewDetail)? $xml->reviewDetails->children()->count():0;
        if ( 1 === $total_reviews )
            $reviews = array( $this->xmlToArray($xml->reviewDetails->reviewDetail) );
        elseif ( $total_reviews > 1 )
        {
            $reviews = $this->xmlToArray($xml->reviewDetails);
            $reviews = $reviews['reviewDetail'];
        }

        return $reviews;
    }

    /**
    * SimpleXMLElement has a nasty way to deal with arrays
    */
    public function hasReviews()
    {
        $xml = $this->getReviewsXml();
        return ( !$xml || !isset($xml->reviewDetails) || !isset($xml->reviewDetails->reviewDetail)? 0: $xml->reviewDetails->reviewDetail->count() ) > 0;
        /*
        $reviews = $this->getReviews();
        return !empty($reviews['reviewDetail']) && isset($reviews['reviewDetail'][0]) &&  is_array($reviews['reviewDetail'][0]);
        */
    }

    public function xmlToArray( $xml )
    {
        $array = json_decode(json_encode($xml), true);
        foreach (array_slice($array, 0) as $key => $value)
        {
            if (empty($value))
            {
                $array[$key] = null;
            }
            elseif (is_array($value))
            {
                $array[$key] = $this->xmlToArray($value);
            }
        }
        return $array;
    }
}
