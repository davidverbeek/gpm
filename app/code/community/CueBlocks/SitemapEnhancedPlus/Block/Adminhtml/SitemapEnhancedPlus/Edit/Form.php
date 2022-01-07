<?php

/**
 * Description of
 * @package   CueBlocks_SitemapEnhancedPlus
 * ** @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhancedPlus_Block_Adminhtml_SitemapEnhancedPlus_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Init form
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('sitemapEnhancedPlus_form');
        $this->setTitle(Mage::helper('adminhtml')->__('Sitemap Information'));
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('sitemapEnhancedPlus_sitemap');

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post'
        ));

        $fieldset = $form->addFieldset('add_sitemap_form', array('legend' => Mage::helper('sitemapEnhancedPlus')->__('Sitemap')));

        if ($model->getId()) {
            $fieldset->addField('sitemap_id', 'hidden', array(
                'name' => 'sitemap_id',
            ));
        }
        if (!$model->getSitemapPath()) {
            $model->setSitemapPath(DIRECTORY_SEPARATOR);
        }

        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'select', array(
                'label' => Mage::helper('sitemapEnhancedPlus')->__('Store View:'),
                'title' => Mage::helper('sitemapEnhancedPlus')->__('Store View'),
                'name' => 'store_id',
                'required' => true,
                'value' => $model->getStoreId(),
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
            ));
        } else {
            $fieldset->addField('store_id', 'hidden', array(
                'name' => 'store_id',
                'value' => Mage::app()->getStore(true)->getId()
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        $fieldset->addField('sitemap_type', 'select', array(
            'label' => Mage::helper('sitemapEnhancedPlus')->__('Type:'),
            'title' => Mage::helper('sitemapEnhancedPlus')->__('Type (Regular/Image/Mobile)'),
            'name' => 'sitemap_type',
            'required' => true,
            'value' => $model->getSitemapType(),
            'values' => Mage::getSingleton('sitemapEnhancedPlus/system_sitemapType')->getValuesForForm(),
            'note' => Mage::helper('adminhtml')->__('
<b>- Regular:</b> <br>
 Compatible with the major search engine. </br>
 For more info: <a target="_blank" href="http://www.sitemaps.org/protocol.html">Sitemap Protocol</a>
</br>
<b>- Images:</b>  <br>
this format is compatible only with Google. <br>
Generate a Sitemap that includes images of your product pages. <br>
For more info: <a target=\'_blank\' href=\'https://support.google.com/webmasters/answer/178636?hl=en\'>Google Image Sitemap</a>
</br>
<b>- Mobile:</b></br>
this format is compatible only with Google. <br>
If your site has a specially formatted version designed for mobile devices, it is strongly recommended to create a separate mobile sitemap and submit it to search engines. That will allow search engines to better serve search requests from mobile devices and lead them to your website pages. <br>
For more info: <a target=\'_blank\' href=\'https://support.google.com/webmasters/topic/8493\'>Google Mobile Sitemap</a> </br>
')
        ));

        $fieldset->addField('sitemap_filename', 'text', array(
            'label' => Mage::helper('sitemapEnhancedPlus')->__('Base FileName:'),
            'name' => 'sitemap_filename',
            'required' => true,
//            'after_element_html' => '<div id="row_sitemapEnhancedPlus_general_useindex_comment" class="system-tooltip-box" style="height: 166px; display: none; ">fsfsdfsdfsd</div>',
            'note' => Mage::helper('adminhtml')->__('This will be the name of your XML Sitemap \'Index\' file. </br>
                                                         Make sure that you declare the exact same sitemap file name in your robots.txt file.</br>
                                                         <b>Warning:</b> If there is a pre-existing XML sitemap file with this exact name, at the same location, it will be over-written and it can\'t be restored. </br>
                                                         Please double check or speak with your SEO team if you are in doubt.'),
            'value' => $model->getSitemapFilename()
        ));

        $fieldset->addField('sitemap_path', 'text', array(
            'label' => Mage::helper('sitemapEnhancedPlus')->__('Root location:'),
            'name' => 'sitemap_path',
            'required' => true,
            'note' => Mage::helper('adminhtml')->__('This is the location (directory) where your Sitemap file will be generated. </br>
                                                         Enter "/" to generate the sitemap at the root location. </br>
                                                         Please follow <a target="_blank" href="http://www.sitemaps.org/protocol.html">Sitemap protocol specification</a> regarding file location.</br>
                                                         For the XML sitemap Index file to be easily picked up by Search Engines, please ensure that this path is specified in your robots.txt file a well.'),
            'value' => $model->getSitemapPath()
        ));

        $fieldset->addField('legend', 'note', array(
            'name' => 'legend',
            'label' => Mage::helper('sitemapEnhancedPlus')->__('Legend:'),
            'note' => Mage::helper('adminhtml')->__('<span class="required">*</span> required fields.'),

        ));

        $fieldset->addField('generate', 'hidden', array(
            'name' => 'generate',
            'value' => ''
        ));

        $form->setValues($model->getData());

        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
