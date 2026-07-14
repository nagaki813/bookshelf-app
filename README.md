# BookShelf

## 概要

BookShelfは、書籍情報の登録・閲覧・編集・削除、レビュー投稿、お気に入り登録、レビューへのいいね、ジャンル管理、評価ランキング表示を行える書籍レビューアプリです。

Laravelを使用し、認証機能、書籍管理機能、レビュー機能、ランキング機能、書籍APIを実装しています。

## 主な機能

### 認証機能

- ユーザー登録
- ログイン
- ログアウト

### 書籍機能

- 書籍一覧表示
- 書籍詳細表示
- 書籍登録
- 書籍編集
- 書籍削除

### レビュー機能

- レビュー投稿
- レビュー編集
- レビュー削除
- レビューへのいいね
- レビューへのいいね解除

### お気に入り機能

- お気に入り登録
- お気に入り解除
- お気に入り一覧表示

### ジャンル機能

- ジャンル一覧表示
- ジャンル登録
- ジャンル詳細表示
- ジャンル編集
- ジャンル削除

### ランキング機能

- 評価ランキング表示

### API機能

- 書籍一覧取得
- 書籍詳細取得
- 書籍登録
- 書籍更新
- 書籍削除

## 使用技術

- PHP 8.5.7
- Laravel 10.50.2
- MySQL
- Docker
- Laravel Sail
- Laravel Fortify
- Laravel Sanctum
- Tailwind CSS
- Alpine.js
- Vite
- Laravel Pint
- PHPUnit

## 環境構築

### 1. リポジトリをクローン

```bash
git clone git@github.com:nagaki813/bookshelf-app.git
cd bookshelf-app
```

### 2. 環境変数ファイルを作成

```bash
cp .env.example .env
```

### 3. Composerパッケージをインストール

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

### 4. Sailを起動

```bash
./vendor/bin/sail up -d
```

### 5. Sailエイリアスを設定

Sailコマンドを簡単に実行できるように、エイリアスを設定します。

```bash
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
```

毎回設定しなくて済むようにする場合は、`~/.bashrc` に追記します。

```bash
echo "alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'" >> ~/.bashrc
source ~/.bashrc
```

設定後は、以下のように `sail` コマンドを使用できます。

```bash
sail up -d
sail artisan migrate
sail npm install
```

### 6. アプリケーションキーを生成

```bash
sail artisan key:generate
```

### 7. マイグレーション・シーディングを実行

```bash
sail artisan migrate:fresh --seed
```

### 8. フロントエンド依存関係をインストール

```bash
sail npm install
```

### 9. フロントエンドをビルド

```bash
sail npm run build
```

## 環境変数

`.env` のデータベース設定は以下の通りです。

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

## 開発環境URL

| 内容 | URL |
| --- | --- |
| アプリケーション | http://localhost |
| 書籍一覧 | http://localhost/books |
| ランキング | http://localhost/ranking |
| phpMyAdmin | http://localhost:8080 |

## phpMyAdmin

phpMyAdminには以下の情報で接続できます。

| 項目 | 値 |
| --- | --- |
| サーバ | mysql |
| ユーザー名 | sail |
| パスワード | password |

## テストユーザー

Seederで以下のユーザーを作成しています。

| 名前 | メールアドレス | パスワード |
| --- | --- | --- |
| 山田太郎 | yamada@example.com | password |
| 鈴木花子 | suzuki@example.com | password |
| 田中一郎 | tanaka@example.com | password |
| 佐藤美咲 | sato@example.com | password |
| 高橋健太 | takahashi@example.com | password |

## APIエンドポイント

書籍APIは以下のエンドポイントを用意しています。

| メソッド | URL | 内容 |
| --- | --- | --- |
| GET | /api/v1/books | 書籍一覧取得 |
| POST | /api/v1/books | 書籍登録 |
| GET | /api/v1/books/{book} | 書籍詳細取得 |
| PUT/PATCH | /api/v1/books/{book} | 書籍更新 |
| DELETE | /api/v1/books/{book} | 書籍削除 |

### APIリクエスト例

#### 書籍一覧取得

```bash
curl -i -H "Accept: application/json" http://localhost/api/v1/books
```

#### 書籍詳細取得

```bash
curl -i -H "Accept: application/json" http://localhost/api/v1/books/1
```

#### 書籍登録

```bash
curl -i -X POST http://localhost/api/v1/books \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "title": "API登録テスト書籍",
    "author": "APIテスト著者",
    "isbn": "9784000004000",
    "published_date": "2026-07-08",
    "description": "API登録テスト用の説明です。",
    "image_url": "https://placehold.co/200x300/e2e8f0/475569?text=api",
    "genres": [1]
  }'
```

#### 書籍更新

```bash
curl -i -X PUT http://localhost/api/v1/books/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "title": "API更新後タイトル",
    "author": "API更新後著者",
    "isbn": "9784101010014",
    "published_date": "2026-07-08",
    "description": "API更新後の説明です。",
    "image_url": "https://placehold.co/200x300/e2e8f0/475569?text=api-update",
    "genres": [2]
  }'
```

#### 書籍削除

```bash
curl -i -X DELETE \
  -H "Accept: application/json" \
  http://localhost/api/v1/books/1
```

## テスト

Featureテストを実装しています。

```bash
sail artisan test
```

確認済みのテスト結果は以下の通りです。

```text
Tests: 53 passed
Assertions: 152 assertions
```

### テスト内容

- 認証機能
- 書籍CRUD
- 書籍バリデーション
- 書籍の権限制御
- レビューCRUD
- レビューの権限制御
- お気に入り登録・解除
- レビューいいね・解除
- ジャンル管理
- ランキング表示
- 書籍API CRUD
- APIバリデーション

## コード整形

Laravel Pintを使用しています。

```bash
sail pint --test
```

自動整形する場合は以下を実行します。

```bash
sail pint
```

## ER図

![ER図](./er.png)

## データベース構成

主なテーブルは以下の通りです。

| テーブル名 | 内容 |
| --- | --- |
| users | ユーザー情報 |
| books | 書籍情報 |
| genres | ジャンル情報 |
| book_genre | 書籍とジャンルの中間テーブル |
| reviews | レビュー情報 |
| favorites | お気に入り情報 |
| review_likes | レビューいいね情報 |

## 作成者

長岐 宗平
