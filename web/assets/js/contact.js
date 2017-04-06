

$(function () {
    $('#phone').focusout(function(){
        var phone, element;
        element = $(this);
        element.unmask();
        phone = element.val().replace(/\D/g, '');
        if(phone.length > 10) {
            element.mask("(99) 99999-999?9");
        } else {
            element.mask("(99) 9999-9999?9");
        }
    }).trigger('focusout');

    $('input').each(function(){
        if ($(this)[0].hasAttribute('data-mask')) {
            $(this).mask($(this).data('mask'));
        }
    });
});
