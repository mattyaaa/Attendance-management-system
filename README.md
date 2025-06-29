# Attendance-management-system
## 環境構築
### Dockerビルド
1. リポジトリをクローンします
```
git clone git@github.com:mattyaaa/Attendance-management-system.git
```

2. DockerDesktopアプリを立ち上げます。
```
docker-compose up -d --build
```

> *MacのM1・M2チップのPCの場合、`no matching manifest for linux/arm64/v8 in the manifest list entries`のメッセージが表示されビルドができないことがあります。
エラーが発生する場合は、docker-compose.ymlファイルの「mysql」内に「platform」の項目を追加で記載してください*
``` bash
mysql:
    platform: linux/x86_64(この文追加)
    image: mysql:8.0.26
    environment:
```

### Laravel環境構築
1. PHPコンテナに入ります。
```
docker-compose exec php bash
```

2. Composerをインストールします。
```
composer install
```

3. 「.env.example」ファイルを 「.env」ファイルに命名を変更します。
```
cp .env.example .env
```

4. .envに以下の環境変数を追加
``` text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```
5. アプリケーションキーを生成します。
``` bash
php artisan key:generate
```

6. データベースのマイグレーションを実行します。
``` bash
php artisan migrate
```

7. データベースのシーディングを実行します。
``` bash
php artisan db:seed
```

### テスト用データベースの作成

```bash
# MySQLコンテナに入る
docker-compose exec mysql bash

# MySQLにrootユーザーでログイン（パスワードは root）
mysql -u root -p

# テスト用DB作成
create database test_database;
```

### テスト用環境ファイルの準備

```bash
# アプリコンテナに入る
docker-compose exec php bash

# .env.testing ファイルを作成
cp .env .env.testing

# .env.testingに以下の環境変数を追加

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=test_database
DB_USERNAME=root
DB_PASSWORD=root
```

# APP_KEYをテスト用に生成
```bash
php artisan key:generate --env=testing
```
- `.env.testing` の `DB_DATABASE` を `test_database` に設定してください。
- テストで必要な Stripe のAPIキー等、他の環境変数も `.env.testing` に必ず設定してください。

### テスト用DBへのマイグレーション

```bash
php artisan migrate:fresh --env=testing
```

### テスト用DBへのシーディング

```bash
php artisan db:seed --class=RoleSeeder --env=testing
```

### キャッシュのクリア

```bash
php artisan config:clear
```

### テストの実行

```bash
php artisan test
```

---
**注意:**
- マイグレーションやテストは必ず `--env=testing` オプションでテスト用データベースに対して行ってください。
- `.env.testing` に本番用の情報が入らないようご注意ください。


## 管理ユーザーのログイン情報
- ユーザー名:Admin User
- メールアドレス:admin@example.com
- パスワード：admin1234

## 一般ユーザーのログイン情報
### ユーザー1
- ユーザー名:田中太郎
- メールアドレス:tanaka@example.com
- パスワード：user1234

### ユーザー2
- ユーザー名:佐藤花子
- メールアドレス:sato@example.com
- パスワード：user5678

## 使用技術(実行環境)
- PHP:7.4.9
- Laravel:8.83.29
- MySQL:10.3.39

## ER図
以下にER図を示します。
![ER図](ER.png)


## URL
- ユーザーログイン画面：http://localhost/login
- 管理者ログイン画面：http://localhost/admin/login
- phpMyAdmin:：http://localhost:8080/