/*

    Orders JS

    File:       /assets/js/Vin.js
    Author:     Todd Bagley

    EdmundsAPI: purv83y79da9v96c4nna2p6r / cXSQbnbje8GWhJrNCgt2hKtW

*/

var Vin = {};

$(document).ready(function() {

console.log('Vin Loaded');

});

jQuery.extend(Vin, {

    decode: function (vin) {

console.log('Vin.decode:'+vin);

		if(vin){

			var options = {
				"api_key": 	"purv83y79da9v96c4nna2p6r",
				"fmt": 		"json"
			};

		    $.ajax({
		        url: 'https://api.edmunds.com/api/vehicle/v2/vins/'+vin+'?fmt=json&api_key=purv83y79da9v96c4nna2p6r&',
		        type: 'POST',
		        options: options,
		        success: function(responseData) {
console.log('SUCCESS');
console.log(responseData);
					if(responseData.make){
console.log(responseData.make);
						if(responseData.make.name){
							if($('#vehicle-make').find('span').html()=='Make'){
								if($('#vehicle-make').is(':visible')){
									Core.Wizard.Input2Link('',responseData.make.name,'vehicle-make');
								} else {
									$('#wizard-input-vehicle-make').val(responseData.make.name);
								}
							}
						}
					}
					if(responseData.model){
console.log(responseData.model);
						if(responseData.model.name){
							if($('#vehicle-model').find('span').html()=='Model'){
								if($('#vehicle-model').is(':visible')){
									Core.Wizard.Input2Link('',responseData.model.name,'vehicle-model');
								} else {
									$('#wizard-input-vehicle-model').val(responseData.model.name);
								}
							} else if($('#vehicle-model').html()!=responseData.model.name){ 
								if(confirm('Update "Model" with "'+responseData.model.name+'"?')){
									Core.Wizard.Input2Link('',responseData.model.name,'vehicle-model');
								}
							}
						}
						if(responseData.model.colors){
console.log(responseData.model.colors);
							if(responseData.model.colors[0].color){
								if($('#vehicle-color').find('span').html()=='Color'){
									if($('#vehicle-color').is(':visible')){
										Core.Wizard.Input2Link('',responseData.model.colors[0].color,'vehicle-color');
									} else {
										$('#wizard-input-vehicle-color').val(responseData.color.name);
									}
								} else if($('#vehicle-color').html()!=responseData.model.colors[0].color){
									if(confirm('Update "Color" with "'+responseData.color.name+'"?')){
										Core.Wizard.Input2Link('',responseData.model.colors[0].color,'vehicle-color');
									}
								}
							}
						}
						if(responseData.model.years){
console.log(responseData.model.years);
							if(responseData.model.years[0].year){
								if($('#vehicle-year').find('span').html()=='Year'){
									if($('#vehicle-year').is(':visible')){
										Core.Wizard.Input2Link('',responseData.model.years[0].year,'vehicle-year');
									} else {
										$('#wizard-input-vehicle-year').val(responseData.year.name);
									}
								} else if($('#vehicle-year').html()!=responseData.model.years[0].year){
									if(confirm('Update "Year" with "'+responseData.year.name+'"?')){
										Core.Wizard.Input2Link('',responseData.model.years[0].year,'vehicle-year');
									}
								}
							}
						}
					}
		        },
			    fail: function(responseData) {
console.log('FAIL');
console.log(responseData);
		        }
			});

		}

	}

});

// window.sdkAsyncInit = function() {

// 	// Instantiate the SDK
// 	var res = new EDMUNDSAPI('purv83y79da9v96c4nna2p6r');

// 	// Optional parameters
// 	var options = {
// 		"manufacturerCode": "3548"
// 	};

// 	// Callback function to be called when the API response is returned
// 	function success(res) {
// 		var body = document.getElementById('div-vin');
// 		body.innerHTML = "The car make is: " + res.styleHolder[0].makeName;
// 	}

// 	// Oops, Houston we have a problem!
// 	function fail(data) {
// 		console.log(data);
// 	}
	
// 	// Fire the API call
// 	res.api('/api/vehicle/v2/vins/WBAVB13586KX41696', options, success, fail);

// 	// Additional initialization code such as adding Event Listeners goes here

// };

// // Load the SDK asynchronously
// (function(d, s, id){
// 	var js, sdkjs = d.getElementsByTagName(s)[0];
// 	if (d.getElementById(id)) {return;}
// 	js = d.createElement(s); js.id = id;
// 	js.src = "path/to/sdk/file";
// 	sdkjs.parentNode.insertBefore(js, sdkjs);
// }(document, 'script', 'edmunds-jssdk'));
