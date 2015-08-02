# Dokumentacja
***

### Konfiguracja:
1. Użyj `auth.sql` do stworzenia wymaganych tabel.
2. Ustaw *base_uri* i inne opcje w pliku `/app/cfg/config.ini`:

```ini
[app]
domain = "example.com"
base_uri = "/"
static_uri = "http://www.example.com/"
admin = "admin@example.com"
```
<br />
Podaj ustawienia do połączenia do bazy danych:
```ini
[database]
host     = "localhost"
username = "base"
password = "password"
dbname   = "base"
```
<br />
Zmień domyślne klucze. To jest **bardzo ważne** ze względów bezpieczeństwa:
```ini
[auth]
hash_key = "secret_key"

[crypt]
key = "secret_key"

[cookie]
salt = "secret_key"
```
<br />
Przygotuj aplikację do pierwszego użycia:
```bash
cd /path/to/base/private
php index.php --module=shell --handler=prepare --action=chmod
```
***

### Wymagania:
* Ice framework

***
### Przykład użycia sleet:
Dostęp do usługi `auth` w widokach:
```twig
{% if this.auth.loggedIn() %}
    {{ this.auth.getUser().username }}
{% endif %}
```
<br />
Łatwe tłumaczenie z funkcją `_t()`:
```twig
{% set username = this.auth.getUser().username %}
{{ _t('Hello :user', [':user' : username]) }}
{{ _t('Hello %s', [username]) }}
```
<br />
Różne:
```twig
{% if this.auth.loggedIn('admin') %}
    {{ _t('Hello :user', [':user' : this.auth.getUser().username]) }}
    {{ link_to(['admin', _t('Admin panel')]) }}
{% endif %}
```
<br />
Używaj klasy w widokach:
```twig
{% use App\Models\Users %}

{% set user = Users::findOne(1) %}
{{ user.username }}
```
<br />
Debuguj zmienne:
```php
{{ dump('string', 1, 2.5, true, null, ['key': 'value']) }}
```