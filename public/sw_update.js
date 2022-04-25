if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/pwabuilder-sw.js').then(regist => {
        swRegist = regist;
        console.log('Service Worker Registered');

        regist.addEventListener('updatefound', () => {
            const newWorker = regist.installing;
            console.log('Service Worker update found!');

            newWorker.addEventListener('statechange', function () {
                console.log('Service Worker state changed:', this.state);
            });
        });
    });

    navigator.serviceWorker.addEventListener('controllerchange', () => {
        console.log('Controller changed');
    });
}
