VueAppInitialize = function(callback,admin) {
    if ($("meta[name=yh-auth-hash-id]").length) {
        var hid = $("meta[name=yh-auth-hash-id]").attr('content');
        if (hid != '') {
            window.yh.auth.hash_id = hid;
            $.post(
                '/api/hash',
                {
                    hash_id:yh.auth.hash_id
                },
                function(data) {
                    if (data.error) {
                        document.location='/logout';
                        return;
                    }
                    if (admin && data.user_type==='user') {
                        document.location='/login';
                    }
                    yh.auth.user_id = data.user_id;
                    yh.auth.user_type = data.user_type;
                    yh.auth.user_name = data.user_name;
                    callback();
                    LaravelSessionPing();
                }
            ).fail(function(response){
                alert('Error connecting to API');
                document.location='/logout';
            });
        }
        else{
            alert('Your session is expired. Please login again.');
            document.location='/logout';
        }
    }
}
