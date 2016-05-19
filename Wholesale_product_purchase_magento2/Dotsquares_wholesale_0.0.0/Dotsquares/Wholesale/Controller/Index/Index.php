<?php 
namespace Dotsquares\Wholesale\Controller\Index; 

class Index extends \Magento\Framework\App\Action\Action {
	protected $catalogSession;
    protected $resultPageFactory;
    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Catalog\Model\Session $catalogSession)     {
        $this->resultPageFactory = $resultPageFactory;
		$this->catalogSession = $catalogSession;
        parent::__construct($context);
    }
	public function execute()
    {
			$post = array(); 
			$result_array = array();
			$post  = $this->getRequest()->getParams();	
			$final_html = '';
			$curr_pid = $post['product'];
			$selected_configurable_option = $post['selected_configurable_option'];
			$curr_formkey =  $post['form_key'];
			$curr_attr = $post['super_attribute'];
			$simpleproductid = $post['selected_configurable_option'];
			$attr_key = implode("_", array_keys($curr_attr));
			$value_key = implode("_", $curr_attr);
			$id = $post['product'];	
			$post['qty'] = abs($post['qty']);
			$ajax_counter = $post['ajax_counter'];	
			if($ajax_counter==0 || $ajax_counter == 'null'){
						$post['ajax_counter']=1;
			}
			//$productId = 52; //this is child product id
			$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
			$currentproduct = $objectManager->create('Magento\Catalog\Model\Product')->load($id);
			$productname = $currentproduct->getName();
			
			/****** qty *****/
			if($simpleproductid != '' || !empty($simpleproductid) || $simpleproductid != null){
				$stock_qty = $this->check_qty($selected_configurable_option,$curr_attr);
				$out_of_stock = false;

				if($stock_qty<$post['qty']){
					$result_array['error_msg'] = "The requested quantity is not available.";
					$out_of_stock = true;
				}
			}else{
				$out_of_stock = false;
			}
			/*check stock ends here*/

			if(!$out_of_stock){
			$optionarray = array(
				'form_key' => $post['form_key'],
				'product' => $post['product'],
				'related_product'=> null,
				'super_attribute' => $post['super_attribute'],
				'qty' => $post['qty'],
				'ajax_counter'=>$post['ajax_counter'],
				'selected_configurable_option' => $post['selected_configurable_option'],
				);
			
			
				$custom_key = $curr_pid.'_'.$attr_key.'_'.$value_key;
				$customary = $this->catalogSession->getMyValue();
				$custom_qty = 0;
				
					if(!empty($customary[$curr_pid]) && array_key_exists($custom_key, $customary[$curr_pid]))
					{		
						if($simpleproductid != '' || !empty($simpleproductid) || $simpleproductid != null){
							$stock_qty = $this->check_qty($selected_configurable_option,$curr_attr);
							$out_of_stock = false;
						
							$qtycus = $customary[$curr_pid][$custom_key]['qty'];
							$qtycus += $post['qty'];
							if($stock_qty<$qtycus){
								$result_array['error_msg'] = "The requested quantity is not available.";
								$out_of_stock = true;
							}
						}else{
							$out_of_stock = false;
						}
						if(!$out_of_stock){
							$customary[$curr_pid][$custom_key]['qty'] += $post['qty'];
							$custom_qty = $customary[$curr_pid][$custom_key]['qty'];
							
						}else{
							$customary[$curr_pid][$custom_key]['qty'];
							$custom_qty = $customary[$curr_pid][$custom_key]['qty'];
						}
						//print_r($customary);
						//echo $custom_qty;
						//die();
						
						$customary[$curr_pid][$custom_key]['ajax_counter'] = $post['ajax_counter'];
					}
					else
					{
						
						$custom_qty = $post['qty'];
						if (empty($custom_qty))
						{
							$custom_qty = $post['default_qty'];
						} 
						$customary[$curr_pid][$custom_key] =  $optionarray;
						
					}
				
				
				/*session is started if you don't write this line can't use $_Session  global variable*/
				//$_SESSION["wholesale"]=$customary;
				
				$this->catalogSession->setMyValue($customary);
				$seesion = $this->catalogSession->getMyValue();
				$remove = 'remove';
				$i=0;
					$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
					$collection = $productCollection->create()->addAttributeToFilter('entity_id', array('in' => $id))->load();
					//$collection->getSelect()->order("find_in_set(entity_id,'".implode(',',$id)."')");	
					//print_r($product);
					foreach($collection as $product){
						$produto_cor_options = $currentproduct->getTypeInstance(true)->getConfigurableAttributesAsArray($currentproduct);
						foreach($produto_cor_options as $options){
							$atributo_cor = $options['attribute_code'];
							//echo $atributo_cor;
							//print_r($curr_attr);
							//die();
								$attr = $currentproduct->getResource()->getAttribute($atributo_cor);
								
								//die();
								if ($attr->usesSource()) {  
									$final_html .= '<td>';
									foreach($curr_attr as $att3){
										$final_html.= $attr->getSource()->getOptionText($att3);
									}  
								//die();
								$final_html .='</td>';
							} 
						}
					}
					//die();
					$update="Update";
					

									//$stock.''
					$final_html .=  '<td><input type="text" name="update-qty" maxlength="12" value="'. $custom_qty . '"  class="input-text updateqtytext qty" /><p id="remove'.$post['ajax_counter'] . '" class="update action updateqty primary tocart" >'.$update.'</p></td>';
					
					$final_html .= '<td><input name="final_product[]" type="hidden" value="'.$custom_key.'"><p id="'.$post['ajax_counter'] . '" class="removebu removebu'. $i .'">'. $remove .'</p></td>';
				//endforeach; 

				$result_array['product_key'] = $custom_key;
				$result_array['product_qty'] = $custom_qty;
				$result_array['product_content'] = $final_html;
			}
			$final_table = json_encode($result_array);
			die($final_table);
		
    }
	public function check_qty($product,$att){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
		$stock = $StockState->getStockQty($product);
		//echo $stock;
		//die();
		$stock = round($stock,2);
		return $stock;
	}
}