
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

    $('#formRestReg').on('submit', function (e) {
        e.preventDefault();
        var form =  $(this);
        $.post( form.attr('action'), form.serialize(), function( response ) {
            if (response.success) {
                window.location = response.url;
            } else {
                var alerthtml =
                    '<div class="alert alert-danger flashbag-message" role="alert" style="z-index: 99999;">\
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
                        '+response.message+'\
                    </div>';
                var alertMsg = $(alerthtml).prependTo('body');

                alertMsg.fadeIn();
                setTimeout(function () {
                    alertMsg.fadeOut();
                    alertMsg.remove();
                }, 3000);
            }
        });
    });
});