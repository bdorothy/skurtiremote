window.CartthrobTokenizer = {};
(function(e){
	var ender = require('ender'),
	    bean = require('bean'),
	    bonzo = require('bonzo'),
	    qwery = require('qwery');
	
	e.form = null;
	
	e.submissionState = null;
	
	e.errorHandler = function(errorMessage){
		alert(errorMessage);
	};
	
	e.setErrorHandler = function(errorHandler){
		this.errorHandler = errorHandler;
		return this;
	};
	
	e.submitHandler = function(){
		CartthrobTokenizer.form.submit();
	};
	
	e.setSubmitHandler = function(submitHandler){
		this.submitHandler = submitHandler;
		return this;
	};
	
	e.bindHandler = function(){
		return true;
	};
	
	e.setBindHandler = function(bindHandler){
		this.bindHandler = bindHandler;
		return this;
	};
	
	e.beforeSubmit = function(){
		return true;
	};
	
	e.setBeforeSubmit = function(beforeSubmit){
		this.beforeSubmit = beforeSubmit;
		return this;
	};
	
	e.addHidden = function(name, value){
		bonzo(CartthrobTokenizer.form).append("<input type=\"hidden\" name=\""+name+"\" value=\""+value+"\">");
		return this;
	};
	
	e.val = function(selector){
		return bonzo(ender(CartthrobTokenizer.form).find(selector)).val();
	};
	
	e.bind = function(){
		bean.add(CartthrobTokenizer.form, "submit", CartthrobTokenizer.submit);
	};
	
	e.unbind = function(){
		bean.remove(CartthrobTokenizer.form, "submit", CartthrobTokenizer.submit);
	};
	
	e.submit = function(e){
		if (CartthrobTokenizer.submissionState === true){
			e.preventDefault();
			return false;
		}
		CartthrobTokenizer.submissionState = true;
		if (CartthrobTokenizer.beforeSubmit() !== false){
			CartthrobTokenizer.bindHandler();
		}
		e.preventDefault();
		return false;
	};
	
	e.init = function(bindHandler){
		this.form = qwery("#checkout_form").shift();
		
		this.setBindHandler(bindHandler);
		
		return CartthrobTokenizer.bind();
	};
	
})(CartthrobTokenizer);
