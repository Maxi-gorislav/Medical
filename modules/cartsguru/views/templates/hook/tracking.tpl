{*
 * Carts Guru
 *
 * @author    LINKT IT
 * @copyright Copyright (c) LINKT IT 2016
 * @license   Commercial license
 *}

<script>
    (function($){
        $(document).ready(function() {
            if (!Array.isArray) {
                Array.isArray = function(arg) {
                    return Object.prototype.toString.call(arg) === '[object Array]';
                };
            }

            var fieldNames = {
                email: ['guest_email', 'email'],
                homePhoneNumber: ['phone'],
                mobilePhoneNumber: ['phone_mobile'],
                firstname: ['firstname', 'customer_firstname'],
                lastname: ['lastname', 'customer_lastname'],
                countryCode: ['id_country']
            };
            
            var fields = {
                    email: [],
                    homePhoneNumber: [],
                    mobilePhoneNumber: [],
                    firstname: [],
                    lastname: [],
                    countryCode: []
            };

            function setupTracking () {
                for (var item in fieldNames) {
                    if (fieldNames.hasOwnProperty(item)) {
                        for (var i = 0; i < fieldNames[item].length; i++) {
                            //Get by name
                            var els = document.getElementsByName(fieldNames[item][i]);
                            for (var j = 0; j < els.length; j++) {
                                fields[item].push(els[j]);
                            }

                            //Get by ID
                            var el = document.getElementById(fieldNames[item][i]);
                            if (el &&  el.name !== fieldNames[item][i]){
                                fields[item].push(el);
                            }
                        }
                    }
                }
                if (fields.email.length > 0 && fields.firstname.length > 0) {
                    for (var item in fields) {
                        if (fields.hasOwnProperty(item)) {
                            for (var i = 0; i < fields[item].length; i++) {
                                $(fields[item][i]).bind('blur', trackData);
                            }
                            
                        }
                    }
                }
            }

            function collectData () {
                var data = {};
                for (var item in fields) {
                    if (fields.hasOwnProperty(item)) {
                        for (var i = 0; i < fields[item].length; i++) {
                            data[item] =  $(fields[item][i]).val();
                            if (data[item] && data[item].trim){
                                data[item].trim();
                            }
                            if (data[item] !== ''){
                                break;
                            }
                        }
                    }
                }
                return data;
            }

            function trackData () {
                var data = collectData();
                if (data.email) {
                    $(document).ready(function() {
                        $.ajax({
                            url: "{$trackingUrl|escape:'javascript':'UTF-8'}",
                            type: "POST",
                            data: data
                        });
                    });
                }
            }

            setupTracking();
        });
    })(jQuery);
</script>
