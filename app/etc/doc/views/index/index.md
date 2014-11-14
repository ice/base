# Documentation
***

### Configuration:
1. Use `ice/framework/auth-mysql.sql` file to create required tables
2. Set *base_uri* and other settings in the `/app/etc/config.ini` file:

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
host     = "localhost"
username = "base"
password = "password"
dbname   = "base"
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
Prepare the application for the first run:
```bash
# go to /path/to/base/private
php index.php shell prepare chmod
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
Easy translation with `__()` function:
```twig
{% set username = this.auth.getUser().username %}
{{ __('Hello :user', [':user' : username]) }}
{{ __(['Hello %s', username]) }}
```
<br />
Mixed usage:
```twig
{% if this.auth.loggedIn('admin') %}
    {{ __('Hello :user', [':user' : this.auth.getUser().username]) }}
    {{ link_to(['admin', __('Admin panel')]) }}
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