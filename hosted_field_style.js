(function() {

	var BTFn = {};

	BTFn.sendJSON = function($obj) {

		$.ajax({
			dataType: "text",
			type: "POST",
			data: { action: "generateclienttoken"},
			url: "braintree_hosted_fields_with_style_demo.php",
			success: function (req) {
				BTFn.initBT(req, $obj);
			},
			error: function() {
			}
		});
	};

	BTFn.initBT = function(req, $obj) {

		// we're on the custom fields tab
		braintree.setup(req, 'custom', {
			id: 'bt_custom_form',
			hostedFields: {
				number: {
					selector: '#bt_card_number'
				},
				cvv: {
					selector: '#bt_cvv'
				},
				expirationDate: {
					selector: '#bt_exp_date'
				},
				onFieldEvent: handleFieldEvent,
				styles: {
				  '.invalid': {
					'color': '#D0041D'
				  }
				}
			},
			onReady: function() {
				BTFn.showBtForm();
			},
			onError: function(error) {
				BTFn.showFormErrors(error);
				$obj.show().closest('.btn_container').find('.loader_img').hide();
			},
			paypal:{
				container:'bt_paypal_container',
			}
		});
	};

	BTFn.showBtForm = function() {

		var eAnimation = this.detectAnimationEvent();
		$('.loader_container').addClass('fadeOut');

		$('.loader_container').one(eAnimation, function(event) {
			$('.loader_container').remove();
			$('.bt_form_wrap').removeClass('hide');
			$('.bt_form_wrap').addClass('fadeIn').removeClass('invisible');
		});
	};

	BTFn.detectAnimationEvent = function() {

		var t, el = document.createElement("fakeelement");

		var animations = {
			"animation"      : "animationend",
			"OAnimation"     : "oAnimationEnd",
			"MozAnimation"   : "animationend",
			"WebkitAnimation": "webkitAnimationEnd"
		}

		for (t in animations){
			if (el.style[t] !== undefined){
				return animations[t];
			}
		}
	};

	BTFn.showFormErrors = function(error) {

		var inputMap = {
			'number' : '#bt_card_number',
			'cvv' : '#bt_cvv',
			'expirationDate' : '#bt_exp_date'
		};
		if(!error.details) {
			$('.inputFields').addClass('inputError');
		}else{
			var i, errorLength = error.details.invalidFieldKeys.length;
			$('.inputFields').removeClass('inputError');
			for(var i=0; i < errorLength; i++) {
				$(inputMap[error.details.invalidFieldKeys[i]]).addClass('inputError');
			}
		}
	};

	handleFieldEvent = function(event) {  
	  //console.log((event.card));
	  
	  /* Other events
		if (event.type === "focus") {
		  // Handle focus
		} else if (event.type === "blur") {
		  // Handle blur
		} else if (event.type === "fieldStateChange") {
		  // Handle a change in validation or card type
		  console.log(event.isValid); // true|false
		  if (event.card) {
			console.log(event.card.type);
			// visa|master-card|american-express|diners-club|discover|jcb|unionpay|maestro
		  }
		}
	  */
	  
	  if (event.type === "focus") {
		  // Handle focus
		  $('.inputFields').removeClass('inputError');
	  }

	  iconType = document.getElementsByClassName("payment-method-icon")[0];
	  iconType.className = "payment-method-icon";
	  if (event.card) iconType.className += " " + event.card.type;
	}

	$(document).ready(function() {

		var $pay_btn = $('.pay-btn');
		BTFn.sendJSON($pay_btn);
		$pay_btn.on('click', function() {
			$(this).hide().closest('.btn_container').find('.loader_img').css('display', 'inline-block');
		});
	});
})();