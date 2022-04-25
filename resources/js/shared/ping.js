window.LaravelSessionPing = function() {
  $.get('/ping');
  setTimeout(LaravelSessionPing,5*60*1000);// ping every 5 minutes to refresh session
}
