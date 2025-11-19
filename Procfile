web: php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=$PORT
worker: php artisan queue:work --tries=3 --timeout=90 --sleep=3 --max-jobs=1000
scheduler: while true; do php artisan schedule:run; sleep 60; done
