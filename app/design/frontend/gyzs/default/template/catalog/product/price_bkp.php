<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    design
 * @package     base_default
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
?>

<?php
/**
 * Template for displaying product price in different places (products grid, product view page etc)
 *
 * @see Mage_Catalog_Block_Product_Abstract
 */
?>
<?php
$_coreHelper = $this->helper('core');
$_weeeHelper = $this->helper('weee');
$_taxHelper = $this->helper('tax');
/* @var $_coreHelper Mage_Core_Helper_Data */
/* @var $_weeeHelper Mage_Weee_Helper_Data */
/* @var $_taxHelper Mage_Tax_Helper_Data */

#$_product = $this->getProduct();
$_product = Mage::getModel('catalog/product')->load($this->getProduct()->getId());
$_storeId = $_product->getStoreId();
$_id = $_product->getId();
$_weeeSeparator = '';


$_product->load($_id);
$prijsfactor = $_product->getData('prijsfactor');
$price_factor = (isset($prijsfactor)) ? (int)$_product->getData('prijsfactor') : 1;

// temp code made by Patrick
if ($_product->isGrouped()) {
    $_associatedProducts = $_product->getTypeInstance(true)->getAssociatedProducts($_product);
    if (count($_associatedProducts) > 0) {
        $_item = array_shift($_associatedProducts);
        $unit = Mage::helper('featured')->getProductUnit($_item->getData('verkoopeenheid'));
        $prijsfactor = $_item->getData('prijsfactor');
    }
}
?>
<?php /*
<?php if ($_product->isGrouped()): ?>
    <?php $_associatedProducts = $_product->getTypeInstance(true)->getAssociatedProducts($_product); ?>
    <?php $_hasAssociatedProducts = count($_associatedProducts) > 0; ?>
    <?php if ($_hasAssociatedProducts): ?>
        <?php $i=0;	?>
        <?php foreach ($_associatedProducts as $_item): ?>
                <?php if($i==0): ?>
                    <?php $item=Mage::getModel('catalog/product')->load($_item->getId());
                    $prijsfactor=$item->getData('prijsfactor');
                    $price_factor = (isset($prijsfactor) ? (int) $item->getData('prijsfactor'):1);?>
                <?php $i++;?>
            <?php endif; ?>
        <?php endforeach; ?>    
    <?php endif; ?>
<?php endif; ?>
*/ ?>

