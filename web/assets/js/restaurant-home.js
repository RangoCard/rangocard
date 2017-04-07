$(function () {
    $('form[name="formSearchUser"]').on('submit', function (e) {
        e.preventDefault();
        var url = $(this).attr('action');
        var search = $(this).find('.searchUser');
        var sale = $(search).data('sale');
        $.post(url, {sale: sale, search: $(search).val()})
            .done(function (response) {
                if (response.success) {
                    $('#bodyUsers-'+response.sale).empty();
                    for (var i in response.users) {
                        var rowHtml =
                            '<tr>\
                                <td>'+response.users[i].name+'</td>\
                                <td>'+response.users[i].createdAt+'</td>\
                                <td>'+response.users[i].numSeals+'</td>\
                                <td>\
                                    <a class="btn-token clearUserSeals pull-right" data-id="'+response.users[i].id+'" data-sale="'+response.sale+'" href="#">Zerar selos</a>\
                                </td>\
                            </tr>';
                        var row = $(rowHtml).appendTo('#bodyUsers-'+response.sale);

                        if (response.users[i].numSeals < response.users[i].saleSealLimit) {
                            row.find('.clearUserSeals').remove();
                        }
                    }
                    clearUserSeals();
                }
            });
    });

    $('#formTokenGenerate').on('submit', function (e) {
        e.preventDefault();

        $.post($(this).attr('action'), $(this).serialize())
            .done(function (response) {
                var type = 'success';
                if (!response.success) {
                    type = 'danger';
                }
                var alerthtml =
                    '<div class="alert alert-'+type+' flashbag-message" role="alert" style="z-index: 99999;">\
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
                        '+response.message+'\
                    </div>';
                var alertMsg = $(alerthtml).prependTo('body');

                alertMsg.fadeIn();
                setTimeout(function () {
                    alertMsg.fadeOut();
                    alertMsg.remove();
                }, 3000);
            });
    });

    $('.btnDeleteSale').on('click', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var saleId = $(this).data('id');
        var modalHtml =
            '<div class="modal fade" tabindex="-1" role="dialog">\
                <div class="modal-dialog" role="document">\
                    <div class="modal-content">\
                        <div class="modal-header">\
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
                            <h4 class="modal-title">Deletar promoção</h4>\
                        </div>\
                        <div class="modal-body">\
                            <p>Deseja deletar a promoção?</p>\
                        </div>\
                        <div class="modal-footer">\
                            <button type="button" class="btn btn-default btn-danger" data-dismiss="modal">Não</button>\
                            <button id="btnConfirmDeleteSale" type="button" class="btn btn-primary">Sim</button>\
                        </div>\
                    </div>\
                </div>\
            </div>';
        var modalMsg = $(modalHtml).appendTo('body');
        modalMsg.modal('show').on('hidden.bs.modal', function () {
            modalMsg.remove();
        });

        $('#btnConfirmDeleteSale').on('click', function () {
            $.post(url, function (response) {
                var type = 'success';
                if (response.success) {
                    $('#sale-'+saleId).remove();
                } else {
                    type = 'danger';
                }
                var alerthtml =
                    '<div class="alert alert-'+type+' flashbag-message" role="alert" style="z-index: 99999;">\
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
                        '+response.message+'\
                    </div>';
                var alertMsg = $(alerthtml).prependTo('body');

                alertMsg.fadeIn();
                setTimeout(function () {
                    alertMsg.fadeOut();
                    alertMsg.remove();
                }, 3000);
            });
            modalMsg.modal('hide');
        });
    });

    clearUserSeals();
});

function clearUserSeals() {
    $('.clearUserSeals').on('click', function (e) {
        e.preventDefault();
        var self = $(this);
        var id = self.data('id'),
            sale = self.data('sale');

        var modalHtml =
            '<div class="modal fade" tabindex="-1" role="dialog">\
                <div class="modal-dialog" role="document">\
                    <div class="modal-content">\
                        <div class="modal-header">\
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
                            <h4 class="modal-title">Zerar selos</h4>\
                        </div>\
                        <div class="modal-body">\
                            <p>Deseja zerar os selos do usuário?</p>\
                        </div>\
                        <div class="modal-footer">\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Não</button>\
                            <button id="btnConfirmEmptySeals" type="button" class="btn btn-primary">Sim</button>\
                        </div>\
                    </div>\
                </div>\
            </div>';
        var modalMsg = $(modalHtml).appendTo('body');
        modalMsg.modal('show').on('hidden.bs.modal', function () {
            modalMsg.remove();
        });

        $('#btnConfirmEmptySeals').on('click', function () {
            $.post(urlClearUserSeals, {user: id, sale: sale}, function (response) {
                if (response.success) {
                    self.parents('tr').remove();
                }
            }, 'json');
            modalMsg.modal('hide');
        });
    });
}