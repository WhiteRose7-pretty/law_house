# iusvitae Game Server

$ npm install
$ node .


# Deploy node server using PM2

## Install PM2
    npm install pm2@latest -g
    # or
    yarn global add pm2

## Start app
    cd /var/www/testy/games
    pm2 start index.js


## Stop app
    pm2 restart index
