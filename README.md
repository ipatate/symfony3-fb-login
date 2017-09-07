### Exemple of login Facebook in Symfony 3

Authentication use AbstractGuardAuthenticator for call FB after Redirect.

#### init

    $ composer install

    $ bin/console doctrine:create:database

    $ bin/console doctrine:schema:udpate --force

    $ bin/console server:start

open browser to http://localhost:8000

#### Routes

/ index is public
/fb-access is only accessible of users with role ROLE_USER


#### FBAuthenticator (src/AppBundle/Utils/Auth)

extends AbstractGuardAuthenticator for connect user.
if user not exist, it is created.


#### FBSDK (src/AppBundle/Utils/Auth)

simple class for use Facebook SDK and implement methods in one point
