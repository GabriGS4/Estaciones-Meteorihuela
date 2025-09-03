<?php
chdir(__DIR__ . '/../estaciones');
system('/usr/bin/php8.3 artisan schedule:run >> /dev/null 2>&1');
