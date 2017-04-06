$(function () {
    // GLOBAL LOADING
    var $loading = $('#globalLoading').hide();
    $(document)
        .ajaxStart(function () {
            $loading.show();
        })
        .ajaxStop(function () {
            $loading.hide();
        });


    var newItemHtml = '';
    $.post(notificationsUrl)
        .done(function (response) {
            if (response.success) {
                $('#notificationTotal').html(response.notifications.length);

                var notificationText = '';
                if (response.notifications.length == 1) {
                    notificationText = 'Notificação';
                } else {
                    notificationText = 'Notificações';
                }
                $('#notificationListTitle').append(response.notifications.length + ' ' + notificationText);

                for (var i in response.notifications) {
                    var icon = 'icon-selo';
                    if (response.notifications[i].type == 2) {
                        icon = 'fa-cutlery';
                    }
                    newItemHtml = '<li>\
                                        <a data-id="'+response.notifications[i].id+'" href="#" onclick="removeNotification(this)">\
                                            <i class="btn btn-xs btn-primary fa '+icon+'"></i>\
                                            '+response.notifications[i].message+'\
                                        </a>\
                                    </li>';
                    $('#notificationList').append(newItemHtml);
                }
            }
        });

    // ALERTS
    $('.flashbag-message').fadeIn();
    setTimeout(function () {
        $('.flashbag-message').fadeOut();
    }, 2000);
});

function removeNotification(item) {
    $.post(removeNotificationUrl, {id: $(item).data('id')})
        .done(function (response) {
            if (response.success) {
                $(item).parent().remove();

                var total = $('#notificationTotal').html() - 1;

                $('#notificationTotal').html(total);

                var notificationText = '';
                if (total == 1) {
                    notificationText = 'Notificação';
                } else {
                    notificationText = 'Notificações';
                }
                $('#notificationListTitle').empty().append('<i class="ace-icon fa fa-exclamation-triangle"></i> ' + total + ' ' + notificationText);
            }
        });
}
