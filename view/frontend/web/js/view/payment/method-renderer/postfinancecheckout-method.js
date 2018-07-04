/**
 * PostFinance Checkout Magento 2
 *
 * This Magento 2 extension enables to process payments with PostFinance Checkout (https://www.postfinance.ch/).
 *
 * @package PostFinanceCheckout_Payment
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)
 */
define([
	'jquery',
	'Magento_Checkout/js/view/payment/default',
	'rjsResolver',
	'Magento_Checkout/js/model/full-screen-loader'
], function(
	$,
	Component,
	resolver,
	fullScreenLoader
){
	'use strict';
	return Component.extend({
		defaults: {
    		template: 'PostFinanceCheckout_Payment/payment/form'
		},
		redirectAfterPlaceOrder: false,
		
		/**
		 * @override
		 */
		initialize: function(){
			this._super();
			
			resolver((function(){
				if (this.isChecked() == this.getCode()) {
					this.createHandler();
				}
			}).bind(this));
		},
        
		getFormId: function(){
			return this.getCode() + '-payment-form';
		},
		
		getConfigurationId: function(){
			return window.checkoutConfig.payment[this.getCode()].configurationId;
		},
		
		createHandler: function(){
			if (this.handler) return;
			
			fullScreenLoader.startLoader();
			this.handler = window.IframeCheckoutHandler(this.getConfigurationId());
			this.handler.create(this.getFormId(), (function(validationResult){
				if (validationResult.success) {
					this.placeOrder();
				}
			}).bind(this), function(){
				fullScreenLoader.stopLoader();
			});
		},
		
		selectPaymentMethod: function(){
			var result = this._super();
			this.createHandler();
			return result;
		},
		
        validateIframe: function(){
        	this.handler.validate();
        },
        
        afterPlaceOrder: function(){
        	this.handler.submit();
        }
	});
});