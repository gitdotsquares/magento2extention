<?php 
namespace Dotsquares\Wholesale\Controller\Deleted; 

class Deleted extends \Magento\Framework\App\Action\Action {
    protected $resultPageFactory;
	public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)     {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
	public function execute()
    {
			$post1  = $this->getRequest()->getParams();
			//print_r($post1);
			//die("test");
			$remove = $post1["remove"];
			  //$customary1 = Mage::getSingleton('core/session')->getMyCustomArray();	
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$productsession = $objectManager->get('Magento\Catalog\Model\Session');
				$customary1 = $productsession->getMyValue();
				foreach ($customary1 as $key => $value) {
					foreach ($value as $key1 => $value1) {
						$removearray = count($value1);
						if($value1['ajax_counter'] == $remove){
							unset($customary1[$key][$key1]);
							$productsession->setMyValue($customary1);
							//Mage::getSingleton('core/session')->setMyCustomArray($customary1);
						}
					}
				}
				//$customary1 = $productsession->getMyValue();
				//print_r($customary1);
				die("complete");	
    }
}