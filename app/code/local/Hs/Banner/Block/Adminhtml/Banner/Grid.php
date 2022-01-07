<?php

class Hs_Banner_Block_Adminhtml_Banner_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId("bannerGrid");
		$this->setDefaultSort("banner_id");
		$this->setDefaultDir("DESC");
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel("banner/banner")->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn("banner_id", array(
			"header" => Mage::helper("banner")->__("ID"),
			"align" => "right",
			"width" => "50px",
			"type" => "number",
			"index" => "banner_id",
		));

		$this->addColumn('image', array(
			'header' => Mage::helper('banner')->__('Image'),
			'align' => 'left',
			'index' => 'image',
			'width' => '97',
			'frame_callback' => array($this, 'callback_image')
		));

		$this->addColumn("link", array(
			"header" => Mage::helper("banner")->__("Banner Link"),
			"index" => "link",
		));
		$this->addColumn('status', array(
			'header' => Mage::helper('banner')->__('Status'),
			'index' => 'status',
			'type' => 'options',
			'options' => Hs_Banner_Block_Adminhtml_Banner_Grid::getOptionArray2(),
		));

		return parent::_prepareColumns();
	}

	public function getRowUrl($row)
	{
		return $this->getUrl("*/*/edit", array("id" => $row->getId()));
	}

	public function callback_image($value)
	{
		$width = 97;
		$height = 97;
		return "<img src='" . Mage::getBaseUrl('media') . $value . "' width=" . $width . " height=" . $height . "/>";
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('banner_id');
		$this->getMassactionBlock()->setFormFieldName('banner_ids');
		$this->getMassactionBlock()->setUseSelectAll(true);
		$this->getMassactionBlock()->addItem('remove_banner', array(
			'label' => Mage::helper('banner')->__('Remove Banner'),
			'url' => $this->getUrl('*/adminhtml_banner/massRemove'),
			'confirm' => Mage::helper('banner')->__('Are you sure?')
		));
		return $this;
	}

	static public function getOptionArray2()
	{
		$data_array = array();
		$data_array[0] = 'Inactive';
		$data_array[1] = 'Active';
		return ($data_array);
	}

	static public function getValueArray2()
	{
		$data_array = array();
		foreach (Hs_Banner_Block_Adminhtml_Banner_Grid::getOptionArray2() as $k => $v) {
			$data_array[] = array('value' => $k, 'label' => $v);
		}
		return ($data_array);

	}
}