# IPInfoFilter

IPInfoFilter は、AbuseFilterと連携し、IPアドレスから得られる情報を利用して不正利用のフィルタリングを強化します。

[GeoLite2 Free Geolocation Data](https://dev.maxmind.com/geoip/geolite2-free-geolocation-data)、[proxycheck\.io](https://proxycheck.io/)　のデータベースを利用し、IPアドレスから国、AS番号（ASN）、などの情報を取得できます。

## インストール
1. ダウンロードし、ファイルを`extensions/`フォルダ内の`IPInfoFilter`ディレクトリに配置します。
2. `LocalSettings.php`に以下のコードを追加します:
   ```php
   wfLoadExtension( 'IPInfoFilter' );
   ```
3. `composer install`を実行して、依存関係をインストールします。
4. 必要に応じて設定を行い、`LocalSettings.php`に設定を追加します。詳細は下記の設定セクションを参照してください。
5. ウィキの「Special:Version」に移動して、拡張機能が正しくインストールされたことを確認します。
6. [GeoLite2 Free Geolocation Data](https://dev.maxmind.com/geoip/geolite2-free-geolocation-data)　より GeoLite2 のデータベースファイルをダウンロードし、サーバーに保存します。
7. `LocalSettings.php` で保存したファイルのパスを設定します：
   ```php
   $wgIPInfoFilterGeoLite2CountryPath = "/path/to/GeoLite2-Country.mmdb";
   $wgIPInfoFilterGeoLite2ASNPath = "/path/to/GeoLite2-ASN.mmdb";
   ```


## 設定

### 設定項目

| 設定キー                                  | 説明                               | 例                                           |
|---------------------------------------|----------------------------------|---------------------------------------------|
| `$wgIPInfoFilterGeoLite2AsnPath `     | GeoLite2-ASN.mmdb データベースへのパス     | `'/var/www/GeoLite2/GeoLite2-ASN.mmdb'`     |
| `$wgIPInfoFilterGeoLite2CountryPath ` | GeoLite2-Country.mmdb データベースへのパス | `'/var/www/GeoLite2/GeoLite2-Country.mmdb'` |
| `$wgIPInfoFilterProxyCheckIoKey`      | proxycheck\.io のAPIキー            | `'your-api-key-here'`                       |


GeoLite2　と　ProxyCheck.io　の両方の設定が行われている場合、GeoLite2 が優先されます。


## データソース

この拡張機能は、GeoLite2 と ProxyCheck.io の2つのデータソースをサポートしています。特に設定を行わない場合、デフォルトで ProxyCheck.io が使用されます。

### GeoLite2

GeoLite2 は MaxMind 社が提供する無料のジオロケーションデータベースです。

サーバー内でローカルに動作するため、外部APIリクエストやリミット制限の問題がありません。大規模なウェブサイトや頻繁にIP情報をチェックする必要がある場合、GeoLite2 の使用を強く推奨します。
#### 設定手順
1. [GeoLite2 Free Geolocation Data](https://dev.maxmind.com/geoip/geolite2-free-geolocation-data) から GeoLite2 のデータベースファイルをダウンロードします。
2. ダウンロードした `.mmdb` ファイルをサーバーに保存します。
3. `LocalSettings.php` で以下のように設定します：

```php
$wgIPInfoFilterGeoLite2CountryPath = "/path/to/GeoLite2-Country.mmdb";
$wgIPInfoFilterGeoLite2ASNPath = "/path/to/GeoLite2-ASN.mmdb";
```

#### 提供する情報
- 国コード
- AS 番号（ASN）

### ProxyCheck.io

ProxyCheck.io はプロキシ検出とリスク評価のための API サービスです。

ProxyCheck.io は外部 API リクエストが必要であり、1日あたりのクエリ制限があります。小規模なウェブサイトや、IP情報のチェックが頻繁に行われない場合に最適です。

#### 設定手順
1. [ProxyCheck.io](https://proxycheck.io/) でアカウントを作成し、API キーを取得します。
2. 取得した API キーを `LocalSettings.php` で以下のように設定します：

```php
$wgIPInfoFilterProxyCheckIoKey = "your-api-key-here";
```

#### 提供する情報
- 国コード
- AS 番号（ASN）
- リスクスコア
- プロキシ判定

## AbuseFilter

### 利用可能な変数

| 変数名 | 説明 | 例 | サービス |
| ------ | ---- | --- | ------- |
| `ipinfo_country` | IP アドレスに基づいた国の ISO 3166-1 コード | `JP` | GeoLite2, ProxyCheck.io |
| `ipinfo_asn` | IP アドレスの AS 番号（ASN） | `234` | GeoLite2, ProxyCheck.io |
| `ipinfo_risk` | IP アドレスのリスクスコア（0から100） | `60` | ProxyCheck.io |
| `ipinfo_proxy` | IP アドレスがプロキシかどうか（true/false） | `true` | ProxyCheck.io |

データソースによって、利用できる変数が制限されます。

### 使用例

1. 特定の国からのすべてのリクエストをブロックする：
    ```
    (ipinfo_country == 'US')
    ```
2. ASN が特定の値を持つ IP をブロックする：
    ```
    (ipinfo_asn == 234)
    ```
3. リスクスコアが一定値以上の IP をブロックする：
    ```
    (ipinfo_risk > 50)
    ```
4. プロキシと判定された IP をブロックする：
    ```
    (ipinfo_proxy == true)
    ```


