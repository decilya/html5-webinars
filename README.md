*Composer, vendor и прочая первоначальная настройка*
-------------------------------

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"

php composer-setup.php

php -r "unlink('composer-setup.php');"


php composer.phar global require "fxp/composer-asset-plugin:^1.2.0"


php composer.phar install
или
php composer.phar update

chown -R apache runtime/
chown -R apache web/assets/

После этого необходимо

1) скопировать конфиги из config/origin в config

2) написать в файле config/db.php название базы (самому создавать БД НЕ НУЖНО), в последующем база создается с помощью скрипта (незабыть указать и подходящий для mysql логин и пароль)

3) в консоли выполнить команду

php yii app/create-db

4) Далее стандартно -  просто применить все миграции

php yii migrate

Затем создадим админа системы php yii app/add-admin "admin-email@email.com"

Примечание: админка логикофизически вынесена в отдельный модуль modules/admin (доступ через http://bla-bla.foo/admin)

Затем необходимо создать папку uploads в корне проекта:

mkdir uploads

chown -R apache uploads/


*NODE.JS*
-------------

Для работы системы необходимо установить помимо сервера yii2, еще и сервер ноды https://github.com/muaz-khan/RTCMultiConnection

Installation Guide - https://github.com/muaz-khan/RTCMultiConnection/blob/master/docs/installation-guide.md

Спасибо, Muaz Khan! =)