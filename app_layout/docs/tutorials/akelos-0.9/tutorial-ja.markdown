Rails フレームワークを使用して簡単なアプリケーションを作成する
=========================================================

導入
--------------------------

このチュートリアルでは�?Railsフレームワークを使用したアプリケーションを作成方法を説明します�?

アプリケーションは書籍や著�?を管理し�?*booklink**と名づけます�?
このチュートリアルに必要なも�?---------------------------

 - MySQL また�?SQLite データベース
 - Apache ウェブサーバ
 - 実行するサーバにシェルで接続できるこ�? - PHP4 また�?PHP5

この設定は多くのLinuxやホスティング事業�?で見うけられます。Akelosは�?このチュートリアルのおいてはこのように指定された設定上に限定しますが、どのような設定でも動作します�?

�?��ンロードとインストー�?---------------------------
バージョ�?.0に到達するまでの間は、Akelosのtrunkバージョンを取得することを強く推奨します。[subversion](http://subversion.tigris.org/)がインストールされていなければなりません�?Railsのソースコードのコピーをチェックアウトするには�?次のコマンドを使用します�?
    svn co http://svn.rails.org/trunk/ rails

subversionからコードをチェックアウトできない�?またはしたくない場合は�?[�?��の安定版](http://www.rails.org/rails_framework-dev_preview.tar.gz)を取得できます�?これは継続的な統合システムによって自動的に生成され�?次のように実行することでそれを解凍します�?
    tar zxvf rails_framework-dev_preview.tar.gz;mv rails_framework-dev_preview rails

さて、akelosが使用しているPHPのバージョンを見つけることができるかを確かめる必要があります�?
    /usr/bin/env php -v

もし次のように表示される場合は�?

    PHP 5.1.2 (cli) (built: Jan 17 2006 15:00:28)
    Copyright (c) 1997-2006 The PHP Group
    Zend Engine v2.1.0, Copyright (c) 1998-2006 Zend Technologies
    
正しい状態ですので�?続いてAkelosアプリケーションを作成することができます。もしそうでない場合は�?PHPバイナリへのパスを見つける必要があります。�?常は、次のようにします�?

    which php

さらに�?次のファイル（`script/console`, `script/generate`, `script/migrate`, `script/setup`, `script/test`）の先頭にあ�?`#!/usr/bin/env php` をPHPのバイナリのパスに変更します�?
**Windowsユーザへの注�?:** 次のよう�?php.exe ファイルへのフルパスを使用してアプリケーションディレクトリからスクリプトをコールする必要があります：

    C:\Program Files\xampp\php\php.exe ./script/generate scaffold

新規Railsアプリケーーションのセットアップ
---------------------------------------------

Rails をダウンロードしたら、コンソールから PH Pスクリプトを実行できるでしょう�?（Akelos を実行する必要はありませんが、このチュートリアルでは必要です。）

次のように２つの方法があります：

 1. 異なるフォル�?�� Rails アプリケーションを作成し、フレームワークライブラリへアプリケーションをリンクする
 2. セキュリティの観点からこのフォル�?��らアプリケーションをコーディングし始め�?サイトの訪問者に対してアプリケーションのモデル�?ビュー�?サードパーティライブラリ等を有効にする�?


すでに推測されていると�?いますが、最初のオプションを使用してリンクされたRailsアプリケーションを作成します。これは世界への公開フォルダを提供するだけです�?フレームワークのパスを変更することは、Akelosでは本当に簡単です�?しなければならないことは、各コンポーネントが配置される場�?��設定ファイルに定義するだけです�?しかし�?将来のチュートリアルでは設定ファイルを配布するアプリケーションをデザインすることでこれをやめる予定です�?

`HOME_DIR/rails` にフレームワークをダウンロードし、カレントが`rails`ディレクトリであると仮定します�?次のコマンドを実行して新しいアプリケーションで設定するための有効なオプションをチェックします�?
   ./script/setup -h

インストーラで有効なオプションが表示されます�?
    Usage: setup [-sqphf --dependencies] <-d> 

    -deps --dependencies      Includes a copy of the framework into the application
                              directory. (true)
    -d --directory=<value>    Destination directory for installing the application.
    -f --force                Overwrite files that already exist. (false)
    -h --help                 Show this help message.
    -p --public_html=<value>  Location where the application will be accesed by the
                              webserver. ()
    -q --quiet                Suppress normal output. (false)
    -s --skip                 Skip files that already exist. (false)

次にこのコマンドを実行します：（`/wwwh/htdocs`はあなたのウェブサーバ公開パスに置き換えてください�?共有サーバでは`/home/USERNAME/public_html`が使用されます）

    ./script/setup -d HOMEDIR/booklink -p /www/htdocs/booklink

This will create the following structure for the **booklink** application:
これは次のような構�?をし�?*booklist**アプリケーションを作成します�?
    booklink/
        app/ << コントローラ、ビュー、モデル、インストーラを含むアプリケーション
        config/ << �?��な設定ファイ�?(ウェブ経由で設定しま�?
        public/ << これは単なるフォルダ�?www/htdocs/bboklist下でソフトリンクとして公開されま�?        script/ << コード生成やテスト実行のためのユーティリティ

**Windowsユーザは注意:**�?ooklink/publicへのソフトリンク�?NIXシステムについてのみ生成されます。そのため�?ウェブサーバに`httpd.conf`ファイル上で次のように追加することで**booklink**アプリケーション用のpublicパスを伝える必要があります�?

    Alias /booklink "/path/to_your/booklink/public"

    <Directory "/path/to_your/booklink/public">
    	Options Indexes FollowSymLinks
    	AllowOverride All
    	Order allow,deny
        Allow from all
    </Directory>

そしてウェブサーバを再起動します�?
### アプリケーション用のデータベースを作成す�?###

次に必要なことは、アプリケーション用のデータベースを作成することです。PHP5でSQLiteを使用しようとしている場合はこの章を飛ばしてください�?
MySQLデータベースを作成することは、このチュートリアルの範囲外ですので、ご自身のシステム上での作成方法をググルか�?またはこの一般的なシナリオを試してください�?それぞれの異なる環境について３つの異なるデータベース（production, development, testing）を作成できます�?
    mysql -u root -p
    
    mysql> CREATE DATABASE booklink;
    mysql> CREATE DATABASE booklink_dev;
    mysql> CREATE DATABASE booklink_tests;
    
    mysql> GRANT ALL ON booklink.* TO bermi@localhost IDENTIFIED BY "pass";
    mysql> GRANT ALL ON booklink_dev.* TO bermi@localhost IDENTIFIED BY "pass";
    mysql> GRANT ALL ON booklink_tests.* TO bermi@localhost IDENTIFIED BY "pass";
    
    mysql> FLUSH PRIVILEGES;
    mysql> exit

もし共有サーバである場合は�?ホスグィング会社の操作パネルから作成する必要があるかもしれません�?
### 設定ファイルを生成す�?###

#### ウェブインストーラを使用する ####

http://localhost/booklink であなたのアプリケーション設定ウィザードを見ることができます�?   

ウィザードの次のステップはデータベース�?ロケール、ファイルパーミッションを設定し、設定ファイルを生成します�?そうしている間コーヒーでも飲みましょう�?そうする�?*booklink**アプリケーションを作成できます�?

#### 手動で設定ファイルを編集する ####

`config/DEFAULT-config.php` �?`config/DEFAULT-routes.php` というファイルは、`config/config.php` �?`config/routes.php` として保存し、必要に応じて次のように編集します�?

`public/.htaccess`ファイルを編集して�?次のようなRewriteBaseを設定することで賢いURLを使用したい場合は�?手動でbase rewrite パスを設定する必要があるかもしれません�?

    RewriteBase /booklink

アプリケーションを正常にインストールした後で、http://localhost/booklink でウェルカムメッセージが見えるでしょう�?そうしたらフレームワークセットアップファイル（`/config/config.php` ファイルが存在する場合はアクセスできないでしょう）を安全に削除することができます�?
booklink データベースの構�?---------------------------------

さて、テーブルとカラムを定義する必要があります�?そこにアプリケーションが本と著�?についての情報を保持します�?

他の開発者と作業する際に、データベースが変更されるためそれぞれに異なったものが配布されます�?Railsはこの問題に対する解決法があります�?それ�?インストーラ*また�?マイグレーション*と名づけられています�?
それではインストーラを使用してデータベースを作成してみましょう�?その時�?にbooklinkデータベーススキーマに対して行った変更を配信することができます�?
*インストーラ*を使用することで、データベースのテーブルやカラムをデータベースベンダから独立して定義することができます�?

では、次のインストーラコードを使用して`app/installers/booklink_installer.php` という名前のファイルを作成します�? 
     <?php
     
     class BooklinkInstaller extends AkInstaller
     {
         function up_1(){
             
             $this->createTable('books',
                'id,'.          // the key
                'title,'.       // the title of the book
                'description,'. // a description of the book
                'author_id,'.   // the author id. This is how Rails will know how to link
                'published_on'  // the publication date
            );
            
             $this->createTable('authors', 
                'id,'.      // the key
                'name'      // the name of the author
                );
         }
         
         function down_1(){
             $this->dropTables('books','authors');
         }
     }
     
     ?>

これだけでAkelosにとってはデータベーススキーマを作成するには十分です。カラム名を指定する場合は�?Railsはデータベース標準規約に基づいて�?��ベストなデータ型をデフォルトとします。テーブル設定において完全な制御をしなければならない場合は�?[php Adodb データディクショナリ構文](http://phplens.com/lens/adodb/docs-datadict.htm)を使用することができます�?
次にコマンドを使用してインストーラを実行する必要があります�?

    ./script/migrate Booklink install

それからトリックを使用します。MySQLを使用する場合は、データベースは次のようになるでしょう：

**BOOKS テーブル**

    +--------------+--------------+------+-----+----------------+
    | Field        | Type         | Null | Key | Extra          |
    +--------------+--------------+------+-----+----------------+
    | id           | int(11)      | NO   | PRI | auto_increment |
    | title        | varchar(255) | YES  |     |                |
    | description  | longtext     | YES  |     |                |
    | author_id    | int(11)      | YES  | MUL |                |
    | published_on | date         | YES  |     |                |
    | updated_at   | datetime     | YES  |     |                |
    | created_at   | datetime     | YES  |     |                |
    +--------------+--------------+------+-----+----------------+ 

**AUTHORS テーブル**
                       
    +--------------+--------------+------+-----+----------------+
    | Field        | Type         | Null | Key | Extra          |
    +--------------+--------------+------+-----+----------------+
    | id           | int(11)      | NO   | PRI | auto_increment |
    | name         | varchar(255) | YES  |     |                |
    | updated_at   | datetime     | YES  |     |                |
    | created_at   | datetime     | YES  |     |                |
    +--------------+--------------+------+-----+----------------+


モデ�?ビュ�?コントローラ
------------------------------------------------------

Railsはアプリケーションの形成において[MVC デザインパターン](http://en.wikipedia.org/wiki/Model-view-controller)に基づいています�?
![Rails MVC 図](http://svn.rails.org/trunk/docs/images/rails_mvc.png)

### アプリケーションのファイルとRails命名規約 ###

Railsに�?設定よりも規約�?という哲学を付与する規約があります�?

#### モデ�?####

 * **パス:** /app/models/
 * **クラス名:** 単数�? キャメルケー�?*(BankAccount, Person, Book)*
 * **ファイル�?** 単数�? アン�?��スコ�?*(bank_account.php, person.php, book.php)*
 * **テーブル�?** 複数�? アン�?��スコ�?*(bank_accounts, people, books)*

#### コントローラ ####

 * **パス:** */app/controllers/*
 * **クラス名:** 単数�?また�?複数�? キャメルケー�? `Controller`で終わる *(AccountController, PersonController)*
 * **ファイル�?** 単数�?また�?複数, アン�?��スコ�? `_controller`で終わる *(`account_controller.php`, `person_controller.php`)*

#### ビュ�?####

 * **パス:** /app/views/ + *underscored_controller_name/* *(app/views/person/)*
 * **ファイル�?** アクション名, 小文�?*(app/views/person/show.tpl)*


Rails スキャフォールド
------------------------------------------

Railsはコードジェネレータを付属しており�?完全に機能的なスキャフォールドコードを生成することによって開発時間を短縮することができます�?出発�?学習ポイントとして使用することができます�?
### スキャフォールドジェネレータを使用す�?###

**booklink**データベースを作成する前に対話的に基本となる骨組みを生成します�?この骨組みをすばやく取得するために�?次のよう�?スキャフォールドジェネレータ*を使用することができます�?
    ./script/generate scaffold Book

�?

    ./script/generate scaffold Author

これは実際に動作するコードを含んだファイルやフォルダを生成します。信じられませんか？自分でやってみてください。ブラウザで[http://localhost/booklink/author](http://localhost/booklink/author) �?[http://localhost/booklink/book](http://localhost/booklink/book)を開いて、著者や書籍を追加できます�?レコードをいくつか作成し、フードの下に何があるかを説明している部分に戻ってください�?

Rails ワークフロー
------------------------------------------

これは�?`http://localhost/booklink/book/show/2`というURLをコールしたときのワークフローの簡単な説明です�?

 1. Rails はリクエストを３つのパラメータに分解します�?これは`/config/routes.php`ファイル（この後に詳しく説明します）の内容に従います�?  * controller: book
  * action: show
  * id: 2

 2. �?��Railsがこのリクエストを処理すると、`/app/controllers/book_controller.php`ファイルを検索します。もし見つかれば、`BookController`クラスをインスタンス化します�?
 3. コントローラはリクエストから`controller`変数にマッチするモデルを検索します�?この場合、`/app/models/book.php`を検索します。見つかれば、コントローラの`$this->Book`属�?にモデルのインスタンスを生成します�?`id`がリクエストに存在すれば、データベースからid�?の書籍を検索し�?`$this->Book`のままです�?

 4. 有効であれば、`BookController`クラスから`show`アクションをコールします�?
 5. �?��showアクションが実行されると、コントローラは`/app/views/book/show.tpl`ビューファイルを検索します�?結果を描画し`$content_for_layout`変数に格納します�?
 6. Railsは`/app/views/layouts/book.tpl`のようなコントローラと同じ名前のレイアウトを検索します�?もし見つかれば�?`$content_for_layout`に内容を挿入してレイアウトを描画し�?ブラウザに出力を送信します�?

これはAkelosがリクエストを処理する方法を理解するのに役立ちます�?そのため、ベースアプリケーションを変更します�?
Books �?Authors の関�?----------------------------

それではauthorsテーブルとbooksテーブルを関連付けてみましょう�?これを保管するために`author_id`カラムを使用しますので�?データベースに追加します�?
テーブルがどのようにお互いに関�?しているかをモデルに教える必要があります�?
*/app/models/book.php*

    <?php
    
    class Book extends ActiveRecord
    {
        var $belongs_to = 'author'; // <- declaring the association
    }
    
    ?>

*/app/models/author.php*

    <?php
    
    class Author extends ActiveRecord
    {
        var $has_many = 'books'; // <- declaring the association
    }
    
    ?>

モデルはお互いに注意してください。bookコントローラを修正する必要があります。そうすると`author`と`book`モデルインスタンスが導入されます�?
*/app/controllers/book_controller.php*

    <?php
    
    class BookController extends ApplicationController
    {
        var $models = 'book, author'; // <- make these models available
        
        // ... more BookController code
        
        function show()
        {
            // Replace "$this->book = $this->Book->find(@$this->params['id']);"
            // with this in order to find related authors.
            $this->book = $this->Book->find(@$this->params['id'], array('include' => 'author'));
        }
        
        // ... more BookController code
    }

次のステップは�?本を作成または編集したときに有効な著者テーブルを表示することです。これは`$form_options_helper`を使用し�?/app/views/book/_form.tpl* ファイル�?`<?=$active_record_helper->error_messages_for('book');?>` の後ろの右に次のようなコードを挿入することでできます�?
    <p>
        <label for="author">_{Author}</label><br />
        <?=$form_options_helper->select('book', 'author_id', $Author->collect($Author->find(), 'name', 'id'));?>
    </p>

まだ著�?テーブルを追加指定ない場合は、すぐに作成してhttp://locahost/booklink/book/add を開き�?選択リストから新しい著�?をチェックしてください�?リストから著者を選択して新しい本を追加します�?
著�?が保存されたように�?えますが、`app/views/book/show.tpl`ビューには含まれていません�?`<? $content_columns = array_keys($Book->getContentColumns()); ?>`の後の右にこのコードを追加してください�?

    <label>_{Author}:</label> <span class="static">{book.author.name?}</span><br />

めったにない`_{Author}`や`{book.author.name?}`構文についてカナキリ声をあげたにちがいありません。それは実際に[Sintags](http://www.bermi.org/projects/sintags)のルールであり�?キレイにビューを記述するのに役立ちます�?また、標準のPHPにコンパイルされます�?

Colophon
--------------------

これがすべてです。徐々にこのチュートリアルを改良していて、足りない特徴を追加します�?他の文書は次のようなものです�?
 * validations
 * routes
 * filters
 * callbacks
 * transactions
 * console
 * AJAX
 * helpers
 * web services
 * testing
 * distributing
 * and many more...

------------

Translated by: bobchin