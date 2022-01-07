<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Hs_Featured_Helper_Data extends Mage_Core_Helper_Abstract
{
	protected $flag       = 0;
    protected $counter    = 0;
    protected $CatHtml       = '';
    protected $top        = '';
    protected $mobile     = '';
    protected $mainCate   = '';
	
	function __construct() {
        global $mainCate,$CatHtml;
        $this->setTop();
        $this->setMobile();
        //$rootCatId     = Mage::app()->getStore()->getRootCategoryId();
        //$mainCate = $this->getTreeCategories($rootCatId, false);
    }
    public function getProductUnit($unitLabel)
    {
        $singularStockLabel=array('stuk'=>'stuks','blister'=>'blisters','doos'=>'dozen','haspel'=>'haspels','kg'=>'kg','koker'=>'kokers','lengte'=>'lengtes','ltr'=>'liter','meter'=>'meter','paar'=>'paar','pak'=>'pakken','rol'=>'rollen','set'=>'sets','stel'=>'stellen','zak'=>'zakken');
        
        if(array_key_exists(strtolower($unitLabel), $singularStockLabel))
        { 
            return $unitLabel."|".$singularStockLabel[strtolower($unitLabel)];
        }
        else{
            return array_search(strtolower($unitLabel),$singularStockLabel)."|".$unitLabel;
        }
        return false;
        
    }
    public function getStockUnit($stock,$unitLabel)
    {
        $stockUnit=  explode("|", $unitLabel);
        
        if(count($stockUnit))
        {
            if($stock==1)
            {
                return strtolower($stockUnit[0]);
            }
            else
            {
                return strtolower($stockUnit[1]);
            }
        }
        
    }
	
	public function getCurrentCategoryObj(){
		$currentCategory = Mage::registry('current_category');
		if ($currentCategory instanceof Mage_Catalog_Model_Category) {
			return $currentCategory;
		}
		return NULL;
		
    }
	
	public function getCustomFiltersData() {
		$subsubcats = '';
		
		$_category = Mage::registry('current_category');

        if (!$_category instanceof Mage_Catalog_Model_Category) {
            return NULL;
        }

		$level = $_category->getLevel();
		$hasChild = $_category->hasChildren();
	
		if($level == 3 && $hasChild == 1) {
			$subsubcats = $_category->getChildren();
		}
		if($level == 4 && $hasChild == 0){
			$subsubcats = $_category->getId();
		}
		foreach (explode(',', $subsubcats) as $subsubCatid) {
			$_subCategory = Mage::getModel('catalog/category')->load($subsubCatid);
			$CatCustomFilter[$subsubCatid] = $_subCategory->getCustomFilters();
		}
		
		if (count($CatCustomFilter) == 0)
			$CatCustomFilter[$_category->getId()] = $_category->getData('custom_filters');
		
		return $CatCustomFilter;
    }
	
	public function getProductCustomFilter($_product,$filters) {
		$custom_filters = array();
		$ProductCat = $_product->getCategoryIds();
		foreach ($ProductCat as $_cat) {
			if (array_key_exists($_cat, $filters)) {
				$custom_filters = explode(",", $filters[$_cat]);
			}
		}
		return $custom_filters;
	}
	public function getGroupedProductSorting($_product) {
			/* The code is for sorting the grouped products listing */
			
			$_associatedProducts = $_product->getTypeInstance(true)->getAssociatedProducts($_product);
			$dispcount = count($_associatedProducts);
			foreach ($_associatedProducts as $_item) {
				$index[$_item->getSku()] = $_item;
			}
			if (isset($index) && is_array($index)) {
				ksort($index);
				$_associatedProducts = $index;
			}
			return $_associatedProducts;
	}
	
	public function getIsFeaturedProduct($_product) {
		$array = array();
		$labelValue = $_product->getResource()->getAttribute('featuredlabel')->getFrontend()->getValue($_product);
		$labelData = '';
        
        if ($labelValue && $labelValue != $this->__('No')) {
            $labelData = Mage::getModel('featured/featuredlabel')->getCollection()
                        ->addFieldToFilter('option_id', $_product->getFeaturedlabel())
                        ->getFirstItem();
        }
		$array['labelvalue'] = $labelValue;
		$array['labeldata'] = $labelData;
		return $array;
    }
	
	public function getFirstAssociativeItem($_product,$_mode) {
		
		$_associatedProducts = $_product->getTypeInstance(true)->getAssociatedProducts($_product); 
		foreach ($_associatedProducts as $_item):
			$_itemProduct = Mage::getModel('catalog/product')->load($_item->getId());
			$unit = $this->getProductUnit($_itemProduct->getData('verkoopeenheid'));
			if (!(int) $_itemProduct->getData('afwijkenidealeverpakking')){
				$prijsfactor = $_itemProduct->getData('idealeverpakking');
			} else {
				$prijsfactor = 1;	
			}
			$stockunit = $this->getStockUnit($prijsfactor, $unit);
			if($_mode == 'list') {
				return ($prijsfactor == '') ? '' : $prijsfactor. " " .$stockunit; 
			}else{
				return $prijsfactor. " " .$stockunit;
			}
			
		endforeach;
		
	}

    public function getAssociativeItemCount($_product) {


        $groupTypeId = Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED;

        $_associatedProducts = $_product->getTypeInstance(true)->getChildrenIds($_product->getId());
        return count($_associatedProducts[$groupTypeId]);
    }

	public function getFirstAssociativeItemPrijsfactor($_product) {
		
		$prijsfactor = '';
		$_associatedProducts = $_product->getTypeInstance(true)->getAssociatedProducts($_product); 
		foreach ($_associatedProducts as $_item):
			$_itemProduct = Mage::getModel('catalog/product')->load($_item->getId());
			$prijsfactor = $_itemProduct->getData('prijsfactor');
			$prijsfactor = (isset($prijsfactor) ? (int) $_itemProduct->getData('prijsfactor'):1);
				
			return $prijsfactor;				
		endforeach;
		
	}
	public function getFirstAssociativeItemPrice($_product) {

		$_associatedProducts = $_product->getTypeInstance(true)->getAssociatedProducts($_product);

		foreach ($_associatedProducts as $_item):
            $price = number_format($_item->getPrice(),4);
            $index[$price] = $_item;
		endforeach;

		if (isset($index) && is_array($index)) {
			ksort($index);
			$_associatedProducts = $index;
		}
		foreach ($_associatedProducts as $_item):
			return $_item;
		endforeach;

		return null;
	}
	
	public function getStockAndUnit($_product) {
		$verkoopeenheid = $_product->getData('verkoopeenheid');
		$unit = $this->getProductUnit($verkoopeenheid);
		
		if (!(int) $_product->getData('afwijkenidealeverpakking')){
			$prijsfactor = $_product->getData('idealeverpakking');
			
		} else {
			//$prijsfactor = $_product->getData('prijsfactor');	
			$prijsfactor = 1;	
		}
		$stock_unit = $this->getStockUnit($prijsfactor, $unit);
		if ($prijsfactor == 1)
			$prijsfactor = '';
		
		return $prijsfactor." ".$stock_unit;
	}
	
	public function getPrijsfactorValue($_product) {
		//$prijsfactor = $_product->getPrijsfactor();
		if (!(int) $_product->getData('afwijkenidealeverpakking')){
			$prijsfactor = $_product->getData('idealeverpakking');
		} else {
			//$prijsfactor = $_product->getData('prijsfactor');	
			$prijsfactor = 1;	
		}
		return $prijsfactor;
	}
	
	// Category function
    public function getTreeCategories($parentId, $isChild,$child = ''){
    	$childcount = 0;
    	$CatHtml = $this->CatHtml;
        global $flag,$counter,$mobile;
        static $v = 0;

        $allCats = Mage::getModel('catalog/category')->getCollection()
                    ->addAttributeToSelect('name')
                    ->addAttributeToSelect('url')
                    ->addAttributeToSelect('category_short_headline')
                    ->addAttributeToSelect('is_topcategory')
                    ->addAttributeToFilter('is_active','1')
                    ->addAttributeToFilter('include_in_menu','1')
                    ->addAttributeToFilter('parent_id',array('eq' => $parentId))
                    ->addAttributeToSort('name');

        $class = ($isChild) ? "sub-cat-list" : "cat-list";

        //2 level category start
        if($class == "sub-cat-list" && $v == 1){
            $CatHtml .= '<ul class="cateremoveme level2 '.$class."-".$v.'">';
            $CatHtml .= '<div class="level2-container">';
            $CatHtml .= '<div class="level2-categories">';
            $CatHtml .= '<div class="category-title"><p><a href="'.$child->getUrl().'" title="'.$child->getName().'">'.$child->getName().'</a></p></div>';
            if ($child->getCategoryShortHeadline()) :
                $CatHtml .= '<div class="category-short-text"><span class="level3-text">'. $child->getCategoryShortHeadline() .'</span></div>';
            endif;
            $CatHtml .= '<div class="sub-categories">';

            $mobile .= "<ul>";
        }

        //3 level category start
        else if($class == "sub-cat-list" && $v == 2){
            $CatHtml .= '<ul>';
        }

        //normal
        else{
            $CatHtml .= '<ul>';
            $mobile .= "<ul>";
        }

        foreach ($allCats as $category) {
            $subcats = $category->hasChildren();

            if($class == "sub-cat-list" && $v == 1 && ($subcats > 0 )){
                $CatHtml .= '<div class="grid_2">';
                $CatHtml .= '<a href="'.$category->getUrl().'" title="'.$category->getName().'"><span>'.$category->getName().'</span></a>';

                $mobile  .= '<li><a href="'.$category->getUrl().'"><span class="cat-name">'.$category->getName().'</span><span class="cat-qty">'.$category->getProductCount().'</span></a></li>';
            }
            else if($class == "sub-cat-list" && $v == 1 && ($subcats <= 1)){
                $CatHtml .= '<div class="grid_2">';
                $CatHtml .= '<a href="'.$category->getUrl().'" title="'.$category->getName().'"><span>'.$category->getName().'</span></a></div>';  

                $mobile  .= '<li><a href="'.$category->getUrl().'"><span class="cat-name">'.$category->getName().'</span><span class="cat-qty">'.$category->getProductCount().'</span></a></li>';
            }
            
            else if($class == "sub-cat-list" && $v == 2){
                if($childcount >= 5){
                    break;
                }
                if($flag == 0){
                    $CatHtml .= "<li class='".$class."-".$v."'><a href='".$category->getUrl()."'><span>".$category->getName()."</span></a></li>";    
                    $childcount++;
                }
            }

            else{
                $CatHtml .= '<li><p data-id="'.$category->getId().'" onclick="window.location=\''.$category->getUrl() .'\';">'.$category->getName().'</p>';

                $mobile .= '<li><p>'.$category->getName().'<span></span></p>';
                if(count($category->getChildrenCategories()) > 12){
                    $flag = 1;
                }else{
                    $flag = 0;
                }
                if($category->getIsTopcategory() == 1){
                    $this->top .= '<li><a href="'.$category->getUrl().'" title="'.$category->getName().'"">'.$category->getName().'</a></li>';
                }
            }
            if($subcats != ''){
                $v++;
                $CatHtml .= $this->getTreeCategories($category->getId(), true,$category);
                $v--;
            }    

            $CatHtml .= '</li>';
        }

        //2 level category start
        if($class == "sub-cat-list" && $v == 1){
            $CatHtml .= '</div></div></div></ul>';
            $mobile .= '</ul>';
        }
        //3 level category end
        else if($class == "sub-cat-list" && $v == 2){
            if($flag == 1){
                $CatHtml .= '</ul></div>';
            }else{
                $CatHtml .= '</ul><a class="all-tertiary" href="'.$child->getUrl().'" title="'.$this->__('View all...').'">'. $this->__('View all...') .'</a></div>';    
                
            }
        }
        else if($class == "cat-list" && $v == 0){
            $CatHtml .= '</ul>';
            $mobile .= '</ul>';
        }
        else{
            $CatHtml .= '</ul></li>';
            $mobile .= '</ul></li>';
        }
        return $CatHtml;
    }
    public function setTop(){
        $this->top ='<ul><li><a href="'.Mage::getUrl('acties.html').'" title="Acties">Acties</a></li>';
    }
    public function setMobile(){
        global $mobile;
        $mobile ='';
    }
    public function getTopCategoriess(){
        return $this->top;
    }
    
    public function getMobileCategories(){
        global $mobile,$mainCate;
        //if maainCate is not set then load that first so that it will update the mobile html because mobile is global variable
        if(empty($mainCate)) {
            $rootCatId     = Mage::app()->getStore()->getRootCategoryId();
            $mainCate = $this->getTreeCategories($rootCatId, false);
        }
        return $mobile;
    }
    public function getMainCategories(){
        global $mainCate;
        $rootCatId     = Mage::app()->getStore()->getRootCategoryId();
        $mainCate = $this->getTreeCategories($rootCatId, false);
        return $mainCate;
    }
	public function getFilterLabels($custom_filters,$_product){
		$filtercols = 0;
		$str = "";
		if (isset($custom_filters) && strlen($custom_filters[0]) > 0) :
			$i = 0;
			foreach ($custom_filters as $f) :
				if ($filtercols < 4) :
					$str .= '<th>';
						$attitle = '&nbsp;';
						$objAttribute = $_product->getResource()->getAttribute($f);
						if(!empty($objAttribute)) {
						$attitle = $objAttribute->getFrontendLabel();
						}
						$asotbl[$i] = strtolower(strtok($attitle, ' '));
						$str .= $attitle;
						$i++;
						$filtercols++;
				 $str .= '</th>';
		
				endif;
			endforeach;
		endif;
        return $str;
    }
	public function getFilterValues($custom_filters,$_asp){
        $_asp->load('media_gallery');
		$filtercols = 0;
		$strValues = "";
		if (isset($custom_filters[0]) && strlen($custom_filters[0]) > 0) :
			$j = 0;
			foreach ($custom_filters as $f) :
				if ($filtercols < 4) :
					$strValues .= '<td>';
						$atttext = '&nbsp;&nbsp;';
						$objAttribute = $_asp->getResource()->getAttribute($f);
						if(!empty($objAttribute)) {
							$atttext = $_asp->getAttributeText($f);
						}
						$strValues .= $atttext;
						$j++;
						$filtercols++;
					$strValues .= '</td>';
		
				endif;
			endforeach;
		endif;
        return $strValues;
    }
	
	public function getCustomerEmail() {
		
		$sessionCustomer = Mage::getSingleton("customer/session");
		if($sessionCustomer->isLoggedIn()) {
			$customerEmail = $sessionCustomer->getCustomer()->getEmail();
		} else {
			$customerEmail = '';
		}   
		return $customerEmail;
    }
	
	public function getInstockDeliveryText() {
		
		$englishMonth=array('January','February','March','April','May','June','July','August','September','October','November','December');
		$duthcMonth=array('januari','februari','maart','april','mei','juni','juli','augustus','september','oktober','november','december');
		$englishDay=array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
		$duthcDay=array('maandag','dinsdag','woensdag','donderdag','vrijdag','zaterdag','zondag');
		
		$curentDate=Mage::getModel('core/date')->date('Y-m-d H:i:s');
		$dw = date( "w",  strtotime($curentDate));
		$weekDays = date( "l j F ",  strtotime($curentDate. "+1 days") );
		$friday = date( "l j F ",  strtotime($curentDate. "+3 days") );
		$saturday = date("l j F ",  strtotime($curentDate. "+2 days") );
		$sunday = date( "l j F ",  strtotime($curentDate. "+1 days") );
		$time=Mage::getModel('core/date')->date('H:i');
	
		if($dw > '0' && $dw < '5' && $time <= '16:30') {
				$deliveryText = 'verzending vandaag';
		} elseif($dw > '0' && $dw < '5' && $time >= '16:30') {
				$deliveryText = 'verzending morgen';
		} elseif($dw == '5' && $time <= '16:30') {
				$deliveryText = 'verzending vandaag';
		} elseif($dw == '5' && $time >= '16:30') {
				$deliveryText = 'verzending '.$friday;
		} elseif($dw == '6') {
				$deliveryText = 'verzending '.$saturday;
		} elseif($dw == '0') {
				$deliveryText = 'verzending '.$sunday;
		}
		foreach($englishMonth as $ekey=>$_eng){
			if(strstr($deliveryText,$_eng)){
				$deliveryText=str_replace($_eng,$duthcMonth[$ekey],$deliveryText);
			}
		}
		foreach($englishDay as $ekey=>$_eng){
			if(strstr($deliveryText,$_eng)){
				$deliveryText=str_replace($_eng,$duthcDay[$ekey],$deliveryText);
			}
		}
		
		return $deliveryText;
	}
	

}
