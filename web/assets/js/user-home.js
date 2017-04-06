$(function () {
    getPosition(loggedUserId);

    $('.openRestData').on('click', function () {
        var id = $(this).data('id');
        $.post(restDataUrl, {id: id}, function (response) {
            if (response.success) {
                $('#restDataName').html(response.restaurant.name);
                $('#restDataPhone').html('<i class="ace-icon fa fa-phone"></i>'+response.restaurant.phone);
                $('#restDataWhats').html('<i class="ace-icon fa fa-whatsapp"></i>'+response.restaurant.whatsapp);
                $('#restDataSite').html('<a href="http://'+response.restaurant.site+'" target="blank"><i class="ace-icon fa fa-globe"></i>'+response.restaurant.site+'</a>');
                $('#restDataAddress').html('<i class="ace-icon fa fa-map-marker"></i>'+response.restaurant.address);
                $('#modal-dados-rest').modal();
            }
        });
    });
});