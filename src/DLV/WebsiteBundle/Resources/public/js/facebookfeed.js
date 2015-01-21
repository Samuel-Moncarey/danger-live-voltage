$(function(){
    $.ajax({
        'type': 'GET',
        'url': 'http://localhost/perso/danger-live-voltage/web/app_dev.php/ajax/get/facebookfeed',
        'dataType': 'json',
        'success': function(data) {
            $.each(data, function() {
                console.log($(this));
            });
        }
    });
});
