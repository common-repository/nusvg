jQuery(document).ready(function() {
   jQuery('#nusvg_item_editor').on('change','[type=color]',function() {
       jQuery(this).prev().val(jQuery(this).val());
   });
});