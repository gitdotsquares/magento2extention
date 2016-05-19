<?php 
namespace Dotsquares\Wholesale\Controller\Update; 

class Update extends \Magento\Framework\App\Action\Action {
    protected $resultPageFactory;
	public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)     {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
	public function execute()
    {
			
		$update  = $this->getRequest()->getParams();
		$update1 = $update['update'];
		$update2 = $update['update_fld'];
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$productsession = $objectManager->get('Magento\Catalog\Model\Session');
		$customary1 = $productsession->getMyValue();
		//print_r($customary1);
		//die();
		foreach ($customary1 as $key => $value) {
			foreach ($value as $key1 => $value1) {
			$removearray = count($value);
				$value12 =  'remove'.$value1['ajax_counter'].'';

				if($value12 == $update2){
					$simpleproductid = $value1['selected_configurable_option'];
					$qty = $value1['qty'];	
					$currentproduct = $objectManager->create('Magento\Catalog\Model\Product')->load($simpleproductid);
					$productname = $currentproduct->getName();
					$att2 = $value1['super_attribute'];
					if($simpleproductid != '' || !empty($simpleproductid) || $simpleproductid != null){
						$stock_qty = $this->check_qty($simpleproductid,$att2);
						$out_of_stock = false;
						if($stock_qty<$update1){
							$result_array['error_msg'] = "The requested quantity is not available.";
							$out_of_stock = true;
						}
					}else{
						$out_of_stock = false;
					}
					if(!$out_of_stock){
						$customary1[$key][$key1]['qty']= $update1;
						$productsession->setMyValue($customary1);
						$result_array['product_qty'] = $update1;
					} 
					$customary1[$key][$key1]['qty']= $update1;
					$productsession->setMyValue($customary1);
					$result_array['product_qty'] = $update1;
					$final_table = json_encode($result_array);
					//$customary1 = $productsession->getMyValue();
					//print_r($customary1);
					die($final_table);
				}	
			}
		}
    }
	public function check_qty($product,$att){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
		$stock = $StockState->getStockQty($product);
		$stock = round($stock,2);
		return $stock;
	}
}