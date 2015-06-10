/*

    Orders JS

    File:       /assets/js/Orders.js
    Author:     Todd Bagley
*/

var ajaxSkip = 0 ;
var buffer = 0 ;
var fulfillment_accounting = 0;
var fulfillment_activations = 0;
var fulfillment_approved = 0;
var fulfillment_credit = 0;
var fulfillment_inventoried = 0;
var fulfillment_invoiced = 0;
var fulfillment_labeled = 0;
var fulfillment_pending = 0;
var fulfillment_read_only = 0;
var fulfillment_read_write = 0;
var fulfillment_shipped = 0;
var fulfillment_user = 0 ;
var lastPagedReport = '' ;
var noskip = 0 ;
var quote_rate = 0 ;
var quote_arate = 0 ;
var quote_handling = 0 ;
var quote_shipping = 0 ;
var rep_dealer_id = 0 ;
var rep_ecode = 0 ;
var rep_m2m_repID = 0 ;
var rep_dealerid = 0 ;
var rep_id = 0 ;
var rep_name = 0 ;
var rep_override = 0 ;
var report_master='';
var resale=0;
var savings_rate = '' ;
var savings_arate = '' ;
var savings_shipping = '' ;
var savings_handling = '' ;

var Orders = {};

$(document).ready(function() {

    buffer = 0 ;
    $.each($('li'), function() {
        $(this).removeClass('active');
    });
    Orders.Quote();

    Orders.isLoaded();
});

