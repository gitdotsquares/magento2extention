<?php
namespace Dotsquares\Wholesale\Controller\Cart;
use Magento\Framework\App\Action;
use Magento\Checkout\Model\Cart;

class Add extends \Magento\Checkout\Controller\Cart\Add
{

	protected $cart;
	
    public function execute()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$productsession = $objectManager->get('Magento\Catalog\Model\Session');
		$customary = $productsession->getMyValue();
		$post  = $this->getRequest()->getParams();
		$pro_to_add = $post['product'];
		
		try {
			if(!empty($customary)){
				if(!empty($customary[$pro_to_add])){
					if(array_key_exists($pro_to_add, $customary)){
						$temp = $customary[$pro_to_add];
						//print_r($temp);
						//die("testing");
						$i = 0;
						//$related = $this->getRequest()->getParam('related_product');
						foreach($temp as $temp1){
							$product = $objectManager->create('Magento\Catalog\Model\Product')->load($temp1['product']);
							$params = array(
								'product' => $temp1['product'],
								'qty' => $temp1['qty'],
								'super_attribute' => $temp1['super_attribute'],
							);
							$request = new \Magento\Framework\DataObject($params);
							$this->cart->addProduct($product, $request);
						}
						$productsession->unsMyValue();
					}
				}else
					{
						$product = $this->_initProduct();
						$request = new \Magento\Framework\DataObject($post);
						$this->cart->addProduct($product, $post);
					}
			}else{
				$product = $this->_initProduct();
				$this->cart->addProduct($product, $post);
				
				
			}
			$this->cart->save();
			if (!$this->_checkoutSession->getNoCartRedirect(true)) {
				if (!$this->cart->getQuote()->getHasError()) {
					
					$message = __(
						'You added %1 to your shopping cart.',
						$product->getName()
					);
					$this->messageManager->addSuccessMessage($message);
				}
				
			}
			return $this->goBack(null, $product);
			
		} catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNotice(
                    $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage())
                );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addError(
                        $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($message)
                    );
                }
            }

            $url = $this->_checkoutSession->getRedirectUrl(true);

            if (!$url) {
                $cartUrl = $this->_objectManager->get('Magento\Checkout\Helper\Cart')->getCartUrl();
                $url = $this->_redirect->getRedirectUrl($cartUrl);
            }
            return $this->goBack($url);

        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return $this->goBack();
        }
		
	}
	
}	