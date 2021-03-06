
$(function () {
    $('#phone,#whatsapp').focusout(function(){
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

    var typingTimer;
    var doneTypingInterval = 1000;
    var inputCep = $('#cep');

    inputCep.on('keyup', function () {
        clearTimeout(typingTimer);
        if (inputCep.val().replace(/[_-]/g, '').length >= 8) {
            typingTimer = setTimeout(function(){
                $.ajax({
                    dataType : 'json',
                    url: 'http://apps.widenet.com.br/busca-cep/api/cep.json?code='+inputCep.val(),
                    beforeSend: function() {
                        $('#loadingCep').show();
                    },
                    success : function(data) {
                        if (data.status == 1) {
                            $('#street').val(data.address);
                            $('#district').val(data.district);
                            $('#city').val(data.city);
                            $('#state').val(data.state);
                        }
                    },
                    complete : function() {
                        $('#loadingCep').hide();
                    }
                });
            }, doneTypingInterval);
        }
    });
});