jQuery.extend(Orders, {

    cookieGet: function (cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i=0; i<ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1);
            if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
        }
        return "";
    },

    cookieSet: function (cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires="+d.toUTCString();
        document.cookie = cname + "=" + cvalue + "; " + expires;
    },

    isLoaded: function() {

        if($('body').data('context')=='orders/'){
            $('#rep-email').val(Orders.cookieGet('rep-email'));
            $('#rep-password').val(Orders.cookieGet('rep-password'));
        } else if(($('body').data('context')=='fulfillment/')||($('body').data('context')=='warehouse/')){
            $('#login-email').val(Orders.cookieGet('login-email'));
            $('#login-password').val(Orders.cookieGet('login-password'));
        } else {
            var os = $('#div-orderstatus').find('.block').offset().top;
            var h = $( window ).height() - os - os;
            $('#div-orderstatus').find('.block').css('overflow','auto');
            $('#div-orderstatus').find('.block').css('height',h+'px');
        }

        $(document).on('change', '#orderform-payment-method', function() {
            switch($(this).val()){
                case                      '2' : $('#orderform-cc').show();
                                                break;
                                      default : $('#orderform-cc').hide();
                                                $('#orderform-cc_num').val('');
                                                $('#orderform-cc_ver').val('');
                                                $('#orderform-cc_exp').val('');
            }
        });

        $(document).on('change', '#customer-reseller', function() {
            switch($(this).val().toLowerCase()){
                case       'n/a' :
                case        'na' : $(this).val('');
                                   alert("If you do not know the Customer's Reseller Number, please leave this field blank... Sales WITHOUT Reseller Number on File are Subject to Applicable Taxes.");
                                   break;
            }
        });

        $(document).on('change', '#orderform-quantity , #orderform-arate , #orderform-arate-override , #orderform-rate , #orderform-handling-fee , #orderform-handling-override , #orderform-rate-override , #orderform-shipping-fee , #orderform-shipping-override', function() {
            Orders.Quote($(this).attr('id'));
        });

        $(document).on('change', '.dataTables-length', function() {
            Orders.PagedReport($(this).closest('div.report-master').attr('id'));
        });

        $(document).on('change', 'input.payment', function() {
            var params = {} ;
            params['fulfillment_user'] = fulfillment_user ;
            params['accounts_id'] = $(this).attr('id').split('-').slice(2)[0] ;
            params['field'] = $(this).attr('id').split('-').slice(1)[0] ;
            params['checked'] = $(this).is(':checked') ;
            Orders.Ajax('payment',params);
            $(this).addClass('background-red');
        });

        $(document).on('change', 'input.states', function() {
            var params = {} ;
            params['fulfillment_user'] = fulfillment_user ;
            params['states_id'] = $(this).attr('id').split('-').slice(1)[0] ;
            params['value'] = $(this).val() ;
            Orders.Ajax('states',params);
            $(this).addClass('background-red');
        });

        $(document).on('change', 'textarea.read-write', function() {
            var params = {} ;
            params['fulfillment_user'] = fulfillment_user ;
            params['orders_id'] = $(this).attr('id').split('-').slice(1)[0] ;
            params['field'] = $(this).attr('id').split('-').slice(0)[0] ;
            params['value'] = $(this).val() ;
            Orders.Ajax('read-write',params);
            $(this).addClass('background-red');
        });

        $(document).on('click', '.backoffice-check', function() {
            var params = {} ;
            params['orders_id'] = $(this).attr('id').split('-').slice(1)[0] ;
            Orders.Ajax('backoffice-check',params);
        });

        $(document).on('click', '.status', function() {
            var params = {} ;
            params['fulfillment_user'] = fulfillment_user ;
            params['orders_id'] = $(this).attr('id') ;
            if($(this).hasClass('status-approve')){
                params['status_id'] = 1 ;
            } else if($(this).hasClass('status-inventory')){
                params['status_id'] = 2 ;
                params['inventory'] = $('#inventory-'+$(this).attr('id')).val() ;
            } else if($(this).hasClass('status-label')){
                params['status_id'] = 3 ;
                params['shipping_track'] = $('#shipping_track-'+$(this).attr('id')).val() ;
            } else if($(this).hasClass('status-shipped')){
                params['status_id'] = 4 ;
            } else if($(this).hasClass('status-invoiced')){
                params['status_id'] = 5 ;
                params['invoiced'] = $('#invoiced-'+$(this).attr('id')).val() ;
            } else if($(this).hasClass('status-activations')){
                params['status_id'] = 6 ;
                params['activations'] = $('#activations-'+$(this).attr('id')).val() ;
            }
            switch(params['status_id']){
                case                1  :
                case               '1' : if(confirm('Approve this Order?')){
                                             Orders.Ajax('status',params);
                                         }
                                         break;
                case                2  :
                case               '2' : if(!(params['inventory'])){
                                             alert('Inventory Sheet is Empty?');
                                         } else if(confirm('Submit Inventory Sheet for this Order?')){
                                             Orders.Ajax('status',params);
                                         }
                                         break;
                case                3  :
                case               '3' : if(!(params['shipping_track'])){
                                             alert('Tracking Number is Missing?');
                                         } else if(confirm('Shipping Label Created for this Order?')){
                                             Orders.Ajax('status',params);
                                         }
                                         break;
                case                4  :
                case               '4' : if(confirm('Confirm this Order has been Shipped?')){
                                             Orders.Ajax('status',params);
                                         }
                                         break;
                case                5  :
                case               '5' : if(!(params['invoiced'])){
                                             alert('Invoice Number is Missing?');
                                         } else if(confirm('Confirm Invoice has been Generated for this Order?')){
                                             Orders.Ajax('status',params);
                                         }
                                         break;
                case                6  :
                case               '6' : if(!(params['activations'])){
                                             alert('Activation Notes are Missing?');
                                         } else if(confirm('Confirm Activation Notes are Correct for this Order?')){
                                             Orders.Ajax('status',params);
                                         }
                                         break;
            }
        });

        $(document).on('click', '.order-delete', function() {
            report_master = $(this).closest('.report-master').attr('id') ;
            var params = {} ;
            params['fulfillment_user'] = fulfillment_user ;
            params['orders_id'] = $(this).attr('id').split('-').slice(1)[0] ;
            Orders.Ajax('order-delete',params);
        });

        $(document).on('click', '.btn', function() {
console.log('btn:'+$(this).attr('id'));
            var account = {} ;
            var error = '';
            var params = {} ;
            switch ($(this).attr('id')){
                case       'btn-customer' : if(!($('#customer-phone').val())){                                              error = 'Phone Number Missing' ;                    }
                                            if(!($('#customer-email').val())){                                              error = 'Email Address Missing' ;                   }
                                            if(!($('#customer-shipping-state').val())){                                     error = 'Shipping Address: Taxable State Missing' ; }
                                            if(!($('#customer-shipping').val())){                                           error = 'Shipping Address Missing' ;                }
                                            if(!($('#customer-billing').val())){                                            error = 'Billing Address Missing' ;                 }
                                            if(!($('#customer-dms').val())){                                                error = 'Dealer Management System Missing' ;        }
                                            if(!($('#customer-company').val())){                                            error = 'Company Name Missing' ;                    }
                                            if($('#customer-customer').find('li.active').attr('data-value')<1){
                                                if(!($('#customer-new-timezone').find('li.active').attr('data-value'))){    error = 'Please Select a Timezone' ;                }
                                                if(!($('#customer-new-lastname').val())){                                   error = 'Account Owner\'s Last Name Missing' ;      }
                                                if(!($('#customer-new-firstname').val())){                                  error = 'Account Owner\'s First Name Missing' ;     }
                                                if(!($('#customer-new-password').val())){                                   error = 'Temporary Password Missing' ;              }
                                                if(!($('#customer-new-username').val())){                                   error = 'Username Missing' ;                        }
                                                $('#orderform-cc_file').hide();
                                                $('#orderform-cc_num').val('');
                                                $('#orderform-cc_ver').val('');
                                                $('#orderform-cc_exp').val('');
                                                $('#orderform-cc_onfile').html('');
                                            }
                                            if(error){
                                                alert(error);
                                            } else {
                                                if(!($('#customer-reseller').val())){
                                                    if(confirm('Collect Sales Tax?')){
                                                        resale=1;
                                                        Orders.Quote();
                                                        $('#orderform').trigger('click');
                                                    }
                                                } else {
                                                    resale=0;
                                                    Orders.Quote();
                                                    $('#orderform').trigger('click');
                                                }
                                            }
                                            break; 
                case  'btn-customer-back' : $('#rep').trigger('click');
                                            break; 
                case          'btn-login' : params['login-email'] = $('#login-email').val();
                                            params['login-password'] = $('#login-password').val();
                                            Orders.cookieSet('login-email',$('#login-email').val(),90);
                                            Orders.cookieSet('login-password',$('#login-password').val(),90);
                                            if(!(params['login-password'])){
                                                error = 'Password Missing' ;
                                            }
                                            if(!(params['login-email'])){
                                                error = 'Email Address Missing' ;
                                            }
                                            if(error){
                                                alert(error);
                                            } else {
                                                Orders.Ajax('login',params);
                                            }
                                            break; 
                case      'btn-orderform' : //if(!($('#orderform-payment-method').find('li.active').attr('data-value'))){     error = 'Payment Method Selection Missing' ;       }
                                            if(!($('#orderform-payment-method').val())){                                     error = 'Payment Method Selection Missing' ;       }
                                            if(($('#orderform-payment-method').val()==2)&&(!($('#orderform-cc_onfile_use').is(':checked')))){
                                                if(!($('#orderform-cc_exp').val())){                                        error = 'Credit Card Expiration Missing' ;          }
                                                if(!($('#orderform-cc_ver').val())){                                        error = 'Credit Card Verification Number Missing' ; }
                                                if(!($('#orderform-cc_num').val())){                                        error = 'Credit Card Number Missing' ;              }
                                            }
                                            if($('#orderform-rate').val()<70){                                              error = 'Unit Price Too Low / Missing' ;            }
                                            if($('#orderform-arate').val()<0){                                              error = 'Accessory Price Too Low / Missing' ;       }
                                            if($('#orderform-quantity').val()<1){                                           error = 'Quantity Missing' ;                        }
                                            // if(!($('#orderform-shipping-method').find('li.active').attr('data-value'))){    error = 'Shipping Method Selection Missing' ;       }
                                            if(!($('#orderform-shipping-method').val())){                                   error = 'Shipping Method Selection Missing' ;       }
                                            // if(!($('#orderform-accessories').find('li.active').attr('data-value'))){        error = 'Accessories Selection Missing' ;           }
                                            if(!($('#orderform-accessories').val())){                                       error = 'Accessories Selection Missing' ;           }
                                            // if(!($('#orderform-plan').find('li.active').attr('data-value'))){               error = 'Service Plan Selection Missing' ;          }
                                            if(!($('#orderform-plan').val())){                                              error = 'Service Plan Selection Missing' ;          }
                                            //if(!($('#orderform-product').find('li.active').attr('data-value'))){            error = 'Product Selection Missing' ;               }
                                            if(!($('#orderform-product').val())){                                           error = 'Product Selection Missing' ;               }
                                            if(error){
                                                alert(error);
                                            } else {
                                                $('#preview-rep').html('<span class="text-14 text-bold">'+rep_name+'</span>');
                                                if(($('#rep-email').val())&&($('#rep-email').val()!='undefined')){$('#preview-rep').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Email:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+$('#rep-email').val()+'</span>');}
                                                if((rep_ecode)&&(rep_ecode!='undefined')){$('#preview-rep').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">ECODE:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+rep_ecode+'</span>');}
                                                //
                                                if($('#customer-customer').find('li.active').attr('data-value')<1){
                                                    $('#preview-customer').html('<span class="text-18 text-red text-bold">CREATING NEW ACCOUNT</span>');
                                                    $('#preview-customer').append('<div class="clearfix row"></div><span class="text-18 text-bold">'+$('#customer-company').val()+'</span>');
                                                    $('#preview-customer').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Username:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-red">'+$('#customer-new-username').val()+'</span></div>');
                                                    $('#preview-customer').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Password:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-red">'+$('#customer-new-password').val()+'</span></div>');
                                                    $('#preview-customer').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">First&nbsp;Name:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+$('#customer-new-firstname').val()+'</span></div>');
                                                    $('#preview-customer').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Last&nbsp;Name:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+$('#customer-new-lastname').val()+'</span></div>');
                                                    $('#preview-customer').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Timezone:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+$('#customer-new-timezone').find('li.active').find('a').html()+'</span></div>');
                                                } else {
                                                    $('#preview-customer').html('<span class="text-18 text-bold">'+$('#customer-company').val()+'</span>');
                                                }
                                                //
                                                if(($('#customer-reseller').val())&&($('#customer-reseller').val()!='undefined')){$('#preview-customer').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Reseller:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+$('#customer-reseller').val()+'</span></div>');}
                                                if(($('#customer-contact').val())&&($('#customer-contact').val()!='undefined')){$('#preview-customer').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Contact:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+$('#customer-contact').val()+'</span></div>');}
                                                if(($('#customer-email').val())&&($('#customer-email').val()!='undefined')){$('#preview-customer').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Email:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+$('#customer-email').val()+'</span></div>');}
                                                if(($('#customer-phone').val())&&($('#customer-phone').val()!='undefined')){$('#preview-customer').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Phone:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+$('#customer-phone').val()+'</span></div>');}
                                                if(($('#customer-fax').val())&&($('#customer-fax').val()!='undefined')){$('#preview-customer').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Fax:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+$('#customer-fax').val()+'</span></div>');}
                                                if(($('#customer-shipping').val())&&($('#customer-shipping').val()!='undefined')){$('#preview-customer').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Shipping:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+$('#customer-shipping').val()+'</span></div>');}
                                                if(($('#customer-billing').val())&&($('#customer-billing').val()!='undefined')){$('#preview-customer').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Billing:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+$('#customer-billing').val()+'</span></div>');}
                                                //
                                                if(($('#customer-vzw_rep').html())&&($('#customer-vzw_rep').html()!='undefined')){$('#preview-customer').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Verizon&nbsp;Rep:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+$('#customer-vzw_rep').html()+'</span></div>');}
                                                //
                                                $('#preview-order').html('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Quantity:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+$('#orderform-quantity').val()+'</span></div>');
                                                $('#preview-order').append('<div class="preview-label pull-left"><span class="text-10 text-grey">Product:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+$('#orderform-product option:selected').text()+'</span></div>');
                                                if($('#orderform-plan').val()){$('#preview-order').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Plan:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+$('#orderform-plan option:selected').text()+'</span></div>');}
                                                if(($('#orderform-rate').val())&&($('#orderform-rate').val()!='undefined')){$('#preview-order').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Product&nbsp;Unit&nbsp;Price:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">$'+$('#orderform-rate').val()+'</span></div>');}
                                                if(($('#orderform-override-reason').val())&&(savings_rate!='0.00')&&(savings_rate!='undefined')&&(savings_rate!=null)){$('#preview-order').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-red">Discount:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-red text-11 text-bold">$'+savings_rate+'&nbsp;per&nbsp;unit</span></div>');}
                                                if($('#orderform-accessories').val()){$('#preview-order').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Accessories:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+$('#orderform-accessories option:selected').text()+'</span></div>');}
                                                if(($('#orderform-arate').val())&&($('#orderform-arate').val()!='undefined')){$('#preview-order').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Accessory&nbsp;Unit&nbsp;Price:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">$'+$('#orderform-arate').val()+'</span></div>');}
                                                if(($('#orderform-override-reason').val())&&(savings_arate!='0.00')&&(savings_arate!='undefined')&&(savings_arate!=null)){$('#preview-order').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-red">Discount:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-red text-11 text-bold">$'+savings_arate+'&nbsp;per&nbsp;unit</span></div>');}
                                                if($('#orderform-handling-fee').val()){$('#preview-order').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Handling:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">$'+$('#orderform-handling-fee').val()+'</span></div>');}
                                                if(($('#orderform-override-reason').val())&&(savings_handling!='0.00')&&(savings_handling!='undefined')&&(savings_handling!=null)){$('#preview-order').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-red">Discount:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-red text-11 text-bold">$'+savings_handling+'</span></div>');}
                                                if((savings_rate!='0.00')||(savings_arate!='0.00')||(savings_handling)){
                                                    $('#preview-order').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Extended:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-18 text-bold">$'+$('#orderform-extended-override').val()+'</span></div>');
                                                    if(!(resale)){
                                                        $('#preview-order').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Reseller:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-18 text-bold text-red">'+$('#customer-reseller').val()+'</span></div>');
                                                    } else {
                                                        $('#preview-order').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Taxes:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-18 text-bold">$'+$('#orderform-extended-taxes-override').val()+'</span></div>');
                                                    }
                                                    $('#preview-order').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Sub-Total:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-18 text-bold">$'+$('#orderform-extended-subtotal-override').val()+'</span></div>');
                                                } else {
                                                    $('#preview-order').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Extended:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-18 text-bold">$'+$('#orderform-extended').val()+'</span></div>');
                                                    if(!(resale)){
                                                        $('#preview-order').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Reseller:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-18 text-bold text-red">'+$('#customer-reseller').val()+'</span></div>');
                                                    } else {
                                                        $('#preview-order').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Taxes:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-18 text-bold">$'+$('#orderform-extended-taxes').val()+'</span></div>');
                                                    }
                                                    $('#preview-order').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Sub-Total:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-18 text-bold">$'+$('#orderform-extended-subtotal').val()+'</span></div>');
                                                }
                                                //
                                                $('#preview-shipandhandle').html('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Shipping&nbsp;Method:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+$('#orderform-shipping-method option:selected').text()+'</span></div>');
                                                if($('#orderform-shipping-fee').val()){$('#preview-shipandhandle').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Shipping:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+$('#orderform-shipping-fee').val()+'</span></div>');}
                                                if(($('#orderform-override-reason').val())&&(savings_shipping!='0.00')&&(savings_shipping!='undefined')&&(savings_shipping!=null)){$('#preview-shipandhandle').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-red">Discount:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-red text-11 text-bold">$'+savings_shipping+'&nbsp;per&nbsp;unit</span></div>');}
                                                if(savings_shipping){
                                                    $('#preview-shipandhandle').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Extended:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-18 text-bold">$'+$('#orderform-extended-shipping-override').val()+'</span></div>');
                                                } else {
                                                    $('#preview-shipandhandle').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Extended:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-18 text-bold">$'+$('#orderform-extended-shipping').val()+'</span></div>');
                                                }
                                                //
                                                if(($('#orderform-override-reason').val())&&((savings_rate!='0.00')||(savings_arate!='0.00')||(savings_shipping!='0.00')||(savings_handling!='0.00'))){
                                                    $('#preview-total').html('<span class="text-24 text-red text-bold">$'+$('#orderform-total-override').val()+'</span>&nbsp;<span class="text-red text-10">'+$('#orderform-override-reason option:selected').text()+'</span>');
                                                } else {
                                                    $('#preview-total').html('<span class="text-24 text-bold">$'+$('#orderform-total').val()+'</span>');
                                                }
                                                //
                                                // $('#preview-notes').html('<div class="preview-label pull-left"><span class="text-10 text-grey">Payment&nbsp;Method:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14">&nbsp;'+$('#orderform-payment-method').find('li.active').find('a').html()+'</span>');
                                                $('#preview-notes').html('<div class="preview-label pull-left"><span class="text-10 text-grey">Payment&nbsp;Method:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14">&nbsp;'+$('#orderform-payment-method option:selected').text()+'</span>');
                                                // if($('#orderform-payment-method').find('li.active').attr('data-value')==2){
                                                if($('#orderform-payment-method').val()==2){
                                                    if($('#orderform-cc_onfile_use').prop('checked')){
                                                        $('#preview-notes').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Credit&nbsp;Card:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">Use Credit Card On File:&nbsp;'+$('#orderform-cc_onfile').html()+'</span>');
                                                    } else {
                                                        $('#preview-notes').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Credit&nbsp;Card:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+$('#orderform-cc_num').val()+', CCV='+$('#orderform-cc_ver').val()+' Exp='+$('#orderform-cc_exp').val()+'</span>');
                                                    }
                                                }
                                                if(($('#orderform-po').val())&&($('#orderform-po').val()!='undefined')){$('#preview-notes').append('<div class="clearfix row"></div><div class="preview-label pull-left"><span class="text-10 text-grey">Purchase&nbsp;Order:&nbsp;</span></div><div class="preview-content pull-left"><span class="text-14 text-bold">'+$('#orderform-po').val()+'</span>');}
                                                if(($('#orderform-notes').val())&&($('#orderform-notes').val()!='undefined')){$('#preview-notes').append('<hr><span class="text-14">'+$('#orderform-notes').val()+'</span>');}
                                                $('#preview').trigger('click');
                                            }
                                            break; 
                case 'btn-orderform-back' : $('#customer').trigger('click');
                                            break; 
                case        'btn-preview' : //params['accessories'] = $('#orderform-accessories').find('li.active').attr('data-value');
                                            params['accessories'] = $('#orderform-accessories').val();
                                            params['accounts_id'] = $('#customer-customer').find('li.active').attr('data-value');
                                            params['arate'] = $('#orderform-arate').val();
                                            params['billing'] = $('#customer-billing').val();
                                            params['cc_onfile_use'] = $('#orderform-cc_onfile_use').prop('checked');
                                            params['cc_num'] = $('#orderform-cc_num').val();
                                            params['cc_ver'] = $('#orderform-cc_ver').val();
                                            params['cc_exp'] = $('#orderform-cc_exp').val();
                                            params['company'] = $('#customer-company').val();
                                            params['contact'] = $('#customer-contact').val();
                                            params['dms'] = $('#customer-dms').val();
                                            params['e_code'] = $('#vzw_rep-lead').val();
                                            params['m2m_repID'] = rep_m2m_repID;
                                            params['email'] = $('#customer-email').val();
                                            params['extended'] = $('#orderform-extended').val();
                                            params['extended_override'] = $('#orderform-extended-override').val();
                                            params['fax'] = $('#customer-fax').val();
                                            params['handling_fee'] = $('#orderform-handling-fee').val();
                                            params['notes'] = $('#orderform-notes').val();
                                            params['override_reason'] = $('#orderform-override-reason').val();
                                            // params['payment-method'] = $('#orderform-payment-method').find('li.active').attr('data-value');
                                            params['payment_method'] = $('#orderform-payment-method').val();
                                            params['phone'] = $('#customer-phone').val();
                                            // params['plan'] = $('#orderform-plan').find('li.active').attr('data-value');
                                            params['plan'] = $('#orderform-plan').val();
                                            params['po'] = $('#orderform-po').val();
                                            // params['product'] = $('#orderform-product').find('li.active').attr('data-value');
                                            params['product'] = $('#orderform-product').val();
                                            params['quantity'] = $('#orderform-quantity').val();
                                            params['rate'] = $('#orderform-rate').val();
                                            params['reps_id'] = rep_id;
                                            params['reseller'] = $('#customer-reseller').val();
                                            params['shipping'] = $('#customer-shipping').val();
                                            if($('#orderform-override-reason').val()){
                                                params['taxes_amount'] = $('#orderform-extended-taxes-override').val();
                                            } else {
                                                params['taxes_amount'] = $('#orderform-extended-taxes').val();
                                            }
                                            params['taxes_state'] = $('#customer-shipping-state').val();
                                            // params['shipping-method'] = $('#orderform-shipping-method').find('li.active').attr('data-value');
                                            params['shipping_method'] = $('#orderform-shipping-method').val();
                                            params['shipping_fee'] = $('#orderform-shipping-fee').val();
                                            params['total'] = $('#orderform-total').val();
                                            //
                                            if($('#orderform-override-reason').val()){
                                                if($('#orderform-arate-override').val()){
                                                    params['arate_override'] = $('#orderform-arate-override').val();
                                                } else {
                                                    params['arate_override'] = $('#orderform-arate').val();
                                                }
                                                params['extended_override'] = $('#orderform-extended-override').val();
                                                if($('#orderform-handling-override').val()){
                                                    params['handling_override'] = $('#orderform-handling-override').val();
                                                } else {
                                                    params['handling_override'] = $('#orderform-handling-fee').val();
                                                }
                                                if($('#orderform-rate-override').val()){
                                                    params['rate_override'] = $('#orderform-rate-override').val();
                                                } else {
                                                    params['rate_override'] = $('#orderform-rate').val();
                                                }
                                                if($('#orderform-shipping-override').val()){
                                                    params['shipping_override'] = $('#orderform-shipping-override').val();
                                                } else {
                                                    params['shipping_override'] = $('#orderform-shipping-fee').val();
                                                }
                                                params['total_override'] = $('#orderform-total-override').val();
                                            } else {
                                                params['arate_override'] = $('#orderform-arate').val();
                                                params['extended_override'] = $('#orderform-extended').val();
                                                params['handling_override'] = $('#orderform-handling-fee').val();
                                                params['rate_override'] = $('#orderform-rate').val();
                                                params['shipping_override'] = $('#orderform-shipping-fee').val();
                                                params['total_override'] = $('#orderform-total').val();
                                            }
                                            //
                                            account['account_username'] = $('#customer-new-username').val();
                                            account['account_password'] = $('#customer-new-password').val();
                                            account['account_firstname'] = $('#customer-new-firstname').val();
                                            account['account_lastname'] = $('#customer-new-lastname').val();
                                            account['account_address'] = $('#customer-billing').val();
                                            account['account_name'] = $('#customer-company').val();
                                            account['account_email'] = $('#customer-email').val();
                                            account['account_phone'] = $('#customer-phone').val();
                                            account['account_timezone'] = $('#customer-new-timezone').find('li.active').attr('data-value');
                                            // account['account_timezone'] = $('#customer-new-timezone').val();
                                            account['account_status'] = 1;
                                            account['account_theme'] = 'crossbones';
                                            account['account_type'] = 1;
                                            account['dealer_id'] = rep_dealer_id;
                                            //
                                            params['account'] = account;
                                            //
                                            $('#confirmation-confirmation').html('Uploading Request...');
                                            //
                                            $('#confirmation').trigger('click');
                                            //
                                            Orders.Ajax('order',params);
                                            //
                                            break; 
                case   'btn-preview-back' : $('#orderform').trigger('click');
                                            Orders.Quote();
                                            break; 
                case            'btn-rep' : params['rep-email'] = $('#rep-email').val();
                                            params['rep-password'] = $('#rep-password').val();
                                            Orders.cookieSet('rep-email',$('#rep-email').val(),90);
                                            Orders.cookieSet('rep-password',$('#rep-password').val(),90);
                                            if(!(params['rep-password'])){
                                                error = 'Password Missing' ;
                                            }
                                            if(!(params['rep-email'])){
                                                error = 'Email Address Missing' ;
                                            }
                                            if(error){
                                                alert(error);
                                            } else {
                                                Orders.Ajax('rep',params);
                                            }
                                            break; 
            }
        });

        $(document).on('click', 'li', function() {
            $.each($(this).closest('ul').find('li'), function(){
                $(this).removeClass('active');
            });
            $(this).addClass('active');
            var params = {} ;
            switch ($(this).closest('ul').attr('id')) {
                case              'customer-customer' : params['selection'] = $(this).attr('data-value') ;
                                                        quote_rate = 0 ;
                                                        quote_arate = 0 ;
                                                        quote_handling = 0 ;
                                                        quote_shipping = 0 ;
                                                        if(params['selection']>0){
                                                            $('#customer-new').hide();
                                                        } else {
                                                            $('#customer-new').show();
                                                        }
                                                        params['rep'] = rep_id ;
                                                        $('#customer-billing').val('');
                                                        $('#customer-company').val('');
                                                        $('#customer-contact').val('');
                                                        $('#customer-dms').val('');
                                                        $('#customer-email').val('');
                                                        $('#customer-fax').val('');
                                                        $('#customer-phone').val('');
                                                        $('#customer-shipping').val('');
                                                        $('#customer-shipping-state').val('');
                                                        $('#customer-reseller').val('');
                                                        $('#orderform-cc_num').val('');
                                                        $('#orderform-cc_var').val('');
                                                        $('#orderform-cc_exp').val('');
                                                        $('#orderform-notes').val('');
                                                        $('#orderform-override-reason').val('');
                                                        $('#orderform-payment-method').empty();
                                                        $('#orderform-po').val('');
                                                        if(params['selection']>0){
                                                            Orders.Ajax($(this).closest('ul').attr('id'),params);
                                                        } else {
                                                            params['e_code'] = $('#vzw_rep-lead').val();
                                                            Orders.Ajax('customer-new',params);
                                                        }
                                                        break;
                case          'orderform-accessories' : 
                case                 'orderform-plan' : 
                case       'orderform-payment-method' : 
                case              'orderform-product' : 
                case      'orderform-shipping-method' : Orders.Quote();
                                                        break;
            }
        });

        $(document).on('change', 'select', function() {
             switch($(this).attr('id')){
                case          'orderform-accessories' : if((quote_arate)&&($('#orderform-override-reason').val())){
                                                            if(confirm('Overwrite Special Pricing for Accessories?')){
                                                                $('#orderform-arate-override').val('');
                                                            }
                                                        } else {
                                                            $('#orderform-arate-override').val('');
                                                        }
                                                        break ;
                case       'orderform-payment-method' : if((quote_handling)&&($('#orderform-override-reason').val())){
                                                            if(confirm('Overwrite Special Pricing for Handling Fees?')){
                                                                $('#orderform-handling-override').val('');
                                                            }
                                                        } else {
                                                            $('#orderform-handling-override').val('');
                                                        }
                                                        break ;
                case                 'orderform-plan' :
                case              'orderform-product' : if((quote_rate)&&($('#orderform-override-reason').val())){
                                                            if(confirm('Overwrite Special Pricing for Product?')){
                                                                $('#orderform-rate-override').val('');
                                                            }
                                                        } else {
                                                            $('#orderform-rate-override').val('');
                                                        }
                                                        break ;
                case      'orderform-shipping-method' : if((quote_handling)&&($('#orderform-override-reason').val())){
                                                            if(confirm('Overwrite Special Pricing for Handling Fees?')){
                                                                $('#orderform-handling-override').val('');
                                                            }
                                                        } else {
                                                            $('#orderform-handling-override').val('');
                                                        }
                                                        if((quote_shipping)&&($('#orderform-override-reason').val())){
                                                            if(confirm('Overwrite Special Pricing for Shipping Fees?')){
                                                                $('#orderform-shipping-override').val('');
                                                            }
                                                        } else {
                                                            $('#orderform-shipping-override').val('');
                                                        }
                                                        break ;
            }
            switch ($(this).attr('id')) {
                case          'orderform-accessories' : 
                case      'orderform-override-reason' : 
                case       'orderform-payment-method' : 
                case                 'orderform-plan' : 
                case              'orderform-product' : 
                case      'orderform-shipping-method' : Orders.Quote($(this).attr('id'));
                                                        break;
            }
        });

        $(document).on('change', '#vzw_rep-lead', function() {
            var params = {} ;
            params['e_code'] = $('#vzw_rep-lead').val();
            Orders.Ajax('customer-new',params);
        });

        $(document).on('click', '.details', function() {
            if($('#details-'+$(this).attr('id')).hasClass('active')){
                $('#details-'+$(this).attr('id')).hide();
                $('#details-'+$(this).attr('id')).removeClass('active');
            } else {
                $('#details-'+$(this).attr('id')).addClass('active');
                $('#details-'+$(this).attr('id')).show();
            }
        });

        $(document).on('click', '.dataTables-begin', function() {
            Orders.PagedReport($(this).closest('div.report-master').attr('id'),'begin');
        });

        $(document).on('click', '.dataTables-end', function() {
            Orders.PagedReport($(this).closest('div.report-master').attr('id'),'end');
        });

        $(document).on('click', '.dataTables-next', function() {
            Orders.PagedReport($(this).closest('div.report-master').attr('id'),'up');
        });

        $(document).on('click', '.dataTables-previous', function() {
            Orders.PagedReport($(this).closest('div.report-master').attr('id'),'down');
        });

        $(document).on('click', '.dataTables-search-btn', function() {
            Orders.PagedReport($(this).closest('div.report-master').attr('id'));
        });

        $(document).on('click', '.li-a-navigation', function() {

            var eid = $(this).attr('id').replace('li-a-','');

            console.log('eid:'+eid);

            $.each($('.div-navigation.active'), function(){
                $(this).removeClass('active');
            });
            $.each($('li.open'), function(){
                $(this).removeClass('open');
            });
            $('#div-'+eid).addClass('active');

            var os = $('#div-'+eid).offset().top;
            var h = $( window ).height() - os;
            $('#div-'+eid).attr('height', h+'px');
            $('#div-'+eid).find('div.block').attr('height', h+'px');
            $('#div-'+eid).find('div.block').css('overflowY', 'scroll');
            $('#div-'+eid).find('div.block').css('overflow-Y', 'scroll');
            var reportMaster = $('#div-'+eid).find('.report-master').attr('id');
            $('#'+reportMaster).find('.panel-report-scroll').attr('height', h+'px');
            $('#'+reportMaster).find('.dataTables-search-btn').trigger('click');

            $('#li-a-'+eid).closest('li').addClass('open');

        });

        $(document).on('click', '.navigation', function() {

            var eid = $(this).attr('id');

            console.log('eid:'+eid);

            $.each($('.div-navigation.active'), function(){
                $(this).removeClass('active');
            });
            $('#div-'+eid).addClass('active');

            var os = $('#div-'+eid).find('.block').offset().top;
            var h = $( window ).height() - os - 20;
            $('#div-'+eid).find('.block').css('overflow','auto');
            $('#div-'+eid).find('.block').css('height',h+'px');

            if(eid=='orderform'){
                // if(rep_override){
                //     $('input.override').each(function() {
                //         $(this).attr('readonly',true);
                //         $(this).removeClass('background-override');
                //         $(this).addClass('background-red');
                //     });
                // }
                // $('#orderform-payment-method').find('li.active').each(function(){
                //     $(this).removeClass('active');
                // });
                // $('#orderform-payment-method').val('');
                $('#orderform-cc').hide();
                // $('#orderform-cc_file').hide();
                $('#orderform-cc_num').val('');
                $('#orderform-cc_ver').val('');
                $('#orderform-cc_exp').val('');
                // $('#orderform-cc_onfile').html('');
                // $('#orderform-arate-override').val('');
                $('#orderform-extended-override').val('');
                // $('#orderform-handling-override').val('');
                $('#orderform-quantity-override').val('');
                // $('#orderform-rate-override').val('');
                // $('#orderform-shipping-override').val('');
                $('#orderform-total-override').val('');
            }
            if(eid=='confirmation'){
                var reportMaster = $('#div-'+eid).find('.report-master').attr('id');
                os = $('#'+reportMaster).find('.panel-report-scroll').offset().top;
                var hh = $('#div-'+eid).height() - os - 10;
                $('#'+reportMaster).find('.block').css('height', hh+'px');
                $('#'+reportMaster).find('.dataTables-search-btn').trigger('click');
            }

        });

        $(document).on('click', '.orders-um', function() {
            var params = {} ;
            params['rep'] = rep_id ;
            params['um'] = $(this).data('value');
            Orders.Ajax('um',params);
            $('#customer-search').val('');
            $('#customer-um').html('');
        });

        // $(document).on('click', '#orderform-payment-method li', function() {
        //     switch($(this).attr('data-value')){
        //         case                      '2' : $('#orderform-cc').show();
        //                                         break;
        //                               default : $('#orderform-cc').hide();
        //                                         $('#orderform-cc_num').val('');
        //                                         $('#orderform-cc_ver').val('');
        //                                         $('#orderform-cc_exp').val('');
        //     }
        // });

        $(document).on('dblclick', 'input.override', function() {
            if(rep_override){
                $('input.override').each(function() {
                    $(this).attr('readonly',false);
                    $(this).removeClass('background-red');
                    $(this).addClass('background-override');
                });
            }
        });

        $(document).on('focus', '#orderform-arate-override , #orderform-handling-override , #orderform-rate-override , #orderform-shipping-override', function() {
            switch($(this).attr('id')){
                case       'orderform-arate-override' : quote_arate++ ;
                                                        break;
                case    'orderform-handling-override' : quote_handling++ ;
                                                        break;
                case        'orderform-rate-override' : quote_rate++ ;
                                                        break;
                case    'orderform-shipping-override' : quote_shipping++ ;
                                                        break;
            }
        });

        $(document).on('focus', 'textarea.read-write', function() {
            $(this).removeClass('background-green');
            $(this).removeClass('background-red');
        });

        $(document).on('keyup', '.cc-new', function() {
            $('#orderform-cc_onfile_use').prop('checked',false);
        });

        $(document).on('keyup', '#customer-search', function() {
            $('#customer-um').html('');
            var params = {} ;
            params['rep'] = rep_id ;
            params['search'] = $(this).val() ;
            Orders.Ajax('search',params);
        });

        $(document).on('keyup', '#vzw_rep-lead', function() {
            var params = {} ;
            params['selection'] = $('#customer-customer').find('li.active').attr('data-value') ;
            params['e_code'] = $(this).val() ;
            Orders.Ajax('customer-vzwecode',params) ;
        });

        $(document).on('keyup', '.dataTables-search', function() {
            Orders.PagedReport($(this).closest('div.report-master').attr('id'));
        });

        console.log('Orders JS Loaded');
    
    },

    Ajax: function(pid,params) {

        $.ajax({
            url: '/orders/ajax',
            type: 'POST',
            dataType: 'json',
            data: {
                pid: pid,
                params: params
            },
            success: function(responseData) {
console.log(responseData);
                if(responseData.code==0){
                    switch(pid){

                        case   'backoffice-check' : $('#results-backoffice-'+responseData.params.orders_id).html('<table class="background-blue"><tbody></tbody></table>') ;
                                                    $('#results-backoffice-'+responseData.params.orders_id).find('tbody').append('<tr><th class="text-grey" colspan="6">LEAD pulled from Backoffice</th><th>ECODE</th></tr>');
                                                    $.each(responseData.backoffice, function( k1, v1 ) {
                                                        if((v1.request_company)&&(v1.e_code)&&(v1.request_company!='undefined')&&(v1.e_code!='undefined')){
                                                            if(v1.e_code==responseData.backoffice.e_code){
                                                                responseData.backoffice.background = 'background-green' ;
                                                            } else {
                                                                responseData.backoffice.background = 'background-red' ;
                                                            }
                                                            $('#results-backoffice-'+responseData.params.orders_id).find('tbody').append('<tr><th class="'+responseData.backoffice.background+'">'+v1.request_company.replace('\\\\\\','')+'</th><th class="'+responseData.backoffice.background+'">'+v1.request_name.replace('\\\\\\','')+'</th><th class="'+responseData.backoffice.background+'">'+v1.request_address.replace('\\\\\\','')+'</th><th class="'+responseData.backoffice.background+'">'+v1.request_city.replace('\\\\\\','')+'</th><th class="'+responseData.backoffice.background+'">'+v1.request_state.replace('\\\\\\','')+'</th><th class="'+responseData.backoffice.background+'">'+v1.request_zip.replace('\\\\\\','')+'</th><th class="'+responseData.backoffice.background+'">'+v1.e_code+'</th></tr>');
                                                        }
                                                    });
                                                    break;

                        case  'customer-customer' : account_id = responseData.accounts_id ;
                                                    if(!(responseData.company.company)){
                                                        responseData.company.company = responseData.company.accountname;
                                                    }
                                                    if(!(responseData.company.address_billing)){
                                                        responseData.company.address_billing = responseData.company.address;
                                                    }
                                                    if(!(responseData.company.address_shipping)){
                                                        responseData.company.address_shipping = responseData.company.address;
                                                    }
                                                    if(!(responseData.company.contact)){
                                                        responseData.company.contact = responseData.company.owner_name;
                                                    }
                                                    if(!(responseData.company.phone)){
                                                        responseData.company.phone = responseData.company.phonenumber;
                                                    }
                                                    $('#orderform-product').html('');
                                                    $.each(responseData.versions, function( k1, product ) {
                                                        // $('#orderform-product').append('<li data-value="'+product.versions_id+'"><a href="javascript:void(0);"><span class="nickname">'+product.nickname+'</span></a></li>');
                                                        $('#orderform-product').append('<option value="'+product.versions_id+'">'+product.nickname+'</option>');
                                                    });
                                                    $('#customer-billing').val(responseData.company.address_billing);
                                                    $('#customer-company').val(responseData.company.company);
                                                    $('#customer-contact').val(responseData.company.contact);
                                                    $('#customer-dms').val(responseData.company.dms);
                                                    $('#customer-email').val(responseData.company.email);
                                                    $('#customer-fax').val(responseData.company.fax);
                                                    $('#customer-phone').val(responseData.company.phone);
                                                    $('#customer-reseller').val(responseData.company.reseller);
                                                    $('#customer-shipping').val(responseData.company.address_shipping);
                                                    $('#customer-shipping-state').val(responseData.company.taxes_state);
                                                    if(responseData.company.cc_onfile){
                                                        $('#orderform-cc_file').show();
                                                        $('#orderform-cc_onfile').html(responseData.company.cc_onfile);
                                                        $('#orderform-cc_onfile_use').prop('checked',true);
                                                    } else {
                                                        $('#orderform-cc_file').hide();
                                                        $('#orderform-cc_onfile').html('');
                                                        $('#orderform-cc_onfile_use').prop('checked',false);
                                                    }
                                                    //
                                                    if(responseData.vzw_rep.e_code){
                                                        rep_m2m_repID = responseData.vzw_rep.m2m_repID;
                                                        $('#vzw_rep-lead').val(responseData.vzw_rep.e_code);
                                                        $('#span-verizon-rep').html('Verizon&nbsp;Rep:&nbsp;');
                                                        $('#customer-vzw_rep').html('<b>'+responseData.vzw_rep.name+'</b>&nbsp;&nbsp;&nbsp;&nbsp;'+responseData.vzw_rep.e_code+'&nbsp;&nbsp;&nbsp;&nbsp;'+responseData.vzw_rep.email);
                                                    } else {
                                                        rep_m2m_repID = 0;
                                                        $('#span-verizon-rep').html('');
                                                        $('#customer-vzw_rep').html('&nbsp;');
                                                    }
                                                    //
                                                    $('#orderform-payment-method').empty();
                                                    $.each(responseData.payment_methods, function( k1, payment_method ) {
                                                        console.log(payment_method);
                                                        // $('#orderform-payment-method').append('<li data-value="'+payment_method.payment_id+'"><a href="javascript:void(0);"><span class="nickname">'+payment_method.payment_method+'</span></a></li>');
                                                        $('#orderform-payment-method').append('<option value="'+payment_method.payment_id+'">'+payment_method.payment_method+'</option>');
                                                    });
                                                    //
                                                    if(responseData.order_recent){
                                                        $('#orderform-product').val(responseData.order_recent.versions_id);
                                                        $('#orderform-plan').val(responseData.order_recent.subscription_id);
                                                        $('#orderform-accessories').val(responseData.order_recent.accessories_id);
                                                        $('#orderform-shipping-method').val(responseData.order_recent.shipping_id);
                                                        $('#orderform-payment-method').val(responseData.order_recent.payment_id);
                                                        $('#orderform-quantity').val(responseData.order_recent.quantity);
                                                        $('#orderform-rate').val(responseData.order_recent.rate);
                                                        $('#orderform-rate-override').val(responseData.order_recent.override_rate);
                                                        $('#orderform-arate').val(responseData.order_recent.arate);
                                                        $('#orderform-arate-override').val(responseData.order_recent.override_arate);
                                                        $('#orderform-shipping-fee').val(responseData.order_recent.shipping_fee);
                                                        $('#orderform-shipping-override').val(responseData.order_recent.override_shipping);
                                                        $('#orderform-handling-fee').val(responseData.order_recent.handling_fee);
                                                        $('#orderform-handling-override').val(responseData.order_recent.override_handling);
                                                        $('#orderform-override-reason').val(responseData.order_recent.override_reason);
                                                    } else {
                                                        $('#orderform-product').val('');
                                                        $('#orderform-plan').val('');
                                                        $('#orderform-accessories').val('');
                                                        $('#orderform-shipping-method').val('');
                                                        $('#orderform-payment-method').val('');
                                                        $('#orderform-quantity').val(0);
                                                        $('#orderform-rate').val('');
                                                        $('#orderform-rate-override').val('');
                                                        $('#orderform-arate').val('');
                                                        $('#orderform-arate-override').val('');
                                                        $('#orderform-shipping-fee').val('');
                                                        $('#orderform-shipping-override').val('');
                                                        $('#orderform-handling-fee').val('');
                                                        $('#orderform-handling-override').val('');
                                                        $('#orderform-override-reason').val('');
                                                    }
                                                    // Orders.Quote();
                                                    break; 

                        case       'customer-new' : $('#orderform-product').html('');
                                                    $.each(responseData.versions, function( k1, product ) {
                                                        // $('#orderform-product').append('<li data-value="'+product.versions_id+'"><a href="javascript:void(0);"><span class="nickname">'+product.nickname+'</span></a></li>');
                                                        $('#orderform-product').append('<option value="'+product.versions_id+'">'+product.nickname+'</option>');
                                                    });
                                                    //
                                                    $('#orderform-payment-method').empty();
                                                    $.each(responseData.payment_methods, function( k1, payment_method ) {
                                                        console.log(payment_method);
                                                        // $('#orderform-payment-method').append('<li data-value="'+payment_method.payment_id+'"><a href="javascript:void(0);"><span class="nickname">'+payment_method.payment_method+'</span></a></li>');
                                                        $('#orderform-payment-method').append('<option value="'+payment_method.payment_id+'">'+payment_method.payment_method+'</option>');
                                                    });
                                                    //
                                                    $('#orderform-product').val('');
                                                    $('#orderform-plan').val('');
                                                    $('#orderform-accessories').val('');
                                                    $('#orderform-shipping-method').val('');
                                                    $('#orderform-payment-method').val('');
                                                    $('#orderform-quantity').val(0);
                                                    $('#orderform-rate').val('');
                                                    $('#orderform-rate-override').val('');
                                                    $('#orderform-arate').val('');
                                                    $('#orderform-arate-override').val('');
                                                    $('#orderform-shipping-fee').val('');
                                                    $('#orderform-shipping-override').val('');
                                                    $('#orderform-handling-fee').val('');
                                                    $('#orderform-handling-override').val('');
                                                    $('#orderform-override-reason').val('');
                                                    // Orders.Quote();
                                                    break; 

                        case  'customer-vzwecode' : if(responseData.vzw_rep.e_code){
                                                        rep_m2m_repID = responseData.vzw_rep.rep_m2m_repID;
                                                        $('#vzw_rep-lead').val(responseData.vzw_rep.e_code);
                                                        $('#span-verizon-rep').html('Verizon&nbsp;Rep:&nbsp;');
                                                        $('#customer-vzw_rep').html('<b>'+responseData.vzw_rep.name+'</b>&nbsp;&nbsp;&nbsp;&nbsp;'+responseData.vzw_rep.e_code+'&nbsp;&nbsp;&nbsp;&nbsp;'+responseData.vzw_rep.email);
                                                    } else {
                                                        rep_m2m_repID = 0;
                                                        $('#span-verizon-rep').html('');
                                                        $('#customer-vzw_rep').html('&nbsp;');
                                                    }
                                                    break; 

                        case              'login' : fulfillment_accounting      = responseData.fulfillment_accounting;
                                                    fulfillment_activations     = responseData.fulfillment_activations;
                                                    fulfillment_approved        = responseData.fulfillment_approved;
                                                    fulfillment_credit          = responseData.fulfillment_credit;
                                                    fulfillment_inventoried     = responseData.fulfillment_inventoried;
                                                    fulfillment_invoiced        = responseData.fulfillment_invoiced;
                                                    fulfillment_labeled         = responseData.fulfillment_labeled;
                                                    fulfillment_pending         = responseData.fulfillment_pending;
                                                    fulfillment_read_only       = responseData.fulfillment_read_only;
                                                    fulfillment_read_write      = responseData.fulfillment_read_write;
                                                    fulfillment_shipped         = responseData.fulfillment_shipped;
                                                    fulfillment_user            = responseData.fulfillment_user ;
                                                    
                                                    if(fulfillment_accounting){
                                                        $('#li-a-accounting').closest('li').show();
                                                    }
                                                    if(fulfillment_activations){
                                                        $('#li-a-activations').closest('li').show();
                                                        $('#li-a-activations_issued').closest('li').show();
                                                    }
                                                    if(fulfillment_approved){
                                                        $('#li-a-approved').closest('li').show();
                                                    }
                                                    if(fulfillment_credit){
                                                        $('#li-a-credit').closest('li').show();
                                                    }
                                                    if(fulfillment_inventoried){
                                                        $('#li-a-inventoried').closest('li').show();
                                                    }
                                                    if(fulfillment_invoiced){
                                                        $('#li-a-invoiced').closest('li').show();
                                                    }
                                                    if(fulfillment_labeled){
                                                        $('#li-a-labeled').closest('li').show();
                                                    }
                                                    if(fulfillment_pending){
                                                        $('#li-a-pending').closest('li').show();
                                                    }
                                                    if(fulfillment_read_only){
                                                        $('#li-a-read_only').closest('li').show();
                                                    }
                                                    if(fulfillment_read_write){
                                                        $('#li-a-read_write').closest('li').show();
                                                    }
                                                    if(fulfillment_shipped){
                                                        $('#li-a-shipped').closest('li').show();
                                                    }

                                                    if(fulfillment_pending){
                                                        $('#li-a-pending').trigger('click');
                                                    } else if (fulfillment_approved) {
                                                        $('#li-a-approved').trigger('click');
                                                    } else if (fulfillment_labeled) {
                                                        $('#li-a-labeled').trigger('click');
                                                    } else if (fulfillment_inventoried) {
                                                        $('#li-a-inventoried').trigger('click');
                                                    } else if (fulfillment_shipped) {
                                                        $('#li-a-shipped').trigger('click');
                                                    } else if (fulfillment_invoiced) {
                                                        $('#li-a-invoiced').trigger('click');
                                                    } else if (fulfillment_read_only) {
                                                        $('#li-a-read_only').trigger('click');
                                                    } else if (fulfillment_read_write) {
                                                        $('#li-a-read_write').trigger('click');
                                                    } else if (fulfillment_activations) {
                                                        $('#li-a-activations').trigger('click');
                                                    } else if (fulfillment_accounting) {
                                                        $('#li-a-accounting').trigger('click');
                                                    } else if (fulfillment_credit) {
                                                        $('#li-a-credit').trigger('click');
                                                    }
                                                    break; 

                        case              'order' : $('#confirmation-confirmation').html('Processing Response...');
                                                    if(responseData.confirm){
                                                        $('#confirmation-confirmation').html(responseData.confirm);
                                                    }
                                                    if(responseData.error) {
                                                        $('#confirmation-confirmation').append('<hr><p>');
                                                        $.each(responseData.error, function( k, v ) {
                                                            $('#confirmation-confirmation').append('<p>'+v);
                                                        });
                                                    }
                                                    $('#orders-submitted-table').find('.dataTables-search-btn').trigger('click');
                                                    break; 

                        case      'order-delete' : if(report_master){
                                                        $('#'+report_master).find('.dataTables-search-btn').trigger('click');;
                                                    }
                                                    break; 

                        case            'payment' : $('#'+responseData.payment).removeClass('background-red');
                                                    $('#'+responseData.payment).addClass('background-green');
                                                    $('#'+responseData.payment).attr('checked',responseData.value);
                                                    break;

                        case              'quote' : $('#orderform-arate').val(responseData.quote.arate);
                                                    $('#orderform-arate-override').val(responseData.quote.arate_override);
                                                    $('#orderform-extended').val(responseData.quote.extended);
                                                    $('#orderform-extended-override').val(responseData.quote.extended_override);
                                                    $('#orderform-extended-shipping').val(responseData.quote.extended_shipping);
                                                    $('#orderform-extended-shipping-override').val(responseData.quote.extended_shipping_override);
                                                    $('#orderform-extended-subtotal').val(responseData.quote.extended_subtotal);
                                                    $('#orderform-extended-subtotal-override').val(responseData.quote.extended_subtotal_override);
                                                    $('#orderform-extended-taxes').val(responseData.quote.extended_taxes);
                                                    $('#orderform-extended-taxes-override').val(responseData.quote.extended_taxes_override);
                                                    $('#orderform-extended-taxrate').html(responseData.quote.extended_taxrate);
                                                    $('#orderform-extended-taxrate-override').html(responseData.quote.extended_taxrate_override);
                                                    $('#orderform-handling-fee').val(responseData.quote.handling_fee);
                                                    $('#orderform-handling-override').val(responseData.quote.handling_override);
                                                    $('#orderform-quantity').val(responseData.quote.quantity);
                                                    $('#orderform-quantity-override').val(responseData.quote.quantity_override);
                                                    $('#orderform-rate').val(responseData.quote.rate);
                                                    $('#orderform-rate-override').val(responseData.quote.rate_override);
                                                    $('#orderform-shipping-fee').val(responseData.quote.shipping_fee);
                                                    $('#orderform-shipping-override').val(responseData.quote.shipping_override);
                                                    $('#orderform-total').val(responseData.quote.total);
                                                    $('#orderform-total-override').val(responseData.quote.total_override);
                                                    savings_rate     = responseData.quote.savings_rate;
                                                    savings_arate    = responseData.quote.savings_arate;
                                                    savings_shipping = responseData.quote.savings_shipping;
                                                    savings_handling = responseData.quote.savings_handling;
                                                    $('#orderform-diff-rate').html(responseData.quote.diff_rate);
                                                    $('#orderform-diff-arate').html(responseData.quote.diff_arate);
                                                    $('#orderform-diff-handling').html(responseData.quote.diff_handling);
                                                    $('#orderform-diff-extended').html(responseData.quote.diff_extended);
                                                    $('#orderform-diff-taxes').html(responseData.quote.diff_taxes);
                                                    $('#orderform-diff-subtotal').html(responseData.quote.diff_subtotal);
                                                    $('#orderform-diff-shipping').html(responseData.quote.diff_shipping);
                                                    $('#orderform-diff-shipping-extended').html(responseData.quote.diff_shipping_extended);
                                                    $('#orderform-diff-total').html(responseData.quote.diff_total);
                                                    break; 

                        case                'rep' : rep_dealer_id = responseData.rep_dealer_id ;
                                                    rep_ecode = responseData.rep_ecode ;
                                                    rep_id = responseData.rep ;
                                                    rep_name = responseData.rep_name ;
                                                    rep_override = responseData.rep_override ;
                                                    $('#customer').trigger('click');
                                                    $('#customer-customer').empty();
                                                    $('#customer-customer').append('<li class="active customer-customer-new" data-value="0"><a href="javascript:void(0);">New Account</a></li>');
                                                    $.each(responseData.companies, function( k1, company ) {
                                                        if(company.accountname){
                                                            company.company = company.accountname;
                                                        }
                                                        $('#customer-customer').append('<li data-value="'+company.accounts_id+'"><a href="javascript:void(0);" title="'+company.contact+', '+company.address_billing+' ['+company.accounts_id+']">'+company.company+'&nbsp;<span class="text-grey pull-right">[&nbsp;'+company.account_id+'&nbsp;]</span></a></li>');
                                                    });
                                                    $('#orderform-product').empty();
                                                    $.each(responseData.versions, function( k1, product ) {
                                                        // $('#orderform-product').append('<li data-value="'+product.versions_id+'"><a href="javascript:void(0);"><span class="nickname">'+product.nickname+'</span></a></li>');
                                                        $('#orderform-product').append('<option value="'+product.versions_id+'">'+product.nickname+'</option>');
                                                    });
                                                    $('#orderform-payment-method').empty();
                                                    $.each(responseData.payment_methods, function( k1, payment_method ) {
                                                        console.log(payment_method);
                                                        // $('#orderform-payment-method').append('<li data-value="'+payment_method.payment_id+'"><a href="javascript:void(0);"><span class="nickname">'+payment_method.payment_method+'</span></a></li>');
                                                        $('#orderform-payment-method').append('<option value="'+payment_method.payment_id+'">'+payment_method.payment_method+'</option>');
                                                    });
                                                    $('#orderform-shipping-method').empty();
                                                    $.each(responseData.shipping_methods, function( k1, shipping_method ) {
                                                        console.log(shipping_method);
                                                        // $('#orderform-shipping-method').append('<li data-value="'+shipping_method.shipping_id+'"><a href="javascript:void(0);"><span class="nickname">'+shipping_method.shipping_method+'&nbsp;<span class="text-grey text-10">$'+shipping_method.cost+' per unit'+shipping_method.flat+'</span></span></a></li>');
                                                        $('#orderform-shipping-method').append('<option value="'+shipping_method.shipping_id+'">'+shipping_method.shipping_method+': $'+shipping_method.cost+' per unit'+shipping_method.flat+'</option>');
                                                    });
                                                    $('.customer-customer-new').trigger('click');
                                                    break;

                        case         'read-write' : $('#'+responseData.read_write).removeClass('background-red');
                                                    $('#'+responseData.read_write).addClass('background-green');
                                                    $('#'+responseData.read_write).val(responseData.value);
                                                    break;

                        case             'search' :
                        case                 'um' : $('#customer-um').html(responseData.um);
                                                    $('#customer-customer').empty();
                                                    $('#customer-customer').append('<li class="active customer-customer-new" data-value="0"><a href="javascript:void(0);">New Account</a></li>');
                                                    $.each(responseData.companies, function( k1, company ) {
                                                        if(company.accountname){
                                                            company.company = company.accountname;
                                                        }
                                                        $('#customer-customer').append('<li data-value="'+company.accounts_id+'"><a href="javascript:void(0);" title="'+company.contact+', '+company.address_billing+' ['+company.accounts_id+']">'+company.company+'&nbsp;<span class="text-grey pull-right">[&nbsp;'+company.account_id+'&nbsp;]</span></a></li>');
                                                    });
                                                    break; 

                        case             'states' : $('#'+responseData.states).removeClass('background-red');
                                                    $('#'+responseData.states).addClass('background-green');
                                                    $('#'+responseData.states).val(responseData.value);
                                                    break;

                        case             'status' : Orders.PagedReport(lastPagedReport,1);
                                                    break; 

                    }
                } else {
                    alert(responseData.message);
                }
            }

        });

    },

    PagedReport: function(pid,pag,noskip) {

        if((!(ajaxSkip))||(noskip)){

            lastPagedReport=pid;

            ajaxSkip=1;
            window.setTimeout("ajaxSkip='';",3000);

            $('#'+pid).find('thead').empty();
            $('#'+pid).find('tbody').empty();
            $('#'+pid).find('thead').append('<tr><th class="text-grey"><i>requesting data...</i></td></tr>');
            $('#'+pid).find('tbody').append('<tr><td style="height:1000px;">&nbsp;</td></tr>');

            var date_range = 0;
            var length = $('#'+pid).find('select.dataTables-length').val();
            var search = $('#'+pid).find('input.dataTables-search').val();
            var pageCount = $('#'+pid).find('span.dataTables-page-count').html();
            var pageTotal = $('#'+pid).find('span.dataTables-page-total').html();

            switch(pag){
                case 'begin' : pageCount=0;
                               break;
                case  'down' : pageCount--;
                               break;
                case   'end' : pageCount=pageTotal;
                               break;
                case    'up' : pageCount++;
                               break;
            }

            if(pageCount<1){
                pageCount=1;
            }else if(pageCount>pageTotal){
                pageCount=pageTotal;
            }

            $.ajax({
                url: '/orders/ajax/reports',
                type: 'POST',
                dataType: 'json',
                data: {
                    daterange: date_range,
                    fulfillment_user: fulfillment_user,
                    fulfillment_pending: fulfillment_pending,
                    fulfillment_approved: fulfillment_approved,
                    fulfillment_labeled: fulfillment_labeled,
                    fulfillment_inventoried: fulfillment_inventoried,
                    fulfillment_shipped: fulfillment_shipped,
                    fulfillment_invoiced: fulfillment_invoiced,                    
                    length: length,
                    pag: pag,
                    pid: pid,
                    rep_id: rep_id,
                    search: search,
                    pageCount: pageCount,
                    pageTotal: pageTotal
                },
                success: function(responseData) {
                    breadcrumbs='';
                    ajaxSkip='';
console.log('Core.DataTable.pagedReport:'+responseData.code+':'+responseData.message);
                    if(responseData.pid){
console.log('Core.DataTable.pagedReport:responseData.pid:'+responseData.pid);
                        $('#'+responseData.pid).find('thead').empty();
                        $('#'+responseData.pid).find('tbody').empty();
                        if(responseData.code === 0){
                            $('#'+responseData.pid).find('thead').append(responseData.thead);
                            $('#'+responseData.pid).find('tbody').append(responseData.tbody);
                            $('#'+responseData.pid).find('span.dataTables-records-count').text(responseData.records);
                            $('#'+responseData.pid).find('span.dataTables-page-count').text(responseData.pageCount);
                            $('#'+responseData.pid).find('span.dataTables-page-total').text(responseData.pageTotal);
                            $('#'+responseData.pid).find('span.dataTables-current-page').text(responseData.pageCount);
                            $('#'+responseData.pid).find('span.dataTables-last-report').html(responseData.lastReport);
                        } else {
                            $('#'+responseData.pid).find('thead').append('<tr><th>Error</td></tr>');
                            $('#'+responseData.pid).find('tbody').append('<tr><td class="error">'+responseData.message+'</td></tr>');
                            $('#'+responseData.pid).find('span.dataTables-records-count').text('0');
                            $('#'+responseData.pid).find('span.dataTables-page-count').text('0');
                            $('#'+responseData.pid).find('span.dataTables-page-total').text('0');
                            $('#'+responseData.pid).find('span.dataTables-current-page').text('0');
                            $('#'+responseData.pid).find('span.dataTables-last-report').text('');
                        }

                        Orders.PagedReportScroll();

                    } else {
console.log('NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! NO! ');
console.log(responseData);
                        // window.location = '/logout';
                    }

                }
            });

        }
    },

    PagedReportFix: function(pid) {
        console.log('PagedReportFix:'+pid);
        var rTop = $('#'+pid).find('.panel-report-scroll').offset().top;
        var mTop = $('#'+pid).closest('.modal-content').offset().top;
        var mHeight = $('#'+pid).closest('.modal-content').height();
        var rHeight = mTop + mHeight - rTop - 10;
        $('#'+pid).find('.panel-report-scroll').height(rHeight);
    },

    PagedReportScroll: function() {

        $('.panel-report-scroll').each(function(){

            console.log('PagedReportScroll:'+$(this).attr('id'));

            if($(this).is(':visible')){
            
                var offset = $(this).offset().top+14;

                var newheight=$(window).height()-offset;

                if(newheight<30){
                    newheight=30;
                }

                if ($(this).height()!=newheight) {
                    $(this).height(newheight+'px');
                }
            
            } else {
            
                $(this).height('100px');
            
            }

        });

    },

    Quote: function(override) {
        // if(!(override)){
        //     $('#orderform-arate').attr('readonly',true);
        //     $('#orderform-arate').removeClass('background-override');
        //     $('#orderform-arate').addClass('background-red');
        //     $('#orderform-rate').attr('readonly',true);
        //     $('#orderform-rate').removeClass('background-override');
        //     $('#orderform-rate').addClass('background-red');
        //     $('#orderform-shipping-fee').attr('readonly',true);
        //     $('#orderform-shipping-fee').removeClass('background-override');
        //     $('#orderform-shipping-fee').addClass('background-red');
        // }
        var params = {};
        // params['accessories-id'] = $('#orderform-accessories').find('li.active').attr('data-value');
        params['accessories-id'] = $('#orderform-accessories').val();
        params['arate'] = $('#orderform-arate').val();
        params['arate-override'] = $('#orderform-arate-override').val();
        params['notes'] = $('#orderform-notes').val();
        params['override'] = override;
        // params['payment-method'] = $('#orderform-payment-method').find('li.active').attr('data-value');
        params['payment-method'] = $('#orderform-payment-method').val();
        // params['plan'] = $('#orderform-plan').find('li.active').attr('data-value');
        params['plan'] = $('#orderform-plan').val();
        params['po'] = $('#orderform-po').val();
        // params['product'] = $('#orderform-product').find('li.active').attr('data-value');
        params['product'] = $('#orderform-product').val();
        params['quantity'] = $('#orderform-quantity').val();
        params['rate'] = $('#orderform-rate').val();
        params['rate-override'] = $('#orderform-rate-override').val();
        params['reseller'] = $('#customer-reseller').val();
        // params['shipping-id'] = $('#orderform-shipping-method').find('li.active').attr('data-value');
        params['shipping-id'] = $('#orderform-shipping-method').val();
        params['shipping-fee'] = $('#orderform-shipping-fee').val();
        params['shipping-override'] = $('#orderform-shipping-override').val();
        params['shipping-state'] = $('#customer-shipping-state').val();
        params['handling-fee'] = $('#orderform-handling-fee').val();
        params['handling-override'] = $('#orderform-handling-override').val();
        params['total'] = $('#orderform-total').val();
        Orders.Ajax('quote',params);
    }

});
