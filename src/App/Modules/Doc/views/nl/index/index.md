# Documentation
***

### Configuration:
Set *base_uri* and other settings in the `/config/config.ini` file:
```ini
[app]
domain = "example.com"
base_uri = "/"
static_uri = "http://www.example.com/"
admin = "admin@example.com"
```
<br />
Enter the settings to connect to the database:
```ini
[database]
type     = "mongodb"
host     = "localhost"
port     = 27017
user     = "demo"
password = "demo"
name     = "demo_base"
options[authMechanism] = "MONGODB-CR"
```
<br />
Change default hash keys. It is **very important** for safety reasons:
```ini
[auth]
hash_key = "secret_key"

[crypt]
key = "secret_key"

[cookie]
salt = "secret_key"
```
<br />
Use `auth.sql` file to create required tables or run for _mongodb_:
```bash
cd /path/to/base/private
php index.php --module=shell --handler=prepare --action=roles
```

Set the chmods:
```bash
php index.php --module=shell --handler=prepare --action=chmod
```

To recompile views or minify assets manually (eg. for _production_ `env`):
```
php index.php --module=shell --handler=prepare --action=sleet
php index.php --module=shell --handler=prepare --action=assets
```
***

### Requirements:
* Ice framework

***
### Example sleet usage:
Access to `auth` service in the views:
```twig
{% if this.auth.loggedIn() %}
    {{ this.auth.getUser().username }}
{% endif %}
```
<br />
Easy translation with `_t()` function:
```twig
{% set username = this.auth.getUser().username %}
{{ _t('Hello :user', [':user' : username]) }}
{{ _t('Hello %s', [username]) }}
```
<br />
Mixed usage:
```twig
{% if this.auth.loggedIn('admin') %}
    {{ _t('Hello :user', [':user' : this.auth.getUser().username]) }}
    {{ link_to(['admin', _t('Admin panel')]) }}
{% endif %}
```
<br />
Use class in the view:
```twig
{% use App\Models\Users %}

{% set user = Users::findOne(1) %}
{{ user.username }}
```
<br />
Debug variables:
```php
{{ dump('string', 1, 2.5, true, null, ['key': 'value']) }}
```