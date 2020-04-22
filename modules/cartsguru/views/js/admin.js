/**
 * Carts Guru
 *
 * @author    LINKT IT
 * @copyright Copyright (c) LINKT IT 2016
 * @license   Commercial license
 */

(function ($) {
    // On ready
    $(document).ready(function ($) {
        // Switch active view
        function switchView(view, backToView){
            if (!view){
                view =  window.cg_backto;
                 window.cg_backto = null;
            }

            $('#cartsguru-welcome').removeClass();
            switch(view){
                case 'view-try-it':
                case 'view-have-account':
                case 'view-success':
                case 'view-no-store-selected':
                     $('#cartsguru-welcome').addClass(view);
            }

            window.cg_backto = backToView;
        }

        //Declare global functions
        window.cg_switchView = switchView;
    });


})(jQuery);
