_$.ready(function ($) {
    $('a[data-login-as]').on('click', function (e) {
        e.preventDefault();
        var userId = this.getAttribute('data-login-as');

        if (userId) {
            UIkit.modal.confirm(this.title).then(
                function () {
                    $.http.post(
                        $hb.uri.base + '/user/login-as',
                        {userId: userId},
                        function (response) {
                            if ('OK' === response) {
                                window.open($hb.uri.root + '/');
                            }
                        },
                    );
                },
                function () {
                },
            );
        }
    });
});