<?php
/* add css class for Group Price in Frontend */
$tierPriceClass = 'group-price';
if (empty($_product->getData('group_price'))) {
    $tierPriceClass = '';
}
?>
<div class="ctpriceblock <?php echo $tierPriceClass; ?>" rel="<?php echo $_id; ?>">

    <?php
    $_simplePricesTax = ($_taxHelper->displayPriceIncludingTax() || $_taxHelper->displayBothPrices());
    $_minimalPriceValue = $_product->getMinimalPrice() * $price_factor;
    $_minimalPrice = $_taxHelper->getPrice($_product, $_minimalPriceValue, $_simplePricesTax);
    ?>

    <?php if (!$_product->isGrouped()): ?>
        <?php $_weeeTaxAmount = $_weeeHelper->getAmountForDisplay($_product); ?>
        <?php if ($_weeeHelper->typeOfDisplay($_product, array(Mage_Weee_Model_Tax::DISPLAY_INCL_DESCR, Mage_Weee_Model_Tax::DISPLAY_EXCL_DESCR_INCL, 4))): ?>
            <?php $_weeeTaxAmount = $_weeeHelper->getAmount($_product); ?>
            <?php $_weeeTaxAttributes = $_weeeHelper->getProductWeeeAttributesForDisplay($_product); ?>
        <?php endif; ?>
        <?php $_weeeTaxAmountInclTaxes = $_weeeTaxAmount; ?>
        <?php if ($_weeeHelper->isTaxable() && !$_taxHelper->priceIncludesTax($_storeId)): ?>
            <?php $_attributes = $_weeeHelper->getProductWeeeAttributesForRenderer($_product, null, null, null, true); ?>
            <?php $_weeeTaxAmountInclTaxes = $_weeeHelper->getAmountInclTaxes($_attributes); ?>
        <?php endif; ?>

        <div class="price-box">
            <?php $_price = $_taxHelper->getPrice($_product, $_product->getPrice()) ?>
            <?php $_regularPrice = $_taxHelper->getPrice($_product, $_product->getPrice() * $price_factor, $_simplePricesTax) ?>
            <?php $_finalPrice = $_taxHelper->getPrice($_product, $_product->getFinalPrice() * $price_factor); ?>
            <?php $_finalPriceInclTax = $_taxHelper->getPrice($_product, $_product->getFinalPrice() * $price_factor, true) ?>
            <?php $_weeeDisplayType = $_weeeHelper->getPriceDisplayType(); ?>
            <?php if ($_finalPrice >= $_price): ?>
                <?php if ($_taxHelper->displayBothPrices()): ?>
                    <?php if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 0)): // including ?>
                        <span class="price-excluding-tax">
                    <span class="label"><?php echo $this->helper('tax')->__('Excl. Tax:') ?></span>
                    <span class="price 1" id="price-excluding-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                        <?php echo $_coreHelper->currency($_price + $_weeeTaxAmount, true, false) ?>
                    </span>
                </span>
                        <span class="price-including-tax">
                    <span class="price" id="price-including-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                        <?php echo $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, true, false) ?>
                    </span>
                    <span class="label"><?php echo $this->helper('tax')->__('Incl. Tax:') ?></span>
                </span>
                    <?php elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 1)): // incl. + weee ?>
                        <span class="price-excluding-tax">
                    <span class="label"><?php echo $this->helper('tax')->__('Excl. Tax:') ?></span>
                    <span class="price 2" id="price-excluding-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                        <?php echo $_coreHelper->currency($_price + $_weeeTaxAmount, true, false) ?>
                    </span>
                </span>
                        <span class="price-including-tax">
                    <span class="price" id="price-including-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                        <?php echo $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, true, false) ?>
                    </span>
                    <span class="label"><?php echo $this->helper('tax')->__('Incl. Tax:') ?></span>
                    <span class="weee">(
                        <?php foreach ($_weeeTaxAttributes as $_weeeTaxAttribute): ?>
                            <?php echo $_weeeSeparator; ?>
                            <?php echo $_weeeTaxAttribute->getName(); ?>: <?php echo $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, true); ?>
                            <?php $_weeeSeparator = ' + '; ?>
                        <?php endforeach; ?>
                        )</span>
                </span>
                    <?php elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 4)): // incl. + weee ?>
                        <span class="price-excluding-tax">
                    <span class="label"><?php echo $this->helper('tax')->__('Excl. Tax:') ?></span>
                    <span class="price 3" id="price-excluding-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                        <?php echo $_coreHelper->currency($_price + $_weeeTaxAmount, true, false) ?>
                    </span>
                </span>
                        <span class="price-including-tax">
                    <span class="price" id="price-including-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                        <?php echo $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, true, false) ?>
                    </span>
                    <span class="label"><?php echo $this->helper('tax')->__('Incl. Tax:') ?></span>
                    <span class="weee">(
                        <?php foreach ($_weeeTaxAttributes as $_weeeTaxAttribute): ?>
                            <?php echo $_weeeSeparator; ?>
                            <?php echo $_weeeTaxAttribute->getName(); ?>: <?php echo $_coreHelper->currency($_weeeTaxAttribute->getAmount() + $_weeeTaxAttribute->getTaxAmount(), true, true); ?>
                            <?php $_weeeSeparator = ' + '; ?>
                        <?php endforeach; ?>
                        )</span>
                </span>
                    <?php elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 2)): // excl. + weee + final ?>
                        <span class="price-excluding-tax">
                    <span class="label"><?php echo $this->helper('tax')->__('Excl. Tax:') ?></span>
                    <span class="price 4" id="price-excluding-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                        <?php echo $_coreHelper->currency($_price, true, false) ?>
                    </span>
                </span>
                        <?php foreach ($_weeeTaxAttributes as $_weeeTaxAttribute): ?>
                            <span class="weee">
                        <?php echo $_weeeTaxAttribute->getName(); ?>
                                : <?php echo $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, true); ?>
                    </span>
                        <?php endforeach; ?>
                        <span class="price-including-tax">
                    <span class="price" id="price-including-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                        <?php echo $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, true, false) ?>
                    </span>
                    <span class="label"><?php echo $this->helper('tax')->__('Incl. Tax:') ?></span>
                </span>
                    <?php else: ?>
                        <span class="price-excluding-tax">
                    <span class="label"><?php echo $this->helper('tax')->__('Excl. Tax:') ?></span>
                    <span class="price 5" id="price-excluding-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                        <?php if ($_finalPrice == $_price): ?>
                            <?php echo $_coreHelper->currency($_price, true, false) ?><span
                                    class="excl-text"> <?php echo $this->helper('tax')->__('Excl.') ?></span>
                        <?php else: ?>
                            <?php echo $_coreHelper->currency($_finalPrice, true, false) ?><span
                                    class="excl-text"> <?php echo $this->helper('tax')->__('Excl.') ?></span>
                        <?php endif; ?>
                    </span>
                </span>
                        <span class="price-including-tax">
                    <span class="price" id="price-including-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                        <?php echo $_coreHelper->currency($_finalPriceInclTax, true, false) ?>
                    </span>
                    <span class="label"><?php echo $this->helper('tax')->__('Incl. BTW') ?></span>
                </span>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 0)): // including ?>
                        <span class="regular-price"
                              id="product-price-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                    <?php echo $_coreHelper->currency($_price + $_weeeTaxAmount, true, true) ?>
                </span>
                    <?php elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 1)): // incl. + weee ?>
                        <span class="regular-price"
                              id="product-price-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                    <?php echo $_coreHelper->currency($_price + $_weeeTaxAmount, true, true) ?>
                </span>
                        <span class="weee">(
                            <?php foreach ($_weeeTaxAttributes as $_weeeTaxAttribute): ?>
                                <?php echo $_weeeSeparator; ?>
                                <?php echo $_weeeTaxAttribute->getName(); ?>: <?php echo $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, true); ?>
                                <?php $_weeeSeparator = ' + '; ?>
                            <?php endforeach; ?>
                            )</span>
                    <?php elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 4)): // incl. + weee ?>
                        <span class="regular-price"
                              id="product-price-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                    <?php echo $_coreHelper->currency($_price + $_weeeTaxAmount, true, true) ?>
                </span>
                        <span class="weee">(
                            <?php foreach ($_weeeTaxAttributes as $_weeeTaxAttribute): ?>
                                <?php echo $_weeeSeparator; ?>
                                <?php echo $_weeeTaxAttribute->getName(); ?>: <?php echo $_coreHelper->currency($_weeeTaxAttribute->getAmount() + $_weeeTaxAttribute->getTaxAmount(), true, true); ?>
                                <?php $_weeeSeparator = ' + '; ?>
                            <?php endforeach; ?>
                            )</span>
                    <?php elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 2)): // excl. + weee + final ?>
                        <span class="regular-price"><?php echo $_coreHelper->currency($_price, true, true) ?></span>
                        <br/>
                        <?php foreach ($_weeeTaxAttributes as $_weeeTaxAttribute): ?>
                            <span class="weee">
                        <?php echo $_weeeTaxAttribute->getName(); ?>
                                : <?php echo $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, true); ?>
                    </span>
                        <?php endforeach; ?>
                        <span class="regular-price"
                              id="product-price-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                    <?php echo $_coreHelper->currency($_price + $_weeeTaxAmount, true, true) ?>
                </span>
                    <?php else: ?>
                        <span class="regular-price"
                              id="product-price-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                    <?php if ($_finalPrice == $_price): ?>
                        <?php echo $_coreHelper->currency($_price, true, true) ?>
                    <?php else: ?>
                        <?php echo $_coreHelper->currency($_finalPrice, true, true) ?>
                    <?php endif; ?>
                </span>
                    <?php endif; ?>
                <?php endif; ?>
            <?php else: /* if ($_finalPrice == $_price): */ ?>
                <?php $_originalWeeeTaxAmount = $_weeeHelper->getOriginalAmount($_product); ?>

                <?php if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 0)): // including ?>
                    <p class="old-price">
                        <span class="price-label"><?php echo $this->__('Regular Price:') ?></span>
                        <span class="price" id="old-price-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                    <?php echo $_coreHelper->currency($_regularPrice + $_originalWeeeTaxAmount, true, false) ?>
                </span>
                    </p>

                    <?php if ($_taxHelper->displayBothPrices()): ?>
                        <p class="special-price">
                            <span class="price-label"><?php echo $this->__('Special Price:') ?></span>
                            <span class="price-excluding-tax">
                        <span class="label"><?php echo $this->helper('tax')->__('Excl. Tax:') ?></span>
                        <span class="price 6"
                              id="price-excluding-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                            <?php echo $_coreHelper->currency($_finalPrice + $_weeeTaxAmount, true, false) ?>
                        </span>
                    </span>
                            <span class="price-including-tax">
                    <span class="price" id="price-including-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                        <?php echo $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, true, false) ?>
                    </span>
                    <span class="label"><?php echo $this->helper('tax')->__('Incl. Tax:') ?></span>
                </span>
                        </p>
                    <?php else: ?>
                        <p class="special-price">
                            <span class="price-label"><?php echo $this->__('Special Price:') ?></span>
                            <span class="price" id="product-price-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                    <?php echo $_coreHelper->currency($_finalPrice + $_weeeTaxAmountInclTaxes, true, false) ?>
                </span>
                        </p>
                    <?php endif; ?>

                <?php elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 1)): // incl. + weee ?>
                    <p class="old-price">
                        <span class="price-label"><?php echo $this->__('Regular Price:') ?></span>
                        <span class="price" id="old-price-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                    <?php echo $_coreHelper->currency($_regularPrice + $_originalWeeeTaxAmount, true, false) ?>
                </span>
                    </p>

                    <p class="special-price">
                        <span class="price-label"><?php echo $this->__('Special Price:') ?></span>
                        <span class="price-excluding-tax">
                    <span class="label"><?php echo $this->helper('tax')->__('Excl. Tax:') ?></span>
                    <span class="price 7" id="price-excluding-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                        <?php echo $_coreHelper->currency($_finalPrice + $_weeeTaxAmount, true, false) ?>
                    </span>
                </span>
                        <span class="weee">(
                            <?php foreach ($_weeeTaxAttributes as $_weeeTaxAttribute): ?>
                                <?php echo $_weeeSeparator; ?>
                                <?php echo $_weeeTaxAttribute->getName(); ?>: <?php echo $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, true); ?>
                                <?php $_weeeSeparator = ' + '; ?>
                            <?php endforeach; ?>
                            )</span>
                        <span class="price-including-tax">
                <span class="price" id="price-including-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                    <?php echo $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, true, false) ?>
                </span>
                <span class="label"><?php echo $this->helper('tax')->__('Incl. Tax:') ?></span>
            </span>
                    </p>
                <?php elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 4)): // incl. + weee ?>
                    <p class="old-price">
                        <span class="price-label"><?php echo $this->__('Regular Price:') ?></span>
                        <span class="price" id="old-price-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                    <?php echo $_coreHelper->currency($_regularPrice + $_originalWeeeTaxAmount, true, false) ?>
                </span>
                    </p>

                    <p class="special-price">
                        <span class="price-label"><?php echo $this->__('Special Price:') ?></span>
                        <span class="price-excluding-tax">
                    <span class="label"><?php echo $this->helper('tax')->__('Excl. Tax:') ?></span>
                    <span class="price 8" id="price-excluding-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                        <?php echo $_coreHelper->currency($_finalPrice + $_weeeTaxAmount, true, false) ?>
                    </span>
                </span>
                        <span class="weee">(
                            <?php foreach ($_weeeTaxAttributes as $_weeeTaxAttribute): ?>
                                <?php echo $_weeeSeparator; ?>
                                <?php echo $_weeeTaxAttribute->getName(); ?>: <?php echo $_coreHelper->currency($_weeeTaxAttribute->getAmount() + $_weeeTaxAttribute->getTaxAmount(), true, true); ?>
                                <?php $_weeeSeparator = ' + '; ?>
                            <?php endforeach; ?>
                            )</span>
                        <span class="price-including-tax">
                <span class="price" id="price-including-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                    <?php echo $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, true, false) ?>
                </span>
                <span class="label"><?php echo $this->helper('tax')->__('Incl. Tax:') ?></span>
            </span>
                    </p>
                <?php elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 2)): // excl. + weee + final ?>
                    <p class="old-price">
                        <span class="price-label"><?php echo $this->__('Regular Price:') ?></span>
                        <span class="price" id="old-price-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                    <?php echo $_coreHelper->currency($_regularPrice, true, false) ?>
                </span>
                    </p>

                    <p class="special-price">
                        <span class="price-label"><?php echo $this->__('Special Price:') ?></span>
                        <span class="price-excluding-tax">
                    <span class="label"><?php echo $this->helper('tax')->__('Excl. Tax:') ?></span>
                    <span class="price 9" id="price-excluding-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                        <?php echo $_coreHelper->currency($_finalPrice, true, false) ?>
                    </span>
                </span>
                        <?php foreach ($_weeeTaxAttributes as $_weeeTaxAttribute): ?>
                            <span class="weee">
                        <?php echo $_weeeTaxAttribute->getName(); ?>
                                : <?php echo $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, true); ?>
                    </span>
                        <?php endforeach; ?>
                        <span class="price-including-tax">

                    <span class="price" id="price-including-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                        <?php echo $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmountInclTaxes, true, false) ?>
                    </span>
                    <span class="label"><?php echo $this->helper('tax')->__('Incl. Tax:') ?></span>
                </span>
                    </p>
                <?php else: // excl. ?>
                    <p class="old-price">
                        <span class="price-label"><?php echo $this->__('Regular Price:') ?></span>
                        <span class="price" id="old-price-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                    <?php echo $_coreHelper->currency($_regularPrice, true, false) ?>
                </span>
                    </p>

                    <?php if ($_taxHelper->displayBothPrices()): ?>
                        <p class="special-price">
                            <span class="price-label"><?php echo $this->__('Special Price:') ?></span>
                            <span class="price-excluding-tax">
                        <span class="label"><?php echo $this->helper('tax')->__('Excl. Tax:') ?></span>
                        <span class="price 10"
                              id="price-excluding-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                            <?php echo $_coreHelper->currency($_finalPrice, true, false) ?>
                            <span class="excl-text"> <?php echo $this->helper('tax')->__('Excl.') ?></span>
                        </span>
                    </span>
                            <span class="price-including-tax">

                        <span class="price"
                              id="price-including-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                            <?php echo $_coreHelper->currency($_finalPriceInclTax, true, false) ?>
                        </span>
                                <!-- Added new for special prices issues start-->
                        <span class="label test"><?php echo $this->helper('tax')->__('Incl. Tax') ?></span>
                                <!-- Added new for special prices issues start-->

                    </span>
                        </p>
                    <?php else: ?>
                        <p class="special-price">
                            <span class="price-label"><?php echo $this->__('Special Price:') ?></span>
                            <span class="price" id="product-price-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                    <?php echo $_coreHelper->currency($_finalPrice, true, false) ?>
                </span>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>

            <?php endif; /* if ($_finalPrice == $_price): */ ?>

            <?php if ($this->getDisplayMinimalPrice() && $_minimalPriceValue && $_minimalPriceValue < $_product->getFinalPrice()): ?>

                <?php $_minimalPriceDisplayValue = $_minimalPrice; ?>
                <?php if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, array(0, 1, 4))): ?>
                    <?php $_minimalPriceDisplayValue = $_minimalPrice + $_weeeTaxAmount; ?>
                <?php endif; ?>

                <?php if ($this->getUseLinkForAsLowAs()): ?>
                    <a href="<?php echo $_product->getProductUrl(); ?>" class="minimal-price-link">
                <?php else: ?>
                    <span class="minimal-price-link">
                <?php endif ?>
                <span class="label"><?php echo $this->__('As low as:') ?></span>
                <span class="price" id="product-minimal-price-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                <?php echo $_coreHelper->currency($_minimalPriceDisplayValue, true, false) ?>
            </span>
                <?php if ($this->getUseLinkForAsLowAs()): ?>
                    </a>
                <?php else: ?>
                    </span>
                <?php endif ?>
            <?php endif; /* if ($this->getDisplayMinimalPrice() && $_minimalPrice && $_minimalPrice < $_finalPrice): */ ?>
        </div>

    <?php else: /* if (!$_product->isGrouped()): */ ?>
        <?php
        $_exclTax = $_taxHelper->getPrice($_product, $_minimalPriceValue);
        $_inclTax = $_taxHelper->getPrice($_product, $_minimalPriceValue, true);

        $_item = Mage::helper('featured')->getFirstAssociativeItemPrice($_product);
        $prijsfactor = $_item->getData('prijsfactor');
        $price_factor = (isset($prijsfactor)) ? (int)$prijsfactor : 1;
        ?>
        <?php if ($this->getDisplayMinimalPrice() && $_minimalPriceValue): ?>
            <div class="price-box">
                <p class="minimal-price">
                    <?php /*<span class="price-label"><?php echo $this->__('Starting at:') ?></span>*/ ?>
                    <?php if ($_taxHelper->displayBothPrices()): ?>
                        <span class="price-excluding-tax">

                        <span class="price 11"
                              id="price-excluding-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                            <?php echo $_coreHelper->currency($_exclTax * $price_factor, true, false) ?>
                        </span>
                        <span class="label"><?php echo $this->helper('tax')->__('Excl. Tax:') ?></span>
                    </span>
                        <span class="price-including-tax">
                       
                        <span class="price"
                              id="price-including-tax-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                            <?php echo $_coreHelper->currency($_inclTax * $price_factor, true, false) ?>
                        </span>
                         <span class="label"><?php echo $this->helper('tax')->__('Incl. Tax:') ?></span>
                    </span>
                    <?php else: ?>
                        <?php
                        $_showPrice = $_inclTax;
                        if (!$_taxHelper->displayPriceIncludingTax()) {
                            $_showPrice = $_exclTax;
                        }
                        ?>
                        <span class="price"
                              id="product-minimal-price-<?php echo $_id ?><?php echo $this->getIdSuffix() ?>">
                    <?php echo $_coreHelper->currency($_showPrice, true, false) ?>
                </span>
                    <?php endif; ?>
                </p>
            </div>

        <?php else: ?>
            <?php
            // TODO: very dirty hack!
            if ($_product->isGrouped()) {
                $lp = false;
                foreach ($_associatedProducts as $__product)
                    if (!$lp)
                        $lp = $__product;
                    elseif ($__product->getPrice() < $lp->getPrice())
                        $lp = $__product;
                if ($lp)
                    echo $this->setProduct($lp)->toHtml();
            }
            ?>
        <?php endif; /* if ($this->getDisplayMinimalPrice() && $_minimalPrice): */ ?>
    <?php endif; /* if (!$_product->isGrouped()): */ ?>

</div>
