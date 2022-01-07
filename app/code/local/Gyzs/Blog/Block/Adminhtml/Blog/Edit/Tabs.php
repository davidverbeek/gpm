    <?php

    class Gyzs_Blog_Block_Adminhtml_Blog_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
    {

      public function __construct()
      {
          parent::__construct();
          $this->setId('blog_tabs');
          $this->setDestElementId('edit_form');
          $this->setTitle(Mage::helper('blog')->__('Blog Information'));
      }

      protected function _beforeToHtml()
      {
          $this->addTab('form_section', array(
              'label'     => Mage::helper('blog')->__('General Information'),
              'title'     => Mage::helper('blog')->__('General Information'),
              'content'   => $this->getLayout()->createBlock('blog/adminhtml_blog_edit_tab_form')->toHtml(),
          ));

              //$this->addTab('product', array(
              //'label'     => Mage::helper('faq')->__('Skus'),
              //'class'     => 'ajax',
              //'url'       => $this->getUrl('*/*/product', array('_current' => true)),
                    //  'content'   => $this->getLayout()->createBlock('faq/adminhtml_faq_edit_tab_product')->toHtml(),
          //));
             $this->addTab('product_section', array(
                            'label'     => Mage::helper('blog')->__('Products'),
                            'class'     => 'ajax',
                            'url'       => $this->getUrl('*/*/product', array('_current' => true)),
              )); 



                    //	$this->addTab('categories', array(
        //            'label'     => Mage::helper('catalog')->__('Categories'),
        //            'url'       => $this->getUrl('*/*/categories', array('_current' => true)),
        //            'class'     => 'ajax',
        //        ));

		 	$this->addTab('categories', array(
				'label'     => Mage::helper('catalog')->__('Categories'),
				'url'       => $this->getUrl('*/*/categories', array('_current' => true)),
				'class'     => 'ajax',
			));	

        /*$this->addTab('categories', array(
              'label'     => Mage::helper('blog')->__('Categories'),
              'title'     => Mage::helper('blog')->__('Categories'),
              'content'   => $this->getLayout()->createBlock('blog/adminhtml_blog_edit_tab_categories')->toHtml(),
          )); */			
          return parent::_beforeToHtml();
      }
    }