# ドキュメント
***

### 設定:
`/config/config.ini` ファイルの *base_uri* とその他の設定をおこなってください:
```ini
[app]
domain = "example.com"
base_uri = "/"
static_uri = "http://www.example.com/"
admin = "admin@example.com"
```
<br />
データベース接続の設定を入力してください:
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
初期設定されているハッシュキーを変更してください。 セキュリティ上の理由から **とても重要** です:
```ini
[auth]
hash_key = "secret_key"

[crypt]
key = "secret_key"

[cookie]
salt = "secret_key"
```
<br />
必要なテーブルを生成するために `auth.sql` を使用するか、 _mongodb_ で実行してください:
```bash
cd /path/to/base/private
php index.php --module=shell --handler=prepare --action=roles
```

chmod を設定してください :
```bash
php index.php --module=shell --handler=prepare --action=chmod
```

ビューの再コンパイル または assets の手動による圧縮 (例. _production_ `env` のために):
```
php index.php --module=shell --handler=prepare --action=sleet
php index.php --module=shell --handler=prepare --action=assets
```
***

### サーバ要件:
* Ice framework

***
### sleet の使用例:
ビュー内で `auth` サービスを使用:
```twig
{% if this.auth.loggedIn() %}
    {{ this.auth.getUser().username }}
{% endif %}
```
<br />
`_t()` メソッドを用いたシンプルな翻訳:
```twig
{% set username = this.auth.getUser().username %}
{{ _t('Hello :user', [':user' : username]) }}
{{ _t('Hello %s', [username]) }}
```
<br />
合わせた使用方法:
```twig
{% if this.auth.loggedIn('admin') %}
    {{ _t('Hello :user', [':user' : this.auth.getUser().username]) }}
    {{ link_to(['admin', _t('Admin panel')]) }}
{% endif %}
```
<br />
ビュー内でクラスを使用:
```twig
{% use App\Models\Users %}

{% set user = Users::findOne(1) %}
{{ user.username }}
```
<br />
変数のデバッグ:
```php
{{ dump('string', 1, 2.5, true, null, ['key': 'value']) }}